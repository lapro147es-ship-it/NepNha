<?php
$rid = (int) ($_GET['request_id'] ?? 0);
if (!$rid) {
    heading('CHỌN HỒ SƠ', 'Soạn báo giá', 'Chỉ hồ sơ đã tiếp nhận và thuộc tư vấn viên hiện tại.');
    $options = [];
    foreach (requestRows($user, 1000, 0, '', 'RECEIVED')['rows'] as $item) {
        if (canConsult($user, $item)) {
            $options[$item['id']] = $item['code'] . ' · ' . $item['apartment_name'];
        }
    }
    echo '<section class="panel action-panel"><form method="get">';
    hidden('page', $page);
    choose('request_id', 'Hồ sơ', $options);
    echo '<button class="button" type="submit">Tiếp tục →</button></form></section>';
    return;
}
$r = privateRequest($user, $rid);
if (!canConsult($user, $r) || $r['status'] !== 'RECEIVED') {
    throw new DomainException('Hồ sơ chưa phù hợp để soạn báo giá.');
}
$draft = Database::one("SELECT * FROM quotations WHERE request_id=? AND status='DRAFT'", [$rid]);
$sourceQuote =
$draft ?:
Database::one(
    "SELECT * FROM quotations WHERE request_id=? AND status='REJECTED' ORDER BY version DESC LIMIT 1",
    [$rid]
);
$spec = $sourceQuote ? quotationSpec((int) $sourceQuote['id']) : [];
$items = $sourceQuote
? Database::all('SELECT * FROM quotation_items WHERE quotation_id=? ORDER BY id', [
        $sourceQuote['id']
    ])
    : [];
    if (isset($old['item_name']) && is_array($old['item_name'])) {
        $items = [];
        foreach (array_slice($old['item_name'], 0, 30) as $i => $name) {
            $items[] = [
                'item_name' => is_scalar($name) ? $name : '',
                'unit' => $old['unit'][$i] ?? '',
                'quantity' => $old['quantity'][$i] ?? 1,
                'unit_price' => $old['unit_price'][$i] ?? 0,
                'zone' => $old['item_zone'][$i] ?? '',
                'detail' => $old['item_detail'][$i] ?? ''
            ];
        }
    }
    foreach ($items as $index => &$line) {
        $line += [
            'zone' => $spec['item_details'][$index]['zone'] ?? '',
            'detail' => $spec['item_details'][$index]['description'] ?? ''
        ];
    }
    unset($line);
    if (isset($old['spec']) && is_array($old['spec'])) {
        $spec = $old['spec'] + $spec;
    }
    $items = $items ?: [
        [
            'item_name' => '',
            'unit' => 'gói',
            'quantity' => 1,
            'unit_price' => 0,
            'zone' => '',
            'detail' => ''
        ]
    ];
    require __DIR__ . '/partials/request-header.php';
    heading(
        'CHI PHÍ VÀ PHẠM VI',
        'Soạn báo giá',
        'Tách từng hạng mục, khối lượng và đơn giá. Bản nháp chỉ hiển thị cho tư vấn viên phụ trách và quản trị viên.'
    );
?>

