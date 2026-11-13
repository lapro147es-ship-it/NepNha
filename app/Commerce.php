<?php
declare(strict_types=1);

require_once __DIR__ . '/ServiceCart.php';
require_once __DIR__ . '/MasterData.php';
require_once __DIR__ . '/ServiceFeedback.php';

final class Commerce
{
    public static function supports(string $action): bool
    {
        return in_array(
            $action,
            [
                'master_save',
                'master_archive',
                'cart_add',
                'cart_update',
                'cart_remove',
                'cart_checkout',
                'voucher_save',
                'review_save',
                'review_reply',
                'review_visibility',
                'notice_read',
                'notice_publish',
                'lead_create',
                'lead_update',
                'apartment_remove'
            ],
            true
        );
    }

    public static function handle(
        ?array $user,
        string $action,
        array $data,
        array $files = []
    ): string {
        if ($action !== 'lead_create' && !$user) {
            throw new DomainException('Vui lòng đăng nhập để sử dụng chức năng này.');
        }
        $stored = [];
        try {
            return Database::transaction(static function () use (
                $user,
                $action,
                $data,
                $files,
                &$stored
            ): string {
                if ($user) {
                    $user = userById((int) $user['id']);
                    if (!$user) {
                        throw new DomainException('Tài khoản không còn hoạt động.');
                    }
                }
                return match ($action) {
                    'master_save', 'master_archive' => MasterData::save($user, $action, $data),
                    'cart_add',
                    'cart_update',
                    'cart_remove',
                    'cart_checkout',
                    'voucher_save'
                        => ServiceCart::handle($user, $action, $data),
                    'review_save', 'review_reply', 'review_visibility' => ServiceFeedback::handle(
                        $user,
                        $action,
                        $data,
                        $files,
                        $stored
                    ),
                    'notice_read' => self::readNotice($user, $data),
                    'notice_publish' => self::publishNotice($user, $data),
                    'lead_create', 'lead_update' => self::lead($user, $action, $data),
                    'apartment_remove' => self::removeApartment($user, $data),
                    default => throw new DomainException('Chức năng không hợp lệ.')
                };
            });
        } catch (Throwable $error) {
            foreach ($stored as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
            throw $error;
        }
    }

    public static function audit(
        array $user,
        string $action,
        string $entity,
        int $id,
        ?int $request = null
    ): void {
        Database::insert('audit_logs', [
            'actor_id' => $user['id'],
            'request_id' => $request,
            'entity_type' => $entity,
            'entity_id' => $id,
            'action' => $action,
            'created_at' => now()
        ]);
    }

    private static function publishNotice(array $user, array $data): string
    {
        requireRole($user, 'ADMIN');
        $recipient = positiveId($data, 'user_id');
        if (!userById($recipient)) {
            throw new DomainException('Người nhận không còn hoạt động.');
        }
        notifyUser(
            $recipient,
            'admin:' . bin2hex(random_bytes(12)),
            txt($data, 'title', 180),
            txt($data, 'body', 3000),
            'inbox'
        );
        self::audit($user, 'NOTICE_PUBLISH', 'user', $recipient);
        return url('inbox');
    }

    private static function readNotice(array $user, array $data): string
    {
        if (!empty($data['all'])) {
            Database::run(
                'UPDATE user_notifications SET read_at=? WHERE user_id=? AND read_at IS NULL',
                [now(), $user['id']]
            );
        } else {
            $id = positiveId($data, 'id');
            if (
                !Database::one('SELECT id FROM user_notifications WHERE id=? AND user_id=?', [
                    $id,
                    $user['id']
                ])
            ) {
                throw new DomainException('Thông báo không thuộc tài khoản của bạn.');
            }
            Database::run('UPDATE user_notifications SET read_at=? WHERE id=? AND user_id=?', [
                now(),
                $id,
                $user['id']
            ]);
        }
        return url('inbox');
    }

    private static function removeApartment(array $user, array $data): string
    {
        requireRole($user, 'CUSTOMER');
        $id = positiveId($data, 'id');
        if (
            !Database::one('SELECT id FROM apartments WHERE id=? AND customer_id=?', [
                $id,
                $user['id']
            ])
        ) {
            throw new DomainException('Căn hộ không thuộc tài khoản của bạn.');
        }
        if (Database::one('SELECT id FROM service_requests WHERE apartment_id=?', [$id])) {
            throw new DomainException('Căn hộ đã có hồ sơ dịch vụ; cần giữ để đối chiếu lịch sử.');
        }
        Database::run('DELETE FROM apartments WHERE id=? AND customer_id=?', [$id, $user['id']]);
        self::audit($user, 'APARTMENT_REMOVE', 'apartment', $id);
        return url('apartments');
    }

    private static function lead(?array $user, string $action, array $data): string
    {
        if ($action === 'lead_update') {
            requireRole($user, 'ADMIN', 'CONSULTANT');
            $id = positiveId($data, 'id');
            if (!Database::one('SELECT id FROM consultation_leads WHERE id=?', [$id])) {
                throw new DomainException('Không tìm thấy yêu cầu tư vấn.');
            }
            $status = txt($data, 'status', 15);
            if (!in_array($status, ['NEW', 'CONTACTED', 'CONVERTED', 'DECLINED'], true)) {
                throw new DomainException('Trạng thái tư vấn không hợp lệ.');
            }
            Database::run(
                'UPDATE consultation_leads SET status=?,staff_note=?,handled_by=?,updated_at=? WHERE id=?',
                [$status, txt($data, 'staff_note', 3000, false), $user['id'], now(), $id]
            );
            self::audit($user, 'LEAD_UPDATE', 'lead', $id);
            return url('consultations', ['id' => $id]);
        }
        if (empty($data['consent'])) {
            throw new DomainException('Cần đồng ý xử lý thông tin để nhận tư vấn.');
        }
        if (time() - (int) ($_SESSION['last_lead_at'] ?? 0) < 30) {
            throw new DomainException('Yêu cầu vừa được gửi. Vui lòng chờ trước khi gửi lại.');
        }
        $email = mb_strtolower(txt($data, 'email', 254));
        $phone = txt($data, 'phone', 30);
        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !preg_match('/^[+0-9 ()-]{8,25}$/', $phone)
        ) {
            throw new DomainException('Email hoặc số điện thoại không hợp lệ.');
        }
        $service = positiveId($data, 'service_id');
        if (!Database::one('SELECT id FROM services WHERE id=? AND is_active=1', [$service])) {
            throw new DomainException('Dịch vụ không còn nhận yêu cầu.');
        }
        $date = txt($data, 'preferred_date', 10, false);
        if (
            $date !== '' &&
            (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
                !checkdate(
                    (int) substr($date, 5, 2),
                    (int) substr($date, 8, 2),
                    (int) substr($date, 0, 4)
                ) ||
                $date < date('Y-m-d'))
        ) {
            throw new DomainException('Ngày mong muốn cần là ngày hợp lệ từ hôm nay.');
        }
        $reference = 'TV-' . strtoupper(bin2hex(random_bytes(5)));
        $id = Database::insert('consultation_leads', [
            'reference_code' => $reference,
            'full_name' => txt($data, 'full_name', 150),
            'email' => $email,
            'phone' => $phone,
            'address' => txt($data, 'address', 500),
            'service_id' => $service,
            'content' => txt($data, 'content', 4000),
            'preferred_date' => $date ?: null,
            'status' => 'NEW',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        foreach (
            Database::all(
                "SELECT DISTINCT u.id FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE u.is_active=1 AND r.code IN ('ADMIN','CONSULTANT')"
            )
            as $staff
        ) {
            notifyUser(
                (int) $staff['id'],
                'lead:' . $id,
                'Có khách đăng ký khảo sát nhanh',
                'Yêu cầu ' . $reference . ' cần được liên hệ xác nhận.',
                'consultations',
                $id
            );
        }
        $_SESSION['last_lead_at'] = time();
        $_SESSION['lead_receipt'] = $reference;
        return url('booking-received');
    }
}
