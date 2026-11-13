<section class="editorial-hero">
    <p class="eyebrow">MINH BẠCH TRONG TỪNG BƯỚC</p>
    <h1>Hiểu hành trình,
        <br>chủ động mỗi quyết định.
    </h1>
    <p>Khách hàng, tư vấn viên và các nhóm chuyên môn phối hợp theo từng hồ sơ. Mỗi giai đoạn có đầu
        vào, người phụ trách và kết quả cần xác nhận.</p>
</section>
<section class="public-section">
    <ol class="journey-list">

        <?php foreach ([
                        [
                            'Chia sẻ nhu cầu',
                            'Khách hàng',
                            'Khai báo căn hộ, mô tả các khu vực muốn thực hiện, ngân sách và ngày mong muốn. Tài liệu mặt bằng, ảnh hiện trạng giúp tư vấn hiểu đúng bối cảnh.'
                        ],
                        [
                            'Khảo sát và làm rõ hiện trạng',
                            'Tư vấn viên',
                            'Thống nhất lịch, xác nhận khả năng tiếp cận căn hộ và ghi nhận kết quả khảo sát. Lịch thay đổi được đưa về trạng thái chờ xác nhận.'
                        ],
                        [
                            'Thống nhất phạm vi và báo giá',
                            'Tư vấn viên và khách hàng',
                            'Xem từng hạng mục, đơn vị, số lượng và đơn giá. Bạn có thể chấp thuận hoặc yêu cầu điều chỉnh; các phiên bản cũ được giữ lại.'
                        ],
                        [
                            'Thiết kế và phản hồi',
                            'Nhân viên thiết kế và khách hàng',
                            'Nhân viên nộp bản thiết kế theo phiên bản. Khách hàng xem tài liệu, ghi nội dung cần sửa hoặc duyệt phương án để chuyển sang bước phù hợp.'
                        ],
                        [
                            'Lắp đặt và kiểm tra',
                            'Nhân viên lắp đặt',
                            'Công việc có người phụ trách và lịch riêng. Nhân viên cập nhật tiến độ, báo cáo kết quả, khách hàng nghiệm thu hoặc yêu cầu khắc phục.'
                        ],
                        [
                            'Đối soát và đồng hành sau bàn giao',
                            'Tư vấn viên và khách hàng',
                            'Đối chiếu báo giá, các lần thu và số còn lại. Hồ sơ được đóng sau khi đủ điều kiện; yêu cầu bảo trì sau đó được tạo thành hồ sơ mới để theo dõi riêng.'
                        ]
                    ]
                    as $index => [$name, $actor, $text]): ?>

        <li>
            <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <div>
                <p class="eyebrow"><?= e($actor) ?></p>
                <h2><?= e($name) ?></h2>
                <p><?= e($text) ?></p>
            </div>
        </li>
        <?php endforeach; ?>

    </ol>
    <div class="editorial-callout">
        <h2>Mỗi nhu cầu có một lộ trình phù hợp.</h2>
        <p>Dịch vụ chỉ thiết kế, chỉ lắp đặt hoặc bảo trì không phải đi qua mọi bước của hồ sơ trọn
            gói. Phạm vi và đầu việc được xác định theo dịch vụ đã chấp thuận.</p>
        <a class="button" href="<?= e(url('request-create')) ?>">Gửi yêu cầu đầu tiên ↗</a>
    </div>
</section>
