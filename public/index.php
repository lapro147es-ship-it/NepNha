<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/php-error.log');

// --- Khởi tạo phiên làm việc (Session & Security Headers) ---
$sessionPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0770, true);
}

session_save_path($sessionPath);
session_name('nepnha_session');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'path' => '/',
]);
session_start();

header('Content-Type: text/html; charset=UTF-8');
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header(
    "Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'"
);

// --- Nạp ứng dụng & Kiểm tra cơ sở dữ liệu ---
try {
    require __DIR__ . '/../app/bootstrap.php';
    require BASE_PATH . '/app/Auth.php';
    Database::one('SELECT id FROM roles LIMIT 1');
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(503);
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>Cài đặt Nếp Nhà</title><h1>Cần cài đặt cơ sở dữ liệu</h1><p>Tại thư mục dự án, chạy <code>php bin/install.php</code> rồi tải lại trang.</p></html>';
    exit();
}

// --- Xác thực người dùng hiện tại ---
$user = isset($_SESSION['user_id']) ? userById((int) $_SESSION['user_id']) : null;
if ($user && isset($_SESSION['auth_stamp'])) {
    $currentHash = Database::one('SELECT password_hash FROM users WHERE id=?', [$user['id']]);
    if (!hash_equals($_SESSION['auth_stamp'], hash('sha256', $currentHash['password_hash']))) {
        $user = null;
    }
}
if (!$user) {
    unset($_SESSION['user_id']);
}

$page = (string) ($_GET['page'] ?? ($user ? 'dashboard' : 'home'));
$routes = routeDefinitions();

// --- Các endpoint đặc biệt (IPN Thanh toán & Ảnh đánh giá công khai) ---
if ($page === 'payment-ipn') {
    header('Content-Type: application/json; charset=UTF-8');
    try {
        $result = OnlinePayment::callback($_GET);
        echo json_encode([
            'RspCode' => !empty($result['duplicate']) ? '02' : '00',
            'Message' => 'Confirm received',
        ]);
    } catch (DomainException $error) {
        echo json_encode(['RspCode' => '97', 'Message' => 'Invalid payment data']);
    } catch (Throwable $error) {
        error_log((string) $error);
        echo json_encode(['RspCode' => '99', 'Message' => 'Retry later']);
    }
    exit();
}

if ($page === 'review-photo') {
    $photo = Database::one(
        "SELECT i.* FROM review_images i JOIN service_reviews r ON r.id=i.review_id WHERE r.id=? AND r.status='PUBLISHED'",
        [(int) ($_GET['id'] ?? 0)]
    );
    if (!$photo || !is_file(BASE_PATH . '/storage/uploads/' . basename($photo['storage_key']))) {
        http_response_code(404);
        exit();
    }
    header('Content-Type: ' . $photo['mime_type']);
    header('Cache-Control: no-store');
    readfile(BASE_PATH . '/storage/uploads/' . basename($photo['storage_key']));
    exit();
}

$pages = array_keys($routes);
if (!in_array($page, $pages, true)) {
    http_response_code(404);
    $page = 'notfound';
}

