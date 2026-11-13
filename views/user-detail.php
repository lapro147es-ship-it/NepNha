<?php
$account = Database::one(
    'SELECT id,full_name,email,phone,is_active,created_at FROM users WHERE id=?',
    [(int) ($_GET['id'] ?? 0)]
);
if (!$account) {
    throw new DomainException('Không tìm thấy tài khoản.');
}
$roles = Database::all(
    'SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=?',
    [$account['id']]
);
$work = Database::all(
    "SELECT id,title,status,request_id FROM work_orders WHERE assignee_id=? AND status NOT IN ('DONE','CANCELLED') ORDER BY starts_at LIMIT 20",
    [$account['id']]
);
heading(
    'CHI TIẾT THÀNH VIÊN',
    $account['full_name'],
    $account['email'],
    url('user-form', ['id' => $account['id']]),
    'Chỉnh sửa tài khoản'
);
?>

<a class="back-link" href="<?= e(url('users')) ?>">← Danh sách tài khoản</a>
<div class="detail-grid">
    <section class="panel form-panel">
        <dl class="info-grid">
            <div>
                <dt>Họ và tên</dt>
                <dd><?= e($account['full_name']) ?></dd>
            </div>
            <div>
                <dt>Điện thoại</dt>
                <dd><?= e($account['phone'] ?: 'Chưa cập nhật') ?></dd>
            </div>
            <div>
                <dt>Ngày tạo</dt>
                <dd><?= e(dt($account['created_at'])) ?></dd>
            </div>
            <div>
                <dt>Trạng thái</dt>
                <dd><?= $account['is_active'] ? 'Đang hoạt động' : 'Đã khóa' ?></dd>
            </div>
        </dl>
        <h2>Vai trò được cấp</h2>
        <div class="actions">

            <?php foreach ($roles as $role): ?>
            <span class="badge"><?= e($role['name']) ?></span>
            <?php endforeach; ?>

        </div>
    </section>
    <aside class="panel form-panel">
        <h2>Công việc đang mở</h2>

        <?php foreach ($work as $w): ?>
        <div class="work-summary">
            <a class="text-link" href="<?= e(url('task-detail', ['id' => $w['id']])) ?>"><?= e($w['title']) ?> ↗</a><?= badge($w['status']) ?>
        </div>

        <?php
        endforeach;
        if (!$work):
        ?>

        <p class="muted">Thành viên không có công việc kỹ thuật đang mở.</p>
        <?php endif; ?>

    </aside>
</div>
