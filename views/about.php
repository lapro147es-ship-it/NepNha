<section class="editorial-hero">
    <p class="eyebrow">NẾP NHÀ / INTERIOR STUDIO</p>
    <h1>Chăm chút không gian.
        <br>Giữ rõ từng cam kết.
    </h1>
    <p>Nếp Nhà kết nối việc thiết kế, lắp đặt và bảo trì nội thất trong một hành trình dễ theo dõi.
        Bạn có thể tìm hiểu dịch vụ trước, sau đó quản lý căn hộ và hồ sơ trong khu vực tài khoản.
    </p>
</section>
<section class="public-section editorial-content">
    <div class="editorial-columns">
        <article>
            <h2>Bắt đầu từ cách bạn sử dụng căn hộ</h2>
            <p>Phong cách là một phần của thiết kế. Những thông tin về thói quen sinh hoạt, nhu cầu
                lưu trữ, số người sử dụng và điều kiện hiện trạng giúp xác định giải pháp có thể
                dùng mỗi ngày.</p>
        </article>
        <article>
            <h2>Quyết định được lưu rõ ràng</h2>
            <p>Báo giá, bản thiết kế, lịch hẹn và kết quả thực hiện được liên kết theo từng hồ sơ.
                Người dùng có thể mở đúng trang để xem nội dung và thực hiện phản hồi thuộc quyền
                của mình.</p>
        </article>
    </div>
    <h2>Những người cùng tham gia</h2>
    <div class="responsibility-grid">

        <?php foreach ([
                       'Tư vấn viên' =>
                           'Tiếp nhận nhu cầu, khảo sát, thống nhất phạm vi và điều phối công việc.',
                       'Nhân viên thiết kế' =>
                           'Xây dựng phương án, quản lý các bản nộp và xử lý phản hồi.',
                       'Nhân viên lắp đặt' =>
                           'Thực hiện hạng mục theo phân công và báo cáo kết quả tại căn hộ.',
                       'Kỹ thuật viên bảo trì' =>
                           'Ghi nhận hiện trạng, xử lý sự cố và lưu lịch sử chăm sóc.'
                   ]
                   as $name => $description): ?>

        <article>
            <h3><?= e($name) ?></h3>
            <p><?= e($description) ?></p>
        </article>
        <?php endforeach; ?>

    </div>
    <p class="service-note">Nếp Nhà trong website này là thương hiệu minh họa của đồ án. Các phương
        án tham khảo và dữ liệu mẫu phục vụ trình diễn chức năng, không phải hồ sơ công trình của
        một doanh nghiệp được khảo sát.</p>
    <a class="button" href="<?= e(url('catalog')) ?>">Tìm hiểu các dịch vụ ↗</a>
</section>
