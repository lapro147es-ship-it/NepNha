<?php
declare(strict_types=1);

require_once __DIR__ . '/quotation-demo.php';
function seedDatabase(): void
{
    ensureCommerceSchema();
    if (Database::one('SELECT id FROM users LIMIT 1')) {
        throw new RuntimeException('Chỉ nạp mẫu vào CSDL trống.');
    }
    Database::transaction(function () {
        $roles = [];
        foreach (ROLE_NAMES as $code => $name) {
            $roles[$code] = Database::insert('roles', ['code' => $code, 'name' => $name]);
        }
        $people = [
            ['CUSTOMER', 'khach', 'Nguyễn Minh Anh', '0901234567'],
            ['CONSULTANT', 'tuvan', 'Trần Ngọc Linh', '0902345678'],
            ['DESIGNER', 'thietke', 'Lê Hoàng Nam', '0903456789'],
            ['INSTALLER', 'lapdat', 'Phạm Đức Huy', '0904567890'],
            ['MAINTAINER', 'baotri', 'Võ Thanh Tùng', '0905678901'],
            ['ADMIN', 'admin', 'Quản trị Nếp Nhà', '0906789012'],
            ['CUSTOMER', 'khach2', 'Đỗ Thu Hà', '0907890123']
        ];
        foreach ($people as [$role, $email, $name, $phone]) {
            $uid = Database::insert('users', [
                'email' => $email . '@nepnha.test',
                'password_hash' => password_hash('NoiThat@123', PASSWORD_DEFAULT),
                'full_name' => $name,
                'phone' => $phone,
                'is_active' => 1,
                'created_at' => now()
            ]);
            Database::insert('user_roles', ['user_id' => $uid, 'role_id' => $roles[$role]]);
        }
        foreach (
            [
                [
                    'TK',
                    'Thiết kế không gian',
                    'DESIGN',
                    'Bố trí mặt bằng, lựa chọn vật liệu và phối cảnh theo phong cách sống của bạn.',
                    18000000
                ],
                [
                    'LD',
                    'Lắp đặt nội thất',
                    'INSTALLATION',
                    'Lắp đặt tủ bếp, phòng ngủ và nội thất rời theo phương án đã thống nhất.',
                    35000000
                ],
                [
                    'BT',
                    'Chăm sóc và bảo trì',
                    'MAINTENANCE',
                    'Kiểm tra phụ kiện, điều chỉnh bản lề, xử lý hư hỏng và bảo dưỡng nội thất.',
                    850000
                ],
                [
                    'TG',
                    'Nội thất trọn gói',
                    'PACKAGE',
                    'Đồng hành từ khảo sát, thiết kế đến lắp đặt và nghiệm thu căn hộ.',
                    125000000
                ]
            ]
            as [$code, $name, $type, $desc, $price]
        ) {
            Database::insert('services', [
                'code' => $code,
                'name' => $name,
                'service_type' => $type,
                'description' => $desc,
                'reference_price' => $price,
                'is_active' => 1
            ]);
        }
        Database::insert('apartments', [
            'customer_id' => 1,
            'name' => 'Căn hộ Lumière Riverside',
            'address' => 'Tầng 12, tòa West, TP. Thủ Đức, TP.HCM',
            'area_m2' => 82,
            'notes' => '2 phòng ngủ, ưu tiên phong cách Japandi.',
            'created_at' => now()
        ]);
        Database::insert('apartments', [
            'customer_id' => 1,
            'name' => 'Studio The Sun Avenue',
            'address' => 'Tầng 8, tòa SAV3, TP. Thủ Đức, TP.HCM',
            'area_m2' => 48,
            'notes' => 'Tối ưu lưu trữ và ánh sáng tự nhiên.',
            'created_at' => now()
        ]);
        Database::insert('apartments', [
            'customer_id' => 7,
            'name' => 'Căn hộ Masteri',
            'address' => 'TP. Thủ Đức, TP.HCM',
            'area_m2' => 70,
            'notes' => 'Dữ liệu kiểm tra phân quyền.',
            'created_at' => now()
        ]);
        $examples = [
            [
                'TG',
                1,
                4,
                'IN_PROGRESS',
                'Thiết kế và lắp đặt phòng khách, phòng ngủ theo phong cách Japandi.',
                145000000
            ],
            ['BT', 1, 3, 'NEW', 'Bản lề tủ bếp bị lệch, cần kiểm tra và cân chỉnh.', 1500000],
            [
                'TK',
                2,
                1,
                'QUOTED',
                'Thiết kế studio tối giản, thêm giải pháp lưu trữ thông minh.',
                18000000
            ],
            ['LD', 2, 2, 'COMPLETED', 'Lắp đặt bộ tủ giày và kệ trang trí phòng khách.', 12000000],
            ['TK', 3, 1, 'NEW', 'Thiết kế căn hộ 2 phòng ngủ.', 25000000]
        ];
        foreach ($examples as $i => [$code, $apt, $sid, $status, $desc, $budget]) {
            $rid = Database::insert('service_requests', [
                'code' => 'NT-DEMO-00' . ($i + 1),
                'customer_id' => $apt === 3 ? 7 : 1,
                'apartment_id' => $apt,
                'service_id' => $sid,
                'consultant_id' => $status === 'NEW' ? null : 2,
                'description' => $desc,
                'budget' => $budget,
                'preferred_date' => date('Y-m-d', strtotime('+7 days')),
                'status' => $status,
                'created_at' => gmdate('Y-m-d H:i:s', time() - ($i + 1) * 86400),
                'updated_at' => now()
            ]);
            Database::insert('audit_logs', [
                'actor_id' => $apt === 3 ? 7 : 1,
                'request_id' => $rid,
                'entity_type' => 'request',
                'entity_id' => $rid,
                'action' => 'DEMO_CREATE',
                'new_status' => $status,
                'created_at' => now()
            ]);
        }
        foreach (
            [
                [1, 'ACCEPTED', 125000000, 'Thiết kế và lắp đặt nội thất căn hộ'],
                [3, 'SENT', 18000000, 'Thiết kế nội thất studio 48 m²'],
                [4, 'ACCEPTED', 12000000, 'Lắp tủ giày và kệ trang trí']
            ]
            as [$rid, $status, $sum, $item]
        ) {
            $qid = Database::insert('quotations', [
                'request_id' => $rid,
                'version' => 1,
                'status' => $status,
                'notes' => 'Phạm vi và vật liệu được xác nhận theo hồ sơ.',
                'created_by' => 2,
                'created_at' => now(),
                'sent_at' => now(),
                'responded_by' => $status === 'ACCEPTED' ? 1 : null,
                'responded_at' => $status === 'ACCEPTED' ? now() : null
            ]);
            Database::insert('quotation_items', [
                'quotation_id' => $qid,
                'item_name' => $item,
                'unit' => 'Gói',
                'quantity' => 1,
                'unit_price' => $sum
            ]);
            $serviceType = Database::one(
                'SELECT s.service_type FROM service_requests sr JOIN services s ON s.id=sr.service_id WHERE sr.id=?',
                [$rid]
            )['service_type'];
            $plan = demoQuotationPlan($serviceType);
            Database::run('DELETE FROM quotation_items WHERE quotation_id=?', [$qid]);
            foreach ($plan['items'] as $planItem) {
                Database::insert('quotation_items', ['quotation_id' => $qid] + $planItem);
            }
            saveQuotationSpec($qid, $plan['spec']);
            if ($rid !== 3) {
                Database::insert('payments', [
                    'request_id' => $rid,
                    'quotation_id' => $qid,
                    'receipt_code' => 'PT-DEMO-' . $rid,
                    'amount' => $rid === 1 ? 40000000 : 12000000,
                    'method' => 'BANK_TRANSFER',
                    'status' => 'POSTED',
                    'paid_at' => now(),
                    'recorded_by' => 2,
                    'created_at' => now()
                ]);
            }
        }
        $start = gmdate('Y-m-d H:i:s', strtotime('+2 days'));
        $end = gmdate('Y-m-d H:i:s', strtotime('+2 days +3 hours'));
        $design = Database::insert('work_orders', [
            'request_id' => 1,
            'assignee_id' => 3,
            'assigned_by' => 2,
            'work_type' => 'DESIGN',
            'title' => 'Thiết kế căn hộ Japandi 82 m²',
            'description' => 'Mặt bằng phòng khách, bếp và hai phòng ngủ.',
            'starts_at' => $start,
            'ends_at' => $end,
            'status' => 'IN_PROGRESS',
            'created_at' => now()
        ]);
        Database::insert('work_updates', [
            'work_order_id' => $design,
            'author_id' => 3,
            'event_type' => 'PROGRESS',
            'progress_percent' => 65,
            'content' =>
                'Đã hoàn thành mặt bằng bố trí, đang phát triển phối cảnh và bảng vật liệu.',
            'created_at' => now()
        ]);
        $work = Database::insert('work_orders', [
            'request_id' => 4,
            'assignee_id' => 4,
            'assigned_by' => 2,
            'work_type' => 'INSTALLATION',
            'title' => 'Lắp tủ giày và kệ phòng khách',
            'starts_at' => gmdate('Y-m-d H:i:s', strtotime('-3 days')),
            'ends_at' => gmdate('Y-m-d H:i:s', strtotime('-3 days +3 hours')),
            'status' => 'DONE',
            'created_at' => now()
        ]);
        Database::insert('work_updates', [
            'work_order_id' => $work,
            'author_id' => 1,
            'event_type' => 'ACCEPTED',
            'content' => 'Nghiệm thu đạt yêu cầu, bàn giao đầy đủ.',
            'created_at' => now()
        ]);
    });
    seedCommerceVariants();
}
