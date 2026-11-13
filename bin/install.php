<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

require __DIR__ . '/../app/bootstrap.php';

try {
    $exists = Database::one('SELECT id FROM users LIMIT 1');
} catch (PDOException $e) {
    if (!str_contains($e->getMessage(), 'no such table') && !str_contains($e->getMessage(), "doesn't exist")) {
        throw $e;
    }
    $exists = null;
}

if ($exists) {
    fwrite(STDERR, "CSDL đã có dữ liệu; không ghi đè.\n");
    exit(1);
}

Database::pdo()->exec(file_get_contents(BASE_PATH . '/database/' . Database::driver() . '.sql'));
require __DIR__ . '/../database/seed.php';
seedDatabase();

echo "Cài đặt xong. 6 tài khoản demo dùng mật khẩu NoiThat@123\n";
echo "khach@nepnha.test, tuvan@nepnha.test, thietke@nepnha.test, lapdat@nepnha.test, baotri@nepnha.test, admin@nepnha.test\n";
echo "Chạy: php start.php\n";
