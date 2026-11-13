<section class="landing-hero">
    <div>
        <p class="eyebrow">THIẾT KẾ TINH TẾ · ĐỒNG HÀNH BỀN LÂU</p>
        <h1>Nếp sống mới,
            <br>từ một <em>không gian.</em>
        </h1>
        <p class="lead">Từ ý tưởng đầu tiên đến những chi tiết sau bàn giao. Nếp Nhà đồng hành cùng
            bạn trong thiết kế, lắp đặt và chăm sóc nội thất căn hộ.</p>
        <div class="actions">
            <a class="button" href="<?= e(url('request-create')) ?>">Bắt đầu căn hộ của bạn ↗</a>
            <a class="text-link" href="<?= e(url('catalog')) ?>">Khám phá dịch vụ ↗</a>
        </div>
        <div class="hero-tags">
            <span>01 / Thiết kế theo nhu cầu</span>
            <span>02 / Theo dõi minh
                bạch</span>
            <span>03 / Chăm sóc lâu dài</span>
        </div>
    </div>
    <div class="landing-art">
        <img <?= photoAttributes('living-room', true) ?>>
        <span class="art-caption">ẢNH THAM KHẢO · KHÔNG GIAN SỐNG
            <br>
            <strong>Cho những ngày bình
                yên.</strong>
        </span>
    </div>
</section>
<section class="public-section" id="services">
    <div class="section-heading">
        <div>
            <p class="eyebrow">GIẢI PHÁP CHO CĂN HỘ</p>
            <h2>Chọn điều không gian bạn cần.</h2>
        </div>
        <span>Đủ đầy từ thiết kế đến chăm sóc.</span>
    </div>
    <div class="service-grid">

        <?php foreach (Database::all('SELECT * FROM services WHERE is_active=1 ORDER BY id') as $i => $s): ?>
        <article class="service-card">
            <a class="service-photo" href="<?= e(url('service-detail', ['id' => $s['id']])) ?>">
                <img <?= photoAttributes(servicePhotoKey($s['service_type'])) ?>>
                <span>ẢNH THAM KHẢO</span>
            </a>
            <span class="service-number">0<?= $i + 1 ?></span>
            <span class="service-glyph"><?= ['⌑', '⌂', '✧', '◇'][$i % 4] ?></span>
            <h3><?= e($s['name']) ?></h3>
            <p><?= e($s['description']) ?></p>
            <div>
                <span>Tham khảo từ <strong><?= money($s['reference_price']) ?></strong>
                </span>
                <a aria-label="Yêu cầu <?= e($s['name']) ?>" href="<?= e(url('service-detail', ['id' => $s['id']])) ?>">↗</a>
            </div>
        </article>
        <?php endforeach; ?>

    </div>
    <p class="muted">Giá thực tế được xác định bằng báo giá sau khi thống nhất nhu cầu và phạm vi
        dịch vụ.</p>
</section>
<section class="process-section" id="process">
    <p class="eyebrow">MỘT HÀNH TRÌNH RÕ RÀNG</p>
    <h2>Từ mong muốn đến không gian hoàn thiện.</h2>
    <div class="process-grid">

        <?php foreach ([
                       'Chia sẻ nhu cầu' => 'Khai báo căn hộ, ngân sách và những điều bạn mong muốn.',
                       'Khảo sát & báo giá' => 'Thống nhất hiện trạng, phạm vi công việc và chi phí.',
                       'Thiết kế & lắp đặt' => 'Duyệt từng bản thiết kế, theo dõi tiến độ thực hiện.',
                       'Bàn giao & chăm sóc' => 'Xác nhận kết quả và gửi yêu cầu bảo trì khi cần.'
                   ]
                   as $name => $desc): ?>

        <article>
            <h3><?= e($name) ?></h3>
            <p><?= e($desc) ?></p>
        </article>
        <?php endforeach; ?>

    </div>
</section>

<section class="public-section">
    <div class="section-heading">
        <div>
            <p class="eyebrow">CẢM HỨNG CHO KHÔNG GIAN SỐNG</p>
            <h2>Bắt đầu từ điều bạn yêu thích.</h2>
        </div>
        <a class="text-link" href="<?= e(url('portfolio')) ?>">Tất cả ý tưởng ↗</a>
    </div>
    <div class="idea-grid">

        <?php foreach (array_slice(designIdeas(), 0, 3, true) as $slug => $idea): ?>
        <article class="idea-card idea-<?= e($slug) ?>">
            <a href="<?= e(url('project', ['slug' => $slug])) ?>">
                <img <?= photoAttributes(ideaPhotoKey($slug)) ?>>
            </a>
            <div>
                <p class="eyebrow"><?= e($idea['area']) ?> · MINH HỌA</p>
                <h3>
                    <a href="<?= e(url('project', ['slug' => $slug])) ?>"><?= e($idea['name']) ?></a>
                </h3>
                <p><?= e($idea['description']) ?></p>
                <a class="text-link" href="<?= e(url('project', ['slug' => $slug])) ?>">Xem bố trí và vật liệu ↗</a>
            </div>
        </article>
        <?php endforeach; ?>

    </div>
</section>
<section class="public-section">
    <div class="editorial-columns">
        <article>
            <p class="eyebrow">RÕ RÀNG TRONG TỪNG BƯỚC</p>
            <h2>Bạn luôn biết căn hộ đang ở đâu trong hành trình.</h2>
            <p>Khảo sát có lịch hẹn và kết quả. Báo giá có hạng mục, phiên bản và phản hồi. Thiết kế
                được duyệt trước khi triển khai lắp đặt trọn gói.</p>
            <p>Khi công việc hoàn thành, khách hàng xem kết quả rồi xác nhận nghiệm thu hoặc yêu cầu
                khắc phục. Các khoản thu và lịch sử xử lý được lưu cùng hồ sơ.</p>
            <a class="button" href="<?= e(url('process')) ?>">Tìm hiểu quy trình ↗</a>
        </article>
        <article class="editorial-callout">
            <h3>Chuẩn bị cho buổi trao đổi đầu tiên</h3>
            <ul class="clean-list">
                <li>Mặt bằng, diện tích và ảnh hiện trạng căn hộ.</li>
                <li>Số người sử dụng và nhu cầu của từng phòng.</li>
                <li>Phong cách, vật liệu yêu thích và ngân sách dự kiến.</li>
                <li>Thời điểm mong muốn thực hiện và quy định của tòa nhà.</li>
            </ul>
            <a class="text-link" href="<?= e(url('contact')) ?>">Bắt đầu trao đổi ↗</a>
        </article>
    </div>
</section>
<section class="public-section">
    <div class="section-heading">
        <div>
            <p class="eyebrow">GIẢI ĐÁP TRƯỚC KHI BẮT ĐẦU</p>
            <h2>Những điều bạn có thể đang băn khoăn.</h2>
        </div>
        <a class="text-link" href="<?= e(url('faq')) ?>">Tất cả câu hỏi ↗</a>
    </div>
    <div class="faq-list">

        <?php foreach (array_slice(frequentlyAsked(), 0, 3) as $item): ?>
        <article>
            <h3><?= e($item['question']) ?></h3>
            <p><?= e($item['answer']) ?></p>
        </article>
        <?php endforeach; ?>

    </div>
</section>
