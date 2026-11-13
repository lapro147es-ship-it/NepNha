<?php
$tier = customerTier((int) $user['id']);
heading(
    'QUYỀN LỢI TRONG TỪNG CHẶNG',
    'Ưu đãi dành cho bạn',
    'Lưu mã, đọc điều kiện và áp dụng khi gửi lựa chọn. Tư vấn xác nhận lại ưu đãi trên báo giá.'
);
$next = Database::one(
    'SELECT * FROM membership_tiers WHERE is_active=1 AND min_spend>? ORDER BY min_spend LIMIT 1',
    [$tier['spent']]
);
?>

<section class="panel commerce-membership">
    <p class="eyebrow">HẠNG HIỆN TẠI</p>
    <h2><?= e($tier['name']) ?></h2>
    <p><?= e($tier['description']) ?></p>
    <strong>Đã thanh toán thực tế: <?= money($tier['spent']) ?></strong>
    <p><?= $next ? 'Còn ' . money($next['min_spend'] - $tier['spent']) . ' để đạt ' . e($next['name']) : 'Bạn đang ở hạng cao nhất hiện có.' ?></p>
    <p class="commerce-note">Khoản thu bị hủy và thanh toán mô phỏng không được tính nâng hạng.</p>
</section>
<h2>Mã ưu đãi có thể lưu</h2>
<div class="commerce-card-grid">

    <?php foreach (Database::all(
               'SELECT v.*,t.name tier_name,sv.user_id saved FROM vouchers v LEFT JOIN membership_tiers t ON t.id=v.required_tier_id LEFT JOIN saved_vouchers sv ON sv.voucher_id=v.id AND sv.user_id=? WHERE v.is_active=1 AND v.ends_at>=? ORDER BY v.ends_at',
               [$user['id'], now()]
           )
           as $voucher): ?>

    <article class="panel commerce-voucher">
        <p class="eyebrow"><?= e($voucher['code']) ?></p>
        <h2>Giảm <?= $voucher['discount_kind'] === 'PERCENT' ? (int) $voucher['discount_value'] . '%' : money($voucher['discount_value']) ?></h2>
        <p><?= e($voucher['description']) ?></p>
        <ul class="clean-list">
            <li>Giá trị hồ sơ từ <?= money($voucher['min_total']) ?></li>
            <li>Tối đa <?= money($voucher['max_discount']) ?> · <?= (int) $voucher['per_user_limit'] ?> lượt/khách</li>
            <li>Hạng tối thiểu: <?= e($voucher['tier_name'] ?? 'Không yêu cầu') ?></li>
            <li><?= dt($voucher['starts_at']) ?> → <?= dt($voucher['ends_at']) ?></li>
        </ul>

        <?php if ($voucher['saved']): ?>
        <p class="commerce-success">✓ Đã lưu vào tài khoản</p>

        <?php
        else:
            formStart('voucher_save');
            hidden('voucher_id', $voucher['id']);
        ?>

        <button class="button secondary">Lưu ưu đãi</button>
        </form>
        <?php endif; ?>

        <a class="text-link" href="<?= e(url('service-selection', ['voucher_code' => $voucher['code']])) ?>">Kiểm tra với lựa chọn →</a>
    </article>
    <?php endforeach; ?>

</div>
<h2>Chương trình đang diễn ra</h2>
<div class="commerce-card-grid">

    <?php foreach (Database::all(
               'SELECT c.*,s.name service_name FROM campaigns c LEFT JOIN services s ON s.id=c.service_id WHERE c.is_active=1 AND c.starts_at<=? AND c.ends_at>=?',
               [now(), now()]
           )
           as $campaign): ?>

    <article class="panel">
        <h3><?= e($campaign['name']) ?></h3>
        <p><?= e($campaign['service_name'] ?? 'Tất cả dịch vụ') ?></p>
        <p>Giảm <?= $campaign['discount_kind'] === 'PERCENT' ? (int) $campaign['discount_value'] . '%' : money($campaign['discount_value']) ?> · tối đa <?= money($campaign['max_discount']) ?></p>
        <p>Đến <?= dt($campaign['ends_at']) ?></p>
    </article>
    <?php endforeach; ?>

</div>
