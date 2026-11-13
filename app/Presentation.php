<?php
declare(strict_types=1);

function moduleStatistics(string $kind, array $user): array
{
    [, $from, $args] = moduleQuery($kind, $user);
    $counts = array_column(
        Database::all('SELECT x.status,COUNT(*) n' . $from . ' GROUP BY x.status', $args),
        'n',
        'status'
    );
    $total = array_sum($counts);
    $definitions = match ($kind) {
        'surveys' => [
            ['Tất cả lịch hẹn', null],
            ['Chờ bạn xác nhận', 'PENDING'],
            ['Đã xác nhận', 'CONFIRMED'],
            ['Đã có kết quả', 'DONE']
        ],
        'quotations' => [
            ['Các phiên bản báo giá', null],
            ['Chờ phản hồi', 'SENT'],
            ['Đã chấp thuận', 'ACCEPTED'],
            ['Cần trao đổi lại', 'REJECTED']
        ],
        'tasks' => [
            ['Tất cả công việc', null],
            ['Chưa bắt đầu', 'ASSIGNED'],
            ['Đang thực hiện', 'IN_PROGRESS'],
            ['Đã nghiệm thu', 'DONE']
        ],
        'designs' => [
            ['Các phiên bản thiết kế', null],
            ['Chờ duyệt', 'SUBMITTED'],
            ['Cần chỉnh sửa', 'REVISION_REQUESTED'],
            ['Đã phê duyệt', 'APPROVED']
        ],
        'payments' => [
            ['Các chứng từ', null],
            ['Đã ghi nhận', 'POSTED'],
            ['Đã hủy ghi nhận', 'VOID']
        ]
    };
    $metrics = [];
    foreach ($definitions as [$label, $status]) {
        $metrics[] = [
            'label' => $label,
            'value' => (string) ($status ? $counts[$status] ?? 0 : $total),
            'note' => 'Trong phạm vi bạn được xem',
            'status' => $status
        ];
    }
    if ($kind === 'payments') {
        $sum = Database::one(
            'SELECT COALESCE(SUM(x.amount),0) n' . $from . " AND x.status='POSTED'",
            $args
        )['n'];
        array_unshift($metrics, [
            'label' => 'Tổng tiền đã ghi nhận',
            'value' => money($sum),
            'note' => 'Không cộng khoản thu đã hủy',
            'status' => 'POSTED'
        ]);
    }
    return $metrics;
}

function summaryCards(array $metrics, string $symbol = 'reports', ?string $route = null): void
{
    echo '<div class="section-metrics">';
    foreach ($metrics as $metric) {
        $tag = $route ? 'a' : 'article';
        $href = $route
            ? ' href="' .
                e(url($route, empty($metric['status']) ? [] : ['status' => $metric['status']])) .
                '"'
            : '';
        echo '<' .
            $tag .
            $href .
            ' class="section-metric"><span class="metric-symbol">' .
            icon($symbol) .
            '</span><div><span>' .
            e($metric['label']) .
            '</span><strong>' .
            e($metric['value']) .
            '</strong><small>' .
            e($metric['note'] ?? '') .
            '</small></div></' .
            $tag .
            '>';
    }
    echo '</div>';
}

function moduleCollection(string $kind, array $rows): void
{
    global $user;
    if ($kind === 'payments') {
        moduleTable($kind, $rows);
        return;
    }
    require BASE_PATH . '/views/partials/module-collection.php';
}

function formGuide(string $title, array $steps, string $symbol = 'file'): void
{
    echo '<aside class="form-companion"><div class="companion-mark">' .
        icon($symbol) .
        '</div><p class="eyebrow">MỘT CHÚT CHUẨN BỊ</p><h2>' .
        e($title) .
        '</h2><ol>';
    foreach ($steps as $step) {
        echo '<li>' . e($step) . '</li>';
    }
    echo '</ol><a class="text-link" href="' .
        e(url('faq')) .
        '">Tìm hiểu thêm ' .
        icon('arrow') .
        '</a></aside>';
}
