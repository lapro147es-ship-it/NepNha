<section class="editorial-hero">
    <p class="eyebrow">PHONG CÁCH VÀ CÔNG NĂNG</p>
    <h1>Một vài gợi mở
        <br>cho không gian của bạn.
    </h1>
    <p>Các phương án dưới đây là nội dung minh họa để tham khảo cách tổ chức căn hộ, bảng màu và vật
        liệu. Mỗi thiết kế thực tế cần được điều chỉnh theo hiện trạng và nhu cầu riêng.</p>
</section>
<section class="public-section">
    <div class="idea-grid">

        <?php foreach (designIdeas() as $slug => $idea): ?>
        <article class="idea-card idea-<?= e($slug) ?>">
            <a href="<?= e(url('project', ['slug' => $slug])) ?>">
                <img <?= photoAttributes(ideaPhotoKey($slug)) ?>>
            </a>
            <div>
                <p class="eyebrow">PHƯƠNG ÁN MINH HỌA · <?= e($idea['area']) ?></p>
                <h2>
                    <a href="<?= e(url('project', ['slug' => $slug])) ?>"><?= e($idea['name']) ?></a>
                </h2>
                <p><?= e($idea['description']) ?></p>
                <a class="text-link" href="<?= e(url('project', ['slug' => $slug])) ?>">Khám phá phương án ↗</a>
            </div>
        </article>
        <?php endforeach; ?>

    </div>
</section>
