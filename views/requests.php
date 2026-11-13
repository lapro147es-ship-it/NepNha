<?php
$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$status = (string) ($_GET['status'] ?? '');
$type = (string) ($_GET['type'] ?? '');
$pg = max(1, (int) ($_GET['p'] ?? 1));
$data = requestRows($user, 10, ($pg - 1) * 10, $search, $status, $type);
heading(
    'THEO DÕI TỪNG GIAI ĐOẠN',
    'Hồ sơ dịch vụ',
    'Tìm hồ sơ, mở trang chi tiết và chọn chức năng cần làm.',
    hasRole($user, 'CUSTOMER') ? url('request-create') : null,
    '＋ Tạo yêu cầu mới'
);
[$caseScope, $caseArgs] = requestScope($user);
$caseCounts = array_column(
    Database::all(
        'SELECT sr.status,COUNT(*) n FROM service_requests sr WHERE ' .
        $caseScope .
        ' GROUP BY sr.status',
        $caseArgs
    ),
    'n',
    'status'
);
summaryCards(
    [
        [
            'label' => 'Tất cả hồ sơ',
            'value' => array_sum($caseCounts),
            'note' => 'Trong phạm vi bạn quản lý'
        ],
        [
            'label' => 'Cần tiếp nhận / phản hồi',
            'value' => ($caseCounts['NEW'] ?? 0) + ($caseCounts['QUOTED'] ?? 0),
            'note' => 'Yêu cầu mới và báo giá chờ duyệt'
        ],
        [
            'label' => 'Đang triển khai',
            'value' =>
            ($caseCounts['RECEIVED'] ?? 0) +
            ($caseCounts['APPROVED'] ?? 0) +
            ($caseCounts['IN_PROGRESS'] ?? 0),
            'note' => 'Từ tiếp nhận đến thực hiện'
        ],
        [
            'label' => 'Đã nghiệm thu / đóng',
            'value' => ($caseCounts['COMPLETED'] ?? 0) + ($caseCounts['CLOSED'] ?? 0),
            'note' => 'Các hồ sơ đã hoàn tất công việc'
        ]
    ],
    'requests'
);
?>

<section class="panel">
    <form class="filters" method="get">
        <input type="hidden" name="page" value="requests">
        <label class="search-box">
            <span>⌕</span>
            <input name="q" value="<?= e($search) ?>" placeholder="Mã hồ sơ, căn hộ hoặc khách hàng"
                aria-label="Tìm kiếm hồ sơ">
        </label>
        <select name="status" aria-label="Lọc trạng thái">
            <option value="">Tất cả trạng thái</option>

            <?php foreach ([
                               'NEW',
                               'RECEIVED',
                               'QUOTED',
                               'APPROVED',
                               'IN_PROGRESS',
                               'COMPLETED',
                               'CLOSED',
                               'CANCELLED',
                               'REJECTED'
                           ]
                           as $code): ?>

            <option value="<?= e($code) ?>" <?= $status === $code ? 'selected' : '' ?>><?= e(STATUS_NAMES[$code]) ?></option>
            <?php endforeach; ?>

        </select>
        <select name="type" aria-label="Lọc loại dịch vụ">
            <option value="">Tất cả dịch vụ</option>

            <?php foreach (TYPE_NAMES as $code => $name): ?>
            <option value="<?= e($code) ?>" <?= $type === $code ? 'selected' : '' ?>><?= e($name) ?></option>
            <?php endforeach; ?>

        </select>
        <button class="button secondary">Tìm hồ sơ</button>
        <a class="text-link" href="<?= e(url('requests')) ?>">Đặt lại</a>
    </form>

    <?php
    requestTable($data['rows']);
    pager($data['count'], $pg, 'requests', ['q' => $search, 'status' => $status, 'type' => $type]);
    ?>

</section>
