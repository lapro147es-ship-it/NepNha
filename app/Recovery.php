<?php
declare(strict_types=1);

final class Recovery
{
    public static function request(array $data, string $ip): void
    {
        $email = mb_strtolower(txt($data, 'email', 254));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Email không đúng định dạng.');
        }
        $key = hash('sha256', $ip . '|' . $email);
        $message = Database::transaction(static function () use ($email, $key): ?array {
            $cutoff = gmdate('Y-m-d H:i:s', time() - 900);
            $count = (int) Database::one(
                'SELECT COUNT(*) n FROM password_reset_tokens WHERE request_key=? AND created_at>=?',
                [$key, $cutoff]
            )['n'];
            if ($count >= 3) {
                return null;
            }
            $u = Database::one(
                'SELECT id,email,full_name FROM users WHERE email=? AND is_active=1',
                [$email]
            );
            $token = $u ? bin2hex(random_bytes(32)) : null;
            if ($u) {
                Database::run(
                    'UPDATE password_reset_tokens SET used_at=? WHERE user_id=? AND used_at IS NULL',
                    [now(), $u['id']]
                );
            }
            Database::insert('password_reset_tokens', [
                'user_id' => $u['id'] ?? null,
                'request_key' => $key,
                'token_hash' => $token ? hash('sha256', $token) : null,
                'expires_at' => gmdate('Y-m-d H:i:s', time() + 900),
                'created_at' => now()
            ]);
            return $u ? ['user' => $u, 'token' => $token] : null;
        });
        if ($message) {
            try {
                self::mail($message['user'], $message['token']);
            } catch (Throwable $error) {
                error_log('Password recovery delivery failed: ' . $error->getMessage());
            }
        }
    }

    public static function reset(array $data): void
    {
        $token = txt($data, 'reset_token', 64);
        $password = txt($data, 'new_password', 72);
        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token) ||
            strlen($password) < 10 ||
            $password !== ($data['confirm_password'] ?? '')
        ) {
            throw new DomainException(
                'Liên kết không hợp lệ hoặc mật khẩu cần 10–72 ký tự và xác nhận trùng khớp.'
            );
        }
        Database::transaction(static function () use ($token, $password): void {
            $row = Database::one(
                'SELECT t.*,u.is_active FROM password_reset_tokens t JOIN users u ON u.id=t.user_id WHERE t.token_hash=?',
                [hash('sha256', $token)]
            );
            if (!$row || $row['used_at'] || $row['expires_at'] < now() || !$row['is_active']) {
                throw new DomainException(
                    'Liên kết đã hết hạn hoặc đã sử dụng. Hãy yêu cầu liên kết mới.'
                );
            }
            Database::run('UPDATE users SET password_hash=? WHERE id=?', [
                password_hash($password, PASSWORD_DEFAULT),
                $row['user_id']
            ]);
            Database::run(
                'UPDATE password_reset_tokens SET used_at=? WHERE user_id=? AND used_at IS NULL',
                [now(), $row['user_id']]
            );
            notifyUser(
                (int) $row['user_id'],
                'password-reset:' . $row['id'],
                'Mật khẩu đã được đặt lại',
                'Mật khẩu của tài khoản vừa được đặt lại. Các phiên đăng nhập cũ sẽ hết hiệu lực.',
                'profile'
            );
        });
    }

    private static function mail(array $user, string $token): void
    {
        global $config;
        require_once BASE_PATH . '/vendor/autoload.php';
        $base = rtrim((string) ($config['app_url'] ?? ''), '/');
        if ($base === '') {
            $base = 'http://127.0.0.1:' . ($config['local_port'] ?? 8080);
        }
        if (
            !filter_var($base, FILTER_VALIDATE_URL) ||
            !in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true)
        ) {
            throw new RuntimeException('Configure a valid app_url for recovery email.');
        }
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = '8bit';
        $mail->setFrom($config['smtp_from'] ?? 'no-reply@nepnha.test', 'Nếp Nhà');
        $mail->addAddress($user['email'], $user['full_name']);
        $mail->Subject = 'Đặt lại mật khẩu Nếp Nhà';
        $mail->Body =
            "Bạn vừa yêu cầu đặt lại mật khẩu Nếp Nhà.\nLiên kết có hiệu lực 15 phút và chỉ dùng một lần:\n" .
            $base .
            '/index.php?page=reset-password&token=' .
            $token .
            "\nNếu bạn không yêu cầu, hãy bỏ qua thư này.\n";
        if (($config['mail_mode'] ?? 'outbox') === 'smtp') {
            if (empty($config['smtp_host'])) {
                throw new RuntimeException('SMTP host is not configured.');
            }
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->Port = (int) ($config['smtp_port'] ?? 587);
            $mail->SMTPAuth = !empty($config['smtp_user']);
            $mail->Username = $config['smtp_user'] ?? '';
            $mail->Password = $config['smtp_password'] ?? '';
            $mail->SMTPSecure = $config['smtp_encryption'] ?? 'tls';
            $mail->Timeout = 10;
            $mail->send();
        } else {
            $directory = BASE_PATH . '/storage/mail';
            if (!is_dir($directory)) {
                mkdir($directory, 0700, true);
            }
            $mail->preSend();
            if (
                file_put_contents(
                    $directory . '/' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.eml',
                    $mail->getSentMIMEMessage(),
                    LOCK_EX
                ) === false
            ) {
                throw new RuntimeException('Cannot save local development email.');
            }
        }
    }
}
