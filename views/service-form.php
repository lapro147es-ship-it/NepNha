<?php
$id = (int) ($_GET['id'] ?? 0);
$s = $id ? Database::one('SELECT * FROM services WHERE id=?', [$id]) : [];
if ($id && !$s) {
    throw new DomainException('Không tìm thấy dịch vụ.');
}
heading(
    'QUẢN LÝ DANH MỤC',
    $id ? 'Chỉnh sửa dịch vụ' : 'Tạo dịch vụ mới',
    'Giá danh mục là tham khảo; báo giá được chấp thuận mới xác định chi phí hồ sơ.'
);
?>

<a class="back-link" href="<?= e(url('services')) ?>">← Danh mục dịch vụ</a>
<div class="guided-form-layout">
    <section class="panel form-panel content-width">

        <?php
        formStart('service_save');
        hidden('id', $id);
        ?>

        <div class="form-grid">

            <?php
            field('code', 'Mã dịch vụ', oldValue('code', $s['code'] ?? ''));
            field('name', 'Tên dịch vụ', oldValue('name', $s['name'] ?? ''));
            choose(
                'service_type',
                'Loại dịch vụ',
                TYPE_NAMES,
                oldValue('service_type', $s['service_type'] ?? '')
            );
            field(
                'reference_price',
                'Giá tham khảo (VND)',
                oldValue('reference_price', $s['reference_price'] ?? 0),
                'number'
            );
            ?>

        </div>

        <?php
        area('description', 'Mô tả dịch vụ', oldValue('description', $s['description'] ?? ''));
        $active =
        ($old['action'] ?? '') === 'service_save'
        ? isset($old['is_active'])
        : !$id || $s['is_active'];
        ?>

        <label class="checkboxes">
            <input name="is_active" type="checkbox" <?= $active ? 'checked' : '' ?>> Đang nhận yêu cầu
        </label>
        <p class="file-hint">Không thay đổi loại dịch vụ đã được dùng trong hồ sơ. Ngừng cung cấp sẽ
            ẩn
            dịch vụ khỏi danh mục công khai và giữ dữ liệu cũ.</p>
        <button class="button" type="submit">Lưu dịch vụ</button>
        </form>
    </section>

    <?php
    formGuide(
        'Phạm vi rõ ràng từ danh mục.',
        [
            'Tên và mô tả cần thể hiện đúng dịch vụ.',
            'Giá danh mục chỉ là tham khảo; báo giá được lập theo hồ sơ.',
            'Ngừng cung cấp để giữ lịch sử thay vì xóa dịch vụ đã dùng.'
        ],
        'services'
    );
    ?>

</div>
