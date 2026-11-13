<?php
declare(strict_types=1);

function commerceSchemaSql(string $driver): array
{
    $id =
        $driver === 'mysql'
            ? 'bigint PRIMARY KEY AUTO_INCREMENT'
            : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $text = $driver === 'mysql' ? 'LONGTEXT' : 'TEXT';
    $suffix =
        $driver === 'mysql'
            ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            : '';
    $tables = [
        'app_migrations' => 'name varchar(80) PRIMARY KEY, applied_at DATETIME NOT NULL',
        'service_categories' => "id $id, name varchar(120) NOT NULL UNIQUE, description TEXT NOT NULL, is_active int NOT NULL DEFAULT 1",
        'material_suppliers' => "id $id, name varchar(120) NOT NULL UNIQUE, description TEXT NOT NULL, is_active int NOT NULL DEFAULT 1",
        'service_variants' => "id $id, service_id int NOT NULL, category_id bigint NOT NULL, supplier_id bigint, name varchar(150) NOT NULL, description TEXT NOT NULL, unit varchar(40) NOT NULL, unit_price bigint NOT NULL, is_active int NOT NULL DEFAULT 1, FOREIGN KEY(service_id) REFERENCES services(id), FOREIGN KEY(category_id) REFERENCES service_categories(id), FOREIGN KEY(supplier_id) REFERENCES material_suppliers(id)",
        'membership_tiers' => "id $id, name varchar(100) NOT NULL UNIQUE, min_spend bigint NOT NULL UNIQUE, description TEXT NOT NULL, is_active int NOT NULL DEFAULT 1",
        'campaigns' => "id $id, name varchar(150) NOT NULL, service_id int, discount_kind varchar(10) NOT NULL, discount_value bigint NOT NULL, max_discount bigint NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, is_active int NOT NULL DEFAULT 1, FOREIGN KEY(service_id) REFERENCES services(id)",
        'vouchers' => "id $id, code varchar(40) NOT NULL UNIQUE, description TEXT NOT NULL, discount_kind varchar(10) NOT NULL, discount_value bigint NOT NULL, min_total bigint NOT NULL DEFAULT 0, max_discount bigint NOT NULL, max_uses int NOT NULL, per_user_limit int NOT NULL, required_tier_id bigint, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, is_active int NOT NULL DEFAULT 1, FOREIGN KEY(required_tier_id) REFERENCES membership_tiers(id)",
        'saved_vouchers' =>
            'user_id bigint NOT NULL, voucher_id bigint NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(user_id,voucher_id), FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(voucher_id) REFERENCES vouchers(id)',
        'cart_items' => "id $id, user_id bigint NOT NULL, variant_id bigint NOT NULL, quantity int NOT NULL, is_selected int NOT NULL DEFAULT 1, UNIQUE(user_id,variant_id), FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(variant_id) REFERENCES service_variants(id)",
        'checkout_batches' => "id $id, customer_id bigint NOT NULL, apartment_id bigint NOT NULL, token_hash varchar(64) NOT NULL UNIQUE, code varchar(40) NOT NULL UNIQUE, voucher_code varchar(40), estimated_total bigint NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY(customer_id) REFERENCES users(id), FOREIGN KEY(apartment_id) REFERENCES apartments(id)",
        'request_orders' =>
            'request_id bigint PRIMARY KEY, batch_id bigint NOT NULL, variant_id bigint NOT NULL, quantity int NOT NULL, estimated_subtotal bigint NOT NULL, campaign_id bigint, voucher_code varchar(40), FOREIGN KEY(request_id) REFERENCES service_requests(id), FOREIGN KEY(batch_id) REFERENCES checkout_batches(id), FOREIGN KEY(variant_id) REFERENCES service_variants(id), FOREIGN KEY(campaign_id) REFERENCES campaigns(id)',
        'quotation_adjustments' =>
            'quotation_id bigint PRIMARY KEY, campaign_id bigint, voucher_id bigint, campaign_discount bigint NOT NULL DEFAULT 0, voucher_discount bigint NOT NULL DEFAULT 0, label varchar(500) NOT NULL, FOREIGN KEY(quotation_id) REFERENCES quotations(id), FOREIGN KEY(campaign_id) REFERENCES campaigns(id), FOREIGN KEY(voucher_id) REFERENCES vouchers(id)',
        'voucher_redemptions' => "id $id, voucher_id bigint NOT NULL, customer_id bigint NOT NULL, quotation_id bigint NOT NULL UNIQUE, status varchar(12) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY(voucher_id) REFERENCES vouchers(id), FOREIGN KEY(customer_id) REFERENCES users(id), FOREIGN KEY(quotation_id) REFERENCES quotations(id)",
        'service_reviews' => "id $id, request_id bigint NOT NULL UNIQUE, customer_id bigint NOT NULL, service_id int NOT NULL, rating int NOT NULL, content TEXT NOT NULL, status varchar(12) NOT NULL DEFAULT 'PUBLISHED', reply TEXT, replied_by bigint, replied_at DATETIME, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY(request_id) REFERENCES service_requests(id), FOREIGN KEY(customer_id) REFERENCES users(id), FOREIGN KEY(service_id) REFERENCES services(id), FOREIGN KEY(replied_by) REFERENCES users(id)",
        'review_images' =>
            'review_id bigint PRIMARY KEY, storage_key varchar(100) NOT NULL UNIQUE, mime_type varchar(50) NOT NULL, size_bytes bigint NOT NULL, FOREIGN KEY(review_id) REFERENCES service_reviews(id)',
        'user_notifications' => "id $id, user_id bigint NOT NULL, event_key varchar(120) NOT NULL, title varchar(180) NOT NULL, body TEXT NOT NULL, target_page varchar(60) NOT NULL, target_id bigint, created_at DATETIME NOT NULL, read_at DATETIME, UNIQUE(user_id,event_key), FOREIGN KEY(user_id) REFERENCES users(id)",
        'password_reset_tokens' => "id $id, user_id bigint, request_key varchar(64) NOT NULL, token_hash varchar(64) UNIQUE, expires_at DATETIME NOT NULL, used_at DATETIME, created_at DATETIME NOT NULL, FOREIGN KEY(user_id) REFERENCES users(id)",
        'consultation_leads' => "id $id, reference_code varchar(40) NOT NULL UNIQUE, full_name varchar(150) NOT NULL, email varchar(254) NOT NULL, phone varchar(30) NOT NULL, address varchar(500) NOT NULL, service_id int NOT NULL, content TEXT NOT NULL, preferred_date varchar(10), status varchar(15) NOT NULL DEFAULT 'NEW', staff_note TEXT, handled_by bigint, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY(service_id) REFERENCES services(id), FOREIGN KEY(handled_by) REFERENCES users(id)",
        'payment_intents' => "id $id, request_id bigint NOT NULL, customer_id bigint NOT NULL, reference_code varchar(60) NOT NULL UNIQUE, amount bigint NOT NULL, provider varchar(20) NOT NULL, status varchar(20) NOT NULL DEFAULT 'PENDING', provider_txn varchar(100), payment_id bigint UNIQUE, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY(request_id) REFERENCES service_requests(id), FOREIGN KEY(customer_id) REFERENCES users(id), FOREIGN KEY(payment_id) REFERENCES payments(id)"
    ];
    return array_map(
        static fn(
            string $name,
            string $body
        ): string => "CREATE TABLE IF NOT EXISTS $name ($body)$suffix;",
        array_keys($tables),
        array_values($tables)
    );
}

