<?php
$survey = entityAccess('surveys', (int) ($_GET['id'] ?? 0), $user);
$r = requestAccess($user, (int) $survey['request_id']);
require __DIR__ . '/partials/request-header.php';
$open = in_array($r['status'], ['RECEIVED', 'QUOTED', 'APPROVED', 'IN_PROGRESS'], true);
?>

<section class="panel form-panel">
    <div class="card-head">
        <h2>Lịch khảo sát hiện trạng</h2><?= badge($survey['status']) ?>
    </div>
    <dl class="info-grid">
        <div>
            <dt>Nhân viên khảo sát</dt>
            <dd><?= e($survey['actor_name']) ?></dd>
        </div>
        <div>
            <dt>Địa điểm</dt>
            <dd><?= e($r['address']) ?></dd>
        </div>
        <div>
            <dt>Bắt đầu</dt>
            <dd><?= e(dt($survey['starts_at'])) ?></dd>
        </div>
        <div>
            <dt>Kết thúc</dt>
            <dd><?= e(dt($survey['ends_at'])) ?></dd>
        </div>
        <div>
            <dt>Khách xác nhận lúc</dt>
            <dd><?= e(dt($survey['customer_confirmed_at'])) ?></dd>
        </div>
    </dl>
    <h2>Nội dung cần khảo sát</h2>
    <p class="request-description"><?= nl2br(e($survey['notes'] ?: 'Trao đổi nhu cầu và kiểm tra hiện trạng căn hộ.')) ?></p>
    <h2>Kết quả khảo sát</h2>
    <p class="request-description"><?= nl2br(e($survey['result'] ?: 'Kết quả được cập nhật sau khi nhân viên thực hiện khảo sát.')) ?></p>
    <div class="actions">

        <?php
        if ($owner && $survey['status'] === 'PENDING' && $open):

            formStart('survey_confirm', $rid);
            hidden('id', $survey['id']);
        ?>

        <button class="button">Xác nhận lịch khảo sát</button>
        </form>
        <?php endif; ?>

        <?php if ($consult && in_array($survey['status'], ['PENDING', 'CONFIRMED'], true) && $open): ?>
        <a class="button secondary" href="<?= e(url('survey-form', ['id' => $survey['id'], 'request_id' => $rid])) ?>">Đổi lịch khảo sát</a>

        <?php
        formStart('survey_cancel', $rid);
        hidden('id', $survey['id']);
        ?>

        <button class="button danger">Hủy lịch này</button>
        </form>
        <?php endif; ?>

        <?php if (($consult || (int) $survey['surveyor_id'] === $user['id']) && $survey['status'] === 'CONFIRMED' && $open): ?>
        <a class="button" href="<?= e(url('survey-result', ['id' => $survey['id']])) ?>">Cập nhật kết quả khảo sát ↗</a>
        <?php endif; ?>

    </div>
</section>
