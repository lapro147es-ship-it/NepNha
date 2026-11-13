<?php
if ($page === 'online-return') {
    $result = OnlinePayment::callback($_GET);
    heading(
        'PHẢN HỒI CÓ KIỂM TRA CHỮ KÝ',
        'Đã nhận kết quả từ cổng',
        'Đăng nhập đúng tài khoản để xem chi tiết giao dịch.'
    );
    echo '<section class="panel"><p>Mã tham chiếu: ' .
    e($result['reference_code']) .
    '</p><p>Kết quả: ' .
    e($result['status']) .
    '</p><a class="button" href="' .
    e(url('online-payment', ['id' => $result['id']])) .
    '">Xem giao dịch</a></section>';
    return;
}
$id = (int) ($_GET['id'] ?? 0);
if (!$id) {

    requireRole($user, 'CUSTOMER');
    $rid = (int) ($_GET['request_id'] ?? 0);
    $request = requestAccess($user, $rid);
    $finance = finances($rid);
    if (!$finance['quote'] || $finance['remaining'] < 1) {
        throw new DomainException('Hồ sơ chưa có báo giá được duyệt hoặc đã thanh toán đủ.');
    }
    heading(
        'THANH TOÁN THEO HỒ SƠ',
        $request['code'],
        'Số tiền được kiểm tra theo công nợ hiện tại.'
    );
?>

<section class="panel form-panel">
    <p>Còn lại: <strong><?= money($finance['remaining']) ?></strong>
    </p>
    <p class="commerce-note"><?= ($config['payment_mode'] ?? 'demo') === 'demo' ? 'Chế độ mô phỏng: không trừ tiền, không cộng vào khoản thu thật.' : 'Bạn sẽ được chuyển sang cổng VNPAY đã cấu hình. Sandbox không ghi nhận tiền thật.' ?></p>

    <?php
    formStart('online_pay', $rid);
    field('amount', 'Số tiền muốn thanh toán (đồng)', $finance['remaining'], 'number');
    ?>

    <button class="button">Tạo phiên thanh toán</button>
    </form>
</section>

<?php
return;
}
$intent = OnlinePayment::access($user, $id);
heading(
    'GIAO DỊCH CÓ THỂ ĐỐI SOÁT',
    $intent['reference_code'],
    'Phiên có hiệu lực 15 phút; kết quả lưu riêng với khoản thu thực tế.'
);
?>

<section class="panel form-panel">
    <p class="eyebrow"><?= e($intent['provider']) ?></p>
    <h2><?= money($intent['amount']) ?></h2>
    <dl class="commerce-totals">
        <div>
            <dt>Trạng thái</dt>
            <dd><?= e($intent['status']) ?></dd>
        </div>
        <div>
            <dt>Tạo lúc</dt>
            <dd><?= dt($intent['created_at']) ?></dd>
        </div>
        <div>
            <dt>Hết hạn</dt>
            <dd><?= dt($intent['expires_at']) ?></dd>
        </div>
        <div>
            <dt>Mã cổng</dt>
            <dd><?= e($intent['provider_txn'] ?: 'Chưa có') ?></dd>
        </div>
    </dl>

    <?php if ($intent['provider'] === 'DEMO'): ?>
    <p class="commerce-note">Đây là mô phỏng học tập. Không trừ tiền, không tạo phiếu thu và không
        nâng hạng thành viên.</p>
    <?php endif; ?>

    <?php if ($intent['status'] === 'PENDING' && $intent['expires_at'] >= now() && (int) $intent['customer_id'] === (int) $user['id']): ?>
    <?php
    if ($intent['provider'] === 'DEMO'):

        formStart('demo_payment');
        hidden('id', $id);
    ?>

    <div class="actions">
        <button class="button" name="result" value="success">Mô phỏng thành công</button>
        <button class="button secondary" name="result" value="fail">Mô phỏng thất bại</button>
        <button class="text-button" name="result" value="cancel">Hủy phiên</button>
    </div>
    </form>
    <?php else: ?>
    <a class="button" href="<?= e(OnlinePayment::signedUrl($intent)) ?>">Tiếp tục tại VNPAY →</a>
    <?php endif; ?>

    <?php elseif ($intent['status'] === 'PENDING'): ?>
    <p>Phiên đã hết hạn. Tạo phiên khác từ hồ sơ.</p>
    <?php endif; ?>

    <div class="actions">
        <a class="text-link" href="<?= e(url('request-payments', ['id' => $intent['request_id']])) ?>">Xem công nợ hồ sơ →</a>
        <a class="text-link" href="<?= e(url('online-history')) ?>">Lịch sử giao dịch →</a>
    </div>
</section>
