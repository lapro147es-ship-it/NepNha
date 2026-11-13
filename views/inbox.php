<?php if (hasRole($user, 'ADMIN')): ?>
<section class="panel form-panel">
    <h2>Gửi thông báo nội bộ</h2>

    <?php
    formStart('notice_publish');
    choose(
        'user_id',
        'Người nhận đang hoạt động',
        array_column(
            Database::all('SELECT id,full_name FROM users WHERE is_active=1 ORDER BY full_name'),
            'full_name',
            'id'
        )
    );
    field('title', 'Tiêu đề');
    area('body', 'Nội dung thông báo');
    ?>

    <button class="button">Gửi vào hộp thông báo</button>
    </form>
    <p class="commerce-note">Chỉ hiển thị trong tài khoản được chọn, không gửi email hoặc tin nhắn
        bên ngoài.</p>
</section>
<?php endif; ?>

<?php
$unread = ($_GET['filter'] ?? '') === 'unread';
$pg = max(1, (int) ($_GET['p'] ?? 1));
$where = 'user_id=?' . ($unread ? ' AND read_at IS NULL' : '');
$count = (int) Database::one('SELECT COUNT(*) n FROM user_notifications WHERE ' . $where, [
        $user['id']
    ])['n'];
    $items = Database::all(
        'SELECT * FROM user_notifications WHERE ' .
        $where .
        ' ORDER BY id DESC LIMIT 15 OFFSET ' .
        ($pg - 1) * 15,
        [$user['id']]
    );
    heading(
        'CẬP NHẬT THEO TÀI KHOẢN',
        'Thông báo của bạn',
        'Trạng thái đã đọc được lưu riêng. Việc cần phản hồi vẫn ở mục riêng để bạn không bỏ lỡ quyết định quan trọng.'
    );
?>

<div class="commerce-chips">
    <a href="<?= e(url('inbox')) ?>">Tất cả</a>
    <a href="<?= e(url('inbox', ['filter' => 'unread'])) ?>">Chưa đọc</a>
    <a href="<?= e(url('notifications')) ?>">Việc cần phản hồi →</a>
</div>

<?php
formStart('notice_read');
hidden('all', 1);
?>

<button class="button secondary">Đánh dấu tất cả đã đọc</button>
</form>
<div class="commerce-stack">

    <?php foreach ($items as $notice): ?>
    <article class="panel commerce-notice <?= $notice['read_at'] ? '' : 'unread' ?>">
        <div>
            <small><?= e(dt($notice['created_at'])) ?> · <?= $notice['read_at'] ? 'Đã đọc' : 'Chưa đọc' ?></small>
            <h2><?= e($notice['title']) ?></h2>
            <p><?= e($notice['body']) ?></p>
            <a class="text-link" href="<?= e(url(array_key_exists($notice['target_page'], routeDefinitions()) ? $notice['target_page'] : 'dashboard', $notice['target_id'] ? ['id' => $notice['target_id']] : [])) ?>">Mở nội dung →</a>
        </div>

        <?php
        if (!$notice['read_at']):

            formStart('notice_read');
            hidden('id', $notice['id']);
        ?>

        <button class="button secondary">Đã đọc</button>
        </form>
        <?php endif; ?>

    </article>
    <?php endforeach; ?>

    <?php if (!$items): ?>
    <div class="panel empty">
        <h2>Bạn đã bắt kịp mọi cập nhật.</h2>
        <p>Thông báo mới sẽ xuất hiện khi hồ sơ hoặc đánh giá có thay đổi liên quan.</p>
    </div>
    <?php endif; ?>

</div>

<?php pager($count, $pg, 'inbox', ['filter' => $unread ? 'unread' : '']); ?>
