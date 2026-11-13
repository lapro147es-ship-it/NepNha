<?php
$r = requestAccess($user, (int) ($_GET['id'] ?? 0));
require __DIR__ . '/partials/request-header.php';
$files = Database::all(
    'SELECT a.*,u.full_name author_name FROM attachments a JOIN users u ON u.id=a.uploaded_by WHERE request_id=? AND design_version_id IS NULL AND work_update_id IS NULL AND NOT EXISTS (SELECT 1 FROM quotation_attachments qa WHERE qa.attachment_id=a.id) ORDER BY a.id DESC',
    [$rid]
);
?>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Mặt bằng và tài liệu bổ sung</h2>
            <p class="muted">Tệp thiết kế được quản lý ở trang Thiết kế; ảnh kết quả nằm trong công
                việc tương ứng.</p>
        </div>

        <?php if (($owner || $consult) && in_array($r['status'], ['NEW', 'RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS'], true)): ?>
        <a class="button" href="<?= e(url('document-upload', ['request_id' => $rid])) ?>">＋ Bổ sung tài liệu</a>
        <?php endif; ?>

    </div>
    <div class="section-body document-library">

        <?php foreach ($files as $file): ?>
        <a class="file-link library-file" href="<?= e(url('document-view', ['id' => $file['id']])) ?>">
            <span class="library-file-symbol"><?= icon('file') ?></span>
            <strong><?= e($file['original_name']) ?></strong>
            <small><?= e($file['author_name']) ?> ·
                <?= number_format($file['size_bytes'] / 1024, 1) ?> KB · <?= e(dt($file['uploaded_at'])) ?></small>
        </a>

        <?php
        endforeach;
        if (!$files):
        ?>

        <div class="empty">
            <h2>Chưa có tài liệu bổ sung</h2>
            <p>Mặt bằng và ảnh hiện trạng có thể được gửi để tư vấn làm rõ nhu cầu.</p>
        </div>
        <?php endif; ?>

    </div>
</section>
