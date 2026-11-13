<?php
$q = entityAccess('quotations', (int) ($_GET['id'] ?? 0), $user);
$r = privateRequest($user, (int) $q['request_id']);
require __DIR__ . '/partials/request-header.php';
$items = Database::all('SELECT * FROM quotation_items WHERE quotation_id=? ORDER BY id', [
        $q['id']
    ]);
    $spec = quotationSpec((int) $q['id']);
    $evidence = quotationEvidence((int) $q['id']);
    $total = totalQuote((int) $q['id']);
    $isDemo = !empty($spec['demo']);
    $filled = count(
        array_filter(
            array_keys(QUOTE_SECTIONS),
            static fn(string $key): bool => trim($spec[$key] ?? '') !== ''
        )
    );
    $surveys = Database::all(
        "SELECT sv.*,u.full_name surveyor_name FROM surveys sv JOIN users u ON u.id=sv.surveyor_id WHERE sv.request_id=? AND sv.status='DONE' ORDER BY sv.id DESC LIMIT 2",
        [$rid]
    );
    $versionFilter = $consult || hasRole($user, 'ADMIN') ? '' : " AND status<>'DRAFT'";
    $versions = Database::all(
        'SELECT id,version,status,created_at FROM quotations WHERE request_id=?' .
        $versionFilter .
        ' ORDER BY version DESC LIMIT 10',
        [$rid]
    );
?>

<section class="quote-dossier-hero">
    <div>
        <p class="eyebrow"><?= $isDemo ? 'BÁO GIÁ MẪU · DỮ LIỆU MINH HỌA' : 'HỒ SƠ ĐỀ XUẤT DỊCH VỤ' ?></p>
        <h2>Biết rõ từng hạng mục,
            <br>an tâm trước mỗi quyết định.
        </h2>
        <p><?= e($r['apartment_name']) ?> · <?= e($r['service_name']) ?></p>
    </div>
    <div class="quote-dossier-amount">
        <span>PHIÊN BẢN <?= (int) $q['version'] ?></span>
        <strong><?= money($total) ?></strong>
        <span><?= badge($q['status']) ?></span>
    </div>
</section>

<?php if ($isDemo): ?>
<div class="quote-disclosure"><?= icon('file') ?><p>
        <strong>Đây là báo giá mẫu để trình diễn đồ án.</strong> Các hạng mục được diễn giải để hình
        dung phạm vi. Sơ đồ và ảnh tham khảo không chứng minh đã khảo sát, đã thi công hoặc đã được
        khách hàng duyệt.
    </p>
</div>
<?php endif; ?>

<?php
$adjustment = quoteAdjustment((int) $q['id']);
if ($adjustment['campaign_discount'] || $adjustment['voucher_discount']):
?>

<section class="panel">
    <h2>Ưu đãi đã ghi nhận</h2>
    <p><?= e($adjustment['label']) ?></p>
    <dl class="commerce-totals">
        <div>
            <dt>Giá trị trước giảm</dt>
            <dd><?= money($total + $adjustment['campaign_discount'] + $adjustment['voucher_discount']) ?></dd>
        </div>
        <div>
            <dt>Giảm theo chương trình</dt>
            <dd>− <?= money($adjustment['campaign_discount']) ?></dd>
        </div>
        <div>
            <dt>Giảm theo mã</dt>
            <dd>− <?= money($adjustment['voucher_discount']) ?></dd>
        </div>
        <div>
            <dt>Giá trị báo giá sau giảm</dt>
            <dd><?= money($total) ?></dd>
        </div>
    </dl>

    <?php
    $reservation = Database::one(
        "SELECT expires_at FROM voucher_redemptions WHERE quotation_id=? AND status='RESERVED'",
        [$q['id']]
    );
    if ($reservation):
    ?>

    <p>Mã được giữ đến <?= dt($reservation['expires_at']) ?>. Sau thời hạn cần tư vấn lập lại báo giá.</p>
    <?php endif; ?>

</section>
<?php endif; ?>

