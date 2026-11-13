<?php
$accountInfo = Database::one('SELECT created_at FROM users WHERE id=?', [$user['id']]);
$accountCode = 'NN-' . str_pad((string) $user['id'], 6, '0', STR_PAD_LEFT);
?>

<?php
if (hasRole($user, 'CUSTOMER')):
    $tier = customerTier((int) $user['id']);
?>

<section class="panel commerce-membership">
    <p class="eyebrow">HẠNG KHÁCH HÀNG</p>
    <h2><?= e($tier['name']) ?></h2>
    <p>Tổng thực thu: <?= money($tier['spent']) ?></p>
    <a class="text-link" href="<?= e(url('offers')) ?>">Xem quyền lợi và ưu đãi →</a>
</section>
<?php endif; ?>

<div class="personal-page-heading">
    <div>
        <p class="eyebrow">NẾP NHÀ / TÀI KHOẢN CỦA BẠN</p>
        <h1>Thông tin cá nhân</h1>
        <p>Một hồ sơ rõ ràng. Một hành trình kết nối.</p>
    </div>
    <a class="button" href="<?= e(url('profile-edit')) ?>"><?= icon('designs') ?> Chỉnh sửa hồ sơ</a>
</div>
<section class="personal-cover">
    <div class="personal-cover-art" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <span class="personal-cover-label">MỖI KHÔNG GIAN BẮT ĐẦU TỪ CON NGƯỜI</span>
    <div class="personal-cover-content">
        <div class="personal-avatar">
            <span><?= e(mb_substr($user['full_name'], 0, 1)) ?></span>
            <span class="personal-active-dot" aria-label="Tài khoản đang hoạt động"></span>
        </div>
        <div class="personal-name">
            <p class="eyebrow">CHÀO MỪNG BẠN ĐẾN NẾP NHÀ</p>
            <h2><?= e($user['full_name']) ?></h2>
            <div class="personal-role-list">

                <?php foreach ($user['roles'] as $role): ?>
                <span><?= e(ROLE_NAMES[$role]) ?></span>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
    <div class="personal-cover-footer">
        <span>MÃ TÀI KHOẢN <strong><?= e($accountCode) ?></strong>
        </span>
        <span>THAM GIA TỪ <strong><?= e(substr(dt($accountInfo['created_at']), 0, 10)) ?></strong>
        </span>
        <span class="personal-active-state">
            <i></i> Đang hoạt động
        </span>
    </div>
</section>
<div class="personal-content-grid">
    <div class="personal-primary">
        <section class="panel personal-contact">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">NHỮNG ĐIỀU CƠ BẢN</p>
                    <h2>Thông tin liên hệ</h2>
                </div>
                <a class="text-link" href="<?= e(url('profile-edit')) ?>">Cập nhật <?= icon('arrow') ?></a>
            </div>
            <dl class="personal-contact-list">
                <div>
                    <dt><?= icon('profile') ?> Họ và tên</dt>
                    <dd><?= e($user['full_name']) ?></dd>
                </div>
                <div>
                    <dt><?= icon('mail') ?> Email đăng nhập</dt>
                    <dd><?= e($user['email']) ?><small>Dùng để đăng nhập vào tài khoản Nếp Nhà.</small>
                    </dd>
                </div>
                <div>
                    <dt><?= icon('phone') ?> Số điện thoại</dt>
                    <dd><?= e($user['phone'] ?: 'Chưa cập nhật') ?>

                        <?php if (!$user['phone']): ?>
                        <a class="text-link" href="<?= e(url('profile-edit')) ?>">Bổ sung thông tin liên hệ
                            <?= icon('arrow') ?></a>
                        <?php else: ?>
                        <small>Giúp người phụ trách liên hệ khi cần trao đổi.</small>
                        <?php endif; ?>

                    </dd>
                </div>
            </dl>
        </section>
        <section class="personal-access-card">
            <span class="personal-access-icon"><?= icon('services') ?></span>
            <div>
                <p class="eyebrow">KHÔNG GIAN LÀM VIỆC</p>
                <h2>Mỗi vai trò, một cách đồng hành.</h2>
                <p>Các mục bạn được xem và thao tác được sắp xếp theo vai trò cùng hồ sơ hoặc công
                    việc được phân công.</p>
                <a class="text-link" href="<?= e(url('dashboard')) ?>">Về trang tổng quan <?= icon('arrow') ?></a>
            </div>
        </section>
    </div>
    <aside class="personal-secondary">
        <section class="personal-security">
            <div class="personal-security-symbol"><?= icon('shield') ?></div>
            <p class="eyebrow">RIÊNG TƯ & AN TÂM</p>
            <h2>Tài khoản của bạn.
                <br>Quyền kiểm soát của bạn.
            </h2>
            <p>Chủ động thay đổi mật khẩu khi cần. Giữ mật khẩu cá nhân và đăng xuất khi sử dụng máy
                dùng chung.</p>
            <a class="button" href="<?= e(url('password')) ?>">Đổi mật khẩu <?= icon('arrow') ?></a>
            <span class="security-footnote"><?= icon('check') ?> Cần xác nhận mật khẩu hiện tại</span>
        </section>
        <a class="personal-assistance" href="<?= e(url('faq')) ?>">
            <span><?= icon('file') ?></span>
            <div>
                <strong>Cần thêm một chút hướng dẫn?</strong>
                <small>Tìm hiểu tài khoản và quy trình dịch vụ</small>
            </div><?= icon('arrow') ?>
        </a>
    </aside>
</div>
