<?php
declare(strict_types=1);

final class MasterData
{
    public const KINDS = [
        'categories' => ['table' => 'service_categories', 'title' => 'Nhóm giải pháp'],
        'suppliers' => ['table' => 'material_suppliers', 'title' => 'Nhà cung cấp vật liệu'],
        'variants' => ['table' => 'service_variants', 'title' => 'Gói và phương án dịch vụ'],
        'tiers' => ['table' => 'membership_tiers', 'title' => 'Hạng khách hàng'],
        'campaigns' => ['table' => 'campaigns', 'title' => 'Chương trình ưu đãi'],
        'vouchers' => ['table' => 'vouchers', 'title' => 'Mã ưu đãi']
    ];

    public static function kind(string $kind): array
    {
        return self::KINDS[$kind] ?? throw new DomainException('Danh mục không hợp lệ.');
    }

    public static function save(array $user, string $action, array $data): string
    {
        requireRole($user, 'ADMIN', 'CONSULTANT');
        $kind = txt($data, 'kind', 20);
        $meta = self::kind($kind);
        $table = $meta['table'];
        $id = (int) ($data['id'] ?? 0);
        if ($id && !Database::one('SELECT id FROM ' . $table . ' WHERE id=?', [$id])) {
            throw new DomainException('Không tìm thấy dữ liệu cần sửa.');
        }
        if ($action === 'master_archive') {
            if (!$id) {
                throw new DomainException('Chưa chọn dữ liệu cần ngừng sử dụng.');
            }
            if (
                $kind === 'tiers' &&
                !Database::one(
                    'SELECT id FROM membership_tiers WHERE min_spend=0 AND is_active=1 AND id<>?',
                    [$id]
                )
            ) {
                $current = Database::one('SELECT min_spend FROM membership_tiers WHERE id=?', [
                    $id
                ]);
                if ((int) $current['min_spend'] === 0) {
                    throw new DomainException('Cần giữ một hạng cơ sở có ngưỡng 0 đồng.');
                }
            }
            Database::run('UPDATE ' . $table . ' SET is_active=0 WHERE id=?', [$id]);
        } else {
            $values = self::values($kind, $data);
            if ($kind === 'tiers' && $id) {
                $old = Database::one('SELECT * FROM membership_tiers WHERE id=?', [$id]);
                if (
                    (int) $old['min_spend'] === 0 &&
                    ((int) $values['min_spend'] !== 0 || !$values['is_active'])
                ) {
                    throw new DomainException(
                        'Hạng cơ sở cần giữ ngưỡng 0 và trạng thái hoạt động.'
                    );
                }
            }
            $unique =
                $kind === 'vouchers'
                    ? 'code'
                    : (in_array($kind, ['categories', 'suppliers', 'tiers'], true)
                        ? 'name'
                        : null);
            if (
                $unique &&
                Database::one('SELECT id FROM ' . $table . ' WHERE ' . $unique . '=? AND id<>?', [
                    $values[$unique],
                    $id
                ])
            ) {
                throw new DomainException('Tên hoặc mã đã tồn tại.');
            }
            if (
                $kind === 'tiers' &&
                Database::one('SELECT id FROM membership_tiers WHERE min_spend=? AND id<>?', [
                    $values['min_spend'],
                    $id
                ])
            ) {
                throw new DomainException('Ngưỡng chi tiêu đã được sử dụng.');
            }
            if ($id) {
                $set = implode(
                    ',',
                    array_map(
                        static fn(string $field): string => $field . '=?',
                        array_keys($values)
                    )
                );
                Database::run('UPDATE ' . $table . ' SET ' . $set . ' WHERE id=?', [
                    ...array_values($values),
                    $id
                ]);
            } else {
                $id = Database::insert($table, $values);
            }
        }
        Commerce::audit(
            $user,
            $action === 'master_archive' ? 'MASTER_ARCHIVE' : 'MASTER_SAVE',
            $table,
            $id
        );
        return url('master-data', ['kind' => $kind]);
    }

    private static function foreign(
        array $data,
        string $field,
        string $table,
        bool $optional = false
    ): ?int {
        if ($optional && empty($data[$field])) {
            return null;
        }
        $id = positiveId($data, $field);
        if (!Database::one('SELECT id FROM ' . $table . ' WHERE id=? AND is_active=1', [$id])) {
            throw new DomainException('Dữ liệu liên kết không tồn tại hoặc đã ngừng sử dụng.');
        }
        return $id;
    }

    private static function values(string $kind, array $data): array
    {
        $v = ['is_active' => isset($data['is_active']) ? 1 : 0];
        if ($kind === 'vouchers') {
            $v['code'] = mb_strtoupper(txt($data, 'code', 40));
            if (!preg_match('/^[A-Z0-9_-]{3,40}$/', $v['code'])) {
                throw new DomainException('Mã ưu đãi gồm 3–40 chữ, số, gạch ngang hoặc gạch dưới.');
            }
        } else {
            $v['name'] = txt($data, 'name', 120);
        }
        if ($kind !== 'campaigns') {
            $v['description'] = txt($data, 'description', 3000);
        }
        if ($kind === 'variants') {
            $v['service_id'] = self::foreign($data, 'service_id', 'services');
            $v['category_id'] = self::foreign($data, 'category_id', 'service_categories');
            $v['supplier_id'] = self::foreign($data, 'supplier_id', 'material_suppliers', true);
            $v['unit'] = txt($data, 'unit', 40);
            $v['unit_price'] = amount($data, 'unit_price', true);
        }
        if ($kind === 'tiers') {
            $v['min_spend'] = amount($data, 'min_spend', true);
        }
        if (in_array($kind, ['campaigns', 'vouchers'], true)) {
            $v['discount_kind'] = txt($data, 'discount_kind', 10);
            $v['discount_value'] = amount($data, 'discount_value');
            if (
                !in_array($v['discount_kind'], ['PERCENT', 'FIXED'], true) ||
                ($v['discount_kind'] === 'PERCENT' && $v['discount_value'] > 100)
            ) {
                throw new DomainException('Loại giảm giá không hợp lệ hoặc phần trăm vượt 100.');
            }
            $v['max_discount'] = amount($data, 'max_discount');
            $v['starts_at'] = localInput(txt($data, 'starts_at', 16));
            $v['ends_at'] = localInput(txt($data, 'ends_at', 16));
            if ($v['ends_at'] <= $v['starts_at']) {
                throw new DomainException('Ngày kết thúc phải sau ngày bắt đầu.');
            }
            if ($kind === 'campaigns') {
                $v['service_id'] = self::foreign($data, 'service_id', 'services', true);
            } else {
                $v['min_total'] = amount($data, 'min_total', true);
                $v['required_tier_id'] = self::foreign(
                    $data,
                    'required_tier_id',
                    'membership_tiers',
                    true
                );
                foreach (['max_uses', 'per_user_limit'] as $field) {
                    $n = filter_var($data[$field] ?? null, FILTER_VALIDATE_INT);
                    if ($n === false || $n < 1 || $n > 1000000) {
                        throw new DomainException('Giới hạn lượt phải từ 1 đến 1.000.000.');
                    }
                    $v[$field] = $n;
                }
                if ($v['per_user_limit'] > $v['max_uses']) {
                    throw new DomainException('Lượt mỗi khách không thể vượt tổng lượt.');
                }
            }
        }
        return $v;
    }
}
