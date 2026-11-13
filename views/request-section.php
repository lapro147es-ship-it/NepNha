<?php
$r = requestAccess($user, (int) ($_GET['id'] ?? 0));
require __DIR__ . '/partials/request-header.php';
$config = [
    'request-surveys' => [
        'surveys',
        'Lịch hẹn khảo sát và kết quả được xác nhận theo hồ sơ.',
        'survey-form',
        '＋ Tạo lịch khảo sát'
    ],
    'request-quotations' => [
        'quotations',
        'Mở báo giá để xem hạng mục, chi phí và phản hồi phiên bản đang gửi.',
        'quotation-form',
        '＋ Lập báo giá'
    ],
    'request-work' => [
        'tasks',
        'Các công việc được phân công theo chuyên môn và thời gian thực hiện.',
        'task-form',
        '＋ Phân công công việc'
    ],
    'request-designs' => [
        'designs',
        'Mở từng phiên bản để xem tệp, phản hồi và quyết định duyệt.',
        null,
        ''
    ]
][$page];
if (in_array($page, ['request-surveys', 'request-quotations'], true) && !$private) {
    throw new DomainException('Bạn chưa được xem phân hệ này của hồ sơ.');
}
$pg = max(1, (int) ($_GET['p'] ?? 1));
$data = moduleRows($config[0], $user, $pg, '', '', $rid);
$canCreate =
$consult &&
($page === 'request-surveys'
    ? in_array($r['status'], ['RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS'], true)
    : ($page === 'request-quotations'
        ? $r['status'] === 'RECEIVED'
        : in_array($r['status'], ['APPROVED', 'IN_PROGRESS'], true)));
?>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2><?= e($title) ?></h2>
            <p class="muted"><?= e($config[1]) ?></p>
        </div>

        <?php if ($config[2] && $canCreate): ?>
        <a class="button" href="<?= e(url($config[2], ['request_id' => $rid])) ?>"><?= e($config[3]) ?></a>
        <?php endif; ?>

    </div>

    <?php
    moduleCollection($config[0], $data['rows']);
    pager($data['count'], $pg, $page, ['id' => $rid]);
    ?>

</section>

<?php
if ($page === 'request-designs'):
    $designJobs = Database::all(
        "SELECT id,title FROM work_orders WHERE request_id=? AND assignee_id=? AND work_type='DESIGN' AND status='IN_PROGRESS'",
        [$rid, $user['id']]
    );
    foreach ($designJobs as $job):
?>

<section class="panel form-panel">
    <h2><?= e($job['title']) ?></h2>
    <p class="muted">Bạn đang phụ trách công việc thiết kế này. Chọn trang nộp thiết kế để gửi bản
        mới.</p>
    <a class="button" href="<?= e(url('design-submit', ['id' => $job['id']])) ?>">Nộp bản thiết kế ↗</a>
</section>

<?php
endforeach;
endif;
?>
