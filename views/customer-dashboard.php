<?php
$overview = customerDashboard($user);
extract($overview, EXTR_SKIP);
$nameParts = explode(' ', trim($user['full_name']));
$greetingName = end($nameParts);
$primaryAction = $actions[0] ?? null;
$apartmentsCount = (int) Database::one('SELECT COUNT(*) n FROM apartments WHERE customer_id=?', [
        $user['id']
    ])['n'];
?>

<div class="portal-heading">
    <div>
        <p class="eyebrow">STUDIO & KHÔNG GIAN CỦA BẠN</p>
        <h1>Tổ ấm trong từng chi tiết.</h1>
        <p>Theo dõi căn hộ, phản hồi đúng lúc và an tâm ở mỗi bước.</p>
    </div>
    <a class="button" href="<?= e(url('request-create')) ?>"><?= icon('plus') ?> Yêu cầu mới</a>
</div>

<div class="client-grid">
    <div class="client-left">
        <section class="client-card greeting-card">
            <span class="quiet-tag"><?= icon('apartments') ?> KHÔNG GIAN CỦA BẠN</span>
            <h2>Chào <?= e($greetingName) ?>,
                <br>
                <span>mừng bạn về Nếp Nhà.</span>
            </h2>
            <p>Mọi trao đổi, tài liệu và những bước tiếp theo của căn hộ đều ở đây.</p>
            <a class="text-link" href="<?= e(url('apartments')) ?>">Quản lý <?= $apartmentsCount ?> căn hộ
                <?= icon('arrow') ?></a>
        </section>

        <section class="client-card response-card <?= $primaryAction ? 'has-response' : '' ?>">
            <div class="client-card-title">
                <span class="icon-tile"><?= icon('notifications') ?></span>
                <p class="eyebrow">VIỆC CẦN BẠN PHẢN HỒI</p>
            </div>

            <?php if ($primaryAction): ?>
            <h2><?= e($primaryAction['label']) ?></h2>
            <p class="response-apartment"><?= e($primaryAction['apartment']) ?></p>
            <p>Mở nội dung để kiểm tra chi tiết và gửi quyết định của bạn. Phản hồi được lưu theo hồ
                sơ.</p>
            <a class="button" href="<?= e($primaryAction['url']) ?>"><?= e($primaryAction['cta']) ?> <?= icon('arrow') ?></a>

            <?php if (count($actions) > 1): ?>
            <a class="text-link" href="<?= e(url('notifications')) ?>">Các nhóm phản hồi khác <?= icon('arrow') ?></a>
            <?php endif; ?>
            <?php else: ?>
            <h2>Bạn đã bắt kịp mọi việc.</h2>
            <p>Chưa có báo giá, bản thiết kế, lịch hẹn hoặc kết quả cần bạn xác nhận.</p>
            <a class="text-link" href="<?= e(url('requests')) ?>">Xem các hồ sơ <?= icon('arrow') ?></a>
            <?php endif; ?>

        </section>

        <section class="client-card team-card">
            <div class="client-card-title">
                <h2>Đồng hành cùng bạn</h2>
                <span class="live-dot"></span>
            </div>

            <?php foreach (array_slice($team, 0, 3) as $person): ?>
            <div class="team-person">
                <span class="person-avatar"><?= e(mb_substr($person['full_name'], 0, 1)) ?></span>
                <div>
                    <strong><?= e($person['full_name']) ?></strong>
                    <small><?= e($person['job_label']) ?></small>

                    <?php if ($person['phone']): ?>
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $person['phone'])) ?>"><?= e($person['phone']) ?></a>
                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!$team): ?>
            <p class="muted">Thông tin người phụ trách sẽ xuất hiện khi hồ sơ được tiếp nhận hoặc
                phân công.</p>
            <?php endif; ?>

        </section>
    </div>

    <div class="client-center">
        <section class="client-card project-card">
            <div class="project-card-top">
                <div>
                    <p class="eyebrow">HỒ SƠ ĐANG THEO DÕI</p>
                    <h2><?= e($project['apartment_name'] ?? 'Bắt đầu với căn hộ của bạn') ?></h2>

                    <?php if ($project): ?>
                    <small><?= e($project['code']) ?> · <?= e(TYPE_NAMES[$project['service_type']]) ?></small>
                    <?php endif; ?>

                </div>

                <?php
                if ($project) {
                    echo badge($project['status']);
                }
                ?>

            </div>
            <div class="project-visual">
                <img <?= photoAttributes($project ? apartmentPhotoKey((int) $project['apartment_id']) : 'living-room', true) ?>>
                <span class="visual-label">GÓC CẢM HỨNG · ẢNH THAM KHẢO</span>
                <div class="visual-copy">
                    <span>ĐƯỢC CHĂM CHÚT ĐỂ SỐNG</span>
                    <strong>Một không gian.
                        <br>Nhiều điều thuộc về bạn.
                    </strong>
                </div>
            </div>

            <?php if ($project): ?>
            <div class="project-progress">
                <div>
                    <span>Tiến độ công việc được báo cáo</span>
                    <strong><?= $progress ?>%</strong>
                </div>
                <progress value="<?= $progress ?>" max="100"
                    aria-label="Tiến độ công việc được báo cáo"></progress>
                <p><?= $done ?>/<?= count($jobs) ?> công việc đã nghiệm thu <span>Tiến độ không
                        thay thế nghiệm thu.</span>
                </p>
            </div>
            <div class="journey-strip">
                <div class="client-card-title">
                    <h3>Hành trình căn hộ</h3>
                    <a class="text-link" href="<?= e(url('request-activity', ['id' => $project['id']])) ?>">Nhật ký <?= icon('arrow') ?></a>
                </div>
                <ol>

                    <?php
                    $currentFound = false;
                    foreach ($steps as $index => [$stepName, $stepDone]):

                        $isCurrent = !$stepDone && !$currentFound;
                        if ($isCurrent) {
                            $currentFound = true;
                        }
                    ?>

                    <li class="<?= $stepDone ? 'is-done' : ($isCurrent ? 'is-current' : '') ?>">
                        <span><?= $stepDone ? icon('check') : (string) ($index + 1) ?></span>
                        <small><?= e($stepName) ?></small>
                    </li>
                    <?php endforeach; ?>

                </ol>
            </div>
            <a class="project-open" href="<?= e(url('request', ['id' => $project['id']])) ?>">
                <span>Xem toàn bộ hồ sơ <small>Báo giá, thiết kế, công việc và tài liệu</small>
                </span><?= icon('arrow') ?>
            </a>
            <?php else: ?>
            <div class="project-empty">
                <h3>Cho ý tưởng đầu tiên một khởi đầu.</h3>
                <p>Thêm căn hộ rồi gửi nhu cầu thiết kế, lắp đặt hoặc bảo trì. Đội ngũ tư vấn sẽ
                    tiếp nhận tại đây.</p>
                <a class="button" href="<?= e(url('request-create')) ?>">Tạo yêu cầu đầu tiên <?= icon('arrow') ?></a>
            </div>
            <?php endif; ?>

        </section>
        <div class="client-metrics">

            <?php foreach ([
                               [
                                   'Báo giá đã duyệt',
                                   $finance['quote'] ? money($finance['total']) : 'Chưa có',
                                   'Theo hồ sơ đang chọn',
                                   'quotations'
                               ],
                               [
                                   'Đã ghi nhận thu',
                                   money($finance['paid']),
                                   $finance['quote']
                                       ? 'Còn lại ' . money($finance['remaining'])
                                       : 'Chờ thống nhất báo giá',
                                   'payments'
                               ],
                               [
                                   'Hồ sơ dịch vụ',
                                   (string) $requests['count'],
                                   $apartmentsCount . ' căn hộ của bạn',
                                   'requests'
                               ],
                               [
                                   'Đã nghiệm thu',
                                   $done . ' / ' . count($jobs),
                                   'Công việc của hồ sơ đang chọn',
                                   'tasks'
                               ]
                           ]
                           as [$metricLabel, $metricValue, $metricNote, $metricIcon]): ?>

            <article class="client-card metric-card">
                <div>
                    <span><?= e($metricLabel) ?></span><?= icon($metricIcon) ?>
                </div>
                <strong><?= e($metricValue) ?></strong>
                <small><?= e($metricNote) ?></small>
            </article>
            <?php endforeach; ?>

        </div>
    </div>

    <div class="client-right">
        <section class="client-card appointments-card">
            <div class="client-card-title">
                <h2>Lịch hẹn sắp tới</h2><?= icon('surveys') ?>
            </div>

            <?php foreach ($appointments as $appointment): ?>
            <article class="appointment">
                <span class="appointment-date"><?= e(substr(dt($appointment['starts_at']), 0, 5)) ?></span>
                <div>
                    <h3>Khảo sát căn hộ</h3>
                    <p><?= e($appointment['apartment_name']) ?></p>
                    <small><?= e(dt($appointment['starts_at'])) ?> – <?= e(substr(dt($appointment['ends_at']), 11)) ?></small>
                </div><?= badge($appointment['status']) ?><a class="button secondary full" href="<?= e(url('survey-detail', ['id' => $appointment['id']])) ?>">
                    <?= $appointment['status'] === 'PENDING' ? 'Xem và xác nhận lịch' : 'Xem chi tiết lịch hẹn' ?></a>
            </article>
            <?php endforeach; ?>

            <?php if (!$appointments): ?>
            <div class="quiet-empty"><?= icon('surveys') ?><h3>Chưa có lịch hẹn sắp tới</h3>
                <p>Lịch khảo sát mới sẽ xuất hiện khi tư vấn viên sắp xếp.</p>
            </div>
            <?php endif; ?>

            <a class="text-link" href="<?= e(url('surveys')) ?>">Tất cả lịch khảo sát <?= icon('arrow') ?></a>
        </section>
        <section class="client-card documents-card">
            <div class="client-card-title">
                <h2>Tài liệu gần đây</h2><?= icon('file') ?>
            </div>

            <?php foreach ($files as $file): ?>
            <a class="document-row" href="<?= e(url('document-view', ['id' => $file['id']])) ?>">
                <span class="document-icon"><?= icon('file') ?></span>
                <span>
                    <strong><?= e($file['original_name']) ?></strong>
                    <small><?= e(strtoupper(pathinfo($file['storage_key'], PATHINFO_EXTENSION))) ?> · <?= number_format($file['size_bytes'] / 1024, 1) ?> KB</small>
                </span><?= icon('arrow') ?>
            </a>
            <?php endforeach; ?>

            <?php if (!$files): ?>
            <div class="quiet-empty"><?= icon('file') ?><h3>Tài liệu sẽ được lưu tại đây</h3>
                <p>Mặt bằng, ảnh hiện trạng và bản thiết kế của hồ sơ đang chọn.</p>
            </div>
            <?php endif; ?>

            <?php if ($project): ?>
            <a class="text-link" href="<?= e(url('request-documents', ['id' => $project['id']])) ?>">Mở tài liệu hồ sơ <?= icon('arrow') ?></a>
            <?php endif; ?>

        </section>
        <a class="care-note" href="<?= e(url('maintenance')) ?>"><?= icon('maintenance') ?><span>
                <strong>Chăm sóc sau bàn giao</strong>
                <small>Xem lịch sử và yêu cầu bảo trì</small>
            </span><?= icon('arrow') ?></a>
    </div>
</div>

<section class="panel recent-projects">
    <div class="panel-heading">
        <div>
            <p class="eyebrow">CÁC KHÔNG GIAN CỦA BẠN</p>
            <h2>Hồ sơ gần đây</h2>
        </div>
        <a class="text-link" href="<?= e(url('requests')) ?>">Xem tất cả <?= icon('arrow') ?></a>
    </div>

    <?php requestTable(array_slice($requests['rows'], 0, 5)); ?>

</section>
