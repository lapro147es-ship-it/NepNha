<?php
declare(strict_types=1);

function routeDefinitions(): array
{
    $routes = [
        'forgot-password' => ['Khôi phục mật khẩu', 'recovery', true],
        'reset-password' => ['Đặt lại mật khẩu', 'recovery', true],
        'booking' => ['Đăng ký khảo sát nhanh', 'booking', true],
        'booking-received' => ['Đã nhận yêu cầu tư vấn', 'booking', true],
        'assistant' => ['Trợ lý tư vấn nội thất', 'assistant', true],
        'review-photo' => ['Ảnh đánh giá công khai', 'review-photo', true],
        'online-return' => ['Kết quả cổng thanh toán', 'online-payment', true],
        'payment-ipn' => ['Đối soát cổng thanh toán', 'online-payment', true],
        'service-selection' => ['Dịch vụ đã chọn', 'service-selection', false, ['CUSTOMER']],
        'selection-receipt' => [
            'Chi tiết lần gửi lựa chọn',
            'service-selection',
            false,
            ['CUSTOMER']
        ],
        'offers' => ['Ưu đãi của bạn', 'offers', false, ['CUSTOMER']],
        'inbox' => ['Thông báo', 'inbox'],
        'master-data' => [
            'Quản lý giải pháp và ưu đãi',
            'master-data',
            false,
            ['ADMIN', 'CONSULTANT']
        ],
        'master-edit' => ['Chỉnh sửa danh mục', 'master-edit', false, ['ADMIN', 'CONSULTANT']],
        'consultations' => [
            'Yêu cầu khảo sát nhanh',
            'consultations',
            false,
            ['ADMIN', 'CONSULTANT']
        ],
        'reviews' => ['Đánh giá dịch vụ', 'reviews', false, ['CUSTOMER', 'ADMIN', 'CONSULTANT']],
        'review-form' => ['Viết đánh giá', 'review-form', false, ['CUSTOMER']],
        'online-payment' => [
            'Thanh toán trực tuyến',
            'online-payment',
            false,
            ['CUSTOMER', 'ADMIN', 'CONSULTANT']
        ],
        'online-history' => [
            'Lịch sử giao dịch trực tuyến',
            'online-history',
            false,
            ['CUSTOMER', 'ADMIN', 'CONSULTANT']
        ],
        'home' => ['Không gian sống của bạn', 'home', true],
        'catalog' => ['Khám phá dịch vụ', 'catalog', true],
        'service-detail' => ['Chi tiết dịch vụ', 'service-detail', true],
        'portfolio' => ['Ý tưởng căn hộ', 'portfolio', true],
        'project' => ['Phương án tham khảo', 'project', true],
        'process' => ['Quy trình đồng hành', 'process', true],
        'about' => ['Về Nếp Nhà', 'about', true],
        'faq' => ['Câu hỏi thường gặp', 'faq', true],
        'contact' => ['Bắt đầu tư vấn', 'contact', true],
        'login' => ['Đăng nhập', 'login', true],
        'register' => ['Tạo tài khoản', 'register', true],
        'dashboard' => ['Tổng quan', 'dashboard'],
        'requests' => ['Hồ sơ dịch vụ', 'requests'],
        'request-create' => ['Tạo yêu cầu dịch vụ', 'request-create', false, ['CUSTOMER']],
        'request' => ['Tổng quan hồ sơ', 'request'],
        'request-surveys' => ['Khảo sát hồ sơ', 'request-section'],
        'request-quotations' => ['Báo giá hồ sơ', 'request-section'],
        'request-work' => ['Công việc hồ sơ', 'request-section'],
        'request-designs' => ['Thiết kế hồ sơ', 'request-section'],
        'request-documents' => ['Tài liệu hồ sơ', 'request-documents'],
        'request-payments' => ['Thanh toán hồ sơ', 'request-payments'],
        'request-activity' => ['Lịch sử hồ sơ', 'request-activity'],
        'request-decision' => ['Xử lý hồ sơ', 'action-form'],
        'surveys' => ['Lịch khảo sát', 'module-list', false, ['CUSTOMER', 'CONSULTANT', 'ADMIN']],
        'survey-detail' => ['Chi tiết khảo sát', 'survey-detail'],
        'survey-form' => ['Lập lịch khảo sát', 'action-form', false, ['CONSULTANT']],
        'survey-result' => ['Kết quả khảo sát', 'action-form', false, ['CONSULTANT']],
        'quotations' => ['Báo giá', 'module-list', false, ['CUSTOMER', 'CONSULTANT', 'ADMIN']],
        'quotation-detail' => ['Chi tiết báo giá', 'quotation-detail'],
        'quotation-form' => ['Soạn báo giá', 'quotation-form', false, ['CONSULTANT']],
        'tasks' => ['Công việc', 'module-list'],
        'task-detail' => ['Chi tiết công việc', 'task-detail'],
        'task-form' => ['Phân công công việc', 'action-form', false, ['CONSULTANT']],
        'task-update' => ['Cập nhật tiến độ', 'action-form'],
        'task-report' => ['Báo cáo hoàn thành', 'action-form'],
        'task-rework' => ['Yêu cầu khắc phục', 'action-form', false, ['CUSTOMER']],
        'task-cancel' => ['Hủy phân công', 'action-form', false, ['CONSULTANT']],
        'designs' => ['Bản thiết kế', 'module-list'],
        'design-detail' => ['Chi tiết thiết kế', 'design-detail'],
        'design-submit' => ['Nộp bản thiết kế', 'action-form', false, ['DESIGNER']],
        'document-upload' => ['Bổ sung tài liệu', 'action-form'],
        'payments' => ['Thanh toán', 'module-list', false, ['CUSTOMER', 'CONSULTANT', 'ADMIN']],
        'payment-detail' => ['Chi tiết khoản thu', 'payment-detail'],
        'payment-form' => ['Ghi nhận thu tiền', 'action-form', false, ['CONSULTANT']],
        'payment-void' => ['Điều chỉnh khoản thu', 'action-form', false, ['CONSULTANT']],
        'invoice' => ['Phiếu tổng hợp thanh toán', 'invoice'],
        'maintenance' => ['Lịch sử bảo trì', 'maintenance'],
        'notifications' => ['Việc cần phản hồi', 'notifications'],
        'apartments' => ['Căn hộ của tôi', 'apartments', false, ['CUSTOMER']],
        'apartment-detail' => ['Chi tiết căn hộ', 'apartment-detail', false, ['CUSTOMER']],
        'apartment-form' => ['Thông tin căn hộ', 'apartment-form', false, ['CUSTOMER']],
        'schedule' => [
            'Lịch làm việc',
            'schedule',
            false,
            ['CONSULTANT', 'DESIGNER', 'INSTALLER', 'MAINTAINER']
        ],
        'services' => ['Danh mục dịch vụ', 'services'],
        'service-form' => ['Thông tin dịch vụ', 'service-form', false, ['ADMIN']],
        'users' => ['Tài khoản và vai trò', 'users', false, ['ADMIN']],
        'user-detail' => ['Chi tiết tài khoản', 'user-detail', false, ['ADMIN']],
        'user-form' => ['Thông tin tài khoản', 'user-form', false, ['ADMIN']],
        'reports' => ['Báo cáo hoạt động', 'reports', false, ['ADMIN']],
        'profile' => ['Thông tin cá nhân', 'profile'],
        'profile-edit' => ['Chỉnh sửa hồ sơ cá nhân', 'profile-edit'],
        'password' => ['Đổi mật khẩu', 'password'],
        'document-view' => ['Chi tiết tài liệu', 'document-view'],
        'file-content' => ['Xem ảnh', 'download'],
        'download' => ['Tải tài liệu', 'download']
    ];

    return array_map(
        static fn(array $r): array => [
            'title' => $r[0],
            'view' => $r[1],
            'public' => $r[2] ?? false,
            'roles' => $r[3] ?? []
        ],
        $routes
    );
}

function actionDestination(string $action, int $result, array $post): string
{
    $entity = (int) ($post['id'] ?? 0);
    $route = match ($action) {
        'apartment_save' => 'apartment-detail',
        'user_save' => 'user-detail',
        'service_save' => 'service-detail',
        'profile_save' => 'profile',
        'survey_save', 'survey_done', 'survey_confirm', 'survey_cancel' => 'request-surveys',
        'quote_save' => 'request-quotations',
        'quote_respond' => 'quotation-detail',
        'task_assign' => 'request-work',
        'task_start',
        'task_update',
        'task_report',
        'task_accept',
        'task_rework',
        'task_cancel'
            => 'task-detail',
        'design_submit' => 'request-designs',
        'design_review' => 'design-detail',
        'request_upload' => 'request-documents',
        'payment_add' => 'request-payments',
        'payment_void' => 'payment-detail',
        default => 'request'
    };

    $id = in_array(
        $route,
        ['quotation-detail', 'task-detail', 'design-detail', 'payment-detail'],
        true
    )
        ? $entity
        : $result;

    return url($route, $route === 'profile' ? [] : ['id' => $id]);
}
