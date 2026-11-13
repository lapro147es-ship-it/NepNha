<?php
declare(strict_types=1);

final class ServiceCart
{
    public static function rows(int $user): array
    {
        return Database::all(
            'SELECT c.*,v.name variant_name,v.unit_price,v.unit,v.description,v.is_active variant_active,v.service_id,v.category_id,s.name service_name,s.is_active service_active,cat.is_active category_active,sup.is_active supplier_active FROM cart_items c JOIN service_variants v ON v.id=c.variant_id JOIN services s ON s.id=v.service_id JOIN service_categories cat ON cat.id=v.category_id LEFT JOIN material_suppliers sup ON sup.id=v.supplier_id WHERE c.user_id=? ORDER BY c.id',
            [$user]
        );
    }

    public static function summary(array $rows, int $user, string $voucher = ''): array
    {
        $subtotal = 0;
        $campaignDiscount = 0;
        $items = [];
        foreach ($rows as $row) {
            if (!$row['is_selected']) {
                continue;
            }
            if (
                !$row['variant_active'] ||
                !$row['service_active'] ||
                !$row['category_active'] ||
                ($row['supplier_active'] !== null && !$row['supplier_active'])
            ) {
                throw new DomainException(
                    'Một gói đã ngừng nhận yêu cầu. Hãy bỏ chọn hoặc xóa gói đó.'
                );
            }
            $line = (int) $row['quantity'] * (int) $row['unit_price'];
            $campaign = activeCampaign((int) $row['service_id'], $line);
            $subtotal += $line;
            $campaignDiscount += $campaign['discount'] ?? 0;
            $items[] = $row + ['subtotal' => $line, 'campaign' => $campaign];
        }
        $code = trim($voucher);
        $voucherItem = null;
        $largest = 0;
        foreach ($items as $item) {
            $net = $item['subtotal'] - ($item['campaign']['discount'] ?? 0);
            if ($net > $largest) {
                $largest = $net;
                $voucherItem = $item['id'];
            }
        }
        $v = $code !== '' ? eligibleVoucher($code, $user, $largest) : null;
        return [
            'items' => $items,
            'voucher_item' => $voucherItem,
            'subtotal' => $subtotal,
            'campaign_discount' => $campaignDiscount,
            'voucher_discount' => (int) ($v['discount'] ?? 0),
            'voucher' => $v,
            'total' => $subtotal - $campaignDiscount - (int) ($v['discount'] ?? 0)
        ];
    }

    public static function handle(array $user, string $action, array $data): string
    {
        requireRole($user, 'CUSTOMER');
        if ($action === 'voucher_save') {
            $id = positiveId($data, 'voucher_id');
            $voucher = Database::one(
                'SELECT * FROM vouchers WHERE id=? AND is_active=1 AND ends_at>=?',
                [$id, now()]
            );
            if (!$voucher) {
                throw new DomainException('Ưu đãi không còn được lưu.');
            }
            if (
                !Database::one(
                    'SELECT user_id FROM saved_vouchers WHERE user_id=? AND voucher_id=?',
                    [$user['id'], $id]
                )
            ) {
                Database::insert('saved_vouchers', [
                    'user_id' => $user['id'],
                    'voucher_id' => $id,
                    'created_at' => now()
                ]);
            }
            return url('offers');
        }
        if ($action === 'cart_checkout') {
            return self::checkout($user, $data);
        }
        if ($action === 'cart_add') {
            $variant = positiveId($data, 'variant_id');
            $row = Database::one(
                'SELECT v.* FROM service_variants v JOIN services s ON s.id=v.service_id JOIN service_categories c ON c.id=v.category_id LEFT JOIN material_suppliers p ON p.id=v.supplier_id WHERE v.id=? AND v.is_active=1 AND s.is_active=1 AND c.is_active=1 AND (p.is_active=1 OR p.id IS NULL)',
                [$variant]
            );
            if (!$row) {
                throw new DomainException('Gói dịch vụ không còn nhận yêu cầu.');
            }
            $old = Database::one('SELECT * FROM cart_items WHERE user_id=? AND variant_id=?', [
                $user['id'],
                $variant
            ]);
            $quantity = self::quantity($data['quantity'] ?? 1) + (int) ($old['quantity'] ?? 0);
            self::quantity($quantity);
            if ($old) {
                Database::run('UPDATE cart_items SET quantity=? WHERE id=?', [
                    $quantity,
                    $old['id']
                ]);
            } else {
                if (count(self::rows((int) $user['id'])) >= 20) {
                    throw new DomainException('Danh sách lựa chọn tối đa 20 gói.');
                }
                Database::insert('cart_items', [
                    'user_id' => $user['id'],
                    'variant_id' => $variant,
                    'quantity' => $quantity,
                    'is_selected' => 1
                ]);
            }
        } else {
            $id = positiveId($data, 'id');
            if (
                !Database::one('SELECT id FROM cart_items WHERE id=? AND user_id=?', [
                    $id,
                    $user['id']
                ])
            ) {
                throw new DomainException('Lựa chọn không thuộc tài khoản của bạn.');
            }
            if ($action === 'cart_remove') {
                Database::run('DELETE FROM cart_items WHERE id=? AND user_id=?', [
                    $id,
                    $user['id']
                ]);
            } else {
                Database::run(
                    'UPDATE cart_items SET quantity=?,is_selected=? WHERE id=? AND user_id=?',
                    [
                        self::quantity($data['quantity'] ?? null),
                        isset($data['is_selected']) ? 1 : 0,
                        $id,
                        $user['id']
                    ]
                );
            }
        }
        return url('service-selection');
    }

