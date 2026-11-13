<?php
$slug = (string) ($_GET['slug'] ?? '');
$idea = designIdeas()[$slug] ?? null;
if (!$idea) {
    throw new DomainException('Không tìm thấy phương án tham khảo.');
}
?>

<section class="public-section">
    <a class="back-link" href="<?= e(url('portfolio')) ?>">← Tất cả ý tưởng</a>
    <div class="service-detail-hero">
        <div>
            <p class="eyebrow">PHƯƠNG ÁN MINH HỌA</p>
            <h1><?= e($idea['name']) ?></h1>
            <p class="lead"><?= e($idea['description']) ?></p>
            <dl class="info-grid">
                <div>
                    <dt>Quy mô tham khảo</dt>
                    <dd><?= e($idea['area']) ?></dd>
                </div>
                <div>
                    <dt>Bảng màu gợi ý</dt>
                    <dd><?= e($idea['palette']) ?></dd>
                </div>
            </dl>
            <a class="button" href="<?= e(url('request-create')) ?>">Trao đổi phương án cho căn hộ của tôi ↗</a>
        </div>
        <img <?= photoAttributes(ideaPhotoKey($slug), true) ?>>
    </div>
</section>
<section class="public-section editorial-content">
    <div class="editorial-columns">
        <article>
            <h2>Tổ chức các khu vực</h2>
            <ul class="clean-list">

                <?php foreach ($idea['rooms'] as $item): ?>
                <li><?= e($item) ?></li>
                <?php endforeach; ?>

            </ul>
        </article>
        <article>
            <h2>Vật liệu và hoàn thiện</h2>
            <ul class="clean-list">

                <?php foreach ($idea['materials'] as $item): ?>
                <li><?= e($item) ?></li>
                <?php endforeach; ?>

            </ul>
        </article>
    </div>
    <h2>Áp dụng cho căn hộ thực tế</h2>
    <p>Một hình ảnh tham khảo chưa xác định được kích thước đồ, cách đi điện hoặc điều kiện lắp đặt.
        Bạn nên gửi mặt bằng, ảnh hiện trạng và ngân sách để nhân viên thiết kế đề xuất phương án
        phù hợp. Các hạng mục và chi phí chỉ được xác nhận trong báo giá của hồ sơ.</p>
    <div class="actions">
        <a class="button" href="<?= e(url('catalog')) ?>">Chọn dịch vụ thiết kế ↗</a>
        <a class="text-link" href="<?= e(url('process')) ?>">Xem quy trình làm việc</a>
    </div>
</section>

<section class="public-section inspiration-gallery">
    <div class="section-heading">
        <div>
            <p class="eyebrow">CHẤT LIỆU CHO Ý TƯỞNG</p>
            <h2>Những góc nhìn để bắt đầu.</h2>
        </div>
        <span>Ảnh tham khảo từ các không gian khác nhau</span>
    </div>
    <div class="inspiration-photos">

        <?php foreach (array_values(array_unique([ideaPhotoKey($slug), 'kitchen-dining', 'warm-bedroom']))
                    as $photoKey
                ):
                    $referencePhoto = interiorPhoto($photoKey);
        ?>

        <figure>
            <img <?= photoAttributes($photoKey) ?>>
            <figcaption>
                <strong><?= e($referencePhoto['alt']) ?></strong>
                <small><?= e($referencePhoto['author']) ?> / Unsplash · Ảnh tham khảo</small>
            </figcaption>
        </figure>
        <?php endforeach; ?>

    </div>
    <p class="muted">Các ảnh thể hiện cảm hứng về màu sắc, vật liệu và bố cục, không phải một công
        trình của Nếp Nhà hoặc mặt bằng chính xác của phương án này.</p>
</section>
