<?php
heading(
    'PHẢN HỒI ĐÚNG NỘI DUNG',
    'Việc cần phản hồi',
    'Mở từng mục để xem thông tin và thực hiện bước thuộc quyền của bạn.'
);
$groups = [];
if (hasRole($user, 'CUSTOMER')) {
    $groups = [
        ['surveys', 'PENDING', 'Lịch khảo sát chờ xác nhận', 'survey-detail'],
        ['quotations', 'SENT', 'Báo giá chờ phản hồi', 'quotation-detail'],
        ['designs', 'SUBMITTED', 'Thiết kế chờ duyệt', 'design-detail'],
        ['tasks', 'REPORTED', 'Kết quả chờ nghiệm thu', 'task-detail']
    ];
}
if (hasRole($user, 'DESIGNER', 'INSTALLER', 'MAINTAINER')) {
    $groups[] = ['tasks', 'ASSIGNED', 'Công việc mới được giao', 'task-detail'];
    $groups[] = ['designs', 'REVISION_REQUESTED', 'Thiết kế cần chỉnh sửa', 'design-detail'];
}
if (hasRole($user, 'CONSULTANT')) {
    $new = requestRows($user, 10, 0, '', 'NEW');
    if ($new['count']):
?>

<section class="panel response-group">
    <div class="panel-heading">
        <h2>Yêu cầu mới chờ tiếp nhận</h2>
        <a class="text-link" href="<?= e(url('requests', ['status' => 'NEW'])) ?>">Xem <?= $new['count'] ?> hồ sơ ↗</a>
    </div>

    <?php requestTable($new['rows']); ?>

</section>

<?php
endif;
}
$found = 0;
foreach ($groups as [$kind, $status, $caption, $detail]):

    $data = moduleRows($kind, $user, 1, '', $status);
    if (!$data['count']) {
        continue;
    }
    $found += $data['count'];
?>

<section class="panel response-group">
    <div class="panel-heading">
        <h2><?= e($caption) ?></h2>
        <a class="text-link" href="<?= e(url($kind, ['status' => $status])) ?>">Xem <?= $data['count'] ?> mục ↗</a>
    </div>

    <?php moduleCollection($kind, $data['rows']); ?>

</section>

<?php
endforeach;
if (!$found && (int) ($new['count'] ?? 0) === 0):
?>

<section class="panel empty">
    <h2>Chưa có mục cần bạn phản hồi</h2>
    <p>Các lịch hẹn, báo giá, bản thiết kế và công việc phù hợp sẽ xuất hiện tại đây.</p>
    <a class="button secondary" href="<?= e(url('requests')) ?>">Xem hồ sơ dịch vụ</a>
</section>
<?php endif; ?>
