<?php
$rid = (int) ($_GET['request_id'] ?? ($_GET['id'] ?? 0));
$request = requestAccess($user, $rid);
if (
    (int) $request['customer_id'] !== (int) $user['id'] ||
    !in_array($request['status'], ['COMPLETED', 'CLOSED'], true)
) {
throw new DomainException('Chỉ đánh giá hồ sơ của bạn sau nghiệm thu.');
}
$review = Database::one('SELECT * FROM service_reviews WHERE request_id=?', [$rid]) ?? [];
heading(
    'TRẢI NGHIỆM ĐƯỢC LẮNG NGHE',
    'Đánh giá ' . $request['code'],
    'Chia sẻ trải nghiệm thực tế sau nghiệm thu. Đánh giá công khai hiển thị tên gọi đầu, không công khai mã căn hộ hay hồ sơ.'
);
?>

<section class="panel form-panel">

    <?php
    formStart('review_save', $rid, true);
    choose(
        'rating',
        'Mức hài lòng',
        [
            5 => '5 · Rất hài lòng',
            4 => '4 · Hài lòng',
            3 => '3 · Bình thường',
            2 => '2 · Chưa hài lòng',
            1 => '1 · Cần cải thiện'
        ],
        oldValue('rating', $review['rating'] ?? 5)
    );
    area('content', 'Trải nghiệm của bạn', oldValue('content', $review['content'] ?? ''));
    ?>

    <label class="field">
        <span>Ảnh thực tế (tùy chọn, JPG/PNG, tối đa 5 MB)</span>
        <input type="file" name="file" accept="image/jpeg,image/png">
    </label>
    <label class="check">
        <input type="checkbox" name="public_photo_consent" value="1"> Tôi đồng ý công khai ảnh này;
        ảnh không chứa thông tin cá nhân nhạy cảm.
    </label>
    <button class="button">Gửi đánh giá</button>
    </form>
</section>
