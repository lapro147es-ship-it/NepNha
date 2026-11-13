<section class="auth-layout">
    <div class="auth-story">
        <p class="eyebrow">KHÔNG GIAN SỐNG CỦA BẠN</p>
        <h1>Mỗi ngôi nhà,
            <br>một câu chuyện.
        </h1>
        <img <?= photoAttributes('cozy-corner', true) ?>>
        <p>Theo dõi từng bước hoàn thiện căn hộ cùng Nếp Nhà.</p>
    </div>
    <div class="auth-panel">
        <p class="eyebrow">NẾP NHÀ / TÀI KHOẢN</p>
        <h2>Chào mừng trở lại.</h2>
        <p class="muted">Đăng nhập để tiếp tục chăm chút không gian của bạn.</p>

        <?php
        formStart('login');
        field('email', 'Email', '', 'email');
        field('password', 'Mật khẩu', '', 'password');
        ?>

        <button class="button full" type="submit">Đăng nhập ↗</button>
        </form>
        <p>
            <a class="text-link" href="<?= e(url('forgot-password')) ?>">Quên mật khẩu?</a>
        </p>
        <p>Chưa có tài khoản? <a class="text-link" href="<?= e(url('register')) ?>">Đăng ký ngay</a>
        </p>
        <details class="demo-help">
            <summary>Tài khoản dùng thử cho đồ án</summary>
            <p>Mật khẩu chung: <code>NoiThat@123</code>
            </p>
            <ul>
                <li>Khách hàng: khach@nepnha.test</li>
                <li>Tư vấn: tuvan@nepnha.test</li>
                <li>Thiết kế: thietke@nepnha.test</li>
                <li>Lắp đặt: lapdat@nepnha.test</li>
                <li>Bảo trì: baotri@nepnha.test</li>
                <li>Quản trị: admin@nepnha.test</li>
            </ul>
        </details>
    </div>
</section>
