<?php
$w = entityAccess('tasks', (int) ($_GET['id'] ?? 0), $user);
$r = requestAccess($user, (int) $w['request_id']);
require __DIR__ . '/partials/request-header.php';
$mine = (int) $w['assignee_id'] === $user['id'];
$open = in_array($r['status'], ['APPROVED', 'IN_PROGRESS'], true);
$progressRow = Database::one(
    'SELECT progress_percent FROM work_updates WHERE work_order_id=? AND progress_percent IS NOT NULL ORDER BY id DESC LIMIT 1',
    [$w['id']]
);
$progress = $w['status'] === 'DONE' ? 100 : (int) ($progressRow['progress_percent'] ?? 0);
$pg = max(1, (int) ($_GET['p'] ?? 1));
$count = (int) Database::one('SELECT COUNT(*) n FROM work_updates WHERE work_order_id=?', [
        $w['id']
    ])['n'];
    $updates = Database::all(
        'SELECT wu.*,u.full_name author_name FROM work_updates wu JOIN users u ON u.id=wu.author_id WHERE wu.work_order_id=? ORDER BY wu.id DESC LIMIT 10 OFFSET ' .
        ($pg - 1) * 10,
        [$w['id']]
    );
?>

<section class="panel form-panel">
    <div class="card-head">
        <div>
            <p class="eyebrow"><?= e(TYPE_NAMES[$w['work_type']]) ?></p>
            <h2><?= e($w['title']) ?></h2>
        </div><?= badge($w['status']) ?>
    </div>
    <dl class="info-grid">
        <div>
            <dt>Nhân viên phụ trách</dt>
            <dd><?= e($w['actor_name']) ?></dd>
        </div>
        <div>
            <dt>Địa điểm</dt>
            <dd><?= e($r['address']) ?></dd>
        </div>
        <div>
            <dt>Bắt đầu</dt>
            <dd><?= e(dt($w['starts_at'])) ?></dd>
        </div>
        <div>
            <dt>Kết thúc</dt>
            <dd><?= e(dt($w['ends_at'])) ?></dd>
        </div>
    </dl>
    <p class="request-description"><?= nl2br(e($w['description'] ?: 'Thực hiện theo phạm vi được phân công và tài liệu của hồ sơ.')) ?></p>
    <progress value="<?= $progress ?>" max="100" aria-label="Tiến độ công việc"></progress>
    <p class="muted"><?= $progress ?>% được báo cáo · <?= e(STATUS_NAMES[$w['status']]) ?></p>
    <div class="actions">

        <?php
        if ($mine && $w['status'] === 'ASSIGNED' && $open):

            formStart('task_start', $rid);
            hidden('id', $w['id']);
        ?>

        <button class="button">Bắt đầu công việc</button>
        </form>
        <?php endif; ?>

        <?php if ($mine && $w['status'] === 'IN_PROGRESS' && $open): ?>
        <a class="button" href="<?= e(url('task-update', ['id' => $w['id']])) ?>">Cập nhật tiến độ ↗</a>
        <a class="button secondary" href="<?= e(url($w['work_type'] === 'DESIGN' ? 'design-submit' : 'task-report', ['id' => $w['id']])) ?>"><?= $w['work_type'] === 'DESIGN' ? 'Nộp bản thiết kế' : 'Gửi báo cáo hoàn thành' ?> ↗</a>
        <?php endif; ?>

        <?php if ($owner &&
                    $w['status'] === 'REPORTED' &&
                    $w['work_type'] !== 'DESIGN' &&
                    $open
                ):

                    formStart('task_accept', $rid);
                    hidden('id', $w['id']);
        ?>

        <button class="button">Xác nhận nghiệm thu</button>
        </form>
        <a class="button secondary" href="<?= e(url('task-rework', ['id' => $w['id']])) ?>">Yêu cầu khắc phục</a>
        <?php endif; ?>

        <?php if ($consult && $w['status'] === 'ASSIGNED' && $open): ?>
        <a class="button danger" href="<?= e(url('task-cancel', ['id' => $w['id']])) ?>">Hủy phân công</a>
        <?php endif; ?>

        <?php if ($w['work_type'] === 'DESIGN'): ?>
        <a class="text-link" href="<?= e(url('request-designs', ['id' => $rid])) ?>">Xem các phiên bản thiết kế ↗</a>
        <?php endif; ?>

    </div>
</section>
<section class="panel">
    <div class="panel-heading">
        <h2>Nhật ký thực hiện</h2>
    </div>
    <div class="section-body">
        <ul class="timeline">

            <?php foreach ($updates as $up): ?>
            <li>
                <strong><?= e($up['author_name']) ?></strong>
                <small><?= e(dt($up['created_at'])) ?> · <?= e(['PROGRESS' => 'Cập nhật tiến độ', 'RESULT' => 'Báo cáo kết quả', 'ACCEPTED' => 'Đã xác nhận', 'REWORK_REQUESTED' => 'Yêu cầu khắc phục'][$up['event_type']] ?? $up['event_type']) ?></small>
                <p><?= nl2br(e($up['content'])) ?></p>

                <?php foreach (Database::all('SELECT * FROM attachments WHERE work_update_id=?', [$up['id']]) as $file): ?>
                <a class="file-link" href="<?= e(url('document-view', ['id' => $file['id']])) ?>">▧ <?= e($file['original_name']) ?> · Xem tài liệu ↗</a>
                <?php endforeach; ?>

            </li>
            <?php endforeach; ?>

        </ul>

        <?php if (!$updates): ?>
        <p class="muted">Nhật ký sẽ xuất hiện khi nhân viên bắt đầu hoặc cập nhật công việc.</p>
        <?php endif; ?>

    </div>

    <?php pager($count, $pg, 'task-detail', ['id' => $w['id']]); ?>

</section>
