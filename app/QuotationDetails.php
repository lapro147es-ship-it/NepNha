<?php
declare(strict_types=1);

const QUOTE_SECTIONS = [
    'scope' => 'Phạm vi và khu vực thực hiện',
    'deliverables' => 'Hồ sơ và kết quả bàn giao',
    'materials' => 'Vật liệu, phụ kiện và quy cách',
    'timeline' => 'Tiến độ và mốc bàn giao',
    'payment_terms' => 'Điều kiện thanh toán',
    'revision_terms' => 'Số lần điều chỉnh và cách xử lý phát sinh',
    'support_terms' => 'Hỗ trợ sau bàn giao',
    'exclusions' => 'Các phần chưa bao gồm',
    'tax_terms' => 'Thuế và cách xác định giá'
];
const QUOTE_EVIDENCE_KINDS = [
    'SURVEY' => 'Khảo sát / hiện trạng',
    'DRAWING' => 'Bản vẽ / phương án',
    'MATERIAL' => 'Vật liệu / phụ kiện',
    'REFERENCE' => 'Minh họa tham khảo',
    'OTHER' => 'Tài liệu khác'
];

function quotationSchemaSql(string $driver): array
{
    $text = $driver === 'mysql' ? 'LONGTEXT' : 'TEXT';
    $suffix = $driver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';
    return [
        'quotation_specs' => "CREATE TABLE IF NOT EXISTS quotation_specs (
            quotation_id bigint PRIMARY KEY, data_json $text NOT NULL, updated_at DATETIME NOT NULL,
            FOREIGN KEY (quotation_id) REFERENCES quotations(id))$suffix",
        'quotation_attachments' => "CREATE TABLE IF NOT EXISTS quotation_attachments (
            attachment_id bigint PRIMARY KEY, quotation_id bigint NOT NULL,
            purpose varchar(20) NOT NULL, description varchar(500) NOT NULL,
            FOREIGN KEY (quotation_id) REFERENCES quotations(id),
            FOREIGN KEY (attachment_id) REFERENCES attachments(id))$suffix"
    ];
}

function ensureQuotationSchema(): void
{
    try {
        Database::one('SELECT id FROM quotations LIMIT 1');
    } catch (PDOException $error) {
        if (
            str_contains($error->getMessage(), 'no such table') ||
            str_contains($error->getMessage(), "doesn't exist")
        ) {
            return;
        }
        throw $error;
    }
    foreach (quotationSchemaSql(Database::driver()) as $table => $sql) {
        try {
            Database::one('SELECT quotation_id FROM ' . $table . ' LIMIT 1');
        } catch (PDOException $error) {
            if (
                !str_contains($error->getMessage(), 'no such table') &&
                !str_contains($error->getMessage(), "doesn't exist")
            ) {
                throw $error;
            }
            Database::pdo()->exec($sql);
        }
    }
}

function quotationSpec(int $id): array
{
    $row = Database::one('SELECT data_json FROM quotation_specs WHERE quotation_id=?', [$id]);
    return $row ? json_decode($row['data_json'], true, 512, JSON_THROW_ON_ERROR) : [];
}

function saveQuotationSpec(int $id, array $spec): void
{
    $json = json_encode($spec, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (strlen($json) > 120000) {
        throw new DomainException('Nội dung báo giá quá dài.');
    }
    Database::run('DELETE FROM quotation_specs WHERE quotation_id=?', [$id]);
    Database::insert('quotation_specs', [
        'quotation_id' => $id,
        'data_json' => $json,
        'updated_at' => now()
    ]);
}

function quotationSpecInput(array $data, array $itemIndices): array
{
    $input = $data['spec'] ?? [];
    if (!is_array($input)) {
        throw new DomainException('Nội dung chi tiết báo giá không hợp lệ.');
    }
    $spec = [];
    foreach (QUOTE_SECTIONS as $key => $label) {
        $spec[$key] = txt($input, $key, 2500, false);
    }
    $date = txt($input, 'valid_until', 10, false);
    if (
        $date &&
        (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            !checkdate(
                (int) substr($date, 5, 2),
                (int) substr($date, 8, 2),
                (int) substr($date, 0, 4)
            ))
    ) {
        throw new DomainException('Ngày hiệu lực báo giá không hợp lệ.');
    }
    $spec['valid_until'] = $date;
    $zones = $data['item_zone'] ?? [];
    $details = $data['item_detail'] ?? [];
    if (!is_array($zones) || !is_array($details)) {
        throw new DomainException('Mô tả hạng mục không hợp lệ.');
    }
    foreach ($itemIndices as $index) {
        $spec['item_details'][] = [
            'zone' => txt(['zone' => $zones[$index] ?? ''], 'zone', 120, false),
            'description' => txt(
                ['description' => $details[$index] ?? ''],
                'description',
                1500,
                false
            )
        ];
    }
    return $spec;
}

function quotationEvidence(int $id): array
{
    return Database::all(
        'SELECT a.*,qa.purpose,qa.description,u.full_name author_name FROM quotation_attachments qa JOIN attachments a ON a.id=qa.attachment_id JOIN users u ON u.id=a.uploaded_by WHERE qa.quotation_id=? ORDER BY a.id',
        [$id]
    );
}
