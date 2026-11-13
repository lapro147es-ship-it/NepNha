<?php
$payment = entityAccess('payments', (int) ($_GET['id'] ?? 0), $user);
$r = privateRequest($user, (int) $payment['request_id']);
require __DIR__ . '/partials/request-header.php';
?>

<section class="panel form-panel">
    <div class="card-head">
        <div>
            <p class="eyebrow">CHỨNG TỪ <?= e($payment['receipt_code']) ?></p>
            <h2><?= money($payment['amount']) ?></h2>
        </div><?= badge($payment['status']) ?>
    </div>
    <dl class="info-grid">
        <div>
            <dt>Phương thức</dt>
            <dd><?= $payment['method'] === 'CASH' ? 'Tiền mặt' : 'Chuyển khoản' ?></dd>
        </div>
        <div>
            <dt>Ngày ghi nhận thu tiền</dt>
            <dd><?= e(dt($payment['paid_at'])) ?></dd>
        </div>
        <div>
            <dt>Người ghi nhận</dt>
            <dd><?= e($payment['actor_name']) ?></dd>
        </div>
        <div>
            <dt>Báo giá liên quan</dt>
            <dd>
                <a class="text-link" href="<?= e(url('quotation-detail', ['id' => $payment['quotation_id']])) ?>">Xem báo giá ↗</a>
            </dd>
        </div>
    </dl>

    <?php if ($payment['status'] === 'VOID'): ?>
    <div class="subtle">
        <strong>Khoản thu đã hủy ghi nhận</strong>
        <p><?= nl2br(e($payment['void_reason'])) ?></p>
        <p><?= e(dt($payment['voided_at'])) ?></p>
    </div>
    <?php endif; ?>

    <p class="muted">Khoản POSTED được cộng vào tổng thực thu; khoản VOID được giữ lại để đối chiếu
        nhưng không cộng vào tổng tiền đã thu.</p>
    <div class="actions">
        <a class="button secondary" href="<?= e(url('invoice', ['id' => $rid])) ?>">Xem phiếu tổng hợp thanh toán ↗</a>

        <?php if ($consult && $payment['status'] === 'POSTED' && in_array($r['status'], ['APPROVED', 'IN_PROGRESS', 'COMPLETED'], true)): ?>
        <a class="button danger" href="<?= e(url('payment-void', ['id' => $payment['id']])) ?>">Điều chỉnh bằng hủy ghi nhận</a>
        <?php endif; ?>

    </div>
</section>
