<?php
declare(strict_types=1);

function customerTier(int $customer): array
{
    $spent = (int) Database::one(
        "SELECT COALESCE(SUM(p.amount),0) n FROM payments p JOIN service_requests r ON r.id=p.request_id WHERE r.customer_id=? AND p.status='POSTED'",
        [$customer]
    )['n'];
    $tier = Database::one(
        'SELECT * FROM membership_tiers WHERE is_active=1 AND min_spend<=? ORDER BY min_spend DESC LIMIT 1',
        [$spent]
    );
    return ($tier ?? [
        'id' => null,
        'name' => 'Chưa xếp hạng',
        'min_spend' => 0,
        'description' => ''
    ]) + ['spent' => $spent];
}

function commercialDiscount(array $rule, int $subtotal): int
{
    $value =
        $rule['discount_kind'] === 'PERCENT'
            ? (int) floor(($subtotal * (int) $rule['discount_value']) / 100)
            : (int) $rule['discount_value'];
    return max(0, min($subtotal, (int) $rule['max_discount'], $value));
}

function activeCampaign(int $service, int $subtotal): ?array
{
    $best = null;
    foreach (
        Database::all(
            'SELECT * FROM campaigns WHERE is_active=1 AND starts_at<=? AND ends_at>=? AND (service_id IS NULL OR service_id=?)',
            [now(), now(), $service]
        )
        as $rule
    ) {
        $rule['discount'] = commercialDiscount($rule, $subtotal);
        if (!$best || $rule['discount'] > $best['discount']) {
            $best = $rule;
        }
    }
    return $best;
}

function eligibleVoucher(
    string $code,
    int $customer,
    int $subtotal,
    ?int $exceptQuote = null
): array {
    $voucher = Database::one(
        'SELECT * FROM vouchers WHERE code=? AND is_active=1 AND starts_at<=? AND ends_at>=?',
        [mb_strtoupper(trim($code)), now(), now()]
    );
    if (!$voucher || $subtotal < (int) $voucher['min_total']) {
        throw new DomainException('Mã ưu đãi không còn hiệu lực hoặc chưa đạt giá trị tối thiểu.');
    }
    if ($voucher['required_tier_id']) {
        $required = Database::one('SELECT * FROM membership_tiers WHERE id=? AND is_active=1', [
            $voucher['required_tier_id']
        ]);
        if (!$required || customerTier($customer)['spent'] < (int) $required['min_spend']) {
            throw new DomainException('Hạng hiện tại chưa đủ điều kiện sử dụng mã này.');
        }
    }
    $uses = Database::all(
        "SELECT customer_id FROM voucher_redemptions WHERE voucher_id=? AND (status='USED' OR (status='RESERVED' AND expires_at>=?)) AND quotation_id<>?",
        [$voucher['id'], now(), $exceptQuote ?? 0]
    );
    $mine = count(
        array_filter($uses, static fn(array $row): bool => (int) $row['customer_id'] === $customer)
    );
    if (count($uses) >= (int) $voucher['max_uses'] || $mine >= (int) $voucher['per_user_limit']) {
        throw new DomainException('Mã ưu đãi đã hết lượt hoặc tài khoản đã dùng hết số lượt.');
    }
    $voucher['discount'] = commercialDiscount($voucher, $subtotal);
    return $voucher;
}

function quoteAdjustment(int $quote): array
{
    return Database::one('SELECT * FROM quotation_adjustments WHERE quotation_id=?', [$quote]) ?? [
        'campaign_discount' => 0,
        'voucher_discount' => 0,
        'label' => '',
        'voucher_id' => null,
        'campaign_id' => null
    ];
}

function saveQuoteAdjustment(int $quote, array $request, array $data, int $subtotal): void
{
    Database::run('DELETE FROM quotation_adjustments WHERE quotation_id=?', [$quote]);
    if (empty($data['apply_benefits'])) {
        return;
    }
    $order = Database::one('SELECT * FROM request_orders WHERE request_id=?', [$request['id']]);
    $campaign = activeCampaign((int) $request['service_id'], $subtotal);
    $campaignDiscount = (int) ($campaign['discount'] ?? 0);
    $code = txt($data, 'voucher_code', 40, false);
    if ($code === '' && $order) {
        $code = $order['voucher_code'] ?? '';
    }
    $voucher =
        $code !== ''
            ? eligibleVoucher(
                $code,
                (int) $request['customer_id'],
                $subtotal - $campaignDiscount,
                $quote
            )
            : null;
    $labels = [];
    if ($campaign) {
        $labels[] = $campaign['name'];
    }
    if ($voucher) {
        $labels[] = $voucher['code'];
    }
    Database::insert('quotation_adjustments', [
        'quotation_id' => $quote,
        'campaign_id' => $campaign['id'] ?? null,
        'voucher_id' => $voucher['id'] ?? null,
        'campaign_discount' => $campaignDiscount,
        'voucher_discount' => $voucher['discount'] ?? 0,
        'label' => mb_substr(implode(' + ', $labels), 0, 500)
    ]);
}

