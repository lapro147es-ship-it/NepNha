<?php
$kind = $page;
$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$status = (string) ($_GET['status'] ?? '');
$pg = max(1, (int) ($_GET['p'] ?? 1));
$config = [
    'surveys' => [
        'Lịch khảo sát',
        'Xem lịch hẹn, xác nhận thời gian và mở kết quả khảo sát.',
        ['PENDING', 'CONFIRMED', 'DONE', 'CANCELLED'],
        'survey-form',
        '＋ Tạo lịch khảo sát'
    ],
    'quotations' => [
        'Báo giá dịch vụ',
        'Mỗi báo giá có chi tiết hạng mục, số phiên bản và quyết định phản hồi.',
        ['DRAFT', 'SENT', 'ACCEPTED', 'REJECTED'],
        'quotation-form',
        '＋ Lập báo giá'
    ],
    'tasks' => [
        'Công việc',
        'Mở công việc để xem phân công, ghi nhận tiến độ và kết quả thực hiện.',
        ['ASSIGNED', 'IN_PROGRESS', 'REPORTED', 'DONE', 'CANCELLED'],
        'task-form',
        '＋ Phân công mới'
    ],
    'designs' => [
        'Bản thiết kế',
        'Các phiên bản được lưu riêng cùng tệp đính kèm và phản hồi.',
        ['SUBMITTED', 'APPROVED', 'REVISION_REQUESTED'],
        null,
        ''
    ],
    'payments' => [
        'Thanh toán',
        'Tra cứu các lần thu tiền và chứng từ theo hồ sơ được phép xem.',
        ['POSTED', 'VOID'],
        'payment-form',
        '＋ Ghi nhận thu tiền'
    ]
][$kind];
$data = moduleRows($kind, $user, $pg, $search, $status);
heading(
    'KHÔNG GIAN LÀM VIỆC',
    $config[0],
    $config[1],
    hasRole($user, 'CONSULTANT') && $config[3] ? url($config[3]) : null,
    $config[4]
);
summaryCards(moduleStatistics($kind, $user), $kind, $page);
?>

<section class="panel">
    <form class="filters" method="get">
        <input type="hidden" name="page" value="<?= e($page) ?>">
        <label class="search-box">
            <span>⌕</span>
            <input name="q" value="<?= e($search) ?>"
                placeholder="Tìm hồ sơ, căn hộ hoặc người phụ trách" aria-label="Tìm nội dung">
        </label>
        <select name="status" aria-label="Lọc trạng thái">
            <option value="">Tất cả trạng thái</option>

            <?php foreach ($config[2] as $code): ?>
            <option value="<?= e($code) ?>" <?= $status === $code ? 'selected' : '' ?>><?= e(STATUS_NAMES[$code]) ?></option>
            <?php endforeach; ?>

        </select>
        <button class="button secondary">Tìm kiếm</button>
        <a class="text-link" href="<?= e(url($page)) ?>">Đặt lại</a>
    </form>

    <?php
    moduleCollection($kind, $data['rows']);
    pager($data['count'], $pg, $page, ['q' => $search, 'status' => $status]);
    ?>

</section>
