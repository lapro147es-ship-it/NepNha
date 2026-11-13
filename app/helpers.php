<?php
declare(strict_types=1);

// --- Hằng số Vai trò, Trạng thái & Loại dịch vụ ---
const ROLE_NAMES = [
    'CUSTOMER' => 'Khách hàng',
    'CONSULTANT' => 'Tư vấn viên',
    'DESIGNER' => 'Nhà thiết kế',
    'INSTALLER' => 'Nhân viên lắp đặt',
    'MAINTAINER' => 'Kỹ thuật bảo trì',
    'ADMIN' => 'Quản trị viên',
];

const STATUS_NAMES = [
    'NEW' => 'Yêu cầu mới',
    'RECEIVED' => 'Đã tiếp nhận',
    'QUOTED' => 'Chờ duyệt báo giá',
    'APPROVED' => 'Đã phê duyệt',
    'IN_PROGRESS' => 'Đang thực hiện',
    'COMPLETED' => 'Đã nghiệm thu',
    'CLOSED' => 'Đã đóng',
    'REJECTED' => 'Đã từ chối',
    'CANCELLED' => 'Đã hủy',
    'DRAFT' => 'Bản nháp',
    'SENT' => 'Chờ phản hồi',
    'ACCEPTED' => 'Đã chấp thuận',
    'ASSIGNED' => 'Đã phân công',
    'REPORTED' => 'Chờ nghiệm thu',
    'DONE' => 'Hoàn thành',
    'SUBMITTED' => 'Chờ duyệt thiết kế',
    'REVISION_REQUESTED' => 'Cần chỉnh sửa',
    'PENDING' => 'Chờ xác nhận',
    'CONFIRMED' => 'Đã xác nhận',
    'POSTED' => 'Đã ghi nhận',
    'VOID' => 'Đã hủy ghi nhận',
];

const TYPE_NAMES = [
    'DESIGN' => 'Thiết kế',
    'INSTALLATION' => 'Lắp đặt',
    'MAINTENANCE' => 'Bảo trì',
    'PACKAGE' => 'Trọn gói',
];

// --- Biểu tượng giao diện (SVG Icons) ---
function icon(string $name): string
{
    $paths = [
        'phone' => '<path d="M6 3 3 6c0 7 8 15 15 15l3-3-5-4-2 2c-3-1-5-3-6-6l2-2z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
        'shield' => '<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6zM8 12l3 3 5-6"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'requests' => '<path d="M5 3h10l4 4v14H5zM14 3v5h5M8 12h8M8 16h5"/>',
        'notifications' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'tasks' => '<rect x="4" y="4" width="16" height="17" rx="2"/><path d="M9 3h6v4H9zM8 14l3 3 5-6"/>',
        'designs' => '<path d="m15 4 5 5-11 11-6 1 1-6zM13 6l5 5M4 15l5 5"/>',
        'surveys' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 11h18M8 15h2M14 15h2"/>',
        'quotations' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2zM9 7h6M9 11h6M9 15h3"/>',
        'payments' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
        'maintenance' => '<path d="m14 6 4 4M21 3l-5 1-4 4 4 4 4-4zM12 10 3 19l2 2 9-9"/>',
        'services' => '<path d="m12 3 9 5-9 5-9-5zM3 12l9 5 9-5M3 16l9 5 9-5"/>',
        'apartments' => '<path d="m3 11 9-8 9 8M5 10v11h14V10M9 21v-8h6v8"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M17 5a3 3 0 0 1 0 6M21 21v-3a6 6 0 0 0-4-5"/>',
        'reports' => '<path d="M4 3v18h17M8 16v-5M13 16V7M18 16v-8"/>',
        'profile' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/>',
        'arrow' => '<path d="M5 12h14M14 7l5 5-5 5"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'file' => '<path d="M5 3h10l4 4v14H5zM14 3v5h5M8 13h8M8 17h5"/>',
        'logout' => '<path d="M9 4H4v16h5M10 12h11M17 8l4 4-4 4"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'schedule' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 11h18M7 15h4M7 18h7"/>',
    ];

    return '<svg class="ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($paths[$name] ?? $paths['file'])
        . '</svg>';
}

