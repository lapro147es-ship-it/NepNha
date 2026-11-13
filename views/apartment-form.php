<?php
$id = (int) ($_GET['id'] ?? 0);
$a = $id ? apartmentAccess($user, $id) : [];
heading(
    'THÔNG TIN KHÔNG GIAN',
    $id ? 'Chỉnh sửa căn hộ' : 'Thêm căn hộ mới',
    'Dùng tên gợi nhớ để dễ phân biệt khi quản lý nhiều căn hộ.'
);
?>

<a class="back-link" href="<?= e(url($id ? 'apartment-detail' : 'apartments', $id ? ['id' => $id] : [])) ?>">← Quay lại căn hộ</a>
<div class="guided-form-layout">
    <section class="panel form-panel content-width">

        <?php
        formStart('apartment_save');
        hidden('id', $id);
        ?>

        <div class="form-grid">

            <?php
            field('name', 'Tên gợi nhớ', oldValue('name', $a['name'] ?? ''));
            field('area_m2', 'Diện tích (m²)', oldValue('area_m2', $a['area_m2'] ?? ''), 'number');
            ?>

        </div>

        <?php
        field('address', 'Địa chỉ đầy đủ', oldValue('address', $a['address'] ?? ''));
        area('notes', 'Ghi chú về căn hộ', oldValue('notes', $a['notes'] ?? ''), false);
        ?>

        <p class="file-hint">Nếu căn hộ có hồ sơ đang xử lý, việc thay đổi địa chỉ được chặn để
            không
            làm sai lịch khảo sát hoặc lắp đặt.</p>
        <button class="button" type="submit">Lưu thông tin căn hộ</button>
        </form>
    </section>

    <?php
    formGuide(
        'Một hồ sơ rõ, nhiều lần đồng hành.',
        [
            'Đặt tên dễ phân biệt giữa các căn hộ.',
            'Địa chỉ và diện tích giúp tư vấn chuẩn bị đúng hiện trạng.',
            'Ghi chú điều kiện tòa nhà và mong muốn riêng.'
        ],
        'apartments'
    );
    ?>

</div>
