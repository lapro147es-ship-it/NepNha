<?php
$entity = null;
$id = (int) ($_GET['id'] ?? 0);
$rid = (int) ($_GET['request_id'] ?? 0);
$kind = str_starts_with($page, 'survey')
    ? 'surveys'
    : (str_starts_with($page, 'payment')
        ? 'payments'
        : 'tasks');
if ($page === 'request-decision') {
    $rid = $id;
} elseif ($id) {
    $entity = entityAccess($kind, $id, $user);
    $rid = (int) $entity['request_id'];
}
if (!$rid && in_array($page, ['survey-form', 'task-form', 'payment-form'], true)) {
    heading('CHỌN HỒ SƠ', $title, 'Chọn hồ sơ trước khi nhập thông tin.');
    $states =
        $page === 'survey-form'
            ? ['RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS']
            : ($page === 'task-form'
                ? ['APPROVED', 'IN_PROGRESS']
                : ['APPROVED', 'IN_PROGRESS', 'COMPLETED']);
    $options = [];
    foreach (requestRows($user, 1000)['rows'] as $row) {
        if (canConsult($user, $row) && in_array($row['status'], $states, true)) {
            $options[$row['id']] = $row['code'] . ' · ' . $row['apartment_name'];
        }
    }
    echo '<section class="panel action-panel"><form method="get">';
    hidden('page', $page);
    choose('request_id', 'Hồ sơ phù hợp', $options);
    echo '<button class="button" type="submit">Tiếp tục →</button></form></section>';
    return;
}
$r = requestAccess($user, $rid);
$owner = isOwner($user, $r);
$consult = canConsult($user, $r);
$active = in_array($r['status'], ['APPROVED', 'IN_PROGRESS'], true);
$allowed = false;
$action = '';
$hint = '';
$button = 'Lưu thông tin';
switch ($page) {
    case 'request-decision':
        $mode = (string) ($_GET['mode'] ?? '');
        $action = 'request_' . $mode;
        $allowed = match ($mode) {
            'receive', 'reject' => hasRole($user, 'CONSULTANT') &&
                $r['status'] === 'NEW' &&
                (!$r['consultant_id'] || $consult),
            'cancel' => ($owner || $consult) &&
                in_array($r['status'], ['NEW', 'RECEIVED', 'QUOTED', 'APPROVED'], true),
            'close' => $consult && $r['status'] === 'COMPLETED',
            default => false
        };
        $title =
            [
                'receive' => 'Tiếp nhận hồ sơ',
                'reject' => 'Từ chối yêu cầu',
                'cancel' => 'Hủy hồ sơ',
                'close' => 'Đóng hồ sơ'
            ][$mode] ?? 'Xử lý hồ sơ';
        $hint =
            $mode === 'close'
                ? 'Chỉ đóng hồ sơ đã nghiệm thu và không còn công nợ.'
                : 'Thao tác được ghi vào lịch sử hồ sơ. Hãy kiểm tra thông tin trước khi xác nhận.';
        break;
    case 'survey-form':
        $allowed =
            $consult &&
            in_array($r['status'], ['RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS'], true) &&
            (!$entity || in_array($entity['status'], ['PENDING', 'CONFIRMED'], true));
        $action = 'survey_save';
        $hint =
            'Chọn người khảo sát, thời gian và những nội dung cần chuẩn bị. Khi thay đổi lịch, khách hàng cần xác nhận lại.';
        break;
    case 'survey-result':
        $allowed =
            $entity &&
            ($consult || (int) $entity['surveyor_id'] === $user['id']) &&
            $entity['status'] === 'CONFIRMED' &&
            in_array($r['status'], ['RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS'], true);
        $action = 'survey_done';
        $hint = 'Ghi hiện trạng, kích thước, hạn chế thi công và kết luận làm cơ sở báo giá.';
        break;
    case 'task-form':
        $allowed = $consult && $active;
        $action = 'task_assign';
        $hint =
            'Giao đúng chuyên môn. Hệ thống kiểm tra lịch trùng và yêu cầu duyệt thiết kế trước khi lắp đặt trọn gói.';
        break;
    case 'task-update':
    case 'task-report':
    case 'design-submit':
        $allowed =
            $entity &&
            (int) $entity['assignee_id'] === $user['id'] &&
            $entity['status'] === 'IN_PROGRESS' &&
            $active;
        if ($page === 'design-submit') {
            $allowed = $allowed && $entity['work_type'] === 'DESIGN';
        }
        if ($page === 'task-report') {
            $allowed = $allowed && $entity['work_type'] !== 'DESIGN';
        }
        $action = [
            'task-update' => 'task_update',
            'task-report' => 'task_report',
            'design-submit' => 'design_submit'
        ][$page];
        $hint =
            'Mô tả rõ kết quả, phần còn lại và đính kèm bằng chứng nếu cần. Báo cáo 100% vẫn cần khách hàng nghiệm thu.';
        break;
    case 'task-rework':
        $allowed =
            $entity &&
            $owner &&
            $entity['status'] === 'REPORTED' &&
            $entity['work_type'] !== 'DESIGN' &&
            $active;
        $action = 'task_rework';
        $hint = 'Nêu vị trí, hiện tượng và kết quả mong muốn để nhân viên xử lý lại.';
        break;
    case 'task-cancel':
        $allowed = $entity && $consult && $entity['status'] === 'ASSIGNED' && $active;
        $action = 'task_cancel';
        $hint = 'Chỉ hủy công việc chưa bắt đầu. Lý do hủy được lưu lại.';
        break;
    case 'document-upload':
        $allowed =
            ($owner || $consult) &&
            in_array($r['status'], ['NEW', 'RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS'], true);
        $action = 'request_upload';
        $hint =
            'Bổ sung mặt bằng, ảnh hiện trạng hoặc tài liệu liên quan. Bản thiết kế cần nộp qua công việc thiết kế để lưu phiên bản.';
        break;
    case 'payment-form':
    case 'payment-void':
        $allowed =
            $consult &&
            in_array($r['status'], ['APPROVED', 'IN_PROGRESS', 'COMPLETED'], true) &&
            ($page !== 'payment-void' || ($entity && $entity['status'] === 'POSTED'));
        $action = $page === 'payment-form' ? 'payment_add' : 'payment_void';
        $hint =
            'Đây là ghi nhận thu tiền nội bộ. Không thực hiện giao dịch ngân hàng và không thay thế hóa đơn thuế.';
        break;
}
if (!$allowed) {
    throw new DomainException('Thao tác không phù hợp quyền truy cập hoặc trạng thái hiện tại.');
}
require __DIR__ . '/partials/request-header.php';
heading('BIỂU MẪU RIÊNG', $title, $hint);
$upload = in_array($page, ['task-update', 'task-report', 'design-submit', 'document-upload'], true);
echo '<div class="editorial-columns"><section class="panel action-panel">';
formStart($action, $rid, $upload);
if ($entity) {
    hidden('id', $entity['id']);
}
if ($page === 'request-decision' && in_array($mode, ['reject', 'cancel'], true)) {
    area('reason', 'Lý do', oldValue('reason'));
}
if (in_array($page, ['survey-form', 'task-form'], true)) {
    $staff = [];
    foreach (
        Database::all('SELECT id,full_name FROM users WHERE is_active=1 ORDER BY full_name')
        as $person
    ) {
        $member = userById((int) $person['id']);
        $roles = $page === 'survey-form' ? ['CONSULTANT'] : ['DESIGNER', 'INSTALLER', 'MAINTAINER'];
        if (hasRole($member, ...$roles)) {
            $staff[$person['id']] =
                $person['full_name'] .
                ' · ' .
                implode(
                    ', ',
                    array_map(
                        fn($role) => ROLE_NAMES[$role],
                        array_intersect($member['roles'], $roles)
                    )
                );
        }
    }
    choose(
        $page === 'survey-form' ? 'surveyor_id' : 'assignee_id',
        'Nhân viên phụ trách',
        $staff,
        oldValue(
            $page === 'survey-form' ? 'surveyor_id' : 'assignee_id',
            $entity['surveyor_id'] ?? ''
        )
    );
    if ($page === 'task-form') {
        $types =
            $r['service_type'] === 'PACKAGE'
                ? ['DESIGN' => 'Thiết kế', 'INSTALLATION' => 'Lắp đặt']
                : [$r['service_type'] => TYPE_NAMES[$r['service_type']]];
        choose('work_type', 'Loại công việc', $types, oldValue('work_type'));
        field('title', 'Tên công việc', oldValue('title'));
        area('description', 'Phạm vi và yêu cầu', oldValue('description'), false);
    }
    foreach (['starts_at' => 'Bắt đầu', 'ends_at' => 'Kết thúc'] as $key => $label) {
        $value = isset($entity[$key])
            ? (new DateTimeImmutable($entity[$key], new DateTimeZone('UTC')))
                ->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))
                ->format('Y-m-d\TH:i')
            : '';
        field($key, $label . ' (giờ Việt Nam)', oldValue($key, $value), 'datetime-local');
    }
    if ($page === 'survey-form') {
        area('notes', 'Nội dung cần chuẩn bị', oldValue('notes', $entity['notes'] ?? ''), false);
    }
}
if ($page === 'survey-result') {
    area('result', 'Kết quả khảo sát', oldValue('result', $entity['result'] ?? ''));
}
if (in_array($page, ['task-update', 'task-report', 'task-rework', 'task-cancel'], true)) {
    area('content', 'Nội dung', oldValue('content'));
}
if ($page === 'task-update') {
    echo '<label class="field"><span>Tiến độ (%)</span><input type="number" name="progress_percent" min="0" max="100" step="1" value="' .
        e(oldValue('progress_percent', 0)) .
        '" required></label>';
}
if ($page === 'design-submit') {
    area('description', 'Mô tả phiên bản và các thay đổi', oldValue('description'));
}
if ($page === 'payment-form') {
    $f = finances($rid);
    echo '<p class="notice">Còn phải thu: <strong>' . money($f['remaining']) . '</strong></p>';
    field(
        'receipt_code',
        'Mã chứng từ',
        oldValue('receipt_code', 'PT-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))))
    );
    field('amount', 'Số tiền (VND)', oldValue('amount'), 'number');
    choose(
        'method',
        'Phương thức',
        ['CASH' => 'Tiền mặt', 'BANK_TRANSFER' => 'Chuyển khoản'],
        oldValue('method')
    );
}
if ($page === 'payment-void') {
    echo '<p>' . e($entity['receipt_code']) . ' · ' . money($entity['amount']) . '</p>';
    area('reason', 'Lý do hủy ghi nhận', oldValue('reason'));
}
if ($upload) {
    echo '<label class="field"><span>Tài liệu PDF, PNG hoặc JPG · tối đa 10 MB</span><input type="file" name="file" accept=".pdf,.png,.jpg,.jpeg"' .
        (in_array($page, ['design-submit', 'document-upload'], true) ? ' required' : '') .
        '></label>';
}
echo '<div class="actions"><button type="submit" class="button">' .
    e($button) .
    '</button><a class="button secondary" href="' .
    e(url('request', ['id' => $rid])) .
    '">Về hồ sơ</a></div></form></section><aside class="panel help-side"><p class="eyebrow">TRƯỚC KHI LƯU</p><h3>Thông tin cần rõ ràng.</h3><p>' .
    e($hint) .
    '</p><p>Hồ sơ: ' .
    e($r['code']) .
    '<br>Căn hộ: ' .
    e($r['apartment_name']) .
    '</p><p>Thông tin được kiểm tra lại khi gửi để tránh thao tác trùng hoặc thay đổi trạng thái từ phiên khác.</p></aside></div>';