    private static function quantity(mixed $value): int
    {
        $q = filter_var($value, FILTER_VALIDATE_INT);
        if ($q === false || $q < 1 || $q > 20) {
            throw new DomainException('Số lượng gói cần từ 1 đến 20.');
        }
        return $q;
    }

    private static function checkout(array $user, array $data): string
    {
        $token = txt($data, 'checkout_token', 64);
        $hash = hash('sha256', $user['id'] . '|' . $token);
        $existing = Database::one(
            'SELECT id FROM checkout_batches WHERE token_hash=? AND customer_id=?',
            [$hash, $user['id']]
        );
        if ($existing) {
            return url('selection-receipt', ['id' => $existing['id']]);
        }
        if (
            !hash_equals((string) ($_SESSION['checkout_token'] ?? ''), $token) ||
            strlen($token) < 32
        ) {
            throw new DomainException('Phiên gửi lựa chọn đã hết hạn. Tải lại trang.');
        }
        $apartment = positiveId($data, 'apartment_id');
        if (
            !Database::one('SELECT id FROM apartments WHERE id=? AND customer_id=?', [
                $apartment,
                $user['id']
            ])
        ) {
            throw new DomainException('Căn hộ không thuộc tài khoản của bạn.');
        }
        $summary = self::summary(
            self::rows((int) $user['id']),
            (int) $user['id'],
            txt($data, 'voucher_code', 40, false)
        );
        if (!$summary['items']) {
            throw new DomainException('Cần chọn ít nhất một gói dịch vụ.');
        }
        if ($summary['subtotal'] > 100000000000) {
            throw new DomainException('Tổng lựa chọn vượt giới hạn cho phép.');
        }
        $description = txt($data, 'description', 3000);
        $batch = Database::insert('checkout_batches', [
            'customer_id' => $user['id'],
            'apartment_id' => $apartment,
            'token_hash' => $hash,
            'code' => 'LC-' . strtoupper(bin2hex(random_bytes(5))),
            'voucher_code' => $summary['voucher']['code'] ?? null,
            'estimated_total' => $summary['total'],
            'created_at' => now()
        ]);
        foreach ($summary['items'] as $item) {
            $rid = Database::insert('service_requests', [
                'code' => 'NT-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                'customer_id' => $user['id'],
                'apartment_id' => $apartment,
                'service_id' => $item['service_id'],
                'description' =>
                    $description .
                    "\nGói lựa chọn: " .
                    $item['variant_name'] .
                    ' · ' .
                    $item['quantity'] .
                    ' ' .
                    $item['unit'] .
                    '. Giá trên lựa chọn là ước tính, cần báo giá được chấp thuận.',
                'budget' => $item['subtotal'],
                'status' => 'NEW',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            Database::insert('request_orders', [
                'request_id' => $rid,
                'batch_id' => $batch,
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'estimated_subtotal' => $item['subtotal'],
                'campaign_id' => $item['campaign']['id'] ?? null,
                'voucher_code' =>
                    (int) $item['id'] === (int) $summary['voucher_item']
                        ? $summary['voucher']['code'] ?? null
                        : null
            ]);
            $audit = Database::insert('audit_logs', [
                'actor_id' => $user['id'],
                'request_id' => $rid,
                'entity_type' => 'request',
                'entity_id' => $rid,
                'action' => 'REQUEST_CREATE',
                'new_status' => 'NEW',
                'created_at' => now()
            ]);
            notifyWorkflow($audit, 'REQUEST_CREATE', $rid, (int) $user['id']);
            Database::run('DELETE FROM cart_items WHERE id=? AND user_id=?', [
                $item['id'],
                $user['id']
            ]);
        }
        unset($_SESSION['checkout_token']);
        return url('selection-receipt', ['id' => $batch]);
    }
}
