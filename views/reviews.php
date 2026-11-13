<?php
heading(
    'SAU MỖI LẦN ĐỒNG HÀNH',
    'Đánh giá dịch vụ',
    'Đánh giá gắn với hồ sơ đã nghiệm thu; phản hồi được lưu cùng lịch sử trải nghiệm.'
);
$customer = hasRole($user, 'CUSTOMER');
$reviews = Database::all(
    'SELECT v.*,s.name service_name,r.code request_code FROM service_reviews v JOIN services s ON s.id=v.service_id JOIN service_requests r ON r.id=v.request_id' .
    ($customer ? ' WHERE v.customer_id=?' : '') .
    ' ORDER BY v.id DESC LIMIT 100',
    $customer ? [$user['id']] : []
);
if ($customer):
    $eligible = Database::all(
        "SELECT r.id,r.code,s.name FROM service_requests r JOIN services s ON s.id=r.service_id WHERE r.customer_id=? AND r.status IN ('COMPLETED','CLOSED') ORDER BY r.id DESC",
        [$user['id']]
    );
?>

<section class="panel">
    <h2>Hồ sơ có thể đánh giá</h2>

    <?php foreach ($eligible as $item): ?>
    <a class="commerce-receipt-line" href="<?= e(url('review-form', ['request_id' => $item['id']])) ?>"><?= e($item['code'] . ' · ' . $item['name']) ?> <span>Viết / sửa →
        </span>
    </a>
    <?php endforeach; ?>

    <?php if (!$eligible): ?>
    <p>Đánh giá sẽ mở sau khi hồ sơ được nghiệm thu.</p>
    <?php endif; ?>

</section>
<?php endif; ?>

<div class="commerce-card-grid">

    <?php foreach ($reviews as $review): ?>
    <article class="panel">
        <p class="eyebrow"><?= e($review['request_code']) ?> · <?= e($review['status']) ?></p>
        <h2><?= str_repeat('★', (int) $review['rating']) ?> <small>/ 5</small>
        </h2>
        <h3><?= e($review['service_name']) ?></h3>
        <p class="multiline"><?= e($review['content']) ?></p>

        <?php if ($review['status'] === 'PUBLISHED' && Database::one('SELECT review_id FROM review_images WHERE review_id=?', [$review['id']])): ?>
        <img class="commerce-review-photo" src="<?= e(url('review-photo', ['id' => $review['id']])) ?>"
            alt="Ảnh khách hàng tự nguyện chia sẻ">
        <?php endif; ?>

        <p><?= dt($review['created_at']) ?></p>

        <?php if ($review['reply']): ?>
        <blockquote>
            <strong>Nếp Nhà phản hồi</strong>
            <p><?= e($review['reply']) ?></p>
        </blockquote>
        <?php endif; ?>

        <?php
        if (!$customer):

            formStart('review_reply');
            hidden('id', $review['id']);
            area('reply', 'Phản hồi khách hàng', $review['reply'] ?? '');
        ?>

        <button class="button secondary">Lưu phản hồi</button>
        </form>
        <?php endif; ?>

        <?php
        if (!$customer || $review['status'] === 'PUBLISHED'):

            formStart('review_visibility');
            hidden('id', $review['id']);
            hidden('status', $review['status'] === 'PUBLISHED' ? 'HIDDEN' : 'PUBLISHED');
        ?>

        <button class="text-button"><?= $review['status'] === 'PUBLISHED' ? 'Ẩn đánh giá' : 'Hiển thị lại' ?></button>
        </form>
        <?php endif; ?>

    </article>
    <?php endforeach; ?>

</div>

<?php if (!$reviews): ?>
<section class="panel empty">Chưa có đánh giá được gửi.</section>
<?php endif; ?>