<section class="panel">

    <?php formStart('quote_save', $rid, true, 'quote-editor'); ?>

    <div class="quote-form-step">
        <span>01</span>
        <div>
            <h2>Hạng mục và khối lượng</h2>
            <p>Mỗi dòng cần làm rõ khu vực, quy cách và đầu ra để khách hàng có thể đối chiếu.</p>
        </div>
    </div>
    <div class="quote-labels">
        <span>Hạng mục</span>
        <span>Đơn vị</span>
        <span>Số lượng</span>
        <span>Đơn giá (VND)</span>
        <span></span>
    </div>
    <div id="quote-lines">

        <?php foreach ($items as $item): ?>
        <div class="quote-line">
            <input aria-label="Hạng mục" name="item_name[]" maxlength="200" required
                value="<?= e($item['item_name']) ?>">
            <input aria-label="Đơn vị" name="unit[]" maxlength="30" required
                value="<?= e($item['unit']) ?>">
            <input aria-label="Số lượng" type="number" min="0.01" max="999999.99" step="0.01"
                name="quantity[]" required value="<?= e($item['quantity']) ?>">
            <input aria-label="Đơn giá" type="number" min="0" max="100000000000" step="1"
                name="unit_price[]" required value="<?= e($item['unit_price']) ?>">
            <button type="button" class="remove-line" aria-label="Xóa dòng">×</button>
            <div class="quote-line-detail">
                <label>
                    <span>Khu vực / vị trí</span>
                    <input name="item_zone[]" maxlength="120" placeholder="Ví dụ: Khu bếp"
                        value="<?= e($item['zone']) ?>">
                </label>
                <label>
                    <span>Quy cách, vật liệu và đầu ra</span>
                    <textarea name="item_detail[]" maxlength="1500" rows="3"
                        placeholder="Nêu kích thước cần chốt, mã vật liệu, cấu hình và tài liệu/sản phẩm bàn giao."><?= e($item['detail']) ?></textarea>
                </label>
            </div>

        </div>
        <?php endforeach; ?>

    </div>
    <template id="quote-line-template">
        <div class="quote-line">
            <input aria-label="Hạng mục" name="item_name[]" maxlength="200" required>
            <input aria-label="Đơn vị" name="unit[]" maxlength="30" value="gói" required>
            <input aria-label="Số lượng" type="number" min="0.01" max="999999.99" step="0.01"
                name="quantity[]" value="1" required>
            <input aria-label="Đơn giá" type="number" min="0" max="100000000000" step="1"
                name="unit_price[]" value="0" required>
            <button type="button" class="remove-line" aria-label="Xóa dòng">×</button>
            <div class="quote-line-detail">
                <label>
                    <span>Khu vực / vị trí</span>
                    <input name="item_zone[]" maxlength="120" placeholder="Ví dụ: Khu bếp">
                </label>
                <label>
                    <span>Quy cách, vật liệu và đầu ra</span>
                    <textarea name="item_detail[]" maxlength="1500" rows="3"
                        placeholder="Nêu kích thước cần chốt, mã vật liệu, cấu hình và tài liệu/sản phẩm bàn giao."></textarea>
                </label>
            </div>

        </div>
    </template>
    <div class="section-heading">
        <button class="button secondary" id="add-line" type="button">+ Thêm hạng mục</button>
        <strong id="quote-total">0 ₫</strong>
    </div>

    <div class="quote-form-step">
        <span>02</span>
        <div>
            <h2>Phạm vi và điều kiện thực hiện</h2>
            <p>Nội dung chưa xác nhận phải được ghi rõ, không dùng một câu ghi chú chung để thay cho
                toàn bộ phạm vi.</p>
        </div>
    </div>
    <div class="quote-spec-fields">

        <?php foreach (QUOTE_SECTIONS as $specKey => $specLabel): ?>
        <label class="field">
            <span><?= e($specLabel) ?></span>
            <textarea name="spec[<?= e($specKey) ?>]" maxlength="2500" rows="5"
                placeholder="Ghi từng ý trên một dòng, chỉ nêu nội dung đã có cơ sở."><?= e($spec[$specKey] ?? '') ?></textarea>
        </label>
        <?php endforeach; ?>

    </div>

    <?php
    field(
        'spec[valid_until]',
        'Ngày hết hiệu lực (nếu có)',
        $spec['valid_until'] ?? '',
        'date',
        false
    );
    area('notes', 'Ghi chú bổ sung', oldValue('notes', $sourceQuote['notes'] ?? ''), false);
    ?>

    <div class="quote-form-step">
        <span>03</span>
        <div>
            <h2>Tài liệu làm căn cứ</h2>
            <p>Tải PDF, PNG hoặc JPG tối đa 10 MB. Mỗi lần lưu có thể bổ sung một tệp, tối đa 8 tệp
                cho phiên bản. Tài liệu được giữ riêng theo từng báo giá.</p>
        </div>
    </div>

    <?php if ($draft): foreach (quotationEvidence((int) $draft['id']) as $evidence): ?>
    <a class="file-link" href="<?= e(url('document-view', ['id' => $evidence['id']])) ?>"><?= e($evidence['original_name']) ?><small><?= e(QUOTE_EVIDENCE_KINDS[$evidence['purpose']]) ?> ·
            <?= e($evidence['description']) ?></small>
    </a>

    <?php
    endforeach;
    endif;
    ?>

    <div class="quote-upload-fields">
        <label class="field">
            <span>Chọn tài liệu bổ sung</span>
            <input type="file" name="file" accept=".pdf,.png,.jpg,.jpeg">
        </label>

        <?php
        choose(
            'upload_kind',
            'Phân loại tài liệu',
            QUOTE_EVIDENCE_KINDS,
            oldValue('upload_kind', 'REFERENCE')
        );
        field(
            'upload_description',
            'Tài liệu này chứng minh / minh họa điều gì?',
            oldValue('upload_description'),
            'text',
            false
        );
        ?>

    </div>
    <p class="quote-upload-note">Nếu là ảnh thư viện hoặc sơ đồ ví dụ, chọn Minh họa tham khảo.
        Không phân loại thành ảnh khảo sát thực tế.</p>
    <fieldset class="commerce-note">
        <legend>Ưu đãi trên báo giá</legend>
        <label class="check">
            <input type="checkbox" name="apply_benefits" value="1" <?= oldValue('apply_benefits', $draft && !empty(quoteAdjustment((int) $draft['id'])['label']) ? 1 : 0) ? 'checked' : '' ?>> Áp dụng chương
            trình phù hợp và mã ưu đãi (nếu có)
        </label>

        <?php
        field(
            'voucher_code',
            'Mã ưu đãi; để trống sẽ dùng mã khách đã gửi theo lựa chọn',
            oldValue('voucher_code'),
            'text',
            false
        );
        ?>

        <p>Mức giảm được lưu riêng theo phiên bản. Lượt mã được giữ khi gửi và xác nhận khi khách
            chấp thuận.</p>
    </fieldset>
    <p class="muted">Tối đa 30 hạng mục. Gửi khách hàng sẽ chuyển hồ sơ sang chờ duyệt báo giá.</p>
    <div class="actions">
        <button class="button" type="submit" name="mode" value="send">Lưu và gửi khách hàng →
        </button>
        <button class="button secondary" type="submit" name="mode" value="draft">Lưu bản nháp
        </button>
    </div>
    </form>
</section>
