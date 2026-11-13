<?php
$r = privateRequest($user, (int) ($_GET['id'] ?? 0));
require __DIR__ . '/partials/request-header.php';
$finance = finances($rid);
$pg = max(1, (int) ($_GET['p'] ?? 1));
$data = moduleRows('payments', $user, $pg, '', '', $rid);
?>

<?php if (hasRole($user, 'CUSTOMER') && $finance['quote'] && $finance['remaining'] > 0): ?>
<section class="panel">
    <h2>Chủ động từng khoản thanh toán</h2>
    <p>Xem công nợ và tạo phiên thanh toán riêng cho hồ sơ này.</p>
    <a class="button" href="<?= e(url('online-payment', ['request_id' => $rid])) ?>">Thanh toán trực tuyến →</a>
</section>
<?php endif; ?>

<div class="finance-cards account-balances">
    <article>
        <span>Báo giá đã chấp thuận</span>
        <strong><?= money($finance['total']) ?></strong>
    </article>
    <article>
        <span>Đã thu thực tế</span>
        <strong><?= money($finance['paid']) ?></strong>
    </article>
    <article>
        <span>Còn phải thu</span>
        <strong><?= money($finance['remaining']) ?></strong>
    </article>
</div>
<section class="panel ledger-panel">
    <div class="panel-heading">
        <div>
            <h2>Các lần thu tiền</h2>
            <p class="muted">Khoản đã hủy ghi nhận vẫn giữ lịch sử và không cộng vào thực thu.</p>
        </div>
        <div class="actions">

            <?php if ($consult &&
                $finance['remaining'] > 0 &&
                in_array($r['status'], ['APPROVED', 'IN_PROGRESS', 'COMPLETED'], true)): ?>

            <a class="button" href="<?= e(url('payment-form', ['request_id' => $rid])) ?>">＋ Ghi nhận thu tiền</a>

            <?php
            endif;
            if ($finance['quote']):
            ?>

            <a class="button secondary" href="<?= e(url('invoice', ['id' => $rid])) ?>">Xem phiếu tổng hợp ↗</a>
            <?php endif; ?>

        </div>
    </div>

    <?php
    moduleTable('payments', $data['rows']);
    pager($data['count'], $pg, $page, ['id' => $rid]);
    ?>

</section>

<?php if (!$finance['quote']): ?>
<p class="muted">Nghĩa vụ thanh toán được xác định sau khi khách hàng chấp thuận báo giá.</p>
<?php endif; ?>
