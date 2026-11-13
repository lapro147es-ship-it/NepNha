<?php
heading(
    'TIẾP NHẬN KHÔNG BỎ SÓT',
    'Yêu cầu khảo sát nhanh',
    'Yêu cầu từ khách chưa có tài khoản. Liên hệ, ghi kết quả và hướng dẫn khách tạo tài khoản/căn hộ để mở hồ sơ chính thức.'
);
?>

<?php
$leads = Database::all(
    'SELECT l.*,s.name service_name FROM consultation_leads l JOIN services s ON s.id=l.service_id ORDER BY l.id DESC LIMIT 100'
);
?>

<div class="commerce-stack">

    <?php foreach ($leads as $lead): ?>
    <article class="panel">
        <p class="eyebrow"><?= e($lead['reference_code']) ?> · <?= dt($lead['created_at']) ?></p>
        <h2><?= e($lead['full_name']) ?> · <?= e($lead['service_name']) ?></h2>
        <p><?= e($lead['phone']) ?> · <?= e($lead['email']) ?></p>
        <p><?= e($lead['address']) ?> · Ngày mong muốn: <?= e($lead['preferred_date'] ?: 'Chưa chọn') ?></p>
        <p class="multiline"><?= e($lead['content']) ?></p>

        <?php
        formStart('lead_update');
        hidden('id', $lead['id']);
        choose(
            'status',
            'Kết quả xử lý',
            [
                'NEW' => 'Chưa liên hệ',
                'CONTACTED' => 'Đã liên hệ',
                'CONVERTED' => 'Đã hướng dẫn mở hồ sơ',
                'DECLINED' => 'Không tiếp tục'
            ],
            $lead['status']
        );
        area('staff_note', 'Ghi chú tư vấn', $lead['staff_note'] ?? '');
        ?>

        <button class="button secondary">Cập nhật tiếp nhận</button>
        </form>
    </article>
    <?php endforeach; ?>

    <?php if (!$leads): ?>
    <div class="panel empty">Chưa có yêu cầu khảo sát nhanh.</div>
    <?php endif; ?>

</div>
