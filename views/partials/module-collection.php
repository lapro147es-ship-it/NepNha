<?php
$destinations = [
    'surveys' => 'survey-detail',
    'quotations' => 'quotation-detail',
    'tasks' => 'task-detail',
    'designs' => 'design-detail'
];
?>

<?php if (!$rows): ?>
<div class="collection-empty">
    <span><?= icon($kind) ?></span>
    <h2>Chưa có nội dung phù hợp</h2>
    <p>Thử thay đổi bộ lọc. Nội dung được hiển thị theo hồ sơ, trạng thái và quyền của bạn.</p>
</div>
<?php else: ?>
<div class="record-collection collection-<?= e($kind) ?>">

    <?php
    foreach ($rows as $row):

        $recordUrl = url($destinations[$kind], ['id' => $row['id']]);
        $recordTitle = match ($kind) {
            'surveys' => 'Khảo sát hiện trạng',
            'quotations' => 'Báo giá phiên bản ' . $row['version'],
            'tasks' => $row['title'],
            'designs' => $row['work_title']
        };
    ?>

    <article class="record-card">

        <?php
        if ($kind === 'designs'):
            $cover = Database::one(
                'SELECT id,original_name,mime_type FROM attachments WHERE design_version_id=? ORDER BY id LIMIT 1',
                [$row['id']]
            );
        ?>

        <a class="drawing-cover" href="<?= e($recordUrl) ?>">

            <?php if ($cover && in_array($cover['mime_type'], ['image/png', 'image/jpeg'], true)): ?>
            <img src="<?= e(url('file-content', ['id' => $cover['id']])) ?>" alt="<?= e($cover['original_name']) ?>" loading="lazy">
            <?php else: ?>
            <span class="drawing-symbol"><?= icon('designs') ?></span>
            <span class="drawing-label"><?= $cover ? 'TÀI LIỆU PDF' : 'HỒ SƠ THIẾT KẾ' ?></span>
            <?php endif; ?>

            <span class="version-marker">PHIÊN BẢN <?= (int) $row['version'] ?></span>
        </a>
        <?php endif; ?>

        <div class="record-content">
            <div class="record-topline">
                <span><?= e($row['request_code']) ?></span>
                <span><?= badge($row['status']) ?></span>
            </div>

            <?php if ($kind === 'surveys'): ?>
            <div class="survey-date-tile">
                <strong><?= e(substr(dt($row['starts_at']), 0, 2)) ?></strong>
                <span><?= e(substr(dt($row['starts_at']), 3, 7)) ?></span>
            </div>
            <?php endif; ?>

            <h2>
                <a href="<?= e($recordUrl) ?>"><?= e($recordTitle) ?></a>
            </h2>
            <p class="record-location"><?= icon('apartments') ?><?= e($row['apartment_name']) ?></p>

            <?php if ($kind === 'quotations'): ?>
            <strong class="quote-amount"><?= money(totalQuote((int) $row['id'])) ?></strong>
            <p class="record-description"><?= e(mb_strimwidth($row['notes'] ?: 'Hạng mục, phạm vi và chi phí được thể hiện trong chi tiết báo giá.', 0, 160, '…')) ?></p>
            <?php endif; ?>

            <?php
            if ($kind === 'tasks'):

                $latestProgress = Database::one(
                    'SELECT progress_percent FROM work_updates WHERE work_order_id=? AND progress_percent IS NOT NULL ORDER BY id DESC LIMIT 1',
                    [$row['id']]
                );
                $percent = in_array($row['status'], ['DONE', 'REPORTED'], true)
                ? 100
                : (int) ($latestProgress['progress_percent'] ?? 0);
            ?>

            <div class="work-progress">
                <div>
                    <span><?= e(TYPE_NAMES[$row['work_type']]) ?></span>
                    <strong><?= $percent ?>%</strong>
                </div>
                <progress max="100" value="<?= $percent ?>" aria-label="Tiến độ được báo cáo">
                </progress>
            </div>
            <?php endif; ?>

            <?php if ($kind === 'designs'): ?>
            <p class="record-description"><?= e(mb_strimwidth($row['description'], 0, 130, '…')) ?></p>
            <?php endif; ?>

            <div class="record-meta">
                <span><?= icon('profile') ?><?= e($row['actor_name']) ?></span>
                <span><?= icon('clock') ?><?= e(dt($row['display_date'])) ?></span>
            </div>
            <a class="record-open" href="<?= e($recordUrl) ?>">
                <span><?= ['surveys' => 'Xem lịch hẹn', 'quotations' => 'Mở chi tiết báo giá', 'tasks' => 'Theo dõi công việc', 'designs' => 'Xem phiên bản'][$kind] ?></span><?= icon('arrow') ?>
            </a>
        </div>
    </article>
    <?php endforeach; ?>

</div>
<?php endif; ?>