// --- Định dạng & Kiểm tra dữ liệu đầu vào ---
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now(): string
{
    return gmdate('Y-m-d H:i:s');
}

function money(mixed $value): string
{
    return number_format((float) $value, 0, ',', '.') . ' ₫';
}

function dt(?string $value): string
{
    if (!$value) {
        return '—';
    }
    return (new DateTimeImmutable($value, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))
        ->format('d/m/Y H:i');
}

function localInput(string $value): string
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value, new DateTimeZone('Asia/Ho_Chi_Minh'));
    if (!$d || $d->format('Y-m-d\TH:i') !== $value) {
        throw new DomainException('Ngày giờ không hợp lệ.');
    }
    return $d->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}

function txt(array $d, string $key, int $max = 3000, bool $required = true): string
{
    if (isset($d[$key]) && !is_scalar($d[$key])) {
        throw new DomainException('Trường ' . $key . ' không đúng định dạng.');
    }
    $value = trim((string) ($d[$key] ?? ''));
    if (($required && $value === '') || mb_strlen($value) > $max) {
        throw new DomainException('Trường ' . $key . ' bị thiếu hoặc vượt ' . $max . ' ký tự.');
    }
    return $value;
}

function positiveId(array $d, string $key): int
{
    $v = filter_var($d[$key] ?? null, FILTER_VALIDATE_INT);
    if ($v === false || $v < 1) {
        throw new DomainException('Mã dữ liệu không hợp lệ.');
    }
    return $v;
}

function amount(array $d, string $key, bool $zero = false): int
{
    $v = filter_var($d[$key] ?? null, FILTER_VALIDATE_INT);
    if ($v === false || $v < ($zero ? 0 : 1) || $v > 100000000000) {
        throw new DomainException('Số tiền phải là số nguyên hợp lệ.');
    }
    return $v;
}

// --- Xác thực, Phân quyền & Điều hướng ---
function userById(int $id): ?array
{
    $u = Database::one('SELECT id, email, full_name, phone, is_active FROM users WHERE id=?', [$id]);
    if (!$u || !$u['is_active']) {
        return null;
    }
    $u['id'] = (int) $u['id'];
    $u['roles'] = array_column(
        Database::all('SELECT r.code FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=?', [$id]),
        'code'
    );
    return $u;
}

function hasRole(array $user, string ...$roles): bool
{
    return (bool) array_intersect($user['roles'], $roles);
}

function requireRole(array $user, string ...$roles): void
{
    if (!hasRole($user, ...$roles)) {
        throw new DomainException('Bạn không có quyền thực hiện thao tác này.');
    }
}

function csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function csrfCheck(): void
{
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        throw new DomainException('Phiên biểu mẫu hết hạn. Vui lòng tải lại trang.');
    }
}

function url(string $page, array $params = []): string
{
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}

function redirect(string $target): never
{
    header('Location: ' . $target, true, 303);
    exit();
}

function badge(string $status): string
{
    return '<span class="badge status-' . e(strtolower($status)) . '">'
        . e(STATUS_NAMES[$status] ?? $status)
        . '</span>';
}

// --- Tính toán Tài chính & Kiểm tra quyền trên Hồ sơ ---
function totalQuote(int $quoteId): int
{
    $total = 0;
    $items = Database::all('SELECT quantity, unit_price FROM quotation_items WHERE quotation_id=?', [$quoteId]);
    foreach ($items as $row) {
        $total += (int) round((float) $row['quantity'] * (int) $row['unit_price']);
    }

    $discount = function_exists('quoteAdjustment')
        ? quoteAdjustment($quoteId)
        : ['campaign_discount' => 0, 'voucher_discount' => 0];

    return max(0, $total - (int) $discount['campaign_discount'] - (int) $discount['voucher_discount']);
}

