<?php
$group = (string) ($_GET['group'] ?? '');
$groups = array_unique(array_column(frequentlyAsked(), 'group'));
?>

<section class="editorial-hero">
    <p class="eyebrow">GIẢI ĐÁP TRƯỚC KHI BẮT ĐẦU</p>
    <h1>Những điều bạn
        <br>có thể đang thắc mắc.
    </h1>
    <p>Từ giá tham khảo, cách duyệt bản thiết kế đến nghiệm thu và bảo trì sau bàn giao.</p>
</section>
<section class="public-section">
    <nav class="filter-pills" aria-label="Nhóm câu hỏi">
        <a class="<?= $group === '' ? 'selected' : '' ?>" href="<?= e(url('faq')) ?>">Tất cả</a>

        <?php foreach ($groups as $name): ?>
        <a class="<?= $group === $name ? 'selected' : '' ?>" href="<?= e(url('faq', ['group' => $name])) ?>"><?= e($name) ?></a>
        <?php endforeach; ?>

    </nav>
    <div class="faq-list">

        <?php
        foreach (frequentlyAsked() as $item):
            if ($group !== '' && $item['group'] !== $group) {
                continue;
            }
        ?>

        <article>
            <p class="eyebrow"><?= e($item['group']) ?></p>
            <h2><?= e($item['question']) ?></h2>
            <p><?= e($item['answer']) ?></p>
        </article>
        <?php endforeach; ?>

    </div>
    <div class="editorial-callout">
        <h2>Câu hỏi của bạn phụ thuộc hiện trạng?</h2>
        <p>Gửi một yêu cầu có thông tin căn hộ và mô tả cụ thể để tư vấn viên trao đổi theo hồ sơ.
        </p>
        <a class="button" href="<?= e(url('contact')) ?>">Bắt đầu tư vấn ↗</a>
    </div>
</section>