function reserveQuoteBenefits(int $quote): void
{
    $adjustment = quoteAdjustment($quote);
    if (!$adjustment['voucher_id']) {
        return;
    }
    $q = Database::one(
        'SELECT q.*,r.customer_id FROM quotations q JOIN service_requests r ON r.id=q.request_id WHERE q.id=?',
        [$quote]
    );
    $voucher = Database::one('SELECT code FROM vouchers WHERE id=?', [$adjustment['voucher_id']]);
    $afterCampaign = totalQuote($quote) + (int) $adjustment['voucher_discount'];
    $voucher = eligibleVoucher($voucher['code'], (int) $q['customer_id'], $afterCampaign, $quote);
    $expiry = min($voucher['ends_at'], gmdate('Y-m-d H:i:s', time() + 7 * 86400));
    $spec = quotationSpec($quote);
    if (!empty($spec['valid_until'])) {
        $date = new DateTimeImmutable(
            $spec['valid_until'] . ' 23:59:59',
            new DateTimeZone('Asia/Ho_Chi_Minh')
        );
        $expiry = min($expiry, $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
    }
    if ($expiry < now()) {
        throw new DomainException('Không thể giữ ưu đãi cho báo giá đã hết hiệu lực.');
    }
    Database::insert('voucher_redemptions', [
        'voucher_id' => $voucher['id'],
        'customer_id' => $q['customer_id'],
        'quotation_id' => $quote,
        'status' => 'RESERVED',
        'expires_at' => $expiry,
        'created_at' => now()
    ]);
}

function settleQuoteBenefits(int $quote, bool $accepted): void
{
    $use = Database::one('SELECT * FROM voucher_redemptions WHERE quotation_id=?', [$quote]);
    if (!$use) {
        return;
    }
    if ($accepted && $use['expires_at'] < now()) {
        throw new DomainException('Thời gian giữ mã ưu đãi đã hết. Vui lòng yêu cầu báo giá mới.');
    }
    Database::run('UPDATE voucher_redemptions SET status=? WHERE quotation_id=?', [
        $accepted ? 'USED' : 'RELEASED',
        $quote
    ]);
}

function notifyUser(
    int $user,
    string $event,
    string $title,
    string $body,
    string $page = 'requests',
    ?int $id = null
): void {
    if (
        !Database::one('SELECT id FROM users WHERE id=? AND is_active=1', [$user]) ||
        Database::one('SELECT id FROM user_notifications WHERE user_id=? AND event_key=?', [
            $user,
            $event
        ])
    ) {
        return;
    }
    Database::insert('user_notifications', [
        'user_id' => $user,
        'event_key' => $event,
        'title' => mb_substr($title, 0, 180),
        'body' => $body,
        'target_page' => $page,
        'target_id' => $id,
        'created_at' => now()
    ]);
}

function notifyWorkflow(int $logId, string $action, ?int $request, int $actor): void
{
    if (!$request) {
        return;
    }
    if ($action === 'QUOTE_SAVE') {
        $audit = Database::one('SELECT entity_id FROM audit_logs WHERE id=?', [$logId]);
        $quotation = Database::one('SELECT status FROM quotations WHERE id=?', [
            $audit['entity_id'] ?? 0
        ]);
        if (!$quotation || $quotation['status'] === 'DRAFT') {
            return;
        }
    }
    $r = Database::one('SELECT * FROM service_requests WHERE id=?', [$request]);
    $targets = [(int) $r['customer_id'], (int) ($r['consultant_id'] ?? 0)];
    foreach (
        Database::all('SELECT assignee_id FROM work_orders WHERE request_id=?', [$request])
        as $work
    ) {
        $targets[] = (int) $work['assignee_id'];
    }
    if ($action === 'REQUEST_CREATE') {
        foreach (
            Database::all(
                "SELECT DISTINCT u.id FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles ro ON ro.id=ur.role_id WHERE u.is_active=1 AND ro.code IN ('ADMIN','CONSULTANT')"
            )
            as $staff
        ) {
            $targets[] = (int) $staff['id'];
        }
    }
    $names = [
        'QUOTE_SAVE' => 'Báo giá đã cập nhật',
        'QUOTE_RESPONSE' => 'Khách hàng đã phản hồi báo giá',
        'REQUEST_CREATE' => 'Có yêu cầu dịch vụ mới',
        'DESIGN_SUBMIT' => 'Có phiên bản thiết kế mới',
        'DESIGN_REVIEW' => 'Có phản hồi thiết kế'
    ];
    foreach (array_unique($targets) as $target) {
        if ($target > 0 && $target !== $actor) {
            notifyUser(
                $target,
                'audit:' . $logId,
                $names[$action] ?? 'Hồ sơ có cập nhật mới',
                'Hồ sơ ' .
                    $r['code'] .
                    ' vừa được cập nhật. Mở hồ sơ để xem nội dung theo quyền của bạn.',
                'request',
                $request
            );
        }
    }
}
