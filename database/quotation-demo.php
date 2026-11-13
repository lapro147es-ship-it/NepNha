<?php
declare(strict_types=1);

function demoQuotationPlan(string $type): array
{
    $spec = [
        'demo' => true,
        'scope' =>
            "Dữ liệu minh họa cho đồ án, dùng để hình dung nội dung một báo giá chi tiết.\nPhạm vi chính thức phải được xác nhận bằng khảo sát, kích thước và tài liệu của từng căn hộ.",
        'materials' =>
            "Tông gỗ sáng, bề mặt dễ vệ sinh, sắc độ be và trắng ấm.\nThương hiệu, mã vật liệu, độ dày và phụ kiện chưa được xác nhận trong dữ liệu mẫu; cần chốt bằng bảng vật liệu có mã và mẫu đối chiếu.",
        'timeline' =>
            "Mốc 1 · Xác nhận mặt bằng, nhu cầu và danh sách hạng mục.\nMốc 2 · Gửi phương án, trao đổi và thống nhất nội dung.\nMốc 3 · Bàn giao hồ sơ/kết quả, kiểm tra và xử lý các điểm còn lại.\nNgày bắt đầu và kết thúc cần được tư vấn xác nhận sau khi thống nhất đầu vào.",
        'payment_terms' =>
            "Phương án minh họa: 50% sau khi chấp thuận, 50% sau khi hoàn tất bàn giao.\nTỷ lệ và lịch thu tiền thực tế phải được hai bên thống nhất; các khoản đã thu được đối chiếu riêng trong trang Thanh toán.",
        'revision_terms' =>
            "Minh họa 2 vòng điều chỉnh trong phạm vi đã thống nhất. Mỗi vòng cần tổng hợp phản hồi cụ thể.\nĐổi công năng, tăng diện tích hoặc thêm hạng mục được lập thành nội dung phát sinh và báo giá mới trước khi thực hiện.",
        'support_terms' =>
            "Bàn giao danh sách tài liệu, giải thích nội dung và ghi nhận các điểm cần làm rõ.\nCác điều kiện bảo hành vật tư, phụ kiện hoặc thi công phải được nêu riêng theo sản phẩm và đơn vị cung cấp; dữ liệu mẫu không tự cam kết thời hạn bảo hành.",
        'exclusions' =>
            "Không bao gồm cải tạo kết cấu, điện nước ngoài phạm vi, thiết bị gia dụng, đồ rời chưa liệt kê, phí quản lý tòa nhà hoặc phát sinh không có trong hạng mục.\nẢnh tham khảo không phải sản phẩm hoặc vật liệu đã được chấp thuận.",
        'tax_terms' =>
            'Giá trị ở đây là tổng các dòng minh họa. Thông tin đã/chưa gồm thuế chưa được xác nhận; cần tư vấn nêu rõ trước khi khách hàng chấp thuận.',
        'valid_until' => '',
        'deliverables' =>
            "Bảng tổng hợp hạng mục và phạm vi.\nTài liệu phương án/bản vẽ hoặc báo cáo thực hiện theo dịch vụ.\nBảng vật liệu và phụ kiện cần xác nhận.\nBiên nhận bàn giao và danh sách các điểm cần xử lý."
    ];
    $rows = match ($type) {
        'DESIGN' => [
            [
                'Khảo sát và tổng hợp nhu cầu',
                'gói',
                1,
                1000000,
                'Toàn căn hộ',
                'Đo hiện trạng khi có lịch khảo sát; ghi nhu cầu sử dụng và các ràng buộc. Đầu ra: biên bản hiện trạng kèm kích thước/ảnh do nhân viên bổ sung.'
            ],
            [
                'Phương án mặt bằng bố trí',
                'phương án',
                2,
                2000000,
                'Studio 48 m²',
                'Bố trí khu ngủ, tiếp khách, bếp và lưu trữ; đối chiếu lối đi. Đầu ra: 2 phương án để lựa chọn trước khi triển khai chi tiết.'
            ],
            [
                'Phối cảnh không gian',
                'góc nhìn',
                4,
                1500000,
                'Các khu vực chính',
                'Thể hiện bảng màu, vật liệu và đồ nội thất theo phương án đã thống nhất. Đầu ra: 4 góc nhìn; ảnh thư viện trên website không thay thế phối cảnh này.'
            ],
            [
                'Hồ sơ bản vẽ kỹ thuật',
                'bộ',
                1,
                4000000,
                'Nội thất trong phạm vi',
                'Mặt bằng, mặt đứng và chi tiết đồ đóng theo hạng mục. Đầu ra: bộ PDF có số hiệu bản vẽ và kích thước để đối chiếu.'
            ],
            [
                'Bảng vật liệu và dự toán tham khảo',
                'bộ',
                1,
                2000000,
                'Vật liệu và phụ kiện',
                'Liệt kê bề mặt, mã vật liệu và nhóm phụ kiện để chốt mẫu; tách rõ phần chưa xác nhận. Đầu ra: bảng vật liệu và khái toán theo phương án.'
            ],
            [
                'Rà soát và bàn giao hồ sơ',
                'buổi',
                1,
                1000000,
                'Hồ sơ thiết kế',
                'Giải thích bộ hồ sơ, đối chiếu danh sách tài liệu và tổng hợp các điểm còn lại. Đầu ra: danh sách bàn giao và nội dung cần làm rõ.'
            ]
        ],
        'INSTALLATION' => [
            [
                'Bộ tủ giày theo phương án',
                'bộ',
                1,
                7000000,
                'Khu cửa vào',
                'Đối chiếu kích thước ô lắp đặt, số khoang và quy cách đã được chốt trước khi gia công.'
            ],
            [
                'Kệ trang trí phòng khách',
                'bộ',
                1,
                3000000,
                'Phòng khách',
                'Xác nhận cao độ, liên kết và tải sử dụng theo phương án; kiểm tra bề mặt khi bàn giao.'
            ],
            [
                'Vận chuyển và lắp đặt',
                'gói',
                1,
                1500000,
                'Căn hộ',
                'Thống nhất tuyến vận chuyển, giờ thi công và lắp đặt các hạng mục đã liệt kê.'
            ],
            [
                'Căn chỉnh và vệ sinh bàn giao',
                'gói',
                1,
                500000,
                'Các hạng mục lắp đặt',
                'Kiểm tra cánh, bản lề, liên kết; ghi nhận tồn tại trước khi khách hàng nghiệm thu.'
            ]
        ],
        default => [
            [
                'Khảo sát và lập phạm vi',
                'gói',
                1,
                1000000,
                'Toàn căn hộ',
                'Ghi hiện trạng và danh sách đầu việc cần xác nhận.'
            ],
            [
                'Hồ sơ thiết kế nội thất',
                'gói',
                1,
                18000000,
                'Các không gian chính',
                'Bố trí mặt bằng, phối cảnh, chi tiết kỹ thuật và bảng vật liệu theo phương án duyệt.'
            ],
            [
                'Hạng mục tủ bếp',
                'gói',
                1,
                55000000,
                'Khu bếp',
                'Khối lượng, mã bề mặt và phụ kiện cần được đối chiếu bằng bản vẽ và bảng vật liệu có xác nhận.'
            ],
            [
                'Hạng mục tủ quần áo',
                'gói',
                1,
                25000000,
                'Phòng ngủ',
                'Kiểm tra kích thước, chia khoang, ray và bản lề theo cấu hình đã thống nhất.'
            ],
            [
                'Kệ và nội thất bổ sung',
                'gói',
                1,
                18000000,
                'Phòng khách',
                'Chốt danh mục từng sản phẩm trong phạm vi; không mặc định bao gồm thiết bị hoặc đồ rời ngoài danh sách.'
            ],
            [
                'Lắp đặt, căn chỉnh và bàn giao',
                'gói',
                1,
                8000000,
                'Căn hộ',
                'Phân công, theo dõi tiến độ, kiểm tra và khách hàng xác nhận nghiệm thu.'
            ]
        ]
    };
    $items = [];
    foreach ($rows as [$name, $unit, $quantity, $price, $zone, $description]) {
        $items[] = [
            'item_name' => $name,
            'unit' => $unit,
            'quantity' => $quantity,
            'unit_price' => $price
        ];
        $spec['item_details'][] = ['zone' => $zone, 'description' => $description];
    }
    if ($type === 'DESIGN') {
        $spec['deliverables'] =
            "02 phương án bố trí mặt bằng để lựa chọn.\n04 góc phối cảnh các khu vực chính.\n01 bộ bản vẽ PDF trong phạm vi đã chốt.\n01 bảng vật liệu/phụ kiện và khái toán tham khảo.\n01 buổi giải thích và bàn giao hồ sơ.";
        $spec['scope'] .=
            "\nVí dụ studio 48 m²: khu tiếp khách, khu ngủ, bếp và các giải pháp lưu trữ. Không thay đổi kết cấu căn hộ.";
    }
    return ['items' => $items, 'spec' => $spec];
}
