<?php
$recent = requestRows($user, 5);
[$scope, $scopeArgs] = requestScope($user);
$caseCounts = array_column(
    Database::all(
        'SELECT sr.status,COUNT(*) n FROM service_requests sr WHERE ' .
        $scope .
        ' GROUP BY sr.status',
        $scopeArgs
    ),
    'n',
    'status'
);
$adminView = hasRole($user, 'ADMIN');
$consultantView = hasRole($user, 'CONSULTANT');
$openJobs = moduleRows('tasks', $user, 1, '', 'ASSIGNED');
$workingJobs = moduleRows('tasks', $user, 1, '', 'IN_PROGRESS');
$focusJobs = array_slice(array_merge($workingJobs['rows'], $openJobs['rows']), 0, 4);
heading(
    'KHÔNG GIAN VẬN HÀNH',
    $adminView ? 'Nhìn tổng thể. Chăm từng chi tiết.' : 'Một ngày làm việc có nhịp.',
    'Các hồ sơ, nhiệm vụ và bước xử lý nằm trong phạm vi phụ trách của bạn.',
    url($adminView ? 'reports' : 'schedule'),
    $adminView ? 'Mở báo cáo hoạt động' : 'Xem lịch làm việc'
);
summaryCards(
    [
        [
            'label' => 'Hồ sơ được xem',
            'value' => $recent['count'],
            'note' => 'Theo quyền và phân công'
        ],
        [
            'label' => 'Yêu cầu chờ tiếp nhận',
            'value' => $caseCounts['NEW'] ?? 0,
            'note' => 'Hồ sơ ở trạng thái mới'
        ],
        [
            'label' => 'Công việc chưa bắt đầu',
            'value' => $openJobs['count'],
            'note' => 'Đã được phân công'
        ],
        [
            'label' => 'Công việc đang làm',
            'value' => $workingJobs['count'],
            'note' => 'Theo tiến độ thực tế'
        ]
    ],
    'reports'
);
?>

<div class="operation-grid">
    <section class="operation-focus">
        <span class="operation-icon"><?= icon($adminView ? 'reports' : 'tasks') ?></span>
        <div>
            <p class="eyebrow"><?= $adminView ? 'GÓC NHÌN QUẢN TRỊ' : 'ĐÚNG VIỆC, ĐÚNG THỜI ĐIỂM' ?></p>
            <h2><?= $adminView ? 'Số liệu có bối cảnh.' : 'Chất lượng bắt đầu từ từng đầu việc.' ?></h2>
            <p><?= $adminView ? 'Đối chiếu nhu cầu dịch vụ, kết quả nghiệm thu và thực thu theo kỳ. Số tiền báo giá và tiền đã thu được theo dõi riêng.' : 'Mở công việc để kiểm tra yêu cầu, cập nhật tiến độ và gửi kết quả. Mỗi thay đổi có lịch sử để các bên cùng theo dõi.' ?></p>
            <a class="button" href="<?= e(url($adminView ? 'reports' : 'tasks')) ?>"><?= $adminView ? 'Đối soát hoạt động' : 'Đến công việc' ?> <?= icon('arrow') ?></a>
        </div>
    </section>
    <aside class="panel operation-shortcuts">
        <p class="eyebrow">LỐI ĐI NHANH</p>

        <?php foreach ($adminView
                       ? [
                           'users' => 'Tài khoản và phân quyền',
                           'services' => 'Danh mục dịch vụ',
                           'reports' => 'Báo cáo theo kỳ'
                       ]
                       : ($consultantView
                           ? [
                               'requests' => 'Tiếp nhận và xử lý hồ sơ',
                               'quotations' => 'Báo giá đang trao đổi',
                               'surveys' => 'Lịch khảo sát'
                           ]
                           : [
                               'schedule' => 'Lịch làm việc',
                               'tasks' => 'Công việc được giao',
                               'notifications' => 'Việc cần phản hồi'
                           ])
                   as $target => $caption): ?>

        <a href="<?= e(url($target)) ?>">
            <span><?= icon($target) ?></span>
            <strong><?= e($caption) ?></strong><?= icon('arrow') ?>
        </a>
        <?php endforeach; ?>

    </aside>
</div>

<?php if (!$adminView): ?>
<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">TẬP TRUNG VÀO THỰC HIỆN</p>
            <h2>Các công việc đang mở</h2>
        </div>
        <a class="text-link" href="<?= e(url('tasks')) ?>">Tất cả công việc <?= icon('arrow') ?></a>
    </div>

    <?php moduleCollection('tasks', $focusJobs); ?>

</section>
<?php endif; ?>

<section class="panel">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">THEO DÕI LIÊN TỤC</p>
            <h2>Hồ sơ gần đây</h2>
        </div>
        <a class="text-link" href="<?= e(url('requests')) ?>">Xem tất cả <?= icon('arrow') ?></a>
    </div>

    <?php requestTable($recent['rows']); ?>

</section>