function ensureCommerceSchema(): void
{
    try {
        if (Database::one("SELECT name FROM app_migrations WHERE name='commerce_v1'")) {
            return;
        }
    } catch (PDOException $error) {
        if (
            !str_contains($error->getMessage(), 'no such table') &&
            !str_contains($error->getMessage(), "doesn't exist")
        ) {
            throw $error;
        }
    }
    try {
        Database::one('SELECT id FROM services LIMIT 1');
    } catch (PDOException $error) {
        if (
            str_contains($error->getMessage(), 'no such table') ||
            str_contains($error->getMessage(), "doesn't exist")
        ) {
            return;
        }
        throw $error;
    }
    foreach (commerceSchemaSql(Database::driver()) as $sql) {
        Database::pdo()->exec($sql);
    }
    Database::transaction(static function (): void {
        if (Database::one("SELECT name FROM app_migrations WHERE name='commerce_v1'")) {
            return;
        }
        Database::insert('service_categories', [
            'name' => 'Thiết kế và hoàn thiện',
            'description' => 'Giải pháp tổ chức không gian và hoàn thiện căn hộ.',
            'is_active' => 1
        ]);
        Database::insert('service_categories', [
            'name' => 'Lắp đặt và chăm sóc',
            'description' => 'Thi công, lắp đặt và bảo trì theo hồ sơ được duyệt.',
            'is_active' => 1
        ]);
        Database::insert('material_suppliers', [
            'name' => 'Xưởng nội thất mẫu',
            'description' =>
                'Nhà cung cấp minh họa của đồ án; không phải xác nhận đối tác thực tế.',
            'is_active' => 1
        ]);
        foreach (
            [['Đồng hành', 0], ['Gắn kết', 50000000], ['Thân thiết', 150000000]]
            as [$name, $spend]
        ) {
            Database::insert('membership_tiers', [
                'name' => $name,
                'min_spend' => $spend,
                'description' =>
                    'Xét theo tổng khoản thu POSTED của tài khoản; khoản VOID không được tính.',
                'is_active' => 1
            ]);
        }
        Database::insert('vouchers', [
            'code' => 'NEPNHA5',
            'description' => 'Ưu đãi mẫu 5%, tối đa 500.000đ, áp dụng khi báo giá đủ điều kiện.',
            'discount_kind' => 'PERCENT',
            'discount_value' => 5,
            'min_total' => 1000000,
            'max_discount' => 500000,
            'max_uses' => 100,
            'per_user_limit' => 1,
            'starts_at' => gmdate('Y-m-d H:i:s', time() - 86400),
            'ends_at' => gmdate('Y-m-d H:i:s', time() + 365 * 86400),
            'is_active' => 1
        ]);
        Database::insert('app_migrations', ['name' => 'commerce_v1', 'applied_at' => now()]);
    });
    seedCommerceVariants();
}

function seedCommerceVariants(): void
{
    $category = Database::one('SELECT id FROM service_categories ORDER BY id LIMIT 1');
    $supplier = Database::one('SELECT id FROM material_suppliers ORDER BY id LIMIT 1');
    if (!$category) {
        return;
    }
    foreach (Database::all('SELECT * FROM services') as $service) {
        if (Database::one('SELECT id FROM service_variants WHERE service_id=?', [$service['id']])) {
            continue;
        }
        foreach ([['Cơ bản', 1], ['Mở rộng', 1.25]] as [$name, $factor]) {
            Database::insert('service_variants', [
                'service_id' => $service['id'],
                'category_id' => $category['id'],
                'supplier_id' => $supplier['id'] ?? null,
                'name' => $name . ' · ' . $service['name'],
                'description' =>
                    'Gói tham khảo để lựa chọn ban đầu. Quy cách, vật liệu và chi phí chính thức cần được xác nhận trong báo giá.',
                'unit' => 'gói',
                'unit_price' => (int) round($service['reference_price'] * $factor),
                'is_active' => $service['is_active']
            ]);
        }
    }
}
