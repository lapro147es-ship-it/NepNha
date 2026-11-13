<?php
declare(strict_types=1);

function catalogResults(array $input): array
{
    $text = static fn(string $key): string => is_scalar($input[$key] ?? '')
        ? mb_substr(trim((string) ($input[$key] ?? '')), 0, 100)
        : '';
    $where = ['s.is_active=1'];
    $args = [];
    $search = $text('q');
    $type = $text('type');
    if ($search !== '') {
        $where[] = '(s.name LIKE ? OR s.description LIKE ?)';
        array_push($args, '%' . $search . '%', '%' . $search . '%');
    }
    if ($type !== '') {
        $where[] = 's.service_type=?';
        $args[] = $type;
    }
    $variant = [
        'v.service_id=s.id',
        'v.is_active=1',
        'c.is_active=1',
        '(p.id IS NULL OR p.is_active=1)'
    ];
    foreach (
        [
            'category' => 'v.category_id',
            'supplier' => 'v.supplier_id',
            'min_price' => 'v.unit_price>=',
            'max_price' => 'v.unit_price<='
        ]
        as $key => $column
    ) {
        $raw = $text($key);
        if ($raw !== '' && ctype_digit($raw)) {
            $variant[] = $column . (str_ends_with($column, '=') ? '?' : '=?');
            $args[] = (int) $raw;
        }
    }
    $where[] =
        'EXISTS (SELECT 1 FROM service_variants v JOIN service_categories c ON c.id=v.category_id LEFT JOIN material_suppliers p ON p.id=v.supplier_id WHERE ' .
        implode(' AND ', $variant) .
        ')';
    if ($text('promotion') === '1') {
        $where[] =
            'EXISTS (SELECT 1 FROM campaigns cp WHERE (cp.service_id IS NULL OR cp.service_id=s.id) AND cp.is_active=1 AND cp.starts_at<=? AND cp.ends_at>=?)';
        array_push($args, now(), now());
    }
    $rating = (int) $text('rating');
    if ($rating >= 1 && $rating <= 5) {
        $where[] =
            "(SELECT AVG(rv.rating) FROM service_reviews rv WHERE rv.service_id=s.id AND rv.status='PUBLISHED')>=?";
        $args[] = $rating;
    }
    $sort = match ($text('sort')) {
        'price_asc' => 's.reference_price ASC',
        'price_desc' => 's.reference_price DESC',
        'newest' => 's.id DESC',
        default => 's.id ASC'
    };
    return Database::all(
        'SELECT s.* FROM services s WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $sort,
        $args
    );
}
