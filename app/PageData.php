<?php
declare(strict_types=1);

function attachmentAccess(array $user, int $id): array
{
    $file = Database::one(
        'SELECT a.*,u.full_name author_name FROM attachments a JOIN users u ON u.id=a.uploaded_by WHERE a.id=?',
        [$id]
    );
    if (!$file) {
        throw new DomainException('Không tìm thấy tài liệu.');
    }
    requestAccess($user, (int) $file['request_id']);
    $quotation = Database::one(
        'SELECT quotation_id FROM quotation_attachments WHERE attachment_id=?',
        [$id]
    );
    if ($quotation) {
        entityAccess('quotations', (int) $quotation['quotation_id'], $user);
    }
    if ($file['design_version_id']) {
        entityAccess('designs', (int) $file['design_version_id'], $user);
    }
    if ($file['work_update_id']) {
        $update = Database::one('SELECT work_order_id FROM work_updates WHERE id=?', [
            $file['work_update_id']
        ]);
        entityAccess('tasks', (int) $update['work_order_id'], $user);
    }
    return $file;
}

function privateRequestScope(array $user): array
{
    if (hasRole($user, 'ADMIN')) {
        return ['1=1', []];
    }

    $parts = [];
    $args = [];

    if (hasRole($user, 'CUSTOMER')) {
        $parts[] = 'sr.customer_id=?';
        $args[] = $user['id'];
    }

    if (hasRole($user, 'CONSULTANT')) {
        $parts[] = 'sr.consultant_id=?';
        $args[] = $user['id'];
    }

    return ['(' . implode(' OR ', $parts ?: ['1=0']) . ')', $args];
}

function moduleQuery(string $kind, array $user): array
{
    [$private, $args] = privateRequestScope($user);

    $definitions = [
        'surveys' => ['surveys', 'surveyor_id', 'starts_at'],
        'quotations' => ['quotations', 'created_by', 'created_at'],
        'tasks' => ['work_orders', 'assignee_id', 'starts_at'],
        'designs' => ['design_versions', 'submitted_by', 'submitted_at'],
        'payments' => ['payments', 'recorded_by', 'paid_at']
    ];

    if (!isset($definitions[$kind])) {
        throw new DomainException('Phân hệ không hợp lệ.');
    }

    [$table, $actor, $date] = $definitions[$kind];
    $join =
        $kind === 'designs'
            ? ' JOIN work_orders w ON w.id=x.work_order_id JOIN service_requests sr ON sr.id=w.request_id'
            : ' JOIN service_requests sr ON sr.id=x.request_id';

    $scope = $private;

    if ($kind === 'tasks' || $kind === 'surveys') {
        $scope = '(' . $scope . ' OR x.' . $actor . '=?)';
        $args[] = $user['id'];
    }

    if ($kind === 'designs') {
        $scope =
            '(' .
            $scope .
            ' OR w.assignee_id=? OR (x.status=\'APPROVED\' AND EXISTS ' .
            '(SELECT 1 FROM work_orders installation WHERE installation.request_id=sr.id ' .
            'AND installation.assignee_id=? AND installation.work_type=\'INSTALLATION\')))';
        array_push($args, $user['id'], $user['id']);
    }

    if ($kind === 'quotations' && !hasRole($user, 'ADMIN')) {
        $scope = '(' . $scope . ') AND (x.status<>\'DRAFT\' OR sr.consultant_id=?)';
        $args[] = $user['id'];
    }

    $from =
        ' FROM ' .
        $table .
        ' x' .
        $join .
        ' JOIN apartments a ON a.id=sr.apartment_id JOIN users u ON u.id=x.' .
        $actor .
        ' WHERE ' .
        $scope;

    $select =
        'SELECT x.*,sr.id request_id,sr.code request_code,a.name apartment_name,' .
        'u.full_name actor_name,x.' .
        $date .
        ' display_date';

    if ($kind === 'designs') {
        $select .= ',w.title work_title';
    }

    return [$select, $from, $args];
}

function moduleRows(
    string $kind,
    array $user,
    int $pageNumber = 1,
    string $search = '',
    string $status = '',
    ?int $requestId = null
): array {
    [$select, $from, $args] = moduleQuery($kind, $user);

    if ($requestId !== null) {
        $from .= ' AND sr.id=?';
        $args[] = $requestId;
    }

    if ($search !== '') {
        $from .= ' AND (sr.code LIKE ? OR a.name LIKE ? OR u.full_name LIKE ?)';
        array_push($args, '%' . $search . '%', '%' . $search . '%', '%' . $search . '%');
    }

    if ($status !== '') {
        $from .= ' AND x.status=?';
        $args[] = $status;
    }

    $count = (int) Database::one('SELECT COUNT(*) n' . $from, $args)['n'];
    $rows = Database::all(
        $select . $from . ' ORDER BY x.id DESC LIMIT 10 OFFSET ' . max(0, $pageNumber - 1) * 10,
        $args
    );

    return ['rows' => $rows, 'count' => $count];
}

