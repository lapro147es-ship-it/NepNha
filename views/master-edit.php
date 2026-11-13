<?php
$kind = is_string($_GET['kind'] ?? null) ? $_GET['kind'] : 'categories';
$meta = MasterData::kind($kind);
$id = (int) ($_GET['id'] ?? 0);
$row = $id ? Database::one('SELECT * FROM ' . $meta['table'] . ' WHERE id=?', [$id]) : [];
if ($id && !$row) {
    throw new DomainException('Không tìm thấy dữ liệu.');
}
$value = static fn(string $key, mixed $default = ''): mixed => oldValue(
    $key,
    $row[$key] ?? $default
);
heading(
    'DỮ LIỆU CÓ THỂ TÁI SỬ DỤNG',
    ($id ? 'Chỉnh sửa · ' : 'Thêm · ') . $meta['title'],
    'Giá gói là giá tham khảo; báo giá từng hồ sơ mới là căn cứ chấp thuận.'
);
?>

<section class="panel form-panel">

    <?php
    formStart('master_save');
    hidden('kind', $kind);
    hidden('id', $id);
    field(
        $kind === 'vouchers' ? 'code' : 'name',
        $kind === 'vouchers' ? 'Mã ưu đãi' : 'Tên',
        $value($kind === 'vouchers' ? 'code' : 'name')
    );
    if ($kind !== 'campaigns') {
        area('description', 'Mô tả', $value('description'));
    }
    if (in_array($kind, ['variants', 'campaigns'], true)) {
        choose(
            'service_id',
            'Dịch vụ' . ($kind === 'campaigns' ? ' (để trống: tất cả)' : ''),
            array_column(
                Database::all('SELECT id,name FROM services WHERE is_active=1'),
                'name',
                'id'
            ),
            $value('service_id'),
            $kind === 'variants'
        );
    }
    if ($kind === 'variants') {
        choose(
            'category_id',
            'Nhóm giải pháp',
            array_column(
                Database::all('SELECT id,name FROM service_categories WHERE is_active=1'),
                'name',
                'id'
            ),
            $value('category_id')
        );
        choose(
            'supplier_id',
            'Nhà cung cấp (không bắt buộc)',
            array_column(
                Database::all('SELECT id,name FROM material_suppliers WHERE is_active=1'),
                'name',
                'id'
            ),
            $value('supplier_id'),
            false
        );
        field('unit', 'Đơn vị', $value('unit', 'Gói'));
        field('unit_price', 'Giá tham khảo (đồng)', $value('unit_price', 0), 'number');
    }
    if ($kind === 'tiers') {
        field('min_spend', 'Ngưỡng tổng thực thu (đồng)', $value('min_spend', 0), 'number');
    }
    if (in_array($kind, ['campaigns', 'vouchers'], true)) {
        choose(
            'discount_kind',
            'Cách giảm',
            ['PERCENT' => 'Phần trăm', 'FIXED' => 'Số tiền cố định'],
            $value('discount_kind', 'PERCENT')
        );
        field(
            'discount_value',
            'Giá trị giảm (% hoặc đồng)',
            $value('discount_value', 5),
            'number'
        );
        field('max_discount', 'Mức giảm tối đa (đồng)', $value('max_discount', 500000), 'number');
        foreach (['starts_at' => 'Bắt đầu', 'ends_at' => 'Kết thúc'] as $key => $label) {
            $raw =
            $row[$key] ?? gmdate('Y-m-d H:i:s', time() + ($key === 'ends_at' ? 30 * 86400 : 0));
            $local = (new DateTimeImmutable($raw, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))
            ->format('Y-m-d\TH:i');
            field($key, $label, oldValue($key, $local), 'datetime-local');
        }
        if ($kind === 'vouchers') {
            field(
                'min_total',
                'Giá trị tối thiểu sau ưu đãi chương trình (đồng)',
                $value('min_total', 0),
                'number'
            );
            field('max_uses', 'Tổng lượt sử dụng', $value('max_uses', 100), 'number');
            field('per_user_limit', 'Lượt tối đa mỗi khách', $value('per_user_limit', 1), 'number');
            choose(
                'required_tier_id',
                'Hạng tối thiểu (không bắt buộc)',
                array_column(
                    Database::all('SELECT id,name FROM membership_tiers WHERE is_active=1'),
                    'name',
                    'id'
                ),
                $value('required_tier_id'),
                false
            );
        }
    }
    ?>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" <?= $value('is_active', 1) ? 'checked' : '' ?>> Đang sử dụng
    </label>
    <div class="actions">
        <button class="button">Lưu dữ liệu</button>
        <a class="button secondary" href="<?= e(url('master-data', ['kind' => $kind])) ?>">Quay lại</a>
    </div>
    </form>
</section>
