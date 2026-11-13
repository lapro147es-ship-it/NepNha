<?php
$service = Database::one('SELECT * FROM services WHERE id=?', [(int) ($_GET['id'] ?? 0)]);
if (!$service || (!$service['is_active'] && (!$user || !hasRole($user, 'ADMIN')))) {
    throw new DomainException('Dịch vụ không còn được cung cấp hoặc không tồn tại.');
}
$copy = serviceContent($service['service_type']);
?>

<section class="public-section service-detail-hero">
    <div>
        <a class="back-link" href="<?= e(url($user ? 'services' : 'catalog')) ?>">← Danh sách dịch vụ</a>
        <p class="eyebrow"><?= e(TYPE_NAMES[$service['service_type']]) ?> / NẾP NHÀ</p>
        <h1><?= e($service['name']) ?></h1>
        <p class="lead"><?= e($copy['subtitle']) ?></p>
        <p><?= e($service['description']) ?></p>
        <p class="reference-price">Giá tham khảo <strong><?= money($service['reference_price']) ?></strong>
        </p>
        <div class="actions">

            <?php if ($service['is_active']): ?>
            <a class="button" href="<?= e(url('request-create', ['service_id' => $service['id']])) ?>">Gửi yêu cầu dịch vụ ↗</a>
            <?php endif; ?>

            <?php if ($user && hasRole($user, 'ADMIN')): ?>
            <a class="button secondary" href="<?= e(url('service-form', ['id' => $service['id']])) ?>">Chỉnh sửa dịch vụ</a>
            <?php endif; ?>

        </div>
    </div>
    <img <?= photoAttributes(servicePhotoKey($service['service_type']), true) ?>>
</section>

<section class="public-section commerce-variants">
    <p class="eyebrow">LỰA CHỌN THEO NHU CẦU</p>
    <h2>Các gói có thể bắt đầu</h2>
    <p>Giá tham khảo theo gói, sẽ được làm rõ qua khảo sát và báo giá chi tiết.</p>
    <div class="commerce-card-grid">

        <?php
        $variants = Database::all(
            'SELECT v.*,c.name category_name,p.name supplier_name FROM service_variants v JOIN service_categories c ON c.id=v.category_id LEFT JOIN material_suppliers p ON p.id=v.supplier_id WHERE v.service_id=? AND v.is_active=1 AND c.is_active=1 AND (p.id IS NULL OR p.is_active=1) ORDER BY v.unit_price',
            [$service['id']]
        );
        foreach ($variants as $variant):
        ?>

        <article class="panel">
            <p class="eyebrow"><?= e($variant['category_name']) ?></p>
            <h2><?= e($variant['name']) ?></h2>
            <p><?= e($variant['description']) ?></p>
            <p>Đơn vị cung cấp: <?= e($variant['supplier_name'] ?: 'Sẽ xác định theo hồ sơ') ?></p>
            <strong><?= money($variant['unit_price']) ?> / <?= e($variant['unit']) ?></strong>

            <?php
            $campaign = activeCampaign((int) $service['id'], (int) $variant['unit_price']);
            if ($campaign):
            ?>

            <p class="commerce-success"><?= e($campaign['name']) ?> · Giảm dự kiến <?= money($campaign['discount']) ?></p>
            <?php endif; ?>

            <?php
            if ($user && hasRole($user, 'CUSTOMER')):

                formStart('cart_add');
                hidden('variant_id', $variant['id']);
                field('quantity', 'Số lượng', 1, 'number');
            ?>

            <button class="button full">Thêm vào lựa chọn +</button>
            </form>
            <a class="text-link" href="<?= e(url('request-create', ['service_id' => $service['id']])) ?>">Gửi yêu cầu ngay →</a>
            <?php else: ?>
            <div class="actions">
                <a class="button" href="<?= e(url('booking', ['service_id' => $service['id']])) ?>">Đăng ký khảo sát ngay</a>
                <a class="text-link" href="<?= e(url('login')) ?>">Đăng nhập để chọn gói →</a>
            </div>
            <?php endif; ?>

        </article>
        <?php endforeach; ?>

    </div>
    <a class="text-link" href="<?= e(url('assistant')) ?>">Cần gợi ý? Hỏi trợ lý tư vấn →</a>
</section>
<section class="public-section">
    <p class="eyebrow">SAU KHI NGHIỆM THU</p>
    <h2>Trải nghiệm khách hàng</h2>

    <?php
    $feedback = Database::all(
        "SELECT v.*,u.full_name FROM service_reviews v JOIN users u ON u.id=v.customer_id WHERE v.service_id=? AND v.status='PUBLISHED' ORDER BY v.id DESC LIMIT 12",
        [$service['id']]
    );
    ?>

    <div class="commerce-card-grid">

        <?php
        foreach ($feedback as $review):
            $parts = preg_split('/\s+/u', trim($review['full_name']));
        ?>

        <article class="panel">
            <p class="commerce-rating"><?= str_repeat('★', (int) $review['rating']) ?> / 5</p>
            <h3>Khách hàng <?= e(end($parts)) ?></h3>
            <p class="multiline"><?= e($review['content']) ?></p>
            <p><?= dt($review['created_at']) ?></p>

            <?php if (Database::one('SELECT review_id FROM review_images WHERE review_id=?', [ $review['id'] ])): ?>
            <img class="commerce-review-photo" src="<?= e(url('review-photo', ['id' => $review['id']])) ?>"
                alt="Ảnh khách hàng tự nguyện chia sẻ">
            <?php endif; ?>

            <?php if ($review['reply']): ?>
            <blockquote>Nếp Nhà: <?= e($review['reply']) ?></blockquote>
            <?php endif; ?>

        </article>
        <?php endforeach; ?>

    </div>

    <?php if (!$feedback): ?>
    <p>Chưa có đánh giá công khai cho dịch vụ này.</p>
    <?php endif; ?>

</section>
<section class="public-section editorial-content">
    <p class="eyebrow">HIỂU RÕ TRƯỚC KHI BẮT ĐẦU</p>
    <h2>Phạm vi được xác định từ nhu cầu thực tế.</h2>
    <p><?= e($copy['intro']) ?></p>
    <div class="editorial-columns">
        <article>
            <h2>Bạn sẽ nhận được gì?</h2>
            <ul class="clean-list">

                <?php foreach ($copy['deliverables'] as $item): ?>
                <li><?= e($item) ?></li>
                <?php endforeach; ?>

            </ul>
        </article>
        <article>
            <h2>Nên chuẩn bị trước</h2>
            <ul class="clean-list">

                <?php foreach ($copy['prepare'] as $item): ?>
                <li><?= e($item) ?></li>
                <?php endforeach; ?>

            </ul>
        </article>
    </div>
    <h2>Các bước thực hiện</h2>
    <ol class="numbered-steps">

        <?php foreach ($copy['steps'] as $step): ?>
        <li><?= e($step) ?></li>
        <?php endforeach; ?>

    </ol>
    <p class="service-note"><?= e($copy['note']) ?></p>
    <div class="actions">
        <a class="button" href="<?= e(url('request-create', ['service_id' => $service['id']])) ?>">Bắt đầu với căn hộ của bạn ↗</a>
        <a class="text-link" href="<?= e(url('faq')) ?>">Xem câu hỏi thường gặp</a>
    </div>
</section>
