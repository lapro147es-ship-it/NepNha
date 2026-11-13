<?php
$from = (string) ($_GET['from'] ?? date('Y-m-d'));
$to = (string) ($_GET['to'] ?? date('Y-m-d', strtotime('+30 days')));
foreach ([$from, $to] as $date) {
    if (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
        !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))
    ) {
    throw new DomainException('Khoảng ngày không hợp lệ.');
}
}
if ($from > $to) {
    throw new DomainException('Ngày kết thúc phải sau ngày bắt đầu.');
}
$start = (new DateTimeImmutable($from . ' 00:00:00'))
->setTimezone(new DateTimeZone('UTC'))
->format('Y-m-d H:i:s');
$end = (new DateTimeImmutable($to . ' 00:00:00'))
->modify('+1 day')
->setTimezone(new DateTimeZone('UTC'))
->format('Y-m-d H:i:s');
$scope = hasRole($user, 'CONSULTANT')
? '(w.assignee_id=? OR sr.consultant_id=?)'
: 'w.assignee_id=?';
$params = hasRole($user, 'CONSULTANT') ? [$user['id'], $user['id']] : [$user['id']];
array_push($params, $end, $start);
$events = Database::all(
    "SELECT w.id,w.title,w.starts_at,w.ends_at,w.status,w.request_id,u.full_name assignee_name FROM work_orders w JOIN service_requests sr ON sr.id=w.request_id JOIN users u ON u.id=w.assignee_id WHERE $scope AND w.starts_at < ? AND w.ends_at > ? AND w.status<>'CANCELLED' ORDER BY w.starts_at LIMIT 200",
    $params
);
if (hasRole($user, 'CONSULTANT')) {
    $surveys = Database::all(
        "SELECT s.*,sr.code,u.full_name surveyor_name FROM surveys s JOIN service_requests sr ON sr.id=s.request_id JOIN users u ON u.id=s.surveyor_id WHERE (s.surveyor_id=? OR sr.consultant_id=?) AND s.starts_at < ? AND s.ends_at > ? AND s.status<>'CANCELLED' LIMIT 200",
        [$user['id'], $user['id'], $end, $start]
    );
    foreach ($surveys as $s) {
        $events[] = [
            'id' => $s['id'],
            'kind' => 'survey',
            'title' => 'Khảo sát · ' . $s['code'],
            'starts_at' => $s['starts_at'],
            'ends_at' => $s['ends_at'],
            'status' => $s['status'],
            'request_id' => $s['request_id'],
            'assignee_name' => $s['surveyor_name']
        ];
    }
    usort($events, fn($a, $b) => strcmp($a['starts_at'], $b['starts_at']));
}
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">SẮP XẾP ĐỂ MỌI VIỆC NHỊP NHÀNG</p>
        <h1>Lịch làm việc</h1>
        <p>Lịch khảo sát và công việc theo khoảng ngày bạn chọn.</p>
    </div>
</div>
<div class="schedule-caption">
    <div>
        <span class="eyebrow">NHỊP LÀM VIỆC</span>
        <h2><?= count($events) ?> lịch trong khoảng đã chọn</h2>
    </div>
    <span><?= e($from) ?> — <?= e($to) ?></span>
</div>
<section class="panel agenda-panel">
    <form class="filters" method="get">
        <input type="hidden" name="page" value="schedule">
        <label>Từ
            ngày
            <input type="date" name="from" value="<?= e($from) ?>" required>
        </label>
        <label>Đến ngày
            <input type="date" name="to" value="<?= e($to) ?>" required>
        </label>
        <button class="button secondary">Xem lịch</button>
    </form>

    <?php foreach ($events as $event): ?>
    <article class="schedule-item agenda-event">
        <div class="agenda-event-symbol"><?= icon(($event['kind'] ?? '') === 'survey' ? 'surveys' : 'tasks') ?></div>
        <div class="schedule-date">
            <strong><?= e(substr(dt($event['starts_at']), 0, 10)) ?></strong>
            <small><?= e(substr(dt($event['starts_at']), 11)) ?> – <?= e(substr(dt($event['ends_at']), 11)) ?></small>
        </div>
        <div>
            <strong><?= e($event['title']) ?></strong>
            <p class="muted"><?= e($event['assignee_name']) ?> · <?= badge($event['status']) ?></p>
        </div>
        <a class="text-link" href="<?= e(url(($event['kind'] ?? '') === 'survey' ? 'survey-detail' : 'task-detail', ['id' => $event['id']])) ?>">Xem chi tiết ↗</a>
    </article>

    <?php
    endforeach;
    if (!$events):
    ?>

    <div class="empty">
        <h3>Lịch trống trong khoảng ngày này</h3>
        <p>Thay đổi khoảng ngày để xem thêm công việc.</p>
    </div>
    <?php endif; ?>

</section>
<p class="muted">Hiển thị tối đa 200 công việc và 200 lịch khảo sát mỗi kỳ; thu hẹp khoảng ngày để
    tra cứu cụ thể.</p>
