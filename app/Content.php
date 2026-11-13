<?php
declare(strict_types=1);

function serviceContent(string $type): array
{
    $content = [
        'DESIGN' => [
            'subtitle' => 'Một phương án phù hợp cách bạn sống, trước khi bắt đầu thi công.',
            'intro' =>
                'Thiết kế bắt đầu bằng những câu hỏi cụ thể: gia đình có bao nhiêu người, cần bao nhiêu chỗ lưu trữ, ai thường nấu ăn và đâu là khoảng thời gian căn hộ đón ánh sáng tốt nhất. Từ hiện trạng và ngân sách, phương án bố trí được phát triển thành bộ tài liệu để khách hàng xem xét và phản hồi.',
            'deliverables' => [
                'Mặt bằng bố trí nội thất và phân khu công năng.',
                'Đề xuất phong cách, bảng màu và vật liệu theo phạm vi báo giá.',
                'Bản vẽ hoặc ảnh phối cảnh được gửi dưới dạng PDF, PNG, JPG.',
                'Các phiên bản điều chỉnh cùng lịch sử phản hồi của khách hàng.',
                'Bộ thiết kế được duyệt làm cơ sở bàn giao cho bước lắp đặt.'
            ],
            'steps' => [
                'Lắng nghe nhu cầu và tiếp nhận mặt bằng.',
                'Khảo sát, ghi nhận kích thước và các điểm cần lưu ý.',
                'Thống nhất phạm vi, ngân sách và báo giá thiết kế.',
                'Phát triển phương án, gửi bản thiết kế và nhận phản hồi.',
                'Điều chỉnh, duyệt phiên bản cuối và bàn giao tài liệu.'
            ],
            'prepare' => [
                'Mặt bằng căn hộ hoặc kích thước hiện trạng nếu có.',
                'Ảnh các khu vực cần thiết kế và đồ nội thất muốn giữ lại.',
                'Số người sử dụng, thói quen sinh hoạt và yêu cầu lưu trữ.',
                'Khoảng ngân sách dự kiến, phong cách hoặc hình ảnh tham khảo.'
            ],
            'note' =>
                'Số lượng bản vẽ, phối cảnh, số lần chỉnh sửa và thời gian bàn giao được thống nhất trong báo giá. Website quản lý tài liệu thiết kế được tải lên; phần dựng hình được thực hiện bằng phần mềm thiết kế bên ngoài.'
        ],
        'INSTALLATION' => [
            'subtitle' => 'Biến phương án đã thống nhất thành những hạng mục có thể nghiệm thu.',
            'intro' =>
                'Lắp đặt cần một danh sách hạng mục rõ ràng, tài liệu phù hợp và lịch làm việc được xác nhận. Mỗi công việc được giao cho nhân viên phụ trách; tiến độ, sự cố và hình ảnh kết quả được cập nhật theo hồ sơ để khách hàng theo dõi trước khi nghiệm thu.',
            'deliverables' => [
                'Danh sách hạng mục lắp đặt theo báo giá đã được chấp thuận.',
                'Lịch làm việc và nhân viên phụ trách từng công việc.',
                'Các cập nhật tiến độ, ghi chú hiện trường và ảnh thực hiện.',
                'Báo cáo kết quả để khách hàng kiểm tra và nghiệm thu.',
                'Lịch sử khắc phục nếu khách hàng ghi nhận hạng mục chưa đạt.'
            ],
            'steps' => [
                'Xác nhận hiện trạng, tài liệu và các hạng mục thực hiện.',
                'Thống nhất báo giá, điều kiện tiếp cận và lịch lắp đặt.',
                'Phân công nhân viên có chuyên môn phù hợp.',
                'Thực hiện lắp đặt, cập nhật tiến độ và báo cáo phát sinh.',
                'Kiểm tra, khắc phục khi cần và khách hàng xác nhận nghiệm thu.'
            ],
            'prepare' => [
                'Bản thiết kế hoặc mô tả hạng mục đã thống nhất.',
                'Thông tin đăng ký thi công, giờ làm việc và quy định của tòa nhà.',
                'Lối vận chuyển, thang máy và nơi tập kết theo điều kiện thực tế.',
                'Người liên hệ tại căn hộ và thời gian có thể bàn giao mặt bằng.'
            ],
            'note' =>
                'Với hồ sơ trọn gói, công việc lắp đặt chỉ được phân công sau khi có thiết kế được duyệt. Các phát sinh tăng phí cần được thống nhất riêng trước khi thực hiện.'
        ],
        'MAINTENANCE' => [
            'subtitle' =>
                'Chăm sóc những chi tiết nhỏ để nội thất tiếp tục được sử dụng thuận tiện.',
            'intro' =>
                'Một cánh tủ khó đóng, ray kéo không trơn hoặc phụ kiện bị lệch đều cần được mô tả và kiểm tra đúng hiện trạng. Hồ sơ bảo trì lưu thông tin sự cố, phương án xử lý, nhân viên thực hiện và kết quả xác nhận; các lần xử lý sau có thể tham chiếu lịch sử trước đó.',
            'deliverables' => [
                'Phiếu yêu cầu ghi nhận vị trí, hiện trạng và ảnh hư hỏng.',
                'Kết quả kiểm tra cùng phương án xử lý được thống nhất.',
                'Báo giá sửa chữa hoặc báo giá 0 đồng khi có căn cứ miễn phí.',
                'Lịch thực hiện, báo cáo công việc và hình ảnh sau xử lý.',
                'Lịch sử bảo trì gắn với căn hộ và hồ sơ gốc nếu có.'
            ],
            'steps' => [
                'Khách hàng mô tả sự cố và chọn hồ sơ gốc nếu có.',
                'Tư vấn tiếp nhận, làm rõ hiện trạng và điều kiện bảo hành.',
                'Thống nhất phương án, chi phí và thời gian thực hiện.',
                'Phân công kỹ thuật viên kiểm tra và xử lý.',
                'Khách hàng xác nhận kết quả hoặc yêu cầu kiểm tra lại.'
            ],
            'prepare' => [
                'Ảnh hoặc mô tả cụ thể vị trí gặp vấn đề.',
                'Thời điểm phát hiện và biểu hiện khi sử dụng.',
                'Mã hồ sơ lắp đặt trước đây nếu có.',
                'Các điều khoản bảo hành hoặc thông tin bàn giao liên quan.'
            ],
            'note' =>
                'Có thể tạo yêu cầu bảo trì độc lập. Hệ thống không tự kết luận miễn phí chỉ dựa trên ngày tạo hồ sơ; điều kiện bảo hành cần được tư vấn kiểm tra theo thỏa thuận và hiện trạng.'
        ],
        'PACKAGE' => [
            'subtitle' => 'Một hồ sơ xuyên suốt từ ý tưởng, thiết kế đến lắp đặt và bàn giao.',
            'intro' =>
                'Dịch vụ trọn gói kết nối các giai đoạn trong cùng một hồ sơ. Khách hàng chấp thuận báo giá, duyệt từng phiên bản thiết kế, theo dõi công việc lắp đặt và xác nhận nghiệm thu. Các tài liệu, lịch hẹn và khoản thanh toán được quản lý riêng nhưng liên kết rõ ràng với căn hộ.',
            'deliverables' => [
                'Kết quả khảo sát, nhu cầu và phạm vi triển khai được lưu theo căn hộ.',
                'Báo giá nhiều hạng mục, có lịch sử gửi và phản hồi.',
                'Bộ thiết kế theo phiên bản cùng quyết định duyệt của khách hàng.',
                'Kế hoạch công việc thiết kế và lắp đặt có nhân viên phụ trách.',
                'Báo cáo nghiệm thu, tổng hợp các khoản thu và hồ sơ bàn giao.'
            ],
            'steps' => [
                'Khảo sát căn hộ và làm rõ nhu cầu của gia đình.',
                'Thống nhất phạm vi, vật liệu và báo giá.',
                'Thiết kế, phản hồi và duyệt phương án.',
                'Phân công lắp đặt theo bản thiết kế đã duyệt.',
                'Nghiệm thu, đối soát thanh toán và đóng hồ sơ.'
            ],
            'prepare' => [
                'Thông tin căn hộ, diện tích và ảnh hiện trạng.',
                'Ngân sách tổng thể và các khu vực ưu tiên.',
                'Mong muốn về phong cách, công năng và đồ muốn giữ lại.',
                'Thời gian dự kiến sử dụng căn hộ và quy định của ban quản lý.'
            ],
            'note' =>
                'Hồ sơ trọn gói chỉ hoàn thành khi các công việc thiết kế và lắp đặt cần thiết đều được xác nhận. Hoàn thành chuyên môn và thanh toán đủ là hai điều kiện riêng trước khi đóng hồ sơ.'
        ]
    ];

    return $content[$type] ?? $content['DESIGN'];
}

