<?php
declare(strict_types=1);
return [
    'local_port' => 8080, // Cổng local khi chạy php start.php, có thể đổi theo bản dự án.
    'app_url' => '', // SMTP/VNPAY: URL gốc chính xác, ví dụ https://ten-mien-cua-ban.vn.
    'mail_mode' => 'outbox', // outbox: thư thử trong storage/mail; smtp: gửi qua cấu hình dưới.
    'smtp_host' => '',
    'smtp_port' => 587,
    'smtp_user' => '',
    'smtp_password' => '',
    'smtp_from' => 'no-reply@nepnha.test',
    'smtp_encryption' => 'tls',
    'payment_mode' => 'demo', // demo, vnpay_sandbox hoặc vnpay; không nhập khóa thật vào Git.
    'vnpay_tmn_code' => '',
    'vnpay_hash_secret' => '',
    'driver' => 'sqlite', // sqlite hoặc mysql
    'sqlite_path' => __DIR__ . '/storage/app.sqlite',
    'mysql_host' => '127.0.0.1',
    'mysql_port' => 3306,
    'mysql_database' => 'noithat_canho',
    'mysql_user' => 'root',
    'mysql_password' => '',
    'debug' => false
];
