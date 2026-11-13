<?php
[$scope, $args] = privateRequestScope($user);
if (!hasRole($user, 'CUSTOMER', 'CONSULTANT', 'ADMIN')) {
    [$scope, $args] = requestScope($user);
}
$where = $scope . " AND s.service_type='MAINTENANCE'";
$apartment = (int) ($_GET['apartment_id'] ?? 0);
if ($apartment) {
    $where .= ' AND sr.apartment_id=?';
    $args[] = $apartment;
}
$pg = max(1, (int) ($_GET['p'] ?? 1));
$from =
' FROM service_requests sr JOIN services s ON s.id=sr.service_id JOIN apartments a ON a.id=sr.apartment_id JOIN users u ON u.id=sr.customer_id WHERE ' .
$where;
$count = (int) Database::one('SELECT COUNT(*) n' . $from, $args)['n'];
$rows = Database::all(
    'SELECT sr.*,s.name service_name,s.service_type,a.name apartment_name,u.full_name customer_name' .
    $from .
    ' ORDER BY sr.id DESC LIMIT 10 OFFSET ' .
    ($pg - 1) * 10,
    $args
);
$service = Database::one(
    "SELECT id FROM services WHERE service_type='MAINTENANCE' AND is_active=1 ORDER BY id LIMIT 1"
);
heading(
    'CHĂM SÓC SAU BÀN GIAO',
    'Lịch sử bảo trì',
    'Mỗi lần kiểm tra hoặc sửa chữa có hồ sơ riêng, giữ liên kết với căn hộ và hồ sơ gốc.',
    hasRole($user, 'CUSTOMER') && $service
    ? url('request-create', ['service_id' => $service['id']])
    : null,
    '＋ Yêu cầu bảo trì'
);
?>

<section class="panel">
    <div class="panel-heading">
        <h2>Yêu cầu và lần xử lý trước đây</h2>
    </div>

    <?php
    requestTable($rows);
    pager($count, $pg, 'maintenance', $apartment ? ['apartment_id' => $apartment] : []);
    ?>

</section>
<section class="panel form-panel">
    <h2>Thông tin giúp kỹ thuật viên kiểm tra đúng hiện trạng</h2>
    <p class="muted">Ghi rõ vị trí, thời điểm phát hiện và biểu hiện khi sử dụng; bổ sung ảnh hoặc
        tài liệu liên quan. Điều kiện bảo hành và chi phí được tư vấn kiểm tra trước khi phân công.
    </p>
</section>
