<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

if (PHP_VERSION_ID < 80200) {
    fwrite(STDERR, "Cần PHP 8.2 trở lên. Kiểm tra bằng php -v.\n");
    exit(1);
}

foreach (['pdo', 'mbstring', 'fileinfo', 'session'] as $extension) {
    if (!extension_loaded($extension)) {
        fwrite(STDERR, "Thiếu extension $extension. Kiểm tra php -m và php --ini.\n");
        exit(1);
    }
}

require_once __DIR__ . '/app/Config.php';
$settings = appConfiguration(__DIR__);

$driverExtension = $settings['driver'] === 'mysql' ? 'pdo_mysql' : 'pdo_sqlite';
if (!extension_loaded($driverExtension)) {
    fwrite(STDERR, "Thiếu extension $driverExtension cho cơ sở dữ liệu đang chọn.\n");
    exit(1);
}

if ($settings['driver'] === 'sqlite' && !is_file($settings['sqlite_path'])) {
    if (!is_dir(__DIR__ . '/storage')) {
        mkdir(__DIR__ . '/storage', 0770, true);
    }
    echo "Chưa có SQLite. Đang tạo dữ liệu mẫu lần đầu...\n";
    require __DIR__ . '/bin/install.php';
}

require __DIR__ . '/bin/serve.php';
