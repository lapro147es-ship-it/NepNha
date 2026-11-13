<?php
declare(strict_types=1);

final class Workflow
{
    public function __construct(private array $actor) {}
    public function handle(string $action, array $d, array $files = []): int
    {
        $stored = [];
        try {
            return Database::transaction(function () use ($action, $d, $files, &$stored) {
                $fresh = userById((int) $this->actor['id']);
                if (!$fresh) {
                    throw new DomainException('Tài khoản không còn hoạt động.');
                }
                $this->actor = $fresh;
                $id = match ($action) {
                    'apartment_save' => $this->apartment($d),
                    'request_create' => $this->createRequest($d),
                    'request_receive',
                    'request_reject',
                    'request_cancel',
                    'request_close'
                        => $this->requestState($action, $d),
                    'survey_save',
                    'survey_confirm',
                    'survey_done',
                    'survey_cancel'
                        => $this->survey($action, $d),
                    'quote_save' => $this->quote($d, $files, $stored),
                    'quote_respond' => $this->respondQuote($d),
                    'task_assign' => $this->assign($d),
                    'task_start',
                    'task_update',
                    'task_report',
                    'task_accept',
                    'task_rework',
                    'task_cancel'
                        => $this->task($action, $d, $files, $stored),
                    'design_submit' => $this->design($d, $files, $stored),
                    'design_review' => $this->reviewDesign($d),
                    'payment_add', 'payment_void' => $this->payment($action, $d),
                    'profile_save' => $this->profile($d),
                    'service_save' => $this->service($d),
                    'user_save' => $this->user($d),
                    'request_upload' => $this->requestUpload($d, $files, $stored),
                    default => throw new DomainException('Thao tác không được hỗ trợ.')
                };
                return $id;
            });
        } catch (Throwable $e) {
            foreach ($stored as $p) {
                if (is_file($p)) {
                    unlink($p);
                }
            }
            throw $e;
        }
    }

    private function log(
        string $action,
        int $id,
        ?int $request = null,
        string $reason = '',
        ?string $from = null,
        ?string $to = null,
        string $entity = 'request'
    ): void {
        $logId = Database::insert('audit_logs', [
            'actor_id' => $this->actor['id'],
            'request_id' => $request,
            'entity_type' => $entity,
            'entity_id' => $id,
            'action' => $action,
            'old_status' => $from,
            'new_status' => $to,
            'reason' => $reason,
            'created_at' => now()
        ]);
        notifyWorkflow($logId, $action, $request, (int) $this->actor['id']);
    }

    private function r(array $d): array
    {
        return requestAccess($this->actor, positiveId($d, 'request_id'));
    }

    private function consultant(array $r): void
    {
        if (!canConsult($this->actor, $r)) {
            throw new DomainException('Chỉ tư vấn viên phụ trách được xử lý hồ sơ.');
        }
    }

    private function owner(array $r): void
    {
        if (!isOwner($this->actor, $r)) {
            throw new DomainException('Chỉ khách hàng sở hữu hồ sơ được xác nhận.');
        }
    }

    private function state(array $r, array $allowed): void
    {
        if (!in_array($r['status'], $allowed, true)) {
            throw new DomainException('Trạng thái hiện tại không cho phép thao tác.');
        }
    }

    private function setState(array $r, string $new, string $reason = ''): void
    {
        Database::run('UPDATE service_requests SET status=?,updated_at=? WHERE id=?', [
            $new,
            now(),
            $r['id']
        ]);
        $this->log('STATUS', (int) $r['id'], (int) $r['id'], $reason, $r['status'], $new);
    }