function designIdeas(): array
{
    return [
        'japandi' => [
            'name' => 'Japandi dịu nhẹ',
            'area' => '82 m² · 2 phòng ngủ',
            'palette' => 'Gỗ sáng · Trắng ấm · Xanh olive',
            'description' =>
                'Phương án minh họa cho gia đình ưu tiên sự gọn gàng, ánh sáng tự nhiên và các bề mặt dễ sử dụng mỗi ngày.',
            'rooms' => [
                'Phòng khách liên thông bếp giúp giữ kết nối khi sinh hoạt.',
                'Tủ cao đến trần khai thác khoảng lưu trữ theo chiều đứng.',
                'Phòng ngủ hạn chế đồ rời, dành khoảng trống quanh giường.'
            ],
            'materials' => [
                'Bề mặt gỗ màu sáng và vân nhẹ.',
                'Vải trơn, sắc độ be và trắng ấm.',
                'Điểm nhấn xanh olive ở đồ rời hoặc cây trang trí.'
            ]
        ],
        'modern' => [
            'name' => 'Hiện đại vừa đủ',
            'area' => '70 m² · 2 phòng ngủ',
            'palette' => 'Trắng · Xám nhạt · Gỗ nâu',
            'description' =>
                'Phương án minh họa tập trung vào lối đi rõ ràng, vị trí đồ nội thất hợp lý và một bảng màu thống nhất giữa các phòng.',
            'rooms' => [
                'Kệ tivi kết hợp lưu trữ để giảm đồ nhỏ trên mặt bàn.',
                'Bàn ăn bố trí cạnh bếp, bảo đảm đường di chuyển thuận tiện.',
                'Phòng làm việc nhỏ kết hợp phòng ngủ phụ khi cần.'
            ],
            'materials' => [
                'Bề mặt phẳng và phụ kiện theo mức sử dụng.',
                'Các mảng sáng làm nền cho gỗ màu trung tính.',
                'Đèn được chia theo khu vực và mục đích sử dụng.'
            ]
        ],
        'studio' => [
            'name' => 'Studio linh hoạt',
            'area' => '48 m² · Không gian mở',
            'palette' => 'Trắng ngà · Gỗ sáng · Nâu đất',
            'description' =>
                'Phương án minh họa dành cho căn hộ nhỏ, cân bằng nơi nghỉ ngơi, làm việc và tiếp khách trong một mặt bằng mở.',
            'rooms' => [
                'Phân khu bằng thảm, ánh sáng và hướng bố trí thay vì vách kín.',
                'Bàn làm việc tận dụng cạnh cửa sổ.',
                'Tủ lưu trữ kết hợp nhiều chức năng để tiết kiệm diện tích.'
            ],
            'materials' => [
                'Màu nền sáng và bề mặt có ít chi tiết.',
                'Rèm phù hợp với hướng nắng thực tế.',
                'Đồ rời có kích thước được kiểm tra trên mặt bằng.'
            ]
        ],
        'warm' => [
            'name' => 'Ấm áp cho gia đình',
            'area' => '95 m² · 3 phòng ngủ',
            'palette' => 'Be · Gỗ ấm · Nâu nhạt',
            'description' =>
                'Phương án minh họa cho gia đình cần không gian sinh hoạt chung rộng rãi và những góc riêng có cách sử dụng khác nhau.',
            'rooms' => [
                'Phòng khách giữ khoảng trống cho sinh hoạt chung.',
                'Phòng trẻ em có khu học và nơi cất đồ dễ tiếp cận.',
                'Khu bếp và bàn ăn được khảo sát theo thói quen của gia đình.'
            ],
            'materials' => [
                'Ưu tiên bề mặt thuận tiện vệ sinh.',
                'Lựa chọn phụ kiện theo tần suất sử dụng.',
                'Phối màu các phòng trong cùng một bảng màu ấm.'
            ]
        ]
    ];
}