// --- Xử lý các yêu cầu gửi biểu mẫu (POST Actions) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $back = (string) ($_POST['back'] ?? 'dashboard');
    if (!in_array($back, $pages, true) || $back === 'download') {
        $back = 'dashboard';
    }

    $backId = (int) ($_POST['back_id'] ?? 0);
    $backRequest = (int) ($_POST['back_request_id'] ?? 0);
    $backMode = (string) ($_POST['back_mode'] ?? '');
    $backParams = [];

    if ($backId) {
        $backParams['id'] = $backId;
    }
    if ($backRequest) {
        $backParams['request_id'] = $backRequest;
    }
    if (in_array($backMode, ['receive', 'reject', 'cancel', 'close'], true)) {
        $backParams['mode'] = $backMode;
    }

    $target = url($back, $backParams);

    try {
        csrfCheck();
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'login') {
            $id = Auth::login($_POST, $_SERVER['REMOTE_ADDR'] ?? 'local');
            session_regenerate_id(true);
            $_SESSION['user_id'] = $id;
            $pwdRow = Database::one('SELECT password_hash FROM users WHERE id=?', [$id]);
            $_SESSION['auth_stamp'] = hash('sha256', $pwdRow['password_hash']);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $target = $_SESSION['after_login'] ?? url('dashboard');
            unset($_SESSION['after_login']);
        } elseif ($action === 'register') {
            Auth::register($_POST);
            $_SESSION['flash'] = ['success', 'Tạo tài khoản thành công. Bạn có thể đăng nhập.'];
            $target = url('login');
        } elseif ($action === 'logout') {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $target = url('home');
        } elseif ($action === 'recovery_request') {
            Recovery::request($_POST, $_SERVER['REMOTE_ADDR'] ?? 'local');
            $_SESSION['flash'] = [
                'success',
                'Nếu tài khoản hợp lệ, hướng dẫn đặt lại mật khẩu sẽ được gửi. Liên kết có hiệu lực 15 phút.',
            ];
            $target = url('forgot-password');
        } elseif ($action === 'recovery_reset') {
            $target = url('reset-password', [
                'token' => is_string($_POST['reset_token'] ?? null) ? $_POST['reset_token'] : '',
            ]);
            Recovery::reset($_POST);
            $_SESSION['flash'] = ['success', 'Đã đặt lại mật khẩu. Hãy đăng nhập bằng mật khẩu mới.'];
            $target = url('login');
        } elseif (Commerce::supports($action)) {
            $target = Commerce::handle($user, $action, $_POST, $_FILES);
            $_SESSION['flash'] = ['success', 'Đã lưu thay đổi thành công.'];
        } elseif (in_array($action, ['online_pay', 'demo_payment'], true)) {
            if (!$user) {
                throw new DomainException('Vui lòng đăng nhập để thanh toán.');
            }
            $target = $action === 'online_pay'
                ? OnlinePayment::create($user, $_POST)
                : OnlinePayment::demo($user, $_POST);
        } elseif ($action === 'password_change') {
            if (!$user) {
                throw new DomainException('Vui lòng đăng nhập.');
            }
            Auth::changePassword($user['id'], $_POST);
            session_regenerate_id(true);
            $pwdRow = Database::one('SELECT password_hash FROM users WHERE id=?', [$user['id']]);
            $_SESSION['auth_stamp'] = hash('sha256', $pwdRow['password_hash']);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $_SESSION['flash'] = [
                'success',
                'Đã đổi mật khẩu. Các phiên đăng nhập cũ có dấu xác thực không còn hợp lệ.',
            ];
            $target = url('profile');
        } else {
            if (!$user) {
                throw new DomainException('Vui lòng đăng nhập để tiếp tục.');
            }
            $id = (new Workflow($user))->handle($action, $_POST, $_FILES);
            $_SESSION['flash'] = ['success', 'Đã lưu thay đổi thành công.'];
            $target = actionDestination($action, $id, $_POST);
        }
    } catch (DomainException $e) {
        $_SESSION['flash'] = ['error', $e->getMessage()];
        $safeOld = $_POST;
        foreach (['csrf', 'password', 'current_password', 'confirm_password', 'new_password', 'reset_token'] as $secret) {
            unset($safeOld[$secret]);
        }
        $_SESSION['old'] = $safeOld;
    } catch (Throwable $e) {
        error_log((string) $e);
        $_SESSION['flash'] = ['error', 'Không lưu được dữ liệu. Vui lòng kiểm tra và thử lại.'];
    }

    redirect($target);
}

// --- Điều hướng & Kiểm tra quyền truy cập trang (GET Routes) ---
$route = $routes[$page] ?? [
    'title' => 'Không tìm thấy trang',
    'view' => 'notfound',
    'public' => true,
    'roles' => [],
];

if (!$user && !$route['public']) {
    $return = [];
    foreach (['id', 'request_id', 'service_id', 'apartment_id', 'parent_request_id'] as $key) {
        if (!empty($_GET[$key]) && filter_var($_GET[$key], FILTER_VALIDATE_INT)) {
            $return[$key] = (int) $_GET[$key];
        }
    }
    $_SESSION['after_login'] = url($page, $return);
    redirect(url('login'));
}

if ($user && in_array($page, ['login', 'register'], true)) {
    redirect(url('dashboard'));
}

// --- Tải xuống hoặc xem trước tài liệu đính kèm ---
if (in_array($page, ['download', 'file-content'], true)) {
    try {
        $attachment = attachmentAccess($user, (int) ($_GET['id'] ?? 0));
        if ($page === 'file-content' && !in_array($attachment['mime_type'], ['image/png', 'image/jpeg'], true)) {
            throw new DomainException('Chỉ xem trước tệp ảnh.');
        }

        $path = BASE_PATH . '/storage/uploads/' . basename($attachment['storage_key']);
        if (!is_file($path)) {
            throw new DomainException('Tệp lưu trữ không tồn tại.');
        }

        $disposition = $page === 'file-content' ? 'inline' : 'attachment';
        $ext = pathinfo($attachment['storage_key'], PATHINFO_EXTENSION);

        header('Content-Type: ' . $attachment['mime_type']);
        header(sprintf(
            'Content-Disposition: %s; filename="document.%s"; filename*=UTF-8\'\'%s',
            $disposition,
            $ext,
            rawurlencode($attachment['original_name'])
        ));
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, no-store');
        readfile($path);
        exit();
    } catch (DomainException $e) {
        http_response_code(403);
        echo e($e->getMessage());
        exit();
    }
}

// --- Render Giao diện ---
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);

$title = $route['title'];
$portal = $user && !$route['public'];

ob_start();
try {
    if ($route['roles']) {
        requireRole($user, ...$route['roles']);
    }
    $view = BASE_PATH . '/views/' . $route['view'] . '.php';
    if (is_file($view)) {
        require $view;
    } else {
        echo '<div class="empty"><h2>Không tìm thấy trang</h2><p>Trang bạn yêu cầu không tồn tại.</p></div>';
    }
} catch (DomainException $e) {
    http_response_code(403);
    echo '<div class="empty"><h2>Không thể mở nội dung</h2><p>'
        . e($e->getMessage())
        . '</p><a class="button" href="' . e(url('dashboard')) . '">Về tổng quan</a></div>';
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    echo '<div class="empty"><h2>Không tải được dữ liệu</h2><p>Vui lòng tải lại trang hoặc kiểm tra cơ sở dữ liệu.</p></div>';
}

$content = ob_get_clean();
require BASE_PATH . '/views/layout.php';
