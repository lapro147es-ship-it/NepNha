<?php
declare(strict_types=1);

final class Auth
{
    public static function changePassword(int $id, array $data): void
    {
        $current = txt($data, 'current_password', 200);
        $next = txt($data, 'new_password', 200);
        $confirm = txt($data, 'confirm_password', 200);

        if (strlen($next) < 10 || strlen($next) > 72 || $next !== $confirm) {
            throw new DomainException('Mật khẩu mới cần 10–72 ký tự và xác nhận trùng khớp.');
        }

        Database::transaction(static function () use ($id, $current, $next): void {
            $row = Database::one('SELECT password_hash FROM users WHERE id=? AND is_active=1', [$id]);
            if (!$row || !password_verify($current, $row['password_hash'])) {
                throw new DomainException('Mật khẩu hiện tại không đúng.');
            }
            if (password_verify($next, $row['password_hash'])) {
                throw new DomainException('Mật khẩu mới cần khác mật khẩu hiện tại.');
            }

            Database::run('UPDATE users SET password_hash=? WHERE id=?', [
                password_hash($next, PASSWORD_DEFAULT),
                $id,
            ]);
        });
    }

    public static function register(array $data): int
    {
        $name = txt($data, 'full_name', 150);
        $email = mb_strtolower(txt($data, 'email', 254));
        $phone = txt($data, 'phone', 20, false);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Email không hợp lệ.');
        }

        $password = (string) ($data['password'] ?? '');
        if (strlen($password) < 10 || strlen($password) > 72 || $password !== ($data['confirm_password'] ?? '')) {
            throw new DomainException('Mật khẩu cần 10–72 ký tự và xác nhận trùng khớp.');
        }

        return Database::transaction(function () use ($name, $email, $password, $phone) {
            if (Database::one('SELECT id FROM users WHERE email=?', [$email])) {
                throw new DomainException('Email đã được đăng ký.');
            }

            $id = Database::insert('users', [
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $name,
                'phone' => $phone,
                'is_active' => 1,
                'created_at' => now(),
            ]);

            $role = Database::one("SELECT id FROM roles WHERE code='CUSTOMER'");
            Database::insert('user_roles', [
                'user_id' => $id,
                'role_id' => $role['id'],
            ]);

            return $id;
        });
    }

    public static function login(array $data, string $ip): int
    {
        $email = mb_strtolower(txt($data, 'email', 254));
        $password = (string) ($data['password'] ?? '');
        $key = hash('sha256', $ip . '|' . $email);
        $clock = time();

        $result = Database::transaction(function () use ($email, $password, $key, $clock) {
            $attempt = Database::one('SELECT * FROM login_attempts WHERE attempt_key=?', [$key]);
            if ($attempt && (int) $attempt['failures'] >= 5 && $clock - (int) $attempt['last_attempt'] < 900) {
                return ['error' => 'Đã thử quá nhiều lần. Vui lòng chờ 15 phút.'];
            }

            $user = Database::one('SELECT * FROM users WHERE email=?', [$email]);
            $hash = $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

            if (!password_verify($password, $hash) || !$user || !$user['is_active']) {
                $failures = ($attempt && $clock - (int) $attempt['last_attempt'] < 900)
                    ? (int) $attempt['failures'] + 1
                    : 1;

                Database::run('DELETE FROM login_attempts WHERE attempt_key=?', [$key]);
                Database::insert('login_attempts', [
                    'attempt_key' => $key,
                    'failures' => $failures,
                    'last_attempt' => $clock,
                ]);

                return ['error' => 'Email hoặc mật khẩu không đúng, hoặc tài khoản đã bị khóa.'];
            }

            Database::run('DELETE FROM login_attempts WHERE attempt_key=?', [$key]);
            return ['id' => (int) $user['id']];
        });

        if (isset($result['error'])) {
            throw new DomainException($result['error']);
        }

        return $result['id'];
    }
}
