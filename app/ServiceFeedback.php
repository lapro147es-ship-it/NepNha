<?php
declare(strict_types=1);

final class ServiceFeedback
{
    public static function handle(
        array $user,
        string $action,
        array $data,
        array $files,
        array &$stored
    ): string {
        if ($action === 'review_save') {
            requireRole($user, 'CUSTOMER');
            $rid = positiveId($data, 'request_id');
            $r = requestAccess($user, $rid);
            if (
                (int) $r['customer_id'] !== (int) $user['id'] ||
                !in_array($r['status'], ['COMPLETED', 'CLOSED'], true)
            ) {
                throw new DomainException('Chỉ chủ hồ sơ đã nghiệm thu mới được đánh giá dịch vụ.');
            }
            $rating = filter_var($data['rating'] ?? null, FILTER_VALIDATE_INT);
            if ($rating === false || $rating < 1 || $rating > 5) {
                throw new DomainException('Điểm đánh giá phải từ 1 đến 5.');
            }
            $values = [
                'rating' => $rating,
                'content' => txt($data, 'content', 2000),
                'updated_at' => now()
            ];
            $old = Database::one('SELECT * FROM service_reviews WHERE request_id=?', [$rid]);
            if ($old) {
                if ($old['status'] === 'HIDDEN') {
                    throw new DomainException(
                        'Đánh giá đang được ẩn. Liên hệ tư vấn để được hỗ trợ.'
                    );
                }
                $id = (int) $old['id'];
                Database::run(
                    'UPDATE service_reviews SET rating=?,content=?,updated_at=? WHERE id=? AND customer_id=?',
                    [$rating, $values['content'], now(), $id, $user['id']]
                );
            } else {
                $id = Database::insert(
                    'service_reviews',
                    $values + [
                        'request_id' => $rid,
                        'customer_id' => $user['id'],
                        'service_id' => $r['service_id'],
                        'status' => 'PUBLISHED',
                        'created_at' => now()
                    ]
                );
            }
            if (isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                if (empty($data['public_photo_consent'])) {
                    throw new DomainException(
                        'Cần đồng ý công khai ảnh đánh giá. Không tải tài liệu cá nhân nhạy cảm.'
                    );
                }
                if (Database::one('SELECT review_id FROM review_images WHERE review_id=?', [$id])) {
                    throw new DomainException(
                        'Đánh giá đã có ảnh. Liên hệ quản trị nếu cần thay ảnh.'
                    );
                }
                $file = $files['file'];
                if (
                    $file['error'] !== UPLOAD_ERR_OK ||
                    $file['size'] < 1 ||
                    $file['size'] > 5 * 1024 * 1024 ||
                    !is_uploaded_file($file['tmp_name'])
                ) {
                    throw new DomainException('Ảnh đánh giá cần là PNG/JPG không quá 5 MB.');
                }
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                $size = @getimagesize($file['tmp_name']);
                if (
                    !in_array($mime, ['image/png', 'image/jpeg'], true) ||
                    !$size ||
                    $size[0] > 6000 ||
                    $size[1] > 6000
                ) {
                    throw new DomainException('Ảnh không hợp lệ hoặc vượt 6000 px.');
                }
                $key =
                    'review-' .
                    bin2hex(random_bytes(20)) .
                    ($mime === 'image/png' ? '.png' : '.jpg');
                $path = BASE_PATH . '/storage/uploads/' . $key;
                if (!move_uploaded_file($file['tmp_name'], $path)) {
                    throw new DomainException('Không lưu được ảnh.');
                }
                $stored[] = $path;
                Database::insert('review_images', [
                    'review_id' => $id,
                    'storage_key' => $key,
                    'mime_type' => $mime,
                    'size_bytes' => $file['size']
                ]);
            }
            foreach (
                Database::all(
                    "SELECT DISTINCT u.id FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles ro ON ro.id=ur.role_id WHERE u.is_active=1 AND ro.code IN ('ADMIN','CONSULTANT')"
                )
                as $staff
            ) {
                notifyUser(
                    (int) $staff['id'],
                    'review:' . $id . ':' . time(),
                    'Có đánh giá dịch vụ',
                    'Khách hàng vừa gửi đánh giá sau nghiệm thu.',
                    'reviews',
                    $id
                );
            }
        } else {
            $id = positiveId($data, 'id');
            $review = Database::one('SELECT * FROM service_reviews WHERE id=?', [$id]);
            if (!$review) {
                throw new DomainException('Không tìm thấy đánh giá.');
            }
            if ($action === 'review_reply') {
                requireRole($user, 'ADMIN', 'CONSULTANT');
                Database::run(
                    'UPDATE service_reviews SET reply=?,replied_by=?,replied_at=? WHERE id=?',
                    [txt($data, 'reply', 2000), $user['id'], now(), $id]
                );
                notifyUser(
                    (int) $review['customer_id'],
                    'reply:' . $id . ':' . time(),
                    'Đánh giá của bạn đã được phản hồi',
                    'Mở đánh giá để xem phản hồi từ Nếp Nhà.',
                    'reviews',
                    $id
                );
            } else {
                if (
                    !hasRole($user, 'ADMIN', 'CONSULTANT') &&
                    (int) $review['customer_id'] !== (int) $user['id']
                ) {
                    throw new DomainException('Bạn không có quyền ẩn đánh giá này.');
                }
                $status = txt($data, 'status', 12);
                if (
                    !in_array($status, ['PUBLISHED', 'HIDDEN'], true) ||
                    ($status === 'PUBLISHED' && !hasRole($user, 'ADMIN', 'CONSULTANT'))
                ) {
                    throw new DomainException('Trạng thái đánh giá không hợp lệ.');
                }
                Database::run('UPDATE service_reviews SET status=?,updated_at=? WHERE id=?', [
                    $status,
                    now(),
                    $id
                ]);
            }
        }
        Commerce::audit(
            $user,
            strtoupper($action),
            'review',
            $id,
            $rid ?? (int) $review['request_id']
        );
        return url('reviews', ['id' => $id]);
    }
}
