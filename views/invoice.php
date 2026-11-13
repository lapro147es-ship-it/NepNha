<?php
$r = privateRequest($user, (int) ($_GET['id'] ?? 0));
$rid = (int) $r['id'];
$finance = finances($rid);
if (!$finance['quote']) {
    throw new DomainException('Hồ sơ chưa có báo giá được chấp thuận để lập phiếu tổng hợp.');
}
$quote = Database::one('SELECT * FROM quotations WHERE id=?', [$finance['quote']]);
$lines = Database::all('SELECT * FROM quotation_items WHERE quotation_id=? ORDER BY id', [
        $finance['quote']
    ]);
    $payments = Database::all('SELECT * FROM payments WHERE request_id=? ORDER BY paid_at,id', [$rid]);
    heading('ĐỐI SOÁT HỒ SƠ', 'Phiếu tổng hợp thanh toán', $r['code']);
?>

<div class="actions no-print">
    <a class="back-link" href="<?= e(url('request-payments', ['id' => $rid])) ?>">← Trang thanh toán</a>
    <button class="button print-button" type="button">In phiếu / Lưu PDF</button>
</div>
<article class="panel form-panel invoice-sheet">
    <header class="invoice-heading">
        <div>
            <h2>Nếp Nhà</h2>
            <p>PHIẾU TỔNG HỢP THANH TOÁN NỘI BỘ</p>
        </div>
        <div>
            <strong><?= e($r['code']) ?></strong>
            <p>Lập lúc <?= e(dt(now())) ?></p>
        </div>
    </header>
    <dl class="info-grid">
        <div>
            <dt>Khách hàng</dt>
            <dd><?= e($r['customer_name']) ?></dd>
        </div>
        <div>
            <dt>Căn hộ</dt>
            <dd><?= e($r['apartment_name']) ?></dd>
        </div>
        <div>
            <dt>Địa chỉ</dt>
            <dd><?= e($r['address']) ?></dd>
        </div>
        <div>
            <dt>Báo giá</dt>
            <dd>Phiên bản <?= (int) $quote['version'] ?> · Duyệt <?= e(dt($quote['responded_at'])) ?></dd>
        </div>
    </dl>
    <h2>Hạng mục đã thống nhất</h2>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Hạng mục</th>
                    <th>SL / Đơn vị</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($lines as $line): ?>
                <tr>
                    <td><?= e($line['item_name']) ?></td>
                    <td><?= e($line['quantity']) ?> <?= e($line['unit']) ?></td>
                    <td><?= money($line['unit_price']) ?></td>
                    <td><?= money(round((float) $line['quantity'] * (int) $line['unit_price'])) ?></td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
    <h2 class="invoice-section-title">Các lần thu đã ghi nhận</h2>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Chứng từ</th>
                    <th>Ngày thu</th>
                    <th>Số tiền</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= e($payment['receipt_code']) ?></td>
                    <td><?= e(dt($payment['paid_at'])) ?></td>
                    <td><?= money($payment['amount']) ?></td>
                    <td><?= e(STATUS_NAMES[$payment['status']]) ?></td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

    <?php
    $benefit = quoteAdjustment((int) $finance['quote']);
    if ($benefit['campaign_discount'] || $benefit['voucher_discount']):
    ?>

    <p>Giá trị trước giảm: <?= money($finance['total'] + $benefit['campaign_discount'] + $benefit['voucher_discount']) ?>. Ưu đãi <?= e($benefit['label']) ?>: − <?= money($benefit['campaign_discount'] + $benefit['voucher_discount']) ?>. Tổng dưới
        đây đã trừ ưu đãi.</p>
    <?php endif; ?>

    <div class="invoice-totals">
        <div class="financial-row">
            <span>Tổng báo giá được duyệt</span>
            <strong><?= money($finance['total']) ?></strong>
        </div>
        <div class="financial-row">
            <span>Đã thu thực tế</span>
            <strong><?= money($finance['paid']) ?></strong>
        </div>
        <div class="financial-row total">
            <span>Còn phải thu</span>
            <strong><?= money($finance['remaining']) ?></strong>
        </div>
    </div>
    <p class="file-hint">Phiếu dùng để tổng hợp và đối chiếu hồ sơ, không phải hóa đơn thuế. Giao
        dịch VOID không được cộng vào thực thu. Số liệu phản ánh trạng thái tại thời điểm mở phiếu.
    </p>
</article>