function entityAccess(string $kind, int $id, array $user): array
{
    [$select, $from, $args] = moduleQuery($kind, $user);
    $from .= ' AND x.id=?';
    $args[] = $id;
    $row = Database::one($select . $from, $args);

    if (!$row) {
        throw new DomainException('Không tìm thấy nội dung hoặc bạn chưa được cấp quyền truy cập.');
    }

    return $row;
}

function privateRequest(array $user, int $id): array
{
    $r = requestAccess($user, $id);

    if (!isOwner($user, $r) && !canConsult($user, $r) && !hasRole($user, 'ADMIN')) {
        throw new DomainException(
            'Thông tin báo giá và thanh toán chỉ dành cho khách hàng, tư vấn phụ trách và quản trị viên.'
        );
    }

    return $r;
}

function apartmentAccess(array $user, int $id): array
{
    $a = Database::one('SELECT * FROM apartments WHERE id=? AND customer_id=?', [$id, $user['id']]);

    if (!$a) {
        throw new DomainException('Không tìm thấy căn hộ của bạn.');
    }

    return $a;
}

function oldValue(string $key, mixed $default = ''): mixed
{
    global $old;
    $value = $old[$key] ?? $default;
    if (is_array($default)) {
        return is_array($value) ? $value : $default;
    }
    return is_scalar($value) || $value === null ? $value : $default;
}

function heading(
    string $eyebrow,
    string $title,
    string $description = '',
    ?string $actionUrl = null,
    string $actionLabel = ''
): void {
    echo '<div class="page-heading"><div><p class="eyebrow">' .
        e($eyebrow) .
        '</p><h1>' .
        e($title) .
        '</h1>';

    if ($description !== '') {
        echo '<p>' . e($description) . '</p>';
    }

    echo '</div>';

    if ($actionUrl) {
        echo '<a class="button" href="' . e($actionUrl) . '">' . e($actionLabel) . '</a>';
    }

    echo '</div>';
}

function moduleTable(string $kind, array $rows): void
{
    $detail = [
        'surveys' => 'survey-detail',
        'quotations' => 'quotation-detail',
        'tasks' => 'task-detail',
        'designs' => 'design-detail',
        'payments' => 'payment-detail'
    ];

    if (!$rows) {
        echo '<div class="empty"><span class="empty-symbol">◌</span><h3>Chưa có dữ liệu phù hợp</h3><p>Dữ liệu sẽ xuất hiện theo hồ sơ và quyền truy cập của bạn.</p></div>';
        return;
    }

    echo '<div class="table-scroll"><table><thead><tr><th>Nội dung</th><th>Hồ sơ / Căn hộ</th><th>Phụ trách</th><th>Thời gian</th><th>Trạng thái</th><th></th></tr></thead><tbody>';

    foreach ($rows as $row) {
        $name = match ($kind) {
            'surveys' => 'Khảo sát hiện trạng',
            'quotations' => 'Báo giá phiên bản ' . $row['version'],
            'tasks' => $row['title'],
            'designs' => $row['work_title'] . ' · Phiên bản ' . $row['version'],
            'payments' => $row['receipt_code']
        };
        $href = url($detail[$kind], ['id' => $row['id']]);
        echo '<tr><td><a class="table-title" href="' . e($href) . '">' . e($name) . '</a>';

        if ($kind === 'payments') {
            echo '<small>' . money($row['amount']) . '</small>';
        }

        if ($kind === 'quotations') {
            echo '<small>' . money(totalQuote((int) $row['id'])) . '</small>';
        }

        echo '</td><td><strong>' .
            e($row['apartment_name']) .
            '</strong><small>' .
            e($row['request_code']) .
            '</small></td><td>' .
            e($row['actor_name']) .
            '</td><td>' .
            e(dt($row['display_date'])) .
            '</td><td>' .
            badge($row['status']) .
            '</td><td><a class="arrow-link" href="' .
            e($href) .
            '" aria-label="Xem chi tiết ' .
            e($name) .
            '">↗</a></td></tr>';
    }

    echo '</tbody></table></div>';
}

function pager(int $count, int $number, string $route, array $filters = []): void
{
    echo '<div class="pagination"><span>' . $count . ' kết quả · Trang ' . $number . '</span><div>';

    if ($number > 1) {
        echo '<a href="' . e(url($route, $filters + ['p' => $number - 1])) . '">← Trước</a>';
    }

    if ($number * 10 < $count) {
        echo '<a href="' . e(url($route, $filters + ['p' => $number + 1])) . '">Sau →</a>';
    }

    echo '</div></div>';
}
