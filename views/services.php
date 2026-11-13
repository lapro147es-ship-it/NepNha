<?php
$isAdmin = hasRole($user, 'ADMIN');
$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$where = $isAdmin ? '1=1' : 'is_active=1';
$args = [];
if ($search !== '') {
    $where .= ' AND (name LIKE ? OR code LIKE ?)';
    array_push($args, '%' . $search . '%', '%' . $search . '%');
}
$services = Database::all('SELECT * FROM services WHERE ' . $where . ' ORDER BY id', $args);
heading(
    'DANH MỤC DỊCH VỤ',
    'Giải pháp nội thất',
    'Mở từng dịch vụ để xem phạm vi, kết quả bàn giao và quy trình thực hiện.',
    $isAdmin ? url('service-form') : url('catalog'),
    $isAdmin ? '＋ Thêm dịch vụ' : 'Khám phá giải pháp ↗'
);
?>

<section class="panel">
    <form class="filters" method="get">
        <input type="hidden" name="page" value="services">
        <input name="q" value="<?= e($search) ?>" placeholder="Tìm tên hoặc mã dịch vụ"
            aria-label="Tìm dịch vụ">
        <button class="button secondary">Tìm kiếm</button>
    </form>
</section>
<div class="service-grid catalog-grid">

    <?php
    foreach ($services as $s):
        $copy = serviceContent($s['service_type']);
    ?>

    <article class="service-card solution-card solution-<?= e(strtolower($s['service_type'])) ?>">
        <a class="service-photo" href="<?= e(url('service-detail', ['id' => $s['id']])) ?>">
            <img <?= photoAttributes(servicePhotoKey($s['service_type'])) ?>>
            <span>ẢNH THAM KHẢO</span>
        </a>
        <span class="solution-icon"><?= icon(['DESIGN' => 'designs', 'INSTALLATION' => 'tasks', 'MAINTENANCE' => 'maintenance', 'PACKAGE' => 'services'][$s['service_type']]) ?></span>
        <span class="service-number"><?= e($s['code']) ?> · <?= e(TYPE_NAMES[$s['service_type']]) ?></span>
        <h2><?= e($s['name']) ?></h2>
        <p><?= e($copy['subtitle']) ?></p>
        <p><?= e($s['description']) ?></p>
        <ul class="solution-deliverables">

            <?php foreach (array_slice($copy['deliverables'], 0, 2) as $deliverable): ?>
            <li><?= e($deliverable) ?></li>
            <?php endforeach; ?>

        </ul>

        <?php if (!$s['is_active']): ?>
        <span class="badge status-cancelled">Ngừng cung cấp</span>
        <?php endif; ?>

        <div>
            <span>Giá tham khảo<strong><?= money($s['reference_price']) ?></strong>
            </span>
            <a class="service-open" href="<?= e(url('service-detail', ['id' => $s['id']])) ?>">Xem chi tiết ↗</a>
        </div>

        <?php if ($isAdmin): ?>
        <a class="text-link card-edit" href="<?= e(url('service-form', ['id' => $s['id']])) ?>">Chỉnh sửa dịch vụ</a>
        <?php endif; ?>

    </article>
    <?php endforeach; ?>

</div>

<?php if (!$services): ?>
<section class="panel empty">
    <h2>Không tìm thấy dịch vụ</h2>
    <p>Thay đổi từ khóa để xem các giải pháp đang có.</p>
</section>
<?php endif; ?>