function frequentlyAsked(): array
{
    return [
        [
            'group' => 'Bắt đầu',
            'question' => 'Cần chuẩn bị gì trước khi gửi yêu cầu?',
            'answer' =>
                'Bạn nên khai báo địa chỉ và diện tích căn hộ, mô tả những khu vực muốn làm, ngân sách dự kiến và thời gian mong muốn. Nếu có mặt bằng hoặc ảnh hiện trạng, bạn có thể tải lên sau khi tạo hồ sơ.'
        ],
        [
            'group' => 'Bắt đầu',
            'question' => 'Có thể chỉ thiết kế hoặc chỉ lắp đặt không?',
            'answer' =>
                'Có. Hệ thống tiếp nhận riêng các dịch vụ thiết kế, lắp đặt, bảo trì và trọn gói. Phạm vi thực hiện được xác định bằng báo giá của từng hồ sơ.'
        ],
        [
            'group' => 'Báo giá',
            'question' => 'Giá trên trang dịch vụ có phải giá cuối cùng?',
            'answer' =>
                'Giá danh mục là mức tham khảo. Giá cam kết của hồ sơ là tổng các hạng mục trong báo giá đã được khách hàng chấp thuận. Diện tích, hiện trạng, vật liệu và phạm vi sẽ được làm rõ trước khi gửi báo giá.'
        ],
        [
            'group' => 'Báo giá',
            'question' => 'Tôi không đồng ý với báo giá thì làm gì?',
            'answer' =>
                'Mở trang chi tiết báo giá, chọn yêu cầu điều chỉnh và ghi nội dung cần trao đổi. Báo giá cũ được giữ nguyên trong lịch sử; tư vấn viên sẽ chuẩn bị phiên bản khác khi hai bên tiếp tục làm việc.'
        ],
        [
            'group' => 'Thiết kế',
            'question' => 'Có thể yêu cầu chỉnh sửa bản thiết kế không?',
            'answer' =>
                'Bạn có thể xem phiên bản đang chờ duyệt và gửi yêu cầu chỉnh sửa kèm nội dung cụ thể. Các lần nộp được lưu riêng để đối chiếu. Số lần chỉnh sửa trong phạm vi dịch vụ cần được thống nhất ở báo giá.'
        ],
        [
            'group' => 'Thiết kế',
            'question' => 'Website có tự tạo phối cảnh 3D không?',
            'answer' =>
                'Website quản lý quá trình thiết kế và tài liệu được nhân viên tải lên. Bản vẽ, phối cảnh được thực hiện bằng phần mềm chuyên dụng rồi gửi dưới dạng PDF hoặc ảnh để khách hàng xem và phản hồi.'
        ],
        [
            'group' => 'Lắp đặt',
            'question' => 'Theo dõi tiến độ thi công ở đâu?',
            'answer' =>
                'Mở hồ sơ, vào trang Công việc và chọn công việc cần xem. Tại trang chi tiết có người phụ trách, lịch thực hiện, cập nhật tiến độ và tệp kết quả được phép truy cập.'
        ],
        [
            'group' => 'Lắp đặt',
            'question' => 'Công việc đạt 100% đã được nghiệm thu chưa?',
            'answer' =>
                'Chưa. Tiến độ do nhân viên cập nhật; nghiệm thu là quyết định riêng của khách hàng sau khi nhân viên gửi báo cáo kết quả. Nếu chưa đạt, khách hàng có thể yêu cầu khắc phục.'
        ],
        [
            'group' => 'Thanh toán',
            'question' => 'Thanh toán được theo dõi như thế nào?',
            'answer' =>
                'Tư vấn viên ghi nhận từng lần thu có mã chứng từ. Khách hàng xem giá trị báo giá được duyệt, số tiền đã thu, còn lại và chi tiết các lần thu trên trang Thanh toán của hồ sơ.'
        ],
        [
            'group' => 'Thanh toán',
            'question' => 'Phiếu tổng hợp thanh toán có phải hóa đơn thuế không?',
            'answer' =>
                'Phiếu trong hệ thống là bản tổng hợp nội bộ theo hồ sơ để đối chiếu và in. Việc phát hành hóa đơn thuế được xử lý theo quy trình riêng của đơn vị cung cấp.'
        ],
        [
            'group' => 'Bảo trì',
            'question' => 'Căn hộ đã bàn giao muốn bảo trì thì làm sao?',
            'answer' =>
                'Tạo yêu cầu bảo trì mới, chọn căn hộ và hồ sơ gốc nếu có, mô tả hư hỏng và bổ sung ảnh. Yêu cầu mới có quá trình tiếp nhận, báo giá, phân công và xác nhận kết quả riêng.'
        ],
        [
            'group' => 'Bảo trì',
            'question' => 'Có phải mọi yêu cầu bảo trì đều miễn phí?',
            'answer' =>
                'Không mặc định. Tư vấn viên kiểm tra điều kiện bảo hành và hiện trạng. Nếu thuộc phạm vi miễn phí, một báo giá 0 đồng được gửi để ghi nhận phương án đã thống nhất; trường hợp khác sẽ có chi phí được chấp thuận trước.'
        ]
    ];
}
