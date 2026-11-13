<?php
if ($page === 'selection-receipt'):

    $batch = Database::one(
        'SELECT b.*,a.name apartment_name FROM checkout_batches b JOIN apartments a ON a.id=b.apartment_id WHERE b.id=? AND b.customer_id=?',
        [(int) ($_GET['id'] ?? 0), $user['id']]
    );
    if (!$batch) {
        throw new DomainException('Lần gửi lựa chọn không thuộc tài khoản của bạn.');
    }
    heading(
        'LỰA CHỌN ĐÃ ĐƯỢC GỬI',
        $batch['code'],
        'Mỗi dịch vụ được tạo thành hồ sơ riêng để tiếp nhận, khảo sát và gửi báo giá.'
    );
    $requests = Database::all(
        'SELECT r.id,r.code,r.status,s.name FROM request_orders o JOIN service_requests r ON r.id=o.request_id JOIN services s ON s.id=r.service_id WHERE o.batch_id=?',
        [$batch['id']]
    );
?>

<section class="panel">
    <h2><?= e($batch['apartment_name']) ?></h2>
    <p>Tổng ước tính khi gửi: <strong><?= money($batch['estimated_total']) ?></strong>. Chi phí thực hiện chỉ xác lập sau
        khi bạn duyệt báo giá.</p>

    <?php foreach ($requests as $request): ?>
    <a class="commerce-receipt-line" href="<?= e(url('request', ['id' => $request['id']])) ?>">
        <span><?= e($request['code'] . ' · ' . $request['name']) ?></span><?= badge($request['status']) ?>
    </a>
    <?php endforeach; ?>

</section>

<?php
else:

    $rows = ServiceCart::rows((int) $user['id']);
    $voucherCode = mb_substr(trim((string) ($_GET['voucher_code'] ?? '')), 0, 40);
    $summary = null;
    $error = '';
    try {
        $summary = ServiceCart::summary($rows, (int) $user['id'], $voucherCode);
    } catch (DomainException $exception) {
    $error = $exception->getMessage();
}
$_SESSION['checkout_token'] ??= bin2hex(random_bytes(24));
heading(
    'CÙNG LÊN MỘT PHƯƠNG ÁN',
    'Dịch vụ bạn đã chọn',
    'Chọn gói, số lượng và căn hộ để gửi nhu cầu. Đây là bước ước tính trước khảo sát, chưa phải báo giá được chấp thuận.',
    url('catalog'),
    'Chọn thêm dịch vụ'
);
?>

<div class="commerce-two-column">
    <section class="commerce-stack">

        <?php foreach ($rows as $item): ?>
        <article class="panel commerce-cart-item">
            <p class="eyebrow"><?= e($item['service_name']) ?></p>
            <h2><?= e($item['variant_name']) ?></h2>
            <p><?= e($item['description']) ?></p>
            <strong><?= money($item['unit_price']) ?> / <?= e($item['unit']) ?></strong>

            <?php
            formStart('cart_update');
            hidden('id', $item['id']);
            ?>

            <div class="commerce-inline">
                <label class="field">
                    <span>Số lượng</span>
                    <input type="number" name="quantity" min="1" max="20" value="<?= (int) $item['quantity'] ?>"
                        required>
                </label>
                <label class="check">
                    <input type="checkbox" name="is_selected" value="1" <?= $item['is_selected'] ? 'checked' : '' ?>> Gửi gói
                    này
                </label>
                <button class="button secondary">Cập nhật</button>
            </div>
            </form>

            <?php
            formStart('cart_remove');
            hidden('id', $item['id']);
            ?>

            <button class="text-button">Bỏ khỏi lựa chọn</button>
            </form>
        </article>
        <?php endforeach; ?>

        <?php if (!$rows): ?>
        <article class="panel empty">
            <h2>Chọn một giải pháp để bắt đầu.</h2>
            <p>Các gói thiết kế, lắp đặt và chăm sóc đang ở danh mục dịch vụ.</p>
            <a class="button" href="<?= e(url('catalog')) ?>">Khám phá dịch vụ</a>
        </article>
        <?php endif; ?>

    </section>
    <aside class="panel form-panel commerce-checkout">
        <h2>Dự kiến cho lựa chọn</h2>
        <p class="commerce-note">Mỗi mã áp dụng cho một hồ sơ có giá trị sau ưu đãi chương trình lớn
            nhất trong lần gửi. Không nhân mã cho mọi gói.</p>
        <form method="get">
            <input type="hidden" name="page" value="service-selection">

            <?php field('voucher_code', 'Mã ưu đãi', $voucherCode, 'text', false); ?>

            <button class="button secondary">Kiểm tra ưu đãi</button>
        </form>

        <?php if ($error): ?>
        <p class="commerce-error"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($summary): ?>
        <dl class="commerce-totals">
            <div>
                <dt>Giá tham khảo</dt>
                <dd><?= money($summary['subtotal']) ?></dd>
            </div>
            <div>
                <dt>Ưu đãi chương trình</dt>
                <dd>− <?= money($summary['campaign_discount']) ?></dd>
            </div>
            <div>
                <dt>Mã ưu đãi</dt>
                <dd>− <?= money($summary['voucher_discount']) ?></dd>
            </div>
            <div>
                <dt>Tổng dự kiến</dt>
                <dd><?= money($summary['total']) ?></dd>
            </div>
        </dl>

        <?php
        if ($summary['items']):

            formStart('cart_checkout');
            hidden('checkout_token', $_SESSION['checkout_token']);
            hidden('voucher_code', $voucherCode);
            choose(
                'apartment_id',
                'Căn hộ của bạn',
                array_column(
                    Database::all('SELECT id,name FROM apartments WHERE customer_id=?', [
                            $user['id']
                        ]),
                        'name',
                        'id'
                    ),
                    oldValue('apartment_id')
                );
                area('description', 'Mong muốn và thông tin bổ sung', oldValue('description'));
        ?>

        <p class="commerce-note">Ưu đãi được kiểm tra lại khi tư vấn gửi báo giá. Không thu tiền ở
            bước gửi nhu cầu.</p>
        <button class="button full">Gửi các dịch vụ đã chọn →</button>
        </form>
        <?php endif; ?>
        <?php endif; ?>

        <a class="text-link" href="<?= e(url('apartment-form')) ?>">Thêm căn hộ →</a>
    </aside>
</div>
<?php endif; ?>
