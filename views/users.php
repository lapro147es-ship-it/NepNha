<?php
$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$pg = max(1, (int) ($_GET['p'] ?? 1));
$args = ['%' . $search . '%', '%' . $search . '%'];
$count = (int) Database::one(
    'SELECT COUNT(*) n FROM users WHERE full_name LIKE ? OR email LIKE ?',
    $args
)['n'];
$users = Database::all(
    'SELECT id,email,full_name,is_active FROM users WHERE full_name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT 10 OFFSET ' .
    ($pg - 1) * 10,
    $args
);
heading(
    'QUẢN LÝ QUYỀN TRUY CẬP',
    'Tài khoản và vai trò',
    'Xem từng tài khoản, phân quyền theo nhiệm vụ và giữ lịch sử nghiệp vụ.',
    url('user-form'),
    '＋ Tạo tài khoản'
);
summaryCards(
    [
        [
            'label' => 'Tất cả thành viên',
            'value' => Database::one('SELECT COUNT(*) n FROM users')['n'],
            'note' => 'Tài khoản trong hệ thống'
        ],
        [
            'label' => 'Đang hoạt động',
            'value' => Database::one('SELECT COUNT(*) n FROM users WHERE is_active=1')['n'],
            'note' => 'Có thể đăng nhập'
        ],
        [
            'label' => 'Đã khóa',
            'value' => Database::one('SELECT COUNT(*) n FROM users WHERE is_active=0')['n'],
            'note' => 'Giữ lại lịch sử nghiệp vụ'
        ],
        [
            'label' => 'Nhóm vai trò',
            'value' => count(ROLE_NAMES),
            'note' => 'Phân quyền theo nhiệm vụ'
        ]
    ],
    'users'
);
?>

<section class="panel">
    <form class="filters" method="get">
        <input type="hidden" name="page" value="users">
        <label class="search-box">
            <span>⌕</span>
            <input name="q" value="<?= e($search) ?>" placeholder="Tìm họ tên hoặc email"
                aria-label="Tìm tài khoản">
        </label>
        <button class="button secondary">Tìm kiếm</button>
    </form>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Thành viên</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

                <?php
                foreach ($users as $account):
                    $names = array_column(
                        Database::all(
                            'SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=?',
                            [$account['id']]
                        ),
                        'name'
                    );
                ?>

                <tr>
                    <td>
                        <a class="table-title" href="<?= e(url('user-detail', ['id' => $account['id']])) ?>"><?= e($account['full_name']) ?></a>
                        <small><?= e($account['email']) ?></small>
                    </td>
                    <td><?= e(implode(' / ', $names)) ?></td>
                    <td>
                        <span class="badge <?= $account['is_active'] ? 'status-done' : 'status-cancelled' ?>"><?= $account['is_active'] ? 'Đang hoạt động' : 'Đã khóa' ?></span>
                    </td>
                    <td>
                        <a class="text-link" href="<?= e(url('user-detail', ['id' => $account['id']])) ?>">Xem chi tiết ↗</a>
                    </td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

    <?php if (!$users): ?>
    <div class="empty">
        <h2>Không có tài khoản phù hợp</h2>
    </div>

    <?php
    endif;
    pager($count, $pg, 'users', ['q' => $search]);
    ?>

</section>