function finances(int $requestId): array
{
    $q = Database::one("SELECT id FROM quotations WHERE request_id=? AND status='ACCEPTED'", [$requestId]);
    $total = $q ? totalQuote((int) $q['id']) : 0;

    $paidRow = Database::one(
        "SELECT COALESCE(SUM(amount), 0) AS n FROM payments WHERE request_id=? AND status='POSTED'",
        [$requestId]
    );
    $paid = (int) ($paidRow['n'] ?? 0);

    return [
        'total' => $total,
        'paid' => $paid,
        'remaining' => $total - $paid,
        'quote' => $q['id'] ?? null,
    ];
}

function requestAccess(array $user, int $id): array
{
    $r = Database::one(
        'SELECT sr.*, s.name service_name, s.service_type, a.name apartment_name, a.address, c.full_name customer_name, c.email customer_email
         FROM service_requests sr
         JOIN services s ON s.id = sr.service_id
         JOIN apartments a ON a.id = sr.apartment_id
         JOIN users c ON c.id = sr.customer_id
         WHERE sr.id = ?',
        [$id]
    );
    if (!$r) {
        throw new DomainException('Không tìm thấy hồ sơ.');
    }

    $allowed = hasRole($user, 'ADMIN')
        || (hasRole($user, 'CUSTOMER') && (int) $r['customer_id'] === $user['id'])
        || (hasRole($user, 'CONSULTANT') && (
            (int) $r['consultant_id'] === $user['id']
            || ($r['status'] === 'NEW' && !$r['consultant_id'])
            || Database::one('SELECT id FROM surveys WHERE request_id=? AND surveyor_id=?', [$id, $user['id']])
        ))
        || Database::one('SELECT id FROM work_orders WHERE request_id=? AND assignee_id=?', [$id, $user['id']]);

    if (!$allowed) {
        throw new DomainException('Bạn không có quyền truy cập hồ sơ này.');
    }

    return $r;
}

function canConsult(array $u, array $r): bool
{
    return hasRole($u, 'CONSULTANT') && (int) $r['consultant_id'] === $u['id'];
}

function isOwner(array $u, array $r): bool
{
    return hasRole($u, 'CUSTOMER') && (int) $r['customer_id'] === $u['id'];
}

// --- Helpers tạo Biểu mẫu (Form Builders) ---
function formStart(string $action, int $request = 0, bool $upload = false, string $class = ''): void
{
    global $page;
    echo '<form method="post"'
        . ($upload ? ' enctype="multipart/form-data"' : '')
        . ' class="' . e($class) . '">'
        . csrf()
        . '<input type="hidden" name="action" value="' . e($action) . '">'
        . '<input type="hidden" name="back" value="' . e($page) . '">'
        . '<input type="hidden" name="back_id" value="' . (int) ($_GET['id'] ?? 0) . '">';

    if ($request) {
        echo '<input type="hidden" name="request_id" value="' . $request . '">';
    }
    hidden('back_request_id', (int) ($_GET['request_id'] ?? 0));
    hidden('back_mode', is_string($_GET['mode'] ?? null) ? $_GET['mode'] : '');
}

function field(string $name, string $label, mixed $value = '', string $type = 'text', bool $required = true): void
{
    echo '<label class="field"><span>' . e($label) . ($required ? ' <b>*</b>' : '') . '</span>'
        . '<input name="' . e($name) . '" type="' . e($type) . '" value="' . e($value) . '"'
        . ($required ? ' required' : '')
        . ($type === 'number' ? ' min="0" max="100000000000" step="any"' : '')
        . ' ' . ($type === 'password' ? 'minlength="10" maxlength="72"' : '')
        . '></label>';
}

function area(string $name, string $label, string $value = '', bool $required = true): void
{
    echo '<label class="field"><span>' . e($label) . ($required ? ' <b>*</b>' : '') . '</span>'
        . '<textarea name="' . e($name) . '" rows="3" maxlength="5000"' . ($required ? ' required' : '') . '>'
        . e($value)
        . '</textarea></label>';
}

function choose(string $name, string $label, array $options, mixed $selected = '', bool $required = true): void
{
    echo '<label class="field"><span>' . e($label) . '</span>'
        . '<select name="' . e($name) . '"' . ($required ? ' required' : '') . '>'
        . '<option value="">Chọn ' . e(mb_strtolower($label)) . '</option>';

    foreach ($options as $key => $text) {
        $isSelected = ((string) $key === (string) $selected) ? ' selected' : '';
        echo '<option value="' . e($key) . '"' . $isSelected . '>' . e($text) . '</option>';
    }
    echo '</select></label>';
}

