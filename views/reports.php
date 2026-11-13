<?php
$from = (string) ($_GET['from'] ?? date('Y-m-01'));
$to = (string) ($_GET['to'] ?? date('Y-m-d'));
foreach ([$from, $to] as $date) {
    if (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
        !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))
    ) {
    throw new DomainException('Khoảng ngày không hợp lệ.');
}
}
if ($from > $to) {
    throw new DomainException('Khoảng ngày không hợp lệ.');
}
$start = (new DateTimeImmutable($from . ' 00:00:00'))
->setTimezone(new DateTimeZone('UTC'))
->format('Y-m-d H:i:s');
$end = (new DateTimeImmutable($to . ' 00:00:00'))
->modify('+1 day')
->setTimezone(new DateTimeZone('UTC'))
->format('Y-m-d H:i:s');
$statusCounts = Database::all(
    'SELECT status,COUNT(*) n FROM service_requests WHERE created_at>=? AND created_at<? GROUP BY status',
    [$start, $end]
);
$total = array_sum(array_column($statusCounts, 'n'));
$payments = Database::all(
    "SELECT p.*,sr.code FROM payments p JOIN service_requests sr ON sr.id=p.request_id WHERE p.paid_at>=? AND p.paid_at<? AND p.status='POSTED' ORDER BY p.paid_at DESC LIMIT 200",
    [$start, $end]
);
$received = (int) Database::one(
    "SELECT COALESCE(SUM(amount),0) n FROM payments WHERE paid_at>=? AND paid_at<? AND status='POSTED'",
    [$start, $end]
)['n'];
$done = (int) Database::one(
    "SELECT COUNT(DISTINCT wu.work_order_id) n FROM work_updates wu JOIN work_orders w ON w.id=wu.work_order_id WHERE wu.event_type='ACCEPTED' AND wu.created_at>=? AND wu.created_at<? AND w.status='DONE'",
    [$start, $end]
)['n'];
$services = Database::all(
    'SELECT s.name,COUNT(sr.id) n FROM services s LEFT JOIN service_requests sr ON sr.service_id=s.id AND sr.created_at>=? AND sr.created_at<? GROUP BY s.id,s.name',
    [$start, $end]
);
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">GÓC NHÌN VẬN HÀNH</p>
        <h1>Báo cáo hoạt động</h1>
        <p>Theo dõi nhu cầu dịch vụ, kết quả công việc và số tiền thực thu.</p>
    </div>
</div>
<section class="panel">
    <form class="filters" method="get">
        <input type="hidden" name="page" value="reports">
        <label>Từ
            ngày
            <input type="date" name="from" value="<?= e($from) ?>" required>
        </label>
        <label>Đến ngày
            <input type="date" name="to" value="<?= e($to) ?>" required>
        </label>
        <button class="button">Xem báo cáo</button>
    </form>
</section>
<div class="stats-grid">
    <article class="stat-card">
        <div>
            <span>Hồ sơ tạo trong kỳ</span>
            <span class="stat-icon"><?= icon('requests') ?></span>
        </div>
        <strong><?= $total ?></strong>
        <small>Theo thời điểm tạo hồ sơ</small>
    </article>
    <article class="stat-card">
        <div>
            <span>Công việc nghiệm thu</span>
            <span class="stat-icon"><?= icon('tasks') ?></span>
        </div>
        <strong><?= $done ?></strong>
        <small>Theo thời điểm xác nhận hoàn thành</small>
    </article>
    <article class="stat-card">
        <div>
            <span>Thực thu trong kỳ</span>
            <span class="stat-icon"><?= icon('payments') ?></span>
        </div>
        <strong class="money-big"><?= money($received) ?></strong>
        <small>Chỉ cộng khoản thu POSTED</small>
    </article>
    <article class="stat-card">
        <div>
            <span>Dịch vụ có yêu cầu</span>
            <span class="stat-icon"><?= icon('services') ?></span>
        </div>
        <strong><?= count(array_filter($services, fn($s) => (int) $s['n'] > 0)) ?></strong>
        <small>Theo ngày tạo yêu cầu trong kỳ</small>
    </article>
</div>
<div class="form-grid">
    <section class="panel form-panel">
        <h2>Hồ sơ theo trạng thái hiện tại</h2>
        <div class="chart-bars">

            <?php foreach ($statusCounts as $s): ?>
            <div class="bar-item">
                <span><?= e(STATUS_NAMES[$s['status']]) ?></span>
                <meter min="0" max="<?= max(1, $total) ?>" value="<?= (int) $s['n'] ?>"
                    aria-label="<?= e(STATUS_NAMES[$s['status']]) ?>"></meter>
                <strong><?= (int) $s['n'] ?></strong>
            </div>
            <?php endforeach; ?>

        </div>
    </section>
    <section class="panel form-panel">
        <h2>Nhu cầu theo dịch vụ</h2>
        <div class="chart-bars">

            <?php foreach ($services as $s): ?>
            <div class="bar-item">
                <span><?= e($s['name']) ?></span>
                <meter min="0" max="<?= max(1, $total) ?>" value="<?= (int) $s['n'] ?>"
                    aria-label="<?= e($s['name']) ?>"></meter>
                <strong><?= (int) $s['n'] ?></strong>
            </div>
            <?php endforeach; ?>

        </div>
    </section>
</div>
<section class="panel">
    <div class="panel-heading">
        <h2>Chi tiết khoản thu trong kỳ</h2>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Chứng từ</th>
                    <th>Hồ sơ</th>
                    <th>Ngày thu</th>
                    <th>Phương thức</th>
                    <th>Số tiền</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= e($p['receipt_code']) ?></td>
                    <td>
                        <a class="text-link" href="<?= e(url('request', ['id' => $p['request_id']])) ?>"><?= e($p['code']) ?></a>
                    </td>
                    <td><?= e(dt($p['paid_at'])) ?></td>
                    <td><?= $p['method'] === 'CASH' ? 'Tiền mặt' : 'Chuyển khoản' ?></td>
                    <td><?= money($p['amount']) ?></td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

    <?php if (!$payments): ?>
    <div class="empty">
        <h3>Chưa có khoản thu trong kỳ</h3>
    </div>
    <?php endif; ?>

</section>
<p class="muted">Thực thu là dòng tiền đã ghi nhận, không phải lợi nhuận. Bảng chi tiết hiển thị tối
    đa 200 lần thu; tổng thực thu vẫn tính trên toàn bộ kỳ.</p>
