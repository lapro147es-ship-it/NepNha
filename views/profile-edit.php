<?php
heading(
    'CHĂM CHÚT THÔNG TIN',
    'Chỉnh sửa hồ sơ cá nhân',
    'Cập nhật tên và số điện thoại để những trao đổi luôn đúng người, đúng thông tin.'
);
?>

<a class="back-link" href="<?= e(url('profile')) ?>">← Về hồ sơ cá nhân</a>
<div class="guided-form-layout">
    <section class="panel form-panel">
        <div class="profile-edit-heading">
            <span class="person-avatar"><?= e(mb_substr($user['full_name'], 0, 1)) ?></span>
            <div>
                <h2>Thông tin liên hệ</h2>
                <p><?= e($user['email']) ?></p>
            </div>
        </div>

        <?php
        formStart('profile_save');
        field('full_name', 'Họ và tên', oldValue('full_name', $user['full_name']));
        field('phone', 'Số điện thoại', oldValue('phone', $user['phone'] ?? ''), 'tel', false);
        ?>

        <div class="profile-email-note"><?= icon('file') ?><p>Email đăng nhập được giữ nguyên khi chỉnh
                sửa thông tin liên hệ.</p>
        </div>
        <div class="actions">
            <button class="button" type="submit">Lưu thay đổi <?= icon('check') ?></button>
            <a class="button secondary" href="<?= e(url('profile')) ?>">Quay lại hồ sơ</a>
        </div>
        </form>
    </section>

    <?php
    formGuide(
        'Thông tin đúng, kết nối trọn vẹn.',
        [
            'Dùng họ tên đầy đủ để dễ nhận biết trong hồ sơ.',
            'Kiểm tra số điện thoại trước khi lưu.',
            'Nếu cần đổi mật khẩu, mở trang bảo mật riêng.'
        ],
        'profile'
    );
    ?>

</div>