function hidden(string $name, mixed $value): void
{
    echo '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
}

// --- Truy vấn & Hiển thị Danh sách Hồ sơ ---
function requestScope(array $u): array
{
    if (hasRole($u, 'ADMIN')) {
        return ['1=1', []];
    }

    $clauses = [];
    $args = [];

    if (hasRole($u, 'CUSTOMER')) {
        $clauses[] = 'sr.customer_id=?';
        $args[] = $u['id'];
    }
    if (hasRole($u, 'CONSULTANT')) {
        $clauses[] = "(sr.consultant_id=? OR (sr.status='NEW' AND sr.consultant_id IS NULL) OR EXISTS (SELECT 1 FROM surveys sv WHERE sv.request_id=sr.id AND sv.surveyor_id=?))";
        array_push($args, $u['id'], $u['id']);
    }
    if (hasRole($u, 'DESIGNER', 'INSTALLER', 'MAINTAINER')) {
        $clauses[] = 'EXISTS (SELECT 1 FROM work_orders w WHERE w.request_id=sr.id AND w.assignee_id=?)';
        $args[] = $u['id'];
    }

    return ['(' . implode(' OR ', $clauses ?: ['1=0']) . ')', $args];
}

function requestRows(
    array $u,
    int $limit = 10,
    int $offset = 0,
    string $search = '',
    string $status = '',
    string $type = ''
): array {
    [$where, $params] = requestScope($u);

    if ($search !== '') {
        $where .= ' AND (sr.code LIKE ? OR a.name LIKE ? OR c.full_name LIKE ?)';
        array_push($params, '%' . $search . '%', '%' . $search . '%', '%' . $search . '%');
    }
    if ($status !== '') {
        $where .= ' AND sr.status=?';
        $params[] = $status;
    }
    if ($type !== '') {
        $where .= ' AND s.service_type=?';
        $params[] = $type;
    }

    $from = ' FROM service_requests sr
              JOIN apartments a ON a.id = sr.apartment_id
              JOIN services s ON s.id = sr.service_id
              JOIN users c ON c.id = sr.customer_id
              WHERE ' . $where;

    $sql = 'SELECT sr.*, a.name apartment_name, s.name service_name, s.service_type, c.full_name customer_name'
        . $from
        . ' ORDER BY sr.created_at DESC, sr.id DESC LIMIT ' . max(1, min(1000, $limit))
        . ' OFFSET ' . max(0, $offset);

    return [
        'rows' => Database::all($sql, $params),
        'count' => (int) Database::one('SELECT COUNT(*) n' . $from, $params)['n'],
    ];
}

function requestTable(array $rows): void
{
    if (!$rows) {
        echo '<div class="empty"><span class="empty-symbol">◌</span><h3>Chưa có hồ sơ phù hợp</h3><p>Tạo yêu cầu mới hoặc thay đổi bộ lọc để tiếp tục.</p></div>';
        return;
    }

    echo '<div class="table-scroll"><table><thead><tr><th>Hồ sơ / Căn hộ</th><th>Dịch vụ</th><th>Trạng thái</th><th>Ngày tạo</th><th></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $href = e(url('request', ['id' => $r['id']]));
        echo '<tr>'
            . '<td><a class="table-title" href="' . $href . '">' . e($r['apartment_name']) . '</a>'
            . '<small>' . e($r['code']) . ' · ' . e($r['customer_name']) . '</small></td>'
            . '<td>' . e(TYPE_NAMES[$r['service_type']]) . '</td>'
            . '<td>' . badge($r['status']) . '</td>'
            . '<td>' . e(substr(dt($r['created_at']), 0, 10)) . '</td>'
            . '<td><a class="arrow-link" aria-label="Mở hồ sơ ' . e($r['code']) . '" href="' . $href . '">↗</a></td>'
            . '</tr>';
    }
    echo '</tbody></table></div>';
}
