<?php
$id = (int) ($_GET['id'] ?? 0);
$account = $id
? Database::one('SELECT id,full_name,email,phone,is_active FROM users WHERE id=?', [$id])
: [];
if ($id && !$account) {
    throw new DomainException('Không tìm thấy tài khoản.');
}
$roles = $id
? array_column(
    Database::all(
        'SELECT r.code FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=?',
        [$id]
    ),
    'code'
)
: [];
$selected = oldValue('roles', $roles);
$active =
($old['action'] ?? '') === 'user_save'
? isset($old['is_active'])
: !$id || $account['is_active'];
heading(
    'TÀI KHOẢN VÀ PHÂN QUYỀN',
    $id ? 'Chỉnh sửa tài khoản' : 'Tạo tài khoản nhân viên',
    'Chọn đúng vai trò tương ứng với nhiệm vụ thực tế.'
);
?>

<a class="back-link" href="<?= e(url('users')) ?>">← Danh sách tài khoản</a>
<div class="guided-form-layout">
    <section class="panel form-panel content-width">

        <?php
        formStart('user_save');
        hidden('id', $id);
        ?>

        <div class="form-grid">

            <?php
            field('full_name', 'Họ và tên', oldValue('full_name', $account['full_name'] ?? ''));
            field(
                'phone',
                'Số điện thoại',
                oldValue('phone', $account['phone'] ?? ''),
                'tel',
                false
            );
            if (!$id) {
                field('email', 'Email đăng nhập', oldValue('email'), 'email');
                field('password', 'Mật khẩu ban đầu (10–72 ký tự)', '', 'password');
            }
            ?>

        </div>

        <?php if ($id): ?>
        <div class="subtle">
            <small>Email đăng nhập</small>
            <p><?= e($account['email']) ?></p>
        </div>
        <?php endif; ?>

        <h2>Vai trò được cấp</h2>
        <div class="checkboxes">

            <?php foreach (ROLE_NAMES as $code => $label): ?>
            <label>
                <input type="checkbox" name="roles[]" value="<?= e($code) ?>" <?= in_array($code, $selected, true) ? 'checked' : '' ?>>
                <?= e($label) ?>
            </label>
            <?php endforeach; ?>

        </div>
        <label class="checkboxes">
            <input type="checkbox" name="is_active" <?= $active ? 'checked' : '' ?>> Tài khoản đang hoạt động
        </label>
        <p class="file-hint">Không thể bỏ quản trị viên cuối cùng, khóa nhân viên còn việc mở hoặc
            bỏ
            vai trò đang được dùng cho hồ sơ phụ trách.</p>
        <button class="button" type="submit">Lưu tài khoản</button>
        </form>
    </section>

    <?php
    formGuide(
        'Đúng người, đúng vai trò.',
        [
            'Dùng email riêng để đăng nhập.',
            'Chỉ cấp vai trò cần thiết cho nhiệm vụ thực tế.',
            'Không khóa nhân viên khi vẫn còn công việc mở.'
        ],
        'users'
    );
    ?>

</div>
