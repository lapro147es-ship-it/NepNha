<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

const LOCAL_HOST = '127.0.0.1';
const DEFAULT_PORT = 8080;
const STARTUP_TIMEOUT_SECONDS = 15;
const POLL_INTERVAL_MICROSECONDS = 50000;

function isOurWebsite(string $url): bool
{
    $context = stream_context_create([
        'http' => ['timeout' => 1, 'ignore_errors' => true]
    ]);
    $stream = @fopen($url, 'r', false, $context);

    if (!$stream) {
        return false;
    }

    $html = stream_get_contents($stream, 131072);
    fclose($stream);

    return is_string($html) && str_contains($html, 'content="NepNhaInterior"');
}

function openChrome(string $url): bool
{
    $candidates = [
        (getenv('PROGRAMFILES') ?: 'C:\\Program Files') . '/Google/Chrome/Application/chrome.exe',
        (getenv('PROGRAMFILES(X86)') ?: 'C:\\Program Files (x86)') .
        '/Google/Chrome/Application/chrome.exe',
        (getenv('LOCALAPPDATA') ?: '') . '/Google/Chrome/Application/chrome.exe'
    ];

    foreach ($candidates as $candidate) {
        if (!is_file($candidate)) {
            continue;
        }

        // Both arguments are local, validated values; START detaches the browser from the server.
        $command = 'start "" ' . escapeshellarg($candidate) . ' --new-tab ' . escapeshellarg($url);
        $launcher = popen($command, 'r');

        return is_resource($launcher) && pclose($launcher) === 0;
    }

    return false;
}

$options = getopt('', ['port:', 'no-browser', 'duration:']);
$port = filter_var(
    $options['port'] ?? ($settings['local_port'] ?? DEFAULT_PORT),
    FILTER_VALIDATE_INT
);
$duration = filter_var($options['duration'] ?? 0, FILTER_VALIDATE_INT);

if (
    $port === false ||
    $port < 1024 ||
    $port > 65535 ||
    $duration === false ||
    $duration < 0 ||
    $duration > 3600
) {
    fwrite(STDERR, "Cổng phải từ 1024–65535; thời lượng phải từ 0–3600 giây.\n");
    exit(1);
}

$root = dirname(__DIR__);
$url = 'http://' . LOCAL_HOST . ':' . $port . '/';
$shouldOpenBrowser = !array_key_exists('no-browser', $options);

$socket = @fsockopen(LOCAL_HOST, $port, $errorCode, $errorMessage, 0.025);

if (is_resource($socket)) {
    fclose($socket);
    if (isOurWebsite($url)) {
        echo "Website đã chạy tại $url\n";
        if ($shouldOpenBrowser && !openChrome($url)) {
            echo "Không tự mở được Chrome. Bạn mở địa chỉ trên trong Chrome.\n";
        }
        exit(0);
    }
    fwrite(
        STDERR,
        "Cổng $port đang được chương trình khác sử dụng. Chạy: php start.php --port=8081\n"
    );
    exit(1);
}

if (!is_dir($root . '/storage/tmp')) {
    mkdir($root . '/storage/tmp', 0770, true);
}

$command = [
    PHP_BINARY,
    '-d',
    'upload_max_filesize=10M',
    '-d',
    'post_max_size=12M',
    '-d',
    'upload_tmp_dir=' . $root . '/storage/tmp',
    '-S',
    LOCAL_HOST . ':' . $port,
    '-t',
    $root . '/public',
    $root . '/public/router.php'
];

$process = proc_open(
    $command,
    [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDERR],
    $pipes,
    $root,
    null,
    ['bypass_shell' => true, 'create_process_group' => true]
);

if (!is_resource($process)) {
    fwrite(STDERR, "Không thể khởi động máy chủ PHP.\n");
    exit(1);
}

$stop = false;

if (function_exists('sapi_windows_set_ctrl_handler')) {
    sapi_windows_set_ctrl_handler(static function (int $event) use (&$stop): bool {
        $stop = true;
        return true;
    });
}

$exitCode = 0;

try {
    $deadline = microtime(true) + STARTUP_TIMEOUT_SECONDS;
    $ready = false;

    while (!$stop && microtime(true) < $deadline) {
        if (!proc_get_status($process)['running']) {
            throw new RuntimeException('PHP đã dừng trước khi website sẵn sàng.');
        }

        // Avoid a slow HTTP connection timeout while the PHP socket is not listening yet.
        $probe = @fsockopen(LOCAL_HOST, $port, $errorCode, $errorMessage, 0.025);
        if (is_resource($probe)) {
            fclose($probe);
            if (isOurWebsite($url)) {
                $ready = true;
                break;
            }
        }

        usleep(POLL_INTERVAL_MICROSECONDS);
    }

    if (!$stop && !$ready) {
        throw new RuntimeException(
            'Website chưa sẵn sàng. Kiểm tra CSDL và storage/php-error.log.'
        );
    }

    if ($ready) {
        echo "\nNẾP NHÀ ĐÃ SẴN SÀNG: $url\n";

        if ($shouldOpenBrowser) {
            echo openChrome($url)
                ? "Đã gửi yêu cầu mở Google Chrome.\n"
                : "Không tự mở được Chrome. Bạn mở địa chỉ trên trong Chrome.\n";
        }

        echo "Giữ terminal này mở khi sử dụng website. Nhấn Ctrl+C để dừng.\n\n";
    }

    $started = microtime(true);

    while (!$stop && proc_get_status($process)['running']) {
        if ($duration > 0 && microtime(true) - $started >= $duration) {
            break;
        }

        usleep(POLL_INTERVAL_MICROSECONDS);
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    $exitCode = 1;
} finally {
    fclose($pipes[0]);

    if (proc_get_status($process)['running']) {
        proc_terminate($process);
    }

    proc_close($process);
    echo "\nĐã dừng phiên máy chủ local.\n";
}

exit($exitCode);
