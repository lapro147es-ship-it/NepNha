<section class="public-section commerce-auth panel">
    <p class="eyebrow">NẾP NHÀ / TÀI KHOẢN</p>
    <h1><?= $page === 'reset-password' ? 'Đặt lại mật khẩu' : 'Quên mật khẩu?' ?></h1>
    <p>Liên kết khôi phục có hiệu lực 15 phút và chỉ sử dụng một lần.</p>

    <?php if ($page === 'reset-password'): ?>
    <?php
    formStart('recovery_reset');
    hidden('reset_token', is_string($_GET['token'] ?? null) ? $_GET['token'] : '');
    field('new_password', 'Mật khẩu mới', '', 'password');
    field('confirm_password', 'Nhập lại mật khẩu', '', 'password');
    ?>

    <button class="button" type="submit">Lưu mật khẩu mới</button>
    </form>
    <?php else: ?>
    <?php
    formStart('recovery_request');
    field('email', 'Email tài khoản', oldValue('email'), 'email');
    ?>

    <button class="button" type="submit">Gửi hướng dẫn khôi phục</button>
    </form>

    <?php if (($config['mail_mode'] ?? 'outbox') === 'outbox'): ?>
    <p class="commerce-note">Bản local đang dùng hộp thư kiểm thử, không gửi email Internet. Người
        chạy đồ án mở thư .eml trong <code>storage/mail</code> để thực hiện demo. Khi cấu hình SMTP,
        thư sẽ được gửi đến email tài khoản.</p>
    <?php endif; ?>
    <?php endif; ?>

    <a class="text-link" href="<?= e(url('login')) ?>">Trở lại đăng nhập →</a>
</section>
