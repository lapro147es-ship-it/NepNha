<?php
$r = requestAccess($user, (int) ($_GET['id'] ?? 0));
require __DIR__ . '/partials/request-header.php';
$where = 'a.request_id=?';
$args = [$rid];
if (!$private) {
    $where .=
    " AND (a.entity_type='request' OR (a.entity_type='work_order' AND EXISTS (SELECT 1 FROM work_orders w WHERE w.id=a.entity_id AND w.assignee_id=?)))";
    $args[] = $user['id'];
}
$pg = max(1, (int) ($_GET['p'] ?? 1));
$count = (int) Database::one('SELECT COUNT(*) n FROM audit_logs a WHERE ' . $where, $args)['n'];
$logs = Database::all(
    'SELECT a.*,u.full_name FROM audit_logs a JOIN users u ON u.id=a.actor_id WHERE ' .
    $where .
    ' ORDER BY a.id DESC LIMIT 10 OFFSET ' .
    ($pg - 1) * 10,
    $args
);
?>

<section class="panel history-panel">
    <div class="panel-heading">
        <h2>Nhật ký xử lý hồ sơ</h2>
    </div>
    <div class="section-body">
        <ul class="timeline">

            <?php foreach ($logs as $log): ?>
            <li>
                <strong><?= e($log['new_status'] ? STATUS_NAMES[$log['new_status']] ?? $log['new_status'] : str_replace('_', ' ', $log['action'])) ?></strong>
                <small><?= e(dt($log['created_at'])) ?> · <?= e($log['full_name']) ?></small>

                <?php if ($log['old_status']): ?>
                <p>Từ <?= e(STATUS_NAMES[$log['old_status']] ?? $log['old_status']) ?></p>

                <?php
                endif;
                if ($log['reason']):
                ?>

                <p><?= nl2br(e($log['reason'])) ?></p>
                <?php endif; ?>

            </li>
            <?php endforeach; ?>

        </ul>

        <?php if (!$logs): ?>
        <p class="muted">Chưa có cập nhật phù hợp quyền truy cập.</p>
        <?php endif; ?>

    </div>

    <?php pager($count, $pg, $page, ['id' => $rid]); ?>

</section>
