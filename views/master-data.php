<?php
$kind = is_string($_GET['kind'] ?? null) ? $_GET['kind'] : 'categories';
$meta = MasterData::kind($kind);
heading(
    'VẬN HÀNH GIẢI PHÁP',
    $meta['title'],
    'Quản lý dữ liệu dùng trong tư vấn và báo giá. Ngừng sử dụng giữ lại lịch sử hồ sơ.',
    url('master-edit', ['kind' => $kind]),
    'Thêm mới'
);
?>

<nav class="commerce-tabs">

    <?php foreach (MasterData::KINDS as $key => $tab): ?>
    <a class="<?= $key === $kind ? 'active' : '' ?>" href="<?= e(url('master-data', ['kind' => $key])) ?>"><?= e($tab['title']) ?></a>
    <?php endforeach; ?>

</nav>

<?php
$rows = Database::all(
    'SELECT * FROM ' . $meta['table'] . ' ORDER BY is_active DESC,id DESC'
);
?>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tên / mã</th>
                    <th>Thông tin</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <strong><?= e($row['name'] ?? $row['code']) ?></strong>
                    </td>
                    <td><?= e($row['description'] ?? '') ?>

                        <?php if (isset($row['unit_price'])): ?>
                        <p><?= money($row['unit_price']) ?> / <?= e($row['unit']) ?></p>
                        <?php endif; ?>

                        <?php if (isset($row['min_spend'])): ?>
                        <p>Từ <?= money($row['min_spend']) ?> thực thu</p>
                        <?php endif; ?>

                        <?php if (isset($row['discount_kind'])): ?>
                        <p>Giảm <?= $row['discount_kind'] === 'PERCENT' ? (int) $row['discount_value'] . '%' : money($row['discount_value']) ?>, tối đa <?= money($row['max_discount']) ?>
                            <br><?= dt($row['starts_at']) ?> → <?= dt($row['ends_at']) ?>
                        </p>
                        <?php endif; ?>

                    </td>
                    <td><?= $row['is_active'] ? 'Đang sử dụng' : 'Đã ngừng' ?></td>
                    <td>
                        <a class="text-link" href="<?= e(url('master-edit', ['kind' => $kind, 'id' => $row['id']])) ?>">Chỉnh sửa</a>

                        <?php
                        if ($row['is_active']):

                            formStart('master_archive');
                            hidden('kind', $kind);
                            hidden('id', $row['id']);
                        ?>

                        <button class="text-button">Ngừng sử dụng</button>
                        </form>
                        <?php endif; ?>

                    </td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

    <?php if (!$rows): ?>
    <p class="empty">Chưa có dữ liệu. Thêm mới để bắt đầu.</p>
    <?php endif; ?>

</section>
