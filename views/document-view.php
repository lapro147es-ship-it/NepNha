<?php
$file = attachmentAccess($user, (int) ($_GET['id'] ?? 0));
$r = requestAccess($user, (int) $file['request_id']);
$quoteLink = Database::one('SELECT quotation_id FROM quotation_attachments WHERE attachment_id=?', [
        $file['id']
    ]);
    require __DIR__ . '/partials/request-header.php';
    heading(
        'TÀI LIỆU HỒ SƠ',
        $file['original_name'],
        'Tệp được kiểm soát quyền truy cập theo hồ sơ và công việc liên quan.'
    );
?>

<section class="panel document-preview">

    <?php if ($quoteLink): ?>
    <a class="text-link" href="<?= e(url('quotation-detail', ['id' => $quoteLink['quotation_id']])) ?>">← Trở lại báo giá chứa tài liệu</a>
    <?php endif; ?>

    <dl class="info-grid">
        <div>
            <dt>Người gửi</dt>
            <dd><?= e($file['author_name']) ?></dd>
        </div>
        <div>
            <dt>Ngày gửi</dt>
            <dd><?= e(dt($file['uploaded_at'])) ?></dd>
        </div>
        <div>
            <dt>Dung lượng</dt>
            <dd><?= number_format($file['size_bytes'] / 1024, 1) ?> KB</dd>
        </div>
        <div>
            <dt>Định dạng</dt>
            <dd><?= e($file['mime_type']) ?></dd>
        </div>
    </dl>

    <?php if (in_array($file['mime_type'], ['image/png', 'image/jpeg'], true)): ?>
    <img src="<?= e(url('file-content', ['id' => $file['id']])) ?>" alt="<?= e($file['original_name']) ?>">
    <?php else: ?>
    <div class="empty">
        <h2>Tài liệu PDF</h2>
        <p>Tải tệp để xem đầy đủ bản vẽ và nội dung.</p>
    </div>
    <?php endif; ?>

    <a class="button" href="<?= e(url('download', ['id' => $file['id']])) ?>">Tải tài liệu ↓</a>
</section>
