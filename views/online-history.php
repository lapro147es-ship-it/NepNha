<?php
heading(
    'THEO DÕI TỪNG LẦN THANH TOÁN',
    'Giao dịch trực tuyến',
    'DEMO_SUCCESS và SANDBOX_SUCCESS là kết quả thử nghiệm, không được cộng vào thực thu.'
);
$where = hasRole($user, 'ADMIN')
? '1=1'
: (hasRole($user, 'CUSTOMER')
    ? 'p.customer_id=?'
    : 'r.consultant_id=?');
    $rows = Database::all(
        'SELECT p.*,r.code request_code FROM payment_intents p JOIN service_requests r ON r.id=p.request_id WHERE ' .
        $where .
        ' ORDER BY p.id DESC LIMIT 100',
        hasRole($user, 'ADMIN') ? [] : [$user['id']]
    );
?>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tham chiếu</th>
                    <th>Hồ sơ</th>
                    <th>Số tiền</th>
                    <th>Kênh / trạng thái</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($rows as $item): ?>
                <tr>
                    <td>
                        <a href="<?= e(url('online-payment', ['id' => $item['id']])) ?>"><?= e($item['reference_code']) ?></a>
                    </td>
                    <td><?= e($item['request_code']) ?></td>
                    <td><?= money($item['amount']) ?></td>
                    <td><?= e($item['provider'] . ' / ' . $item['status']) ?></td>
                    <td><?= dt($item['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

    <?php if (!$rows): ?>
    <p class="empty">Chưa có giao dịch trực tuyến.</p>
    <?php endif; ?>

</section>