<div class="quote-dossier-grid">
    <div class="quote-dossier-main">
        <section class="panel form-panel quote-context">
            <div class="dossier-section-title">
                <span>01</span>
                <div>
                    <p class="eyebrow">ĐẦU VÀO CỦA PHƯƠNG ÁN</p>
                    <h2>Nhu cầu và thông tin đối chiếu</h2>
                </div>
            </div>
            <p class="request-description"><?= nl2br(e($r['description'])) ?></p>
            <dl class="info-grid">
                <div>
                    <dt>Căn hộ</dt>
                    <dd><?= e($r['apartment_name']) ?></dd>
                </div>
                <div>
                    <dt>Địa chỉ</dt>
                    <dd><?= e($r['address']) ?></dd>
                </div>
                <div>
                    <dt>Người lập báo giá</dt>
                    <dd><?= e($q['actor_name']) ?></dd>
                </div>
                <div>
                    <dt>Ngày gửi / lập</dt>
                    <dd><?= e(dt($q['sent_at'] ?: $q['created_at'])) ?></dd>
                </div>
            </dl>
        </section>

        <section class="panel quote-items-panel" id="quote-costs">
            <div class="panel-heading">
                <div class="dossier-section-title">
                    <span>02</span>
                    <div>
                        <p class="eyebrow">KHỐI LƯỢNG & CHI PHÍ</p>
                        <h2><?= count($items) ?> hạng mục được đề xuất</h2>
                    </div>
                </div>
                <span class="quote-total-chip"><?= money($total) ?></span>
            </div>

            <?php
            foreach ($items as $index => $item):

                $description = $spec['item_details'][$index] ?? [];
                $lineTotal = (int) round((float) $item['quantity'] * (int) $item['unit_price']);
            ?>

            <article class="quote-item-card">
                <div class="quote-item-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></div>
                <div class="quote-item-content">
                    <div class="quote-item-title">
                        <div>
                            <h3><?= e($item['item_name']) ?></h3>

                            <?php if (!empty($description['zone'])): ?>
                            <span class="quote-zone"><?= icon('apartments') ?><?= e($description['zone']) ?></span>
                            <?php endif; ?>

                        </div>
                        <strong><?= money($lineTotal) ?></strong>
                    </div>
                    <p><?= nl2br(e($description['description'] ?? '' ?: 'Chưa cung cấp quy cách và kết quả bàn giao riêng cho hạng mục này. Nên yêu cầu làm rõ trước khi chấp thuận.')) ?></p>
                    <div class="quote-line-calculation">
                        <span><?= e($item['quantity']) ?> <?= e($item['unit']) ?></span>
                        <span>× <?= money($item['unit_price']) ?></span>
                        <span>= <?= money($lineTotal) ?></span>
                    </div>
                    <div class="quote-cost-share">
                        <meter min="0" max="<?= max(1, $total) ?>" value="<?= $lineTotal ?>"
                            aria-label="Tỷ trọng chi phí <?= e($item['item_name']) ?>"></meter>
                        <small><?= $total > 0 ? number_format(($lineTotal / $total) * 100, 1, ',', '.') . '% tổng giá trị' : 'Hạng mục 0 đồng' ?></small>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>

            <div class="quote-final-total">
                <span>TỔNG CÁC HẠNG MỤC</span>
                <strong><?= money($total) ?></strong>
            </div>
        </section>

        <section class="panel form-panel" id="quote-specifications">
            <div class="dossier-section-title">
                <span>03</span>
                <div>
                    <p class="eyebrow">CAM KẾT CẦN LÀM RÕ</p>
                    <h2>Phạm vi, đầu ra và cách triển khai</h2>
                </div>
            </div>
            <div class="quote-spec-grid">

                <?php foreach (QUOTE_SECTIONS as $key => $label): ?>
                <article class="quote-spec-block">
                    <h3><?= e($label) ?></h3>

                    <?php if (!empty($spec[$key])): ?>
                    <ul>

                        <?php foreach (array_filter(array_map('trim', preg_split('/\R/', $spec[$key]))) as $line): ?>
                        <li><?= e($line) ?></li>
                        <?php endforeach; ?>

                    </ul>
                    <?php else: ?>
                    <p class="quote-missing">Chưa cung cấp. Cần tư vấn xác nhận nội dung này.</p>
                    <?php endif; ?>

                </article>
                <?php endforeach; ?>

            </div>

            <?php if ($q['notes']): ?>
            <div class="quote-extra-notes">
                <strong>Ghi chú bổ sung</strong>
                <p><?= nl2br(e($q['notes'])) ?></p>
            </div>
            <?php endif; ?>

        </section>

        <section class="panel form-panel quote-evidence-panel" id="quote-evidence">
            <div class="dossier-section-title">
                <span>04</span>
                <div>
                    <p class="eyebrow">TÀI LIỆU ĐỂ KIỂM TRA</p>
                    <h2>Căn cứ và tệp đính kèm phiên bản này</h2>
                </div>
            </div>

            <?php if (!$evidence): ?>
            <div class="quote-evidence-empty"><?= icon('file') ?><div>
                    <h3>Chưa có tệp căn cứ đính kèm báo giá.</h3>
                    <p>Chưa thể đối chiếu kích thước, mã vật liệu hoặc cấu hình chỉ từ tổng tiền.
                        Bạn có thể yêu cầu tư vấn bổ sung tài liệu và gửi phiên bản mới.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="quote-evidence-grid">

                <?php foreach ($evidence as $file): ?>
                <a class="quote-evidence-card" href="<?= e(url('document-view', ['id' => $file['id']])) ?>">

                    <?php if (in_array($file['mime_type'], ['image/png', 'image/jpeg'], true)): ?>
                    <img src="<?= e(url('file-content', ['id' => $file['id']])) ?>" alt="<?= e($file['description']) ?>">
                    <?php else: ?>
                    <span class="quote-pdf-icon"><?= icon('file') ?></span>
                    <?php endif; ?>

                    <span class="quote-evidence-kind"><?= e(QUOTE_EVIDENCE_KINDS[$file['purpose']] ?? 'Tài liệu') ?></span>
                    <strong><?= e($file['original_name']) ?></strong>
                    <p><?= e($file['description']) ?></p>
                    <small><?= e($file['author_name']) ?> · <?= e(dt($file['uploaded_at'])) ?> · <?= number_format($file['size_bytes'] / 1024, 1) ?> KB</small>
                    <span class="text-link">Mở tài liệu <?= icon('arrow') ?></span>
                </a>
                <?php endforeach; ?>

            </div>
            <div class="quote-related-surveys">
                <h3>Kết quả khảo sát hiện có của hồ sơ</h3>
                <p class="quote-source-note">Thông tin liên quan để tham chiếu; không tự xác nhận
                    đây là tài liệu đã dùng khi lập báo giá.</p>

                <?php foreach ($surveys as $survey): ?>
                <article>
                    <strong><?= e($survey['surveyor_name']) ?> · <?= e(dt($survey['starts_at'])) ?></strong>
                    <p><?= nl2br(e($survey['result'])) ?></p>
                    <a class="text-link" href="<?= e(url('survey-detail', ['id' => $survey['id']])) ?>">Xem lịch khảo sát và kết quả
                        <?= icon('arrow') ?></a>
                </article>
                <?php endforeach; ?>

                <?php if (!$surveys): ?>
                <p class="quote-missing">Hồ sơ chưa có kết quả khảo sát hoàn thành.</p>
                <?php endif; ?>

            </div>
        </section>

        <?php if ($isDemo): ?>
        <section class="panel form-panel quote-visual-reference" id="quote-inspiration">
            <div class="dossier-section-title">
                <span>05</span>
                <div>
                    <p class="eyebrow">ĐỂ DỄ HÌNH DUNG</p>
                    <h2>Sơ đồ và cảm hứng minh họa</h2>
                </div>
            </div>
            <div class="quote-visual-grid">
                <figure>
                    <img src="assets/quote-plan-reference.png"
                        alt="Sơ đồ căn hộ minh họa đồ án, không có tỷ lệ và không phải bản vẽ của căn hộ trong hồ sơ">
                    <figcaption>Sơ đồ minh họa cách phân khu. Không có tỷ lệ, không dùng để thi công
                        hoặc xác nhận hiện trạng.</figcaption>
                </figure>
                <figure>
                    <img <?= photoAttributes('cozy-corner') ?>>
                    <figcaption>Cảm hứng về bảng màu và vật liệu. Ảnh tham khảo từ Unsplash, không
                        phải phối cảnh đã duyệt hoặc sản phẩm bàn giao.</figcaption>
                </figure>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($q['customer_feedback']): ?>
        <section class="panel form-panel quote-recorded-feedback">
            <p class="eyebrow">ĐÃ LƯU THEO PHIÊN BẢN</p>
            <h2>Phản hồi của khách hàng</h2>
            <p><?= nl2br(e($q['customer_feedback'])) ?></p>
            <small><?= e(dt($q['responded_at'])) ?></small>
        </section>
        <?php endif; ?>

        <?php if ($owner && $q['status'] === 'SENT' && $r['status'] === 'QUOTED'): ?>
        <section class="panel form-panel quote-decision">
            <p class="eyebrow">SAU KHI ĐÃ KIỂM TRA NỘI DUNG</p>
            <h2>Quyết định của bạn</h2>
            <p>Chấp thuận khi phạm vi, chi phí và điều kiện đã rõ. Nếu thiếu căn cứ, nêu cụ thể nội
                dung cần bổ sung để tư vấn gửi phiên bản khác.</p>
            <div class="quote-feedback-suggestions">
                <button type="button"
                    data-quote-feedback="Cần bổ sung kích thước và khối lượng chi tiết.">Kích thước
                    / khối lượng</button>
                <button type="button"
                    data-quote-feedback="Cần bổ sung mã vật liệu, phụ kiện và quy cách.">Vật liệu /
                    phụ kiện</button>
                <button type="button"
                    data-quote-feedback="Cần bổ sung bản vẽ hoặc tài liệu khảo sát làm căn cứ.">Tài
                    liệu làm căn cứ</button>
            </div>

            <?php
            formStart('quote_respond', $rid);
            hidden('id', $q['id']);
            area(
                'feedback',
                'Ý kiến phản hồi (bắt buộc khi yêu cầu điều chỉnh)',
                oldValue('feedback'),
                false
            );
            ?>

            <div class="actions">
                <button class="button" name="decision" value="accept">Chấp thuận báo giá</button>
                <button class="button secondary" name="decision" value="reject">Yêu cầu điều chỉnh /
                    bổ sung</button>
            </div>
            </form>
        </section>
        <?php endif; ?>

    </div>

    <aside class="quote-dossier-aside">
        <section class="panel form-panel quote-check-panel">
            <nav class="quote-section-nav" aria-label="Mục lục báo giá">
                <a href="#quote-costs">Hạng mục & chi phí <?= icon('arrow') ?></a>
                <a href="#quote-specifications">Phạm vi & điều kiện <?= icon('arrow') ?></a>
                <a href="#quote-evidence">Tài liệu làm căn cứ <?= icon('arrow') ?></a>

                <?php if ($isDemo): ?>
                <a href="#quote-inspiration">Sơ đồ & cảm hứng <?= icon('arrow') ?></a>
                <?php endif; ?>

            </nav>
            <p class="eyebrow">TÓM TẮT ĐỂ ĐỐI CHIẾU</p>
            <h2>Trước khi chấp thuận</h2>
            <dl>
                <div>
                    <dt>Hạng mục</dt>
                    <dd><?= count($items) ?></dd>
                </div>
                <div>
                    <dt>Nhóm nội dung đã cung cấp</dt>
                    <dd><?= $filled ?>/<?= count(QUOTE_SECTIONS) ?></dd>
                </div>
                <div>
                    <dt>Tệp đính kèm phiên bản</dt>
                    <dd><?= count($evidence) ?></dd>
                </div>
                <div>
                    <dt>Ngày hết hiệu lực</dt>
                    <dd><?= e(!empty($spec['valid_until']) ? date('d/m/Y', strtotime($spec['valid_until'])) : 'Chưa xác nhận') ?></dd>
                </div>
            </dl>
            <p>Có tài liệu không đồng nghĩa đã được thẩm định. Kiểm tra người gửi, nội dung, ngày
                lập và sự phù hợp với căn hộ.</p>
            <button class="button secondary full print-button" type="button">In / Lưu PDF
                <?= icon('file') ?></button>

            <?php if ($consult && $q['status'] === 'DRAFT' && $r['status'] === 'RECEIVED'): ?>
            <a class="button full" href="<?= e(url('quotation-form', ['request_id' => $rid])) ?>">Tiếp tục hoàn thiện <?= icon('arrow') ?>
            </a>
            <?php endif; ?>

        </section>
        <section class="panel form-panel quote-version-panel">
            <p class="eyebrow">THEO DÕI THAY ĐỔI</p>
            <h2>Các phiên bản</h2>

            <?php foreach ($versions as $version): ?>
            <a class="<?= (int) $version['id'] === (int) $q['id'] ? 'current' : '' ?>" href="<?= e(url('quotation-detail', ['id' => $version['id']])) ?>">
                <strong>Phiên bản <?= (int) $version['version'] ?></strong>
                <span><?= e(STATUS_NAMES[$version['status']]) ?></span>
                <small><?= e(dt($version['created_at'])) ?></small>
            </a>
            <?php endforeach; ?>

            <a class="text-link" href="<?= e(url('request-quotations', ['id' => $rid])) ?>">Tất cả báo giá của hồ sơ <?= icon('arrow') ?>
            </a>
        </section>
    </aside>
</div>