    private function apartment(array $d): int
    {
        requireRole($this->actor, 'CUSTOMER');
        $id = (int) ($d['id'] ?? 0);
        $name = txt($d, 'name', 150);
        $address = txt($d, 'address', 500);
        $area = filter_var($d['area_m2'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($area === false || $area <= 0 || $area > 100000) {
            throw new DomainException('Diện tích phải lớn hơn 0 và không quá 100.000 m².');
        }
        if ($id) {
            $a = Database::one('SELECT * FROM apartments WHERE id=? AND customer_id=?', [
                $id,
                $this->actor['id']
            ]);
            if (!$a) {
                throw new DomainException('Không tìm thấy căn hộ của bạn.');
            }
            if (
                Database::one(
                    "SELECT id FROM service_requests WHERE apartment_id=? AND status NOT IN ('CLOSED','CANCELLED','REJECTED')",
                    [$id]
                ) &&
                $address !== $a['address']
            ) {
                throw new DomainException(
                    'Căn hộ có hồ sơ đang xử lý; giữ địa chỉ hiện tại để bảo toàn lịch hẹn.'
                );
            }
            Database::run('UPDATE apartments SET name=?,address=?,area_m2=?,notes=? WHERE id=?', [
                $name,
                $address,
                $area,
                txt($d, 'notes', 2000, false),
                $id
            ]);
        } else {
            $id = Database::insert('apartments', [
                'customer_id' => $this->actor['id'],
                'name' => $name,
                'address' => $address,
                'area_m2' => $area,
                'notes' => txt($d, 'notes', 2000, false),
                'created_at' => now()
            ]);
        }
        $this->log('APARTMENT_SAVE', $id, null, '', null, null, 'apartment');
        return $id;
    }

    private function createRequest(array $d): int
    {
        requireRole($this->actor, 'CUSTOMER');
        $aid = positiveId($d, 'apartment_id');
        $sid = positiveId($d, 'service_id');
        if (
            !Database::one('SELECT id FROM apartments WHERE id=? AND customer_id=?', [
                $aid,
                $this->actor['id']
            ])
        ) {
            throw new DomainException('Căn hộ không thuộc tài khoản của bạn.');
        }
        $s = Database::one('SELECT * FROM services WHERE id=? AND is_active=1', [$sid]);
        if (!$s) {
            throw new DomainException('Dịch vụ không còn nhận yêu cầu.');
        }
        $parent = null;
        if (!empty($d['parent_request_id'])) {
            $parent = positiveId($d, 'parent_request_id');
            $p = Database::one(
                'SELECT * FROM service_requests WHERE id=? AND customer_id=? AND apartment_id=?',
                [$parent, $this->actor['id'], $aid]
            );
            if (!$p || $s['service_type'] !== 'MAINTENANCE') {
                throw new DomainException(
                    'Hồ sơ gốc phải cùng khách hàng, căn hộ và chỉ dùng cho bảo trì.'
                );
            }
        }
        $date = txt($d, 'preferred_date', 10, false);
        if (
            $date &&
            (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
                !checkdate(
                    (int) substr($date, 5, 2),
                    (int) substr($date, 8, 2),
                    (int) substr($date, 0, 4)
                ))
        ) {
            throw new DomainException('Ngày mong muốn không hợp lệ.');
        }
        $budget = ($d['budget'] ?? '') === '' ? null : amount($d, 'budget', true);
        $id = Database::insert('service_requests', [
            'code' => 'NT-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
            'customer_id' => $this->actor['id'],
            'apartment_id' => $aid,
            'service_id' => $sid,
            'parent_request_id' => $parent,
            'description' => txt($d, 'description', 5000),
            'budget' => $budget,
            'preferred_date' => $date ?: null,
            'status' => 'NEW',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $this->log('CREATE', $id, $id, '', null, 'NEW');
        return $id;
    }

    private function requestState(string $action, array $d): int
    {
        $r = $this->r($d);
        $id = (int) $r['id'];
        if ($action === 'request_receive' || $action === 'request_reject') {
            requireRole($this->actor, 'CONSULTANT');
            $this->state($r, ['NEW']);
            if ($r['consultant_id'] && (int) $r['consultant_id'] !== $this->actor['id']) {
                throw new DomainException('Hồ sơ đã được giao tư vấn viên khác.');
            }
            Database::run('UPDATE service_requests SET consultant_id=? WHERE id=?', [
                $this->actor['id'],
                $id
            ]);
            $this->setState(
                $r,
                $action === 'request_receive' ? 'RECEIVED' : 'REJECTED',
                $action === 'request_reject' ? txt($d, 'reason', 1000) : ''
            );
        } elseif ($action === 'request_cancel') {
            if (!isOwner($this->actor, $r) && !canConsult($this->actor, $r)) {
                throw new DomainException('Bạn không có quyền hủy hồ sơ.');
            }
            $this->state($r, ['NEW', 'RECEIVED', 'QUOTED', 'APPROVED']);
            $reason = txt($d, 'reason', 1000);
            if (
                finances($id)['paid'] > 0 ||
                Database::one(
                    "SELECT id FROM work_orders WHERE request_id=? AND status NOT IN ('ASSIGNED','CANCELLED')",
                    [$id]
                )
            ) {
                throw new DomainException(
                    'Hồ sơ đã thu tiền hoặc bắt đầu công việc, không thể tự hủy.'
                );
            }
            Database::run(
                "UPDATE work_orders SET status='CANCELLED' WHERE request_id=? AND status='ASSIGNED'",
                [$id]
            );
            Database::run(
                "UPDATE surveys SET status='CANCELLED' WHERE request_id=? AND status IN ('PENDING','CONFIRMED')",
                [$id]
            );
            Database::run('UPDATE service_requests SET cancellation_reason=? WHERE id=?', [
                $reason,
                $id
            ]);
            $this->setState($r, 'CANCELLED', $reason);
            Database::run(
                "UPDATE voucher_redemptions SET status='RELEASED' WHERE status='RESERVED' AND quotation_id IN (SELECT id FROM quotations WHERE request_id=?)",
                [$id]
            );
        } else {
            $this->consultant($r);
            $this->state($r, ['COMPLETED']);
            if (finances($id)['remaining'] !== 0) {
                throw new DomainException('Hồ sơ còn công nợ.');
            }
            $this->setState($r, 'CLOSED');
        }
        return $id;
    }

    private function freeSlot(
        int $user,
        string $start,
        string $end,
        ?int $surveyId = null,
        ?int $taskId = null
    ): void {
        if ($end <= $start) {
            throw new DomainException('Giờ kết thúc phải sau giờ bắt đầu.');
        }
        $u = userById($user);
        if (!$u) {
            throw new DomainException('Nhân viên không hoạt động.');
        }
        $s = Database::one(
            "SELECT id FROM surveys WHERE surveyor_id=? AND status IN ('PENDING','CONFIRMED','DONE') AND starts_at < ? AND ends_at > ? AND id <> ?",
            [$user, $end, $start, $surveyId ?? 0]
        );
        $w = Database::one(
            "SELECT id FROM work_orders WHERE assignee_id=? AND status <> 'CANCELLED' AND starts_at < ? AND ends_at > ? AND id <> ?",
            [$user, $end, $start, $taskId ?? 0]
        );
        if ($s || $w) {
            throw new DomainException('Nhân viên đã có lịch trong khoảng thời gian này.');
        }
    }

    private function survey(string $action, array $d): int
    {
        $r = $this->r($d);
        $rid = (int) $r['id'];
        $this->state($r, ['RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS']);
        if ($action === 'survey_save') {
            $this->consultant($r);
            $id = (int) ($d['id'] ?? 0);
            $u = positiveId($d, 'surveyor_id');
            $worker = userById($u);
            if (!$worker || !hasRole($worker, 'CONSULTANT')) {
                throw new DomainException('Người khảo sát phải là tư vấn viên.');
            }
            if (
                $id &&
                !Database::one(
                    "SELECT id FROM surveys WHERE id=? AND request_id=? AND status IN ('PENDING','CONFIRMED')",
                    [$id, $rid]
                )
            ) {
                throw new DomainException('Lịch khảo sát không thể sửa.');
            }
            $start = localInput(txt($d, 'starts_at', 16));
            $end = localInput(txt($d, 'ends_at', 16));
            $this->freeSlot($u, $start, $end, $id);
            if ($id) {
                Database::run(
                    "UPDATE surveys SET surveyor_id=?,starts_at=?,ends_at=?,notes=?,status='PENDING',customer_confirmed_at=NULL WHERE id=?",
                    [$u, $start, $end, txt($d, 'notes', 2000, false), $id]
                );
            } else {
                $id = Database::insert('surveys', [
                    'request_id' => $rid,
                    'surveyor_id' => $u,
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'notes' => txt($d, 'notes', 2000, false),
                    'status' => 'PENDING',
                    'created_by' => $this->actor['id'],
                    'created_at' => now()
                ]);
            }
        } else {
            $id = positiveId($d, 'id');
            $s = Database::one('SELECT * FROM surveys WHERE id=? AND request_id=?', [$id, $rid]);
            if (!$s) {
                throw new DomainException('Không tìm thấy lịch khảo sát.');
            }
            if ($action === 'survey_confirm') {
                $this->owner($r);
                $this->state($s, ['PENDING']);
                Database::run(
                    "UPDATE surveys SET status='CONFIRMED',customer_confirmed_at=? WHERE id=?",
                    [now(), $id]
                );
            } elseif ($action === 'survey_done') {
                if (
                    !canConsult($this->actor, $r) &&
                    (int) $s['surveyor_id'] !== $this->actor['id']
                ) {
                    throw new DomainException(
                        'Chỉ người khảo sát hoặc tư vấn viên phụ trách được ghi kết quả.'
                    );
                }
                requireRole($this->actor, 'CONSULTANT');
                $this->state($s, ['CONFIRMED']);
                Database::run("UPDATE surveys SET status='DONE',result=? WHERE id=?", [
                    txt($d, 'result', 4000),
                    $id
                ]);
            } else {
                $this->consultant($r);
                $this->state($s, ['PENDING', 'CONFIRMED']);
                Database::run("UPDATE surveys SET status='CANCELLED' WHERE id=?", [$id]);
            }
        }
        $this->log($action, $id, $rid, '', null, null, 'survey');
        return $rid;
    }

    private function quote(array $d, array $files, array &$stored): int
    {
        $r = $this->r($d);
        $this->consultant($r);
        $this->state($r, ['RECEIVED']);
        $rid = (int) $r['id'];
        if (
            Database::one(
                "SELECT id FROM quotations WHERE request_id=? AND status IN ('ACCEPTED','SENT')",
                [$rid]
            )
        ) {
            throw new DomainException('Đã có báo giá chờ duyệt hoặc được chấp thuận.');
        }
        $names = $d['item_name'] ?? [];
        $quantities = $d['quantity'] ?? [];
        $prices = $d['unit_price'] ?? [];
        $units = $d['unit'] ?? [];
        if (!is_array($names) || count($names) > 30) {
            throw new DomainException('Báo giá tối đa 30 dòng.');
        }
        $items = [];
        $itemIndices = [];
        $total = 0;
        foreach ($names as $i => $name) {
            if (trim((string) $name) === '') {
                continue;
            }
            $qty = (string) ($quantities[$i] ?? '');
            if (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $qty) || (float) $qty <= 0) {
                throw new DomainException('Số lượng cần lớn hơn 0, tối đa 2 chữ số thập phân.');
            }
            $price = amount(['price' => $prices[$i] ?? null], 'price', true);
            $unit = txt(['unit' => $units[$i] ?? ''], 'unit', 30);
            $item = [
                'item_name' => txt(['name' => $name], 'name', 200),
                'quantity' => $qty,
                'unit' => $unit,
                'unit_price' => $price
            ];
            $total += (int) round((float) $qty * $price);
            $items[] = $item;
            $itemIndices[] = $i;
        }
        if (!$items || $total > 100000000000) {
            throw new DomainException('Báo giá phải có hạng mục và tổng không quá 100 tỷ đồng.');
        }
        $existing = Database::one(
            "SELECT * FROM quotations WHERE request_id=? AND status='DRAFT'",
            [$rid]
        );
        if ($existing) {
            $qid = (int) $existing['id'];
            Database::run('DELETE FROM quotation_items WHERE quotation_id=?', [$qid]);
            Database::run('UPDATE quotations SET notes=? WHERE id=?', [
                txt($d, 'notes', 3000, false),
                $qid
            ]);
        } else {
            $version =
                1 +
                (int) Database::one(
                    'SELECT COALESCE(MAX(version),0) n FROM quotations WHERE request_id=?',
                    [$rid]
                )['n'];
            $qid = Database::insert('quotations', [
                'request_id' => $rid,
                'version' => $version,
                'status' => 'DRAFT',
                'notes' => txt($d, 'notes', 3000, false),
                'created_by' => $this->actor['id'],
                'created_at' => now()
            ]);
        }
        foreach ($items as $item) {
            Database::insert('quotation_items', ['quotation_id' => $qid] + $item);
        }
        saveQuotationSpec($qid, quotationSpecInput($d, $itemIndices));
        saveQuoteAdjustment($qid, $r, $d, $total);
        if (isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE) {
            if (count(quotationEvidence($qid)) >= 8) {
                throw new DomainException('Mỗi phiên bản báo giá nhận tối đa 8 tài liệu.');
            }
            $purpose = txt($d, 'upload_kind', 20);
            if (!isset(QUOTE_EVIDENCE_KINDS[$purpose])) {
                throw new DomainException('Loại tài liệu không hợp lệ.');
            }
            $description = txt($d, 'upload_description', 500);
            $attachment = $this->upload($rid, $files['file'], $stored);
            Database::insert('quotation_attachments', [
                'attachment_id' => $attachment,
                'quotation_id' => $qid,
                'purpose' => $purpose,
                'description' => $description
            ]);
        }
        if (($d['mode'] ?? '') === 'send') {
            reserveQuoteBenefits($qid);
            Database::run("UPDATE quotations SET status='SENT',sent_at=? WHERE id=?", [
                now(),
                $qid
            ]);
            $this->setState($r, 'QUOTED');
        }
        $this->log('QUOTE_SAVE', $qid, $rid, '', null, null, 'quotation');
        return $rid;
    }

    private function respondQuote(array $d): int
    {
        $r = $this->r($d);
        $this->owner($r);
        $this->state($r, ['QUOTED']);
        $id = positiveId($d, 'id');
        $q = Database::one(
            "SELECT id FROM quotations WHERE id=? AND request_id=? AND status='SENT'",
            [$id, $r['id']]
        );
        if (!$q) {
            throw new DomainException('Báo giá không còn chờ phản hồi.');
        }
        $accept = ($d['decision'] ?? '') === 'accept';
        $spec = quotationSpec($id);
        if ($accept && !empty($spec['valid_until']) && $spec['valid_until'] < date('Y-m-d')) {
            throw new DomainException(
                'Báo giá đã hết hiệu lực. Vui lòng yêu cầu tư vấn cập nhật phiên bản mới.'
            );
        }
        if (!in_array($d['decision'] ?? '', ['accept', 'reject'], true)) {
            throw new DomainException('Phản hồi không hợp lệ.');
        }
        $feedback = txt($d, 'feedback', 2000, !$accept);
        $status = $accept ? 'ACCEPTED' : 'REJECTED';
        settleQuoteBenefits($id, $accept);
        Database::run(
            'UPDATE quotations SET status=?,customer_feedback=?,responded_by=?,responded_at=? WHERE id=?',
            [$status, $feedback, $this->actor['id'], now(), $id]
        );
        $this->setState($r, $accept ? 'APPROVED' : 'RECEIVED');
        $this->log('QUOTE_RESPONSE', $id, (int) $r['id'], $feedback, 'SENT', $status, 'quotation');
        return (int) $r['id'];
    }

    private function assign(array $d): int
    {
        $r = $this->r($d);
        $this->consultant($r);
        $this->state($r, ['APPROVED', 'IN_PROGRESS']);
        $type = txt($d, 'work_type', 20);
        $map = [
            'DESIGN' => 'DESIGNER',
            'INSTALLATION' => 'INSTALLER',
            'MAINTENANCE' => 'MAINTAINER'
        ];
        $allowed =
            $r['service_type'] === 'PACKAGE' ? ['DESIGN', 'INSTALLATION'] : [$r['service_type']];
        if (!isset($map[$type]) || !in_array($type, $allowed, true)) {
            throw new DomainException('Loại công việc không phù hợp dịch vụ.');
        }
        if (!finances((int) $r['id'])['quote']) {
            throw new DomainException('Cần báo giá được chấp thuận trước khi phân công.');
        }
        $uid = positiveId($d, 'assignee_id');
        $u = userById($uid);
        if (!$u || !hasRole($u, $map[$type])) {
            throw new DomainException('Nhân viên không đúng chuyên môn hoặc đã khóa.');
        }
        if (
            $type === 'INSTALLATION' &&
            $r['service_type'] === 'PACKAGE' &&
            !Database::one(
                "SELECT dv.id FROM design_versions dv JOIN work_orders w ON w.id=dv.work_order_id WHERE w.request_id=? AND dv.status='APPROVED'",
                [$r['id']]
            )
        ) {
            throw new DomainException('Phải duyệt bản thiết kế trước khi giao lắp đặt.');
        }
        $start = localInput(txt($d, 'starts_at', 16));
        $end = localInput(txt($d, 'ends_at', 16));
        $this->freeSlot($uid, $start, $end);
        $id = Database::insert('work_orders', [
            'request_id' => $r['id'],
            'assignee_id' => $uid,
            'assigned_by' => $this->actor['id'],
            'work_type' => $type,
            'title' => txt($d, 'title', 200),
            'description' => txt($d, 'description', 3000, false),
            'starts_at' => $start,
            'ends_at' => $end,
            'status' => 'ASSIGNED',
            'created_at' => now()
        ]);
        $this->log('ASSIGN', $id, (int) $r['id'], '', null, 'ASSIGNED', 'work_order');
        return (int) $r['id'];
    }

    private function taskRecord(array $d): array
    {
        $w = Database::one('SELECT * FROM work_orders WHERE id=?', [positiveId($d, 'id')]);
        if (!$w) {
            throw new DomainException('Không tìm thấy công việc.');
        }
        return $w;
    }

    private function task(string $action, array $d, array $files, array &$stored): int
    {
        $w = $this->taskRecord($d);
        $r = requestAccess($this->actor, (int) $w['request_id']);
        $this->state($r, ['APPROVED', 'IN_PROGRESS']);
        $id = (int) $w['id'];
        $rid = (int) $r['id'];
        $event = 'PROGRESS';
        $content = '';
        $progress = null;
        $new = $w['status'];
        if ($action === 'task_accept' || $action === 'task_rework') {
            $this->owner($r);
            $this->state($w, ['REPORTED']);
            if ($w['work_type'] === 'DESIGN') {
                throw new DomainException('Hãy phản hồi ở mục bản thiết kế.');
            }
            $event = $action === 'task_accept' ? 'ACCEPTED' : 'REWORK_REQUESTED';
            $new = $action === 'task_accept' ? 'DONE' : 'IN_PROGRESS';
            $content =
                $action === 'task_accept'
                    ? 'Khách hàng xác nhận nghiệm thu.'
                    : txt($d, 'content', 3000);
        } elseif ($action === 'task_cancel') {
            $this->consultant($r);
            $this->state($w, ['ASSIGNED']);
            $new = 'CANCELLED';
            $content = txt($d, 'content', 1000);
        } else {
            if ((int) $w['assignee_id'] !== $this->actor['id']) {
                throw new DomainException('Chỉ nhân viên được giao việc được cập nhật.');
            }
            $role = [
                'DESIGN' => 'DESIGNER',
                'INSTALLATION' => 'INSTALLER',
                'MAINTENANCE' => 'MAINTAINER'
            ][$w['work_type']];
            requireRole($this->actor, $role);
            if ($action === 'task_start') {
                $this->state($w, ['ASSIGNED']);
                $new = 'IN_PROGRESS';
                $content = 'Bắt đầu công việc.';
                if ($r['status'] === 'APPROVED') {
                    $this->setState($r, 'IN_PROGRESS');
                }
            } else {
                $this->state($w, ['IN_PROGRESS']);
                $content = txt($d, 'content', 4000);
                $progress = filter_var($d['progress_percent'] ?? 0, FILTER_VALIDATE_INT);
                if ($progress === false || $progress < 0 || $progress > 100) {
                    throw new DomainException('Tiến độ từ 0 đến 100.');
                }
                if ($action === 'task_report') {
                    if ($w['work_type'] === 'DESIGN') {
                        throw new DomainException(
                            'Kết quả thiết kế phải nộp ở mục phiên bản thiết kế.'
                        );
                    }
                    $new = 'REPORTED';
                    $event = 'RESULT';
                    $progress = 100;
                }
            }
        }
        Database::run('UPDATE work_orders SET status=? WHERE id=?', [$new, $id]);
        $update = Database::insert('work_updates', [
            'work_order_id' => $id,
            'author_id' => $this->actor['id'],
            'event_type' => $event,
            'progress_percent' => $progress,
            'content' => $content,
            'created_at' => now()
        ]);
        if (isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $this->upload($rid, $files['file'], $stored, null, $update);
        }
        $this->log($action, $id, $rid, $content, $w['status'], $new, 'work_order');
        if ($new === 'DONE') {
            $this->complete($rid);
        }
        return $rid;
    }

    private function complete(int $rid): void
    {
        $r = requestAccess($this->actor, $rid);
        $tasks = Database::all(
            "SELECT work_type,status FROM work_orders WHERE request_id=? AND status<>'CANCELLED'",
            [$rid]
        );
        if (!$tasks) {
            return;
        }
        $types = [];
        foreach ($tasks as $w) {
            if ($w['status'] !== 'DONE') {
                return;
            }
            $types[] = $w['work_type'];
        }
        $required =
            $r['service_type'] === 'PACKAGE' ? ['DESIGN', 'INSTALLATION'] : [$r['service_type']];
        if (!array_diff($required, $types)) {
            $this->setState($r, 'COMPLETED');
        }
    }

    private function design(array $d, array $files, array &$stored): int
    {
        requireRole($this->actor, 'DESIGNER');
        $w = $this->taskRecord($d);
        $r = requestAccess($this->actor, (int) $w['request_id']);
        $this->state($r, ['IN_PROGRESS']);
        $this->state($w, ['IN_PROGRESS']);
        if ($w['work_type'] !== 'DESIGN' || (int) $w['assignee_id'] !== $this->actor['id']) {
            throw new DomainException('Không phải công việc thiết kế của bạn.');
        }
        if (
            Database::one(
                "SELECT id FROM design_versions WHERE work_order_id=? AND status IN ('SUBMITTED','APPROVED')",
                [$w['id']]
            )
        ) {
            throw new DomainException('Thiết kế đang chờ duyệt hoặc đã duyệt.');
        }
        $version =
            1 +
            (int) Database::one(
                'SELECT COALESCE(MAX(version),0) n FROM design_versions WHERE work_order_id=?',
                [$w['id']]
            )['n'];
        $id = Database::insert('design_versions', [
            'work_order_id' => $w['id'],
            'version' => $version,
            'description' => txt($d, 'description', 3000),
            'status' => 'SUBMITTED',
            'submitted_by' => $this->actor['id'],
            'submitted_at' => now()
        ]);
        $this->upload((int) $r['id'], $files['file'] ?? [], $stored, $id);
        Database::run("UPDATE work_orders SET status='REPORTED' WHERE id=?", [$w['id']]);
        $this->log('DESIGN_SUBMIT', $id, (int) $r['id'], '', null, 'SUBMITTED', 'design_version');
        return (int) $r['id'];
    }

    private function reviewDesign(array $d): int
    {
        $id = positiveId($d, 'id');
        $v = Database::one(
            'SELECT dv.*,w.request_id FROM design_versions dv JOIN work_orders w ON w.id=dv.work_order_id WHERE dv.id=?',
            [$id]
        );
        if (!$v) {
            throw new DomainException('Không tìm thấy bản thiết kế.');
        }
        $r = requestAccess($this->actor, (int) $v['request_id']);
        $this->owner($r);
        $this->state($r, ['IN_PROGRESS']);
        $this->state($v, ['SUBMITTED']);
        $latest = Database::one(
            'SELECT MAX(version) n FROM design_versions WHERE work_order_id=?',
            [$v['work_order_id']]
        );
        if ((int) $v['version'] !== (int) $latest['n']) {
            throw new DomainException('Chỉ phản hồi phiên bản mới nhất.');
        }
        $decision = txt($d, 'decision', 20);
        if (!in_array($decision, ['approve', 'revise'], true)) {
            throw new DomainException('Quyết định không hợp lệ.');
        }
        $yes = $decision === 'approve';
        $feedback = txt($d, 'feedback', 3000, !$yes);
        $new = $yes ? 'APPROVED' : 'REVISION_REQUESTED';
        Database::run(
            'UPDATE design_versions SET status=?,feedback=?,reviewed_by=?,reviewed_at=? WHERE id=?',
            [$new, $feedback, $this->actor['id'], now(), $id]
        );
        Database::run('UPDATE work_orders SET status=? WHERE id=?', [
            $yes ? 'DONE' : 'IN_PROGRESS',
            $v['work_order_id']
        ]);
        Database::insert('work_updates', [
            'work_order_id' => $v['work_order_id'],
            'author_id' => $this->actor['id'],
            'event_type' => $yes ? 'ACCEPTED' : 'REWORK_REQUESTED',
            'content' => $feedback ?: 'Khách hàng duyệt thiết kế.',
            'created_at' => now()
        ]);
        $this->log(
            'DESIGN_REVIEW',
            $id,
            (int) $r['id'],
            $feedback,
            'SUBMITTED',
            $new,
            'design_version'
        );
        if ($yes) {
            $this->complete((int) $r['id']);
        }
        return (int) $r['id'];
    }

    private function upload(
        int $rid,
        array $f,
        array &$stored,
        ?int $version = null,
        ?int $update = null
    ): int {
        if (
            ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
            !isset($f['tmp_name']) ||
            !is_uploaded_file($f['tmp_name'])
        ) {
            throw new DomainException('Vui lòng chọn tệp hợp lệ; giới hạn 10 MB.');
        }
        $size = filesize($f['tmp_name']);
        if (!$size || $size > 10485760) {
            throw new DomainException('Tệp phải từ 1 byte đến 10 MB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $types = ['application/pdf' => 'pdf', 'image/png' => 'png', 'image/jpeg' => 'jpg'];
        if (!isset($types[$mime])) {
            throw new DomainException('Chỉ nhận PDF, PNG hoặc JPG.');
        }
        $key = bin2hex(random_bytes(20)) . '.' . $types[$mime];
        $path = BASE_PATH . '/storage/uploads/' . $key;
        if (!move_uploaded_file($f['tmp_name'], $path)) {
            throw new RuntimeException('Không lưu được tệp đính kèm.');
        }
        $stored[] = $path;
        return Database::insert('attachments', [
            'request_id' => $rid,
            'design_version_id' => $version,
            'work_update_id' => $update,
            'uploaded_by' => $this->actor['id'],
            'original_name' => mb_substr(basename((string) $f['name']), 0, 255),
            'storage_key' => $key,
            'mime_type' => $mime,
            'size_bytes' => $size,
            'uploaded_at' => now()
        ]);
    }

    private function requestUpload(array $d, array $files, array &$stored): int
    {
        $r = $this->r($d);
        if (!isOwner($this->actor, $r) && !canConsult($this->actor, $r)) {
            throw new DomainException('Bạn không có quyền bổ sung tài liệu hồ sơ.');
        }
        $this->state($r, ['NEW', 'RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS']);
        $id = $this->upload((int) $r['id'], $files['file'] ?? [], $stored);
        $this->log('UPLOAD', $id, (int) $r['id'], '', null, null, 'attachment');
        return (int) $r['id'];
    }

    private function payment(string $action, array $d): int
    {
        $r = $this->r($d);
        $this->consultant($r);
        $this->state($r, ['APPROVED', 'IN_PROGRESS', 'COMPLETED']);
        $rid = (int) $r['id'];
        $f = finances($rid);
        if ($action === 'payment_add') {
            if (!$f['quote']) {
                throw new DomainException('Hồ sơ chưa có báo giá được duyệt.');
            }
            $sum = amount($d, 'amount');
            if ($sum > $f['remaining']) {
                throw new DomainException('Số tiền vượt số còn phải thu.');
            }
            $method = txt($d, 'method', 20);
            if (!in_array($method, ['CASH', 'BANK_TRANSFER'], true)) {
                throw new DomainException('Phương thức không hợp lệ.');
            }
            $receipt = txt($d, 'receipt_code', 50);
            if (Database::one('SELECT id FROM payments WHERE receipt_code=?', [$receipt])) {
                throw new DomainException('Mã chứng từ đã được dùng.');
            }
            $id = Database::insert('payments', [
                'request_id' => $rid,
                'quotation_id' => $f['quote'],
                'receipt_code' => $receipt,
                'amount' => $sum,
                'method' => $method,
                'status' => 'POSTED',
                'paid_at' => now(),
                'recorded_by' => $this->actor['id'],
                'created_at' => now()
            ]);
            $this->log('PAYMENT', $id, $rid, '', null, 'POSTED', 'payment');
        } else {
            $id = positiveId($d, 'id');
            $p = Database::one(
                "SELECT id FROM payments WHERE id=? AND request_id=? AND status='POSTED'",
                [$id, $rid]
            );
            if (!$p) {
                throw new DomainException('Khoản thu không thể hủy ghi nhận.');
            }
            $reason = txt($d, 'reason', 1000);
            Database::run(
                "UPDATE payments SET status='VOID',void_reason=?,voided_by=?,voided_at=? WHERE id=?",
                [$reason, $this->actor['id'], now(), $id]
            );
            $this->log('PAYMENT_VOID', $id, $rid, $reason, 'POSTED', 'VOID', 'payment');
        }
        return $rid;
    }

    private function profile(array $d): int
    {
        Database::run('UPDATE users SET full_name=?,phone=? WHERE id=?', [
            txt($d, 'full_name', 150),
            txt($d, 'phone', 20, false),
            $this->actor['id']
        ]);
        $this->log('PROFILE', $this->actor['id'], null, '', null, null, 'user');
        return $this->actor['id'];
    }

    private function service(array $d): int
    {
        requireRole($this->actor, 'ADMIN');
        $id = (int) ($d['id'] ?? 0);
        $type = txt($d, 'service_type', 20);
        if (!isset(TYPE_NAMES[$type])) {
            throw new DomainException('Loại dịch vụ không hợp lệ.');
        }
        $code = txt($d, 'code', 30);
        $name = txt($d, 'name', 150);
        $price = amount($d, 'reference_price', true);
        $active = isset($d['is_active']) ? 1 : 0;
        $description = txt($d, 'description', 4000);
        if (Database::one('SELECT id FROM services WHERE code=? AND id<>?', [$code, $id])) {
            throw new DomainException('Mã dịch vụ đã tồn tại.');
        }
        if ($id) {
            $old = Database::one('SELECT * FROM services WHERE id=?', [$id]);
            if (!$old) {
                throw new DomainException('Không tìm thấy dịch vụ.');
            }
            if (
                $old['service_type'] !== $type &&
                Database::one('SELECT id FROM service_requests WHERE service_id=?', [$id])
            ) {
                throw new DomainException('Không đổi loại dịch vụ đã có hồ sơ.');
            }
            Database::run(
                'UPDATE services SET code=?,name=?,service_type=?,reference_price=?,is_active=?,description=? WHERE id=?',
                [$code, $name, $type, $price, $active, $description, $id]
            );
        } else {
            $id = Database::insert('services', [
                'code' => $code,
                'name' => $name,
                'service_type' => $type,
                'reference_price' => $price,
                'is_active' => $active,
                'description' => $description
            ]);
        }
        $this->log('SERVICE_SAVE', $id, null, '', null, null, 'service');
        return $id;
    }

    private function user(array $d): int
    {
        requireRole($this->actor, 'ADMIN');
        $id = (int) ($d['id'] ?? 0);
        $roles = $d['roles'] ?? [];
        if (!is_array($roles) || !$roles || array_diff($roles, array_keys(ROLE_NAMES))) {
            throw new DomainException('Chọn ít nhất một vai trò hợp lệ.');
        }
        $active = isset($d['is_active']) ? 1 : 0;
        $name = txt($d, 'full_name', 150);
        $phone = txt($d, 'phone', 20, false);
        if ($id) {
            $old = Database::one('SELECT * FROM users WHERE id=?', [$id]);
            if (!$old) {
                throw new DomainException('Không tìm thấy tài khoản.');
            }
            if (!$active || !in_array('ADMIN', $roles, true)) {
                $admins = Database::all(
                    "SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE u.is_active=1 AND r.code='ADMIN'"
                );
                if (count($admins) === 1 && (int) $admins[0]['id'] === $id) {
                    throw new DomainException('Phải giữ ít nhất một quản trị viên hoạt động.');
                }
            }
            $open = Database::all(
                "SELECT work_type FROM work_orders WHERE assignee_id=? AND status NOT IN ('DONE','CANCELLED')",
                [$id]
            );
            $map = [
                'DESIGN' => 'DESIGNER',
                'INSTALLATION' => 'INSTALLER',
                'MAINTENANCE' => 'MAINTAINER'
            ];
            foreach ($open as $w) {
                if (!$active || !in_array($map[$w['work_type']], $roles, true)) {
                    throw new DomainException(
                        'Nhân viên còn công việc mở; chưa thể khóa hoặc bỏ vai trò chuyên môn.'
                    );
                }
            }
            if (
                (!$active || !in_array('CONSULTANT', $roles, true)) &&
                (Database::one(
                    "SELECT id FROM service_requests WHERE consultant_id=? AND status NOT IN ('CLOSED','CANCELLED','REJECTED')",
                    [$id]
                ) ||
                    Database::one(
                        "SELECT id FROM surveys WHERE surveyor_id=? AND status IN ('PENDING','CONFIRMED')",
                        [$id]
                    ))
            ) {
                throw new DomainException('Tư vấn viên còn hồ sơ hoặc lịch khảo sát mở.');
            }
            if (
                !in_array('CUSTOMER', $roles, true) &&
                Database::one('SELECT id FROM apartments WHERE customer_id=?', [$id])
            ) {
                throw new DomainException('Tài khoản có căn hộ cần giữ vai trò khách hàng.');
            }
            Database::run('UPDATE users SET full_name=?,phone=?,is_active=? WHERE id=?', [
                $name,
                $phone,
                $active,
                $id
            ]);
            Database::run('DELETE FROM user_roles WHERE user_id=?', [$id]);
        } else {
            $email = mb_strtolower(txt($d, 'email', 254));
            if (
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                Database::one('SELECT id FROM users WHERE email=?', [$email])
            ) {
                throw new DomainException('Email sai định dạng hoặc đã tồn tại.');
            }
            $password = txt($d, 'password', 72);
            if (strlen($password) < 10) {
                throw new DomainException('Mật khẩu tối thiểu 10 ký tự.');
            }
            $id = Database::insert('users', [
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $name,
                'phone' => $phone,
                'is_active' => $active,
                'created_at' => now()
            ]);
        }
        foreach (array_unique($roles) as $code) {
            $role = Database::one('SELECT id FROM roles WHERE code=?', [$code]);
            Database::insert('user_roles', ['user_id' => $id, 'role_id' => $role['id']]);
        }
        $this->log('USER_SAVE', $id, null, '', null, null, 'user');
        return $id;
    }
}
