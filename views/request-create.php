<?php
$apts = Database::all('SELECT id,name FROM apartments WHERE customer_id=? ORDER BY id DESC', [
        $user['id']
    ]);
    $services = Database::all('SELECT id,name FROM services WHERE is_active=1 ORDER BY id');
    $parents = Database::all(
        'SELECT sr.id,sr.code,a.name apartment_name FROM service_requests sr JOIN apartments a ON a.id=sr.apartment_id WHERE sr.customer_id=? ORDER BY sr.id DESC',
        [$user['id']]
    );
    $parentOptions = [];
    foreach ($parents as $parent) {
        $parentOptions[$parent['id']] = $parent['code'] . ' · ' . $parent['apartment_name'];
    }
    heading(
        'BẮT ĐẦU MỘT HỒ SƠ MỚI',
        'Chia sẻ nhu cầu căn hộ',
        'Thông tin càng cụ thể, tư vấn viên càng dễ chuẩn bị phương án trao đổi.'
    );
?>

<a class="back-link" href="<?= e(url('requests')) ?>">← Danh sách hồ sơ</a>
<div class="detail-grid">
    <section class="panel form-panel">

        <?php if (!$apts): ?>
        <div class="empty">
            <h2>Bạn cần khai báo căn hộ trước</h2>
            <p>Địa chỉ và diện tích sẽ được dùng để tư vấn đúng hiện trạng.</p>
            <a class="button" href="<?= e(url('apartment-form')) ?>">Thêm căn hộ ↗</a>
        </div>
        <?php else: ?>
        <?php formStart('request_create'); ?>

        <div class="form-grid">

            <?php
            choose(
                'apartment_id',
                'Căn hộ',
                array_column($apts, 'name', 'id'),
                oldValue('apartment_id', $_GET['apartment_id'] ?? '')
            );
            choose(
                'service_id',
                'Dịch vụ',
                array_column($services, 'name', 'id'),
                oldValue('service_id', $_GET['service_id'] ?? '')
            );
            field('budget', 'Ngân sách dự kiến (VND)', oldValue('budget'), 'number', false);
            field('preferred_date', 'Ngày mong muốn', oldValue('preferred_date'), 'date', false);
            ?>

        </div>

        <?php
        area('description', 'Nhu cầu và các khu vực cần thực hiện', oldValue('description'));
        choose(
            'parent_request_id',
            'Hồ sơ gốc nếu yêu cầu bảo trì',
            $parentOptions,
            oldValue('parent_request_id', $_GET['parent_request_id'] ?? ''),
            false
        );
        ?>

        <p class="file-hint">Chỉ chọn hồ sơ gốc khi dịch vụ là bảo trì và thuộc đúng căn hộ. Sau khi
            tạo hồ sơ, bạn có thể bổ sung mặt bằng hoặc ảnh hiện trạng tại trang Tài liệu.</p>
        <div class="actions">
            <button class="button" type="submit">Gửi yêu cầu dịch vụ ↗</button>
            <a class="text-link" href="<?= e(url('requests')) ?>">Quay lại</a>
        </div>
        </form>
        <?php endif; ?>

    </section>
    <aside class="panel form-panel">
        <p class="eyebrow">GỢI Ý NỘI DUNG</p>
        <h2>Hãy nói rõ điều bạn cần.</h2>
        <ul class="clean-list">
            <li>Phòng hoặc hạng mục muốn thiết kế, lắp đặt hay sửa chữa.</li>
            <li>Số người sử dụng và nhu cầu lưu trữ.</li>
            <li>Phong cách mong muốn, đồ muốn giữ lại.</li>
            <li>Thông tin liên hệ, điều kiện khảo sát của tòa nhà.</li>
        </ul>
        <a class="text-link" href="<?= e(url('catalog')) ?>">Tìm hiểu các dịch vụ ↗</a>
    </aside>
</div>
