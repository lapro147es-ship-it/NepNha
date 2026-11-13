<?php
heading(
    'BẢO VỆ TÀI KHOẢN',
    'Đổi mật khẩu',
    'Nhập mật khẩu hiện tại và xác nhận mật khẩu mới của bạn.'
);
?>

<a class="back-link" href="<?= e(url('profile')) ?>">← Thông tin cá nhân</a>
<div class="guided-form-layout">
    <section class="panel form-panel content-width">

        <?php
        formStart('password_change');
        field('current_password', 'Mật khẩu hiện tại', '', 'password');
        field('new_password', 'Mật khẩu mới (10–72 ký tự)', '', 'password');
        field('confirm_password', 'Xác nhận mật khẩu mới', '', 'password');
        ?>

        <p class="file-hint">Mật khẩu mới cần khác mật khẩu hiện tại. Không chia sẻ mật khẩu với
            người
            khác.</p>
        <button class="button" type="submit">Lưu mật khẩu mới</button>
        </form>
    </section>

    <?php
    formGuide(
        'Giữ kết nối, bảo vệ tài khoản.',
        [
            'Nhập đúng mật khẩu hiện tại.',
            'Mật khẩu mới khác mật khẩu cũ và xác nhận trùng khớp.',
            'Không chia sẻ mật khẩu cá nhân.'
        ],
        'profile'
    );
    ?>

</div>
