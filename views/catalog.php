<?php
$type = is_string($_GET['type'] ?? null) ? $_GET['type'] : '';
$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$where = 'is_active=1';
$args = [];
if ($type !== '') {
    $where .= ' AND service_type=?';
    $args[] = $type;
}
if ($search !== '') {
    $where .= ' AND (name LIKE ? OR description LIKE ?)';
    array_push($args, '%' . $search . '%', '%' . $search . '%');
}
$services = catalogResults($_GET);
?>

<section class="editorial-hero">
    <p class="eyebrow">THIẾT KẾ · LẮP ĐẶT · CHĂM SÓC</p>
    <h1>Mỗi giai đoạn của căn hộ,
        <br>một giải pháp phù hợp.
    </h1>
    <p>Tìm hiểu phạm vi công việc, tài liệu bàn giao và những điều cần chuẩn bị trước khi bắt đầu.
        Mỗi yêu cầu được tư vấn theo hiện trạng và ngân sách của bạn.</p>
</section>

<section class="public-section">
    <form class="filters catalog-filters" method="get">
        <input type="hidden" name="page" value="catalog">
        <input name="q" value="<?= e($search) ?>" placeholder="Tìm dịch vụ theo nhu cầu"
            aria-label="Tìm dịch vụ">
        <select name="type" aria-label="Loại dịch vụ">
            <option value="">Tất cả giải pháp</option>

            <?php foreach (TYPE_NAMES as $code => $name): ?>
            <option value="<?= e($code) ?>" <?= $type === $code ? 'selected' : '' ?>><?= e($name) ?></option>
            <?php endforeach; ?>

        </select>
        <button class="button" type="submit">Tìm dịch vụ</button>
        <a class="text-link" href="<?= e(url('catalog')) ?>">Đặt lại</a>
    </form>

    <form class="commerce-filter-grid" method="get">
        <input type="hidden" name="page" value="catalog">
        <input type="hidden" name="q" value="<?= e($search) ?>">
        <input type="hidden" name="type" value="<?= e($type) ?>">

        <?php foreach ([
                       'category' => ['Nhóm giải pháp', 'service_categories'],
                       'supplier' => ['Nhà cung cấp', 'material_suppliers']
                   ]
                   as $key => [$label, $table]): ?>

        <label><?= e($label) ?><select name="<?= e($key) ?>">
                <option value="">Tất cả</option>

                <?php foreach (Database::all('SELECT id,name FROM ' . $table . ' WHERE is_active=1') as $option): ?>
                <option value="<?= (int) $option['id'] ?>" <?= (int) ($_GET[$key] ?? 0) === (int) $option['id'] ? 'selected' : '' ?>><?= e($option['name']) ?></option>
                <?php endforeach; ?>

            </select>
        </label>
        <?php endforeach; ?>

        <?php foreach (['min_price' => 'Giá gói từ (đồng)', 'max_price' => 'Giá gói đến (đồng)'] as $key => $label): ?>
        <label><?= e($label) ?>
            <input type="number" min="0" name="<?= e($key) ?>" value="<?= e(is_scalar($_GET[$key] ?? '') ? $_GET[$key] ?? '' : '') ?>">
        </label>
        <?php endforeach; ?>

        <label>Đánh giá tối thiểu<select name="rating">
                <option value="">Tất cả</option>

                <?php foreach ([5, 4, 3, 2, 1] as $score): ?>
                <option value="<?= $score ?>" <?= (int) ($_GET['rating'] ?? 0) === $score ? 'selected' : '' ?>><?= $score ?> sao</option>
                <?php endforeach; ?>

            </select>
        </label>
        <label>Sắp xếp<select name="sort">

                <?php foreach ([
                                       'default' => 'Đề xuất',
                                       'price_asc' => 'Giá tăng dần',
                                       'price_desc' => 'Giá giảm dần',
                                       'newest' => 'Mới nhất'
                                   ]
                                   as $code => $label): ?>

                <option value="<?= e($code) ?>" <?= ($_GET['sort'] ?? '') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>

            </select>
        </label>
        <label class="check">
            <input type="checkbox" name="promotion" value="1" <?= ($_GET['promotion'] ?? '') === '1' ? 'checked' : '' ?>> Có chương trình ưu
            đãi
        </label>
        <button class="button">Áp dụng bộ lọc</button>
    </form>
    <p><?= count($services) ?> giải pháp phù hợp · Lọc giá theo gói dịch vụ đang cung cấp.</p>
    <div class="service-grid catalog-grid">

        <?php
        foreach ($services as $service):
            $copy = serviceContent($service['service_type']);
        ?>

        <article class="service-card expanded-card">
            <a class="service-photo" href="<?= e(url('service-detail', ['id' => $service['id']])) ?>">
                <img <?= photoAttributes(servicePhotoKey($service['service_type'])) ?>>
                <span>ẢNH THAM KHẢO</span>
            </a>
            <span class="service-number"><?= e(TYPE_NAMES[$service['service_type']]) ?></span>
            <h2><?= e($service['name']) ?></h2>
            <p><?= e($copy['subtitle']) ?></p>
            <ul class="clean-list">

                <?php foreach (array_slice($copy['deliverables'], 0, 3) as $item): ?>
                <li><?= e($item) ?></li>
                <?php endforeach; ?>

            </ul>
            <div>
                <span>Giá tham khảo<strong><?= money($service['reference_price']) ?></strong>
                </span>
                <a class="service-open" href="<?= e(url('service-detail', ['id' => $service['id']])) ?>">Xem chi tiết ↗</a>
            </div>
        </article>
        <?php endforeach; ?>

    </div>

    <?php if (!$services): ?>
    <div class="empty">
        <h2>Chưa có dịch vụ phù hợp</h2>
        <p>Thử thay đổi từ khóa hoặc loại dịch vụ.</p>
    </div>
    <?php endif; ?>

    <div class="editorial-callout">
        <h2>Chưa biết nên bắt đầu từ đâu?</h2>
        <p>Chuẩn bị thông tin căn hộ và các khu vực bạn muốn thay đổi. Tư vấn viên sẽ cùng làm rõ
            phạm vi phù hợp.</p>
        <a class="button" href="<?= e(url('contact')) ?>">Xem cách gửi yêu cầu ↗</a>
    </div>
</section>
