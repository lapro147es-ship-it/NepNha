<?php
$question = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 300);
$answers = [];
foreach (frequentlyAsked() as $faq) {
    $score = 0;
    foreach (preg_split('/\s+/u', mb_strtolower($question)) as $word) {
        if (
            mb_strlen($word) >= 3 &&
            mb_stripos($faq['question'] . ' ' . $faq['answer'] . ' ' . $faq['group'], $word) !==
            false
        ) {
        $score++;
    }
}
if ($score > 0) {
    $answers[] = $faq + ['score' => $score];
}
}
usort($answers, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);
?>

<section class="public-section commerce-assistant">
    <p class="eyebrow">HỖ TRỢ TỰ ĐỘNG</p>
    <h1>Bắt đầu từ câu hỏi của bạn.</h1>
    <p>Trợ lý tra cứu nội dung hướng dẫn của Nếp Nhà theo từ khóa. Không thay kiến trúc sư, không tự
        duyệt báo giá và không phải mô hình AI bên ngoài.</p>
    <form method="get" class="panel filters">
        <input type="hidden" name="page" value="assistant">
        <input name="q" value="<?= e($question) ?>" maxlength="300" required
            placeholder="Ví dụ: khảo sát, bản thiết kế, nghiệm thu, bảo trì...">
        <button class="button">Tìm hướng dẫn</button>
    </form>
    <div class="commerce-chips">

        <?php foreach (['khảo sát', 'thiết kế', 'bảo trì', 'thanh toán'] as $topic): ?>
        <a href="<?= e(url('assistant', ['q' => $topic])) ?>"><?= e($topic) ?></a>
        <?php endforeach; ?>

    </div>

    <?php foreach (array_slice($answers, 0, 3) as $answer): ?>
    <article class="panel">
        <small><?= e($answer['group']) ?></small>
        <h2><?= e($answer['question']) ?></h2>
        <p><?= e($answer['answer']) ?></p>
    </article>
    <?php endforeach; ?>

    <?php if ($question !== '' && !$answers): ?>
    <section class="panel">
        <h2>Chưa có hướng dẫn khớp câu hỏi.</h2>
        <p>Hãy thử từ khóa ngắn hơn hoặc gửi đăng ký tư vấn để nhân viên kiểm tra tình huống cụ thể.
        </p>
    </section>
    <?php endif; ?>

    <a class="button secondary" href="<?= e(url('booking')) ?>">Trao đổi với tư vấn viên →</a>
</section>
