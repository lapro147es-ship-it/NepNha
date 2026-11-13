<?php
$r = requestAccess($user, (int) ($_GET['id'] ?? 0));
require __DIR__ . '/partials/request-header.php';
$workWhere = $private ? 'request_id=?' : 'request_id=? AND assignee_id=?';
$workArgs = $private ? [$rid] : [$rid, $user['id']];
$works = Database::all(
    'SELECT id,status,work_type,title FROM work_orders WHERE ' . $workWhere . ' ORDER BY id',
    $workArgs
);
$done = count(array_filter($works, fn($w) => $w['status'] === 'DONE'));
$consultant = $r['consultant_id']
? Database::one('SELECT full_name,phone FROM users WHERE id=?', [$r['consultant_id']])
: null;
$modules = [
    ['request-surveys', 'Khảo sát', 'Lịch hẹn, xác nhận và kết quả khảo sát.'],
    ['request-quotations', 'Báo giá', 'Các phiên bản chi phí và phản hồi chấp thuận.'],
    ['request-work', 'Công việc', 'Người phụ trách, lịch và tiến độ từng hạng mục.'],
    ['request-designs', 'Bản thiết kế', 'Tài liệu theo phiên bản và ý kiến chỉnh sửa.'],
    ['request-documents', 'Tài liệu hồ sơ', 'Mặt bằng, ảnh hiện trạng và tệp bổ sung.'],
    ['request-payments', 'Thanh toán', 'Các lần thu, chứng từ và số tiền còn lại.'],
    ['request-activity', 'Lịch sử xử lý', 'Những mốc thay đổi đã được ghi nhận.']
];
?>

<div class="detail-grid">
    <div>
        <section class="panel form-panel">
            <h2>Thông tin căn hộ và nhu cầu</h2>
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
                    <dt>Ngày mong muốn</dt>
                    <dd><?= e($r['preferred_date'] ?: 'Chưa xác định') ?></dd>
                </div>
                <div>
                    <dt>Tư vấn phụ trách</dt>
                    <dd><?= e($consultant['full_name'] ?? 'Đang chờ tiếp nhận') ?></dd>
                </div>

                <?php if ($private): ?>
                <div>
                    <dt>Ngân sách dự kiến</dt>
                    <dd><?= $r['budget'] !== null ? money($r['budget']) : 'Cần trao đổi' ?></dd>
                </div>
                <div>
                    <dt>Khách hàng</dt>
                    <dd><?= e($r['customer_name']) ?><small class="muted"><?= e($r['customer_email']) ?></small>
                    </dd>
                </div>
                <?php endif; ?>

            </dl>

            <?php if ($r['parent_request_id'] && $private): ?>
            <a class="text-link" href="<?= e(url('request', ['id' => $r['parent_request_id']])) ?>">Mở hồ sơ gốc #<?= (int) $r['parent_request_id'] ?> ↗</a>

            <?php
            endif;
            if ($r['cancellation_reason']):
            ?>

            <p class="error-text">Lý do hủy: <?= e($r['cancellation_reason']) ?></p>
            <?php endif; ?>

        </section>
        <div class="module-cards">

            <?php
            foreach ($modules as [$module, $name, $description]):
                if (
                    !$private &&
                    in_array(
                        $module,
                        ['request-surveys', 'request-quotations', 'request-payments'],
                        true
                    )
                ) {
                continue;
            }
            ?>

            <a class="module-card" href="<?= e(url($module, ['id' => $rid])) ?>">
                <span>↗</span>
                <h2><?= e($name) ?></h2>
                <p><?= e($description) ?></p>
                <strong>Mở trang <?= e(mb_strtolower($name)) ?></strong>
            </a>
            <?php endforeach; ?>

        </div>
    </div>
    <aside>
        <section class="panel form-panel">
            <p class="eyebrow">TIẾN ĐỘ HỒ SƠ</p>
            <h2><?= $done ?> / <?= count($works) ?> công việc hoàn thành</h2>
            <progress value="<?= $done ?>" max="<?= max(1, count($works)) ?>"
                aria-label="Công việc hoàn thành"></progress>
            <p class="muted">Báo cáo của nhân viên và xác nhận của khách hàng là hai bước riêng.</p>
            <a class="button secondary full" href="<?= e(url('request-work', ['id' => $rid])) ?>">Xem công việc ↗</a>
        </section>
        <section class="panel form-panel">
            <h2>Thao tác tiếp theo</h2>
            <div class="stack">

                <?php if (hasRole($user, 'CONSULTANT') && $r['status'] === 'NEW' && !$r['consultant_id']): ?>
                <a class="button" href="<?= e(url('request-decision', ['id' => $rid, 'mode' => 'receive'])) ?>">Tiếp nhận hồ sơ</a>
                <a class="button secondary" href="<?= e(url('request-decision', ['id' => $rid, 'mode' => 'reject'])) ?>">Từ chối có lý do</a>
                <?php endif; ?>

                <?php if ($consult && $r['status'] === 'RECEIVED'): ?>
                <a class="button" href="<?= e(url('quotation-form', ['request_id' => $rid])) ?>">Lập báo giá</a>
                <a class="button secondary" href="<?= e(url('survey-form', ['request_id' => $rid])) ?>">Đặt lịch khảo sát</a>
                <?php endif; ?>

                <?php if ($consult && in_array($r['status'], ['APPROVED', 'IN_PROGRESS'], true)): ?>
                <a class="button" href="<?= e(url('task-form', ['request_id' => $rid])) ?>">Phân công công việc</a>
                <?php endif; ?>

                <?php if (($owner || $consult) && in_array($r['status'], ['NEW', 'RECEIVED', 'QUOTED', 'APPROVED'], true)): ?>
                <a class="text-link" href="<?= e(url('request-decision', ['id' => $rid, 'mode' => 'cancel'])) ?>">Hủy yêu cầu dịch vụ</a>
                <?php endif; ?>

                <?php if ($consult && $r['status'] === 'COMPLETED' && finances($rid)['remaining'] === 0): ?>
                <a class="button" href="<?= e(url('request-decision', ['id' => $rid, 'mode' => 'close'])) ?>">Đóng hồ sơ</a>
                <?php endif; ?>

                <?php if ($owner &&
                                    in_array($r['status'], ['COMPLETED', 'CLOSED'], true)
                                ):
                                    $maintenance = Database::one(
                                        "SELECT id FROM services WHERE service_type='MAINTENANCE' AND is_active=1 ORDER BY id LIMIT 1"
                                    );
                                    if ($maintenance):
                ?>

                <a class="button" href="<?= e(url('request-create', ['service_id' => $maintenance['id'], 'apartment_id' => $r['apartment_id'], 'parent_request_id' => $rid])) ?>">Yêu cầu bảo trì mới</a>

                <?php
                endif;
                endif;
                ?>

                <a class="text-link" href="<?= e(url('process')) ?>">Xem quy trình dịch vụ ↗</a>
            </div>
        </section>
    </aside>
</div>
