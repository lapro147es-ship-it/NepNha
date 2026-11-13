<?php
$a = apartmentAccess($user, (int) ($_GET['id'] ?? 0));
$requests = Database::all(
    'SELECT sr.*,a.name apartment_name,s.name service_name,s.service_type,u.full_name customer_name FROM service_requests sr JOIN apartments a ON a.id=sr.apartment_id JOIN services s ON s.id=sr.service_id JOIN users u ON u.id=sr.customer_id WHERE sr.apartment_id=? AND sr.customer_id=? ORDER BY sr.id DESC',
    [$a['id'], $user['id']]
);
heading(
    'CĂN HỘ CỦA TÔI',
    $a['name'],
    $a['address'],
    url('apartment-form', ['id' => $a['id']]),
    'Chỉnh sửa thông tin'
);
?>

<a class="back-link" href="<?= e(url('apartments')) ?>">← Tất cả căn hộ</a>

<?php if (!$requests): ?>
<section class="panel">

    <?php
    formStart('apartment_remove');
    hidden('id', $a['id']);
    ?>

    <p>Căn hộ chưa có hồ sơ có thể xóa. Căn hộ đã phát sinh dịch vụ sẽ được giữ để bảo toàn lịch sử.
    </p>
    <button class="text-button">Xóa căn hộ chưa sử dụng</button>
    </form>
</section>
<?php endif; ?>

<div class="detail-grid">
    <section class="panel form-panel">
        <img class="detail-cover" <?= photoAttributes(apartmentPhotoKey((int) $a['id']), true) ?>>
        <p class="photo-disclosure">Ảnh tham khảo, không phải ảnh hiện trạng của căn hộ.</p>
        <dl class="info-grid">
            <div>
                <dt>Diện tích</dt>
                <dd><?= e($a['area_m2']) ?> m²</dd>
            </div>
            <div>
                <dt>Ngày khai báo</dt>
                <dd><?= e(dt($a['created_at'])) ?></dd>
            </div>
        </dl>
        <h2>Ghi chú</h2>
        <p><?= nl2br(e($a['notes'] ?: 'Chưa có ghi chú bổ sung.')) ?></p>
    </section>
    <aside class="panel form-panel">
        <p class="eyebrow">HÀNH TRÌNH KHÔNG GIAN</p>
        <h2><?= count($requests) ?> hồ sơ dịch vụ</h2>
        <p class="muted">Các yêu cầu thiết kế, lắp đặt và bảo trì được lưu theo đúng căn hộ này.</p>
        <a class="button full" href="<?= e(url('request-create', ['apartment_id' => $a['id']])) ?>">Tạo yêu cầu cho căn hộ ↗</a>
        <a class="text-link" href="<?= e(url('maintenance', ['apartment_id' => $a['id']])) ?>">Xem lịch sử bảo trì</a>
    </aside>
</div>
<section class="panel">
    <div class="panel-heading">
        <h2>Hồ sơ của căn hộ</h2>
    </div>

    <?php requestTable($requests); ?>

</section>
