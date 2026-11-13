<?php
$v = entityAccess('designs', (int) ($_GET['id'] ?? 0), $user);
$r = requestAccess($user, (int) $v['request_id']);
require __DIR__ . '/partials/request-header.php';
$files = Database::all('SELECT * FROM attachments WHERE design_version_id=? ORDER BY id', [
        $v['id']
    ]);
?>

<section class="panel form-panel">
    <div class="card-head">
        <div>
            <p class="eyebrow">PHIÊN BẢN <?= (int) $v['version'] ?></p>
            <h2><?= e($v['work_title']) ?></h2>
        </div><?= badge($v['status']) ?>
    </div>
    <dl class="info-grid">
        <div>
            <dt>Người nộp</dt>
            <dd><?= e($v['actor_name']) ?></dd>
        </div>
        <div>
            <dt>Thời điểm gửi</dt>
            <dd><?= e(dt($v['submitted_at'])) ?></dd>
        </div>
        <div>
            <dt>Thời điểm phản hồi</dt>
            <dd><?= e(dt($v['reviewed_at'])) ?></dd>
        </div>
    </dl>
    <h2>Nội dung phiên bản</h2>
    <p class="request-description"><?= nl2br(e($v['description'])) ?></p>
    <h2>Tài liệu thiết kế</h2>

    <?php
    if ($files):
        $readerFile = $files[0];
    ?>

    <a class="design-reader" href="<?= e(url('document-view', ['id' => $readerFile['id']])) ?>">

        <?php if (in_array($readerFile['mime_type'], ['image/png', 'image/jpeg'], true)): ?>
        <img src="<?= e(url('file-content', ['id' => $readerFile['id']])) ?>" alt="<?= e($readerFile['original_name']) ?>">
        <?php else: ?>
        <span><?= icon('file') ?></span>
        <strong>Mở bản vẽ để xem toàn bộ nội dung</strong>
        <small><?= e($readerFile['original_name']) ?></small>
        <?php endif; ?>

        <span class="reader-caption">Mở tài liệu thiết kế <?= icon('arrow') ?></span>
    </a>
    <?php endif; ?>

    <?php foreach ($files as $file): ?>
    <a class="file-link" href="<?= e(url('document-view', ['id' => $file['id']])) ?>">▧ <?= e($file['original_name']) ?><small><?= number_format($file['size_bytes'] / 1024, 1) ?> KB · Xem
            tài liệu ↗</small>
    </a>

    <?php
    endforeach;
    if (!$files):
    ?>

    <p class="muted">Phiên bản chưa có tệp tài liệu để xem.</p>

    <?php
    endif;
    if ($v['feedback']):
    ?>

    <div class="subtle">
        <strong>Ý kiến đã phản hồi</strong>
        <p><?= nl2br(e($v['feedback'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($owner && $v['status'] === 'SUBMITTED' && $r['status'] === 'IN_PROGRESS'): ?>
    <hr>
    <h2>Phản hồi bản thiết kế</h2>
    <p class="muted">Mở tài liệu trước khi duyệt. Nội dung cần chỉnh sửa nên nêu rõ khu vực và yêu
        cầu cụ thể.</p>

    <?php
    formStart('design_review', $rid);
    hidden('id', $v['id']);
    area('feedback', 'Ý kiến phản hồi (bắt buộc nếu yêu cầu sửa)', oldValue('feedback'), false);
    ?>

    <div class="actions">
        <button class="button" name="decision" value="approve">Duyệt bản thiết kế</button>
        <button class="button secondary" name="decision" value="revise">Yêu cầu chỉnh sửa</button>
    </div>
    </form>
    <?php endif; ?>

    <div class="card-footer">
        <a class="text-link" href="<?= e(url('request-designs', ['id' => $rid])) ?>">Xem tất cả phiên bản trong hồ sơ ↗</a>

        <?php if ($private ||
                   Database::one('SELECT id FROM work_orders WHERE id=? AND assignee_id=?', [
                       $v['work_order_id'],
                       $user['id']
                   ])): ?>

        <a class="text-link" href="<?= e(url('task-detail', ['id' => $v['work_order_id']])) ?>">Công việc thiết kế ↗</a>
        <?php endif; ?>

    </div>
</section>
