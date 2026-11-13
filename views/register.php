<section class="auth-layout">
    <div class="auth-story">
        <p class="eyebrow">BẮT ĐẦU CÙNG NẾP NHÀ</p>
        <h1>Một tài khoản.
            <br>Trọn hành trình.
        </h1>
        <img <?= photoAttributes('warm-bedroom', true) ?>>
        <p>Lưu thông tin căn hộ, duyệt thiết kế và theo dõi tiến độ tại một nơi.</p>
    </div>
    <div class="auth-panel">
        <h2>Tạo tài khoản khách hàng</h2>
        <p class="muted">Chúng mình sẽ cùng bắt đầu từ nhu cầu của bạn.</p>

        <?php
        formStart('register');
        field('full_name', 'Họ và tên');
        field('email', 'Email', '', 'email');
        field('phone', 'Số điện thoại', '', 'tel', false);
        field('password', 'Mật khẩu (10–72 ký tự)', '', 'password');
        field('confirm_password', 'Xác nhận mật khẩu', '', 'password');
        ?>

        <button class="button full" type="submit">Tạo tài khoản ↗</button>
        </form>
        <p>Đã có tài khoản? <a class="text-link" href="<?= e(url('login')) ?>">Đăng nhập</a>
        </p>
    </div>
</section>
