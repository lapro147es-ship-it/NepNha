<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="application-name" content="NepNhaInterior">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Nếp Nhà — quản lý thiết kế, lắp đặt và bảo trì nội thất căn hộ.">
    <title><?= e($title) ?> · Nếp Nhà</title>
    <link rel="icon" href="assets/mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/portal.css">
    <link rel="stylesheet" href="assets/sections.css">
    <link rel="stylesheet" href="assets/commerce.css">
    <link rel="stylesheet" href="assets/commerce-tables.css">
    <script defer src="assets/app.js"></script>
</head>

<body class="<?= $portal ? 'workspace' : 'public-page' ?> page-<?= e($page) ?>">

    <?php if ($portal): ?>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= e(url('dashboard')) ?>">
            <img src="assets/mark.svg" alt="">
            <span>Nếp Nhà<small>INTERIOR STUDIO</small>
            </span>
        </a>

        <?php
        if (hasRole($user, 'CUSTOMER')):
            $workspaceProjects = Database::all(
                'SELECT sr.id,sr.code,a.name apartment_name FROM service_requests sr JOIN apartments a ON a.id=sr.apartment_id WHERE sr.customer_id=? ORDER BY sr.created_at DESC,sr.id DESC LIMIT 1000',
                [$user['id']]
            );
        ?>

        <form class="workspace-project-picker" method="get">
            <input type="hidden" name="page" value="dashboard">
            <label for="workspace-project">Hồ sơ tổng quan</label>
            <select id="workspace-project" name="project_id">

                <?php foreach ($workspaceProjects as $workspaceProject): ?>
                <option value="<?= (int) $workspaceProject['id'] ?>" <?= (int) ($project['id'] ?? ($_GET['project_id'] ?? 0)) === (int) $workspaceProject['id'] ? 'selected' : '' ?>><?= e($workspaceProject['apartment_name'] . ' · ' . $workspaceProject['code']) ?></option>
                <?php endforeach; ?>

                <?php if (!$workspaceProjects): ?>
                <option value="">Chưa có hồ sơ</option>
                <?php endif; ?>

            </select>
            <button type="submit" <?= !$workspaceProjects ? 'disabled' : '' ?>>Mở tổng quan <?= icon('arrow') ?></button>
        </form>
        <?php endif; ?>

        <p class="nav-label">KHÔNG GIAN LÀM VIỆC</p>
        <nav aria-label="Điều hướng chính">

            <?php
            $nav = [
                'dashboard' => ['◫', 'Tổng quan'],
                'requests' => ['▤', 'Hồ sơ dịch vụ'],
                'notifications' => ['◉', 'Việc cần phản hồi'],
                'tasks' => ['✓', 'Công việc'],
                'designs' => ['◇', 'Bản thiết kế']
            ];
            if (hasRole($user, 'CUSTOMER', 'CONSULTANT', 'ADMIN')) {
                $nav['surveys'] = ['▦', 'Lịch khảo sát'];
                $nav['quotations'] = ['▧', 'Báo giá'];
                $nav['payments'] = ['₫', 'Thanh toán'];
            }
            $nav['maintenance'] = ['✧', 'Lịch sử bảo trì'];
            $nav['services'] = ['⌑', 'Dịch vụ nội thất'];
            if (hasRole($user, 'CUSTOMER')) {
                $nav['apartments'] = ['⌂', 'Căn hộ của tôi'];
                $nav['service-selection'] = ['+', 'Dịch vụ đã chọn'];
                $nav['offers'] = ['◇', 'Ưu đãi & hạng khách'];
            }
            $nav['inbox'] = ['◉', 'Thông báo'];
            if (hasRole($user, 'CUSTOMER', 'ADMIN', 'CONSULTANT')) {
                $nav['reviews'] = ['☆', 'Đánh giá dịch vụ'];
                $nav['online-history'] = ['₫', 'Giao dịch trực tuyến'];
            }
            if (hasRole($user, 'ADMIN', 'CONSULTANT')) {
                $nav['master-data'] = ['⌑', 'Giải pháp & ưu đãi'];
                $nav['consultations'] = ['◉', 'Đăng ký khảo sát nhanh'];
            }
            if (hasRole($user, 'CONSULTANT', 'DESIGNER', 'INSTALLER', 'MAINTAINER')) {
                $nav['schedule'] = ['▦', 'Lịch làm việc'];
            }
            if (hasRole($user, 'ADMIN')) {
                $nav['users'] = ['♧', 'Tài khoản & vai trò'];
                $nav['reports'] = ['▥', 'Báo cáo'];
            }
            $activePage = match (true) {
                str_starts_with($page, 'request'),
                in_array($page, ['document-view', 'document-upload', 'invoice'], true)
                => 'requests',
                str_starts_with($page, 'quotation-') => 'quotations',
                str_starts_with($page, 'survey-') => 'surveys',
                str_starts_with($page, 'task-') => 'tasks',
                str_starts_with($page, 'design-') => 'designs',
                str_starts_with($page, 'payment-') => 'payments',
                str_starts_with($page, 'apartment-') => 'apartments',
                str_starts_with($page, 'user-') => 'users',
                $page === 'service-form' => 'services',
                default => $page
            };
            foreach ($nav as $p => [$symbol, $label]):
            ?>

            <a class="nav-item <?= $activePage === $p ? 'active' : '' ?>" href="<?= e(url($p)) ?>">
                <span><?= icon($p) ?></span><?= e($label) ?>
            </a>
            <?php endforeach; ?>

        </nav>
        <div class="sidebar-bottom">
            <a class="sidebar-help" href="<?= e(url('faq')) ?>"><?= icon('services') ?><span>Cần một chút hướng
                    dẫn?<small>Tìm câu trả lời tại đây</small>
                </span><?= icon('arrow') ?></a>
            <a class="nav-item <?= in_array($page, ['profile', 'profile-edit', 'password'], true) ? 'active' : '' ?>" href="<?= e(url('profile')) ?>">
                <span><?= icon('profile') ?></span>Thông tin cá nhân
            </a>

            <?php formStart('logout'); ?>

            <button class="nav-item logout" type="submit">
                <span><?= icon('logout') ?></span>Đăng xuất
            </button>
            </form>
            <a class="back-to-studio" href="<?= e(url('home')) ?>">← Về trang giới thiệu</a>
        </div>
    </aside>
    <div class="app-shell">
        <header class="topbar">
            <button type="button" class="mobile-toggle" aria-label="Mở menu" aria-controls="sidebar"
                aria-expanded="false">☰</button>
            <div class="breadcrumb">Không gian của bạn <span>/</span>
                <strong><?= e($title) ?></strong>
            </div>
            <form class="top-search" method="get">
                <input type="hidden" name="page" value="requests">
                <label for="portal-search" class="sr-only">Tìm mã hồ sơ hoặc căn hộ</label>
                <input id="portal-search" name="q" placeholder="Tìm mã hồ sơ, căn hộ…"
                    maxlength="100">
                <button type="submit" aria-label="Tìm hồ sơ"><?= icon('search') ?></button>
            </form>
            <a class="top-notifications" href="<?= e(url('notifications')) ?>" aria-label="Mở việc cần phản hồi">
                <?= icon('notifications') ?></a>
            <a class="top-user" href="<?= e(url('profile')) ?>">
                <span class="date-label"><?= date('d.m.Y') ?></span>
                <div class="avatar"><?= e(mb_substr($user['full_name'], 0, 1)) ?></div>
                <span>
                    <strong><?= e($user['full_name']) ?></strong>
                    <small><?= e(ROLE_NAMES[$user['roles'][0]] ?? 'Thành viên') ?></small>
                </span>
            </a>
        </header>
        <main class="main-content">
            <?php else: ?>
            <header class="public-header">
                <a class="brand" href="<?= e(url('home')) ?>">
                    <img src="assets/mark.svg" alt="">
                    <span>Nếp Nhà<small>INTERIOR STUDIO</small>
                    </span>
                </a>
                <button class="public-menu-toggle" type="button" aria-controls="public-nav"
                    aria-expanded="false" aria-label="Mở điều hướng">☰</button>
                <nav id="public-nav" aria-label="Điều hướng website">

                    <?php foreach ([
                                               'catalog' => 'Dịch vụ',
                                               'portfolio' => 'Ý tưởng',
                                               'process' => 'Quy trình',
                                               'about' => 'Giới thiệu',
                                               'faq' => 'Hỏi đáp',
                                               'contact' => 'Tư vấn'
                                           ]
                                           as $target => $label): ?>

                    <a class="<?= $page === $target ? 'active' : '' ?>" href="<?= e(url($target)) ?>"><?= e($label) ?></a>
                    <?php endforeach; ?>

                    <a class="button small" href="<?= e(url($user ? 'dashboard' : 'login')) ?>"><?= $user ? 'Vào hồ sơ ↗' : 'Đăng nhập ↗' ?></a>
                </nav>
            </header>
            <main>
                <?php endif; ?>

                <?php if ($flash): ?>
                <div class="flash <?= e($flash[0]) ?>" role="status"><?= e($flash[1]) ?><button
                        class="dismiss" type="button" aria-label="Đóng thông báo">×</button>
                </div>
                <?php endif; ?>

                <?= $content ?>
            </main>

            <?php if ($portal): ?>
            <footer class="app-footer">
                <span>Nếp Nhà · Thiết kế cho cuộc sống</span>
                <span>Thiết kế · Lắp
                    đặt · Bảo trì</span>
            </footer>
    </div>
    <?php else: ?>
    <footer class="public-footer">
        <a class="brand" href="<?= e(url('home')) ?>">Nếp Nhà</a>
        <span>Đồng hành cùng không gian sống của bạn.</span>
        <a href="<?= e(url('faq')) ?>">Hỏi đáp</a>
        <a href="<?= e(url('booking')) ?>">Đăng ký khảo sát</a>
        <a href="<?= e(url('assistant')) ?>">Trợ lý tư vấn</a>
        <span>Thiết kế · Lắp đặt · Bảo
            trì</span>
    </footer>
    <?php endif; ?>

</body>

</html>
