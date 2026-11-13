<?php
$apartments = Database::all(
    'SELECT a.*,(SELECT COUNT(*) FROM service_requests sr WHERE sr.apartment_id=a.id) request_count FROM apartments a WHERE customer_id=? ORDER BY a.id DESC',
    [$user['id']]
);
heading(
    'KHÔNG GIAN CỦA RIÊNG BẠN',
    'Căn hộ của tôi',
    'Mỗi căn hộ có thông tin, hồ sơ dịch vụ và lịch sử chăm sóc riêng.',
    url('apartment-form'),
    '＋ Thêm căn hộ'
);
?>

<div class="space-intro">
    <span class="space-intro-icon"><?= icon('apartments') ?></span>
    <div>
        <strong><?= count($apartments) ?> không gian, một nơi để quản lý.</strong>
        <p>Thông tin căn hộ kết nối các lần thiết kế, lắp đặt và chăm sóc sau bàn giao.</p>
    </div>
    <a class="text-link" href="<?= e(url('maintenance')) ?>">Lịch sử chăm sóc <?= icon('arrow') ?></a>
</div>
<div class="apartment-grid">

    <?php foreach ($apartments as $a): ?>
    <article class="panel form-panel space-card">
        <a class="apartment-cover" href="<?= e(url('apartment-detail', ['id' => $a['id']])) ?>">
            <img <?= photoAttributes(apartmentPhotoKey((int) $a['id'])) ?>>
            <span><?= e($a['area_m2']) ?> m²</span>
            <small>KHÔNG GIAN <?= str_pad((string) $a['id'], 2, '0', STR_PAD_LEFT) ?> · ẢNH THAM KHẢO</small>
        </a>
        <h2>
            <a href="<?= e(url('apartment-detail', ['id' => $a['id']])) ?>"><?= e($a['name']) ?></a>
        </h2>
        <p class="muted"><?= e($a['address']) ?></p>
        <p class="space-record-count"><?= icon('requests') ?><?= (int) $a['request_count'] ?> hồ sơ dịch vụ</p>
        <div class="card-footer">
            <a class="text-link" href="<?= e(url('apartment-detail', ['id' => $a['id']])) ?>">Xem căn hộ ↗</a>
            <a class="text-link" href="<?= e(url('apartment-form', ['id' => $a['id']])) ?>">Chỉnh sửa</a>
        </div>
    </article>
    <?php endforeach; ?>

</div>

<?php if (!$apartments): ?>
<section class="panel empty">
    <h2>Thêm căn hộ đầu tiên</h2>
    <p>Thông tin căn hộ giúp bạn gửi yêu cầu và theo dõi lịch sử dịch vụ đúng địa điểm.</p>
    <a class="button" href="<?= e(url('apartment-form')) ?>">Khai báo căn hộ</a>
</section>
<?php endif; ?>
