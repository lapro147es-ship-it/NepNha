# Nếp Nhà — Website Dịch Vụ Nội Thất Căn Hộ

Hệ thống quản lý dịch vụ thiết kế, lắp đặt và bảo trì nội thất căn hộ (PHP 8.2+, PDO SQLite/MySQL, HTML/CSS/JS thuần).

## 1. Cách chạy nhanh trong VS Code

1. Mở VS Code → **File → Open Folder** → chọn thư mục **`NepNha-Clean`** này.
2. Mở Terminal (**`Ctrl + ` `**) và chạy lệnh:

```powershell
php start.php
```

*(Hoặc nhấn **`Ctrl + Shift + B`** để chạy Task đã cấu hình sẵn).*

- Máy chủ local sẽ chạy tại `http://127.0.0.1:8080/` và tự động mở Google Chrome.
- Cơ sở dữ liệu SQLite (`storage/app.sqlite`) đã có sẵn dữ liệu mẫu, chạy ngay không cần cài đặt thêm.
- Nhấn `Ctrl + C` tại Terminal khi muốn dừng server.

---

## 2. Tài khoản Demo (Mật khẩu chung: `NoiThat@123`)

| Email | Vai trò | Chức năng chính |
|---|---|---|
| `khach@nepnha.test` | Khách hàng | Chọn gói dịch vụ, tạo yêu cầu, xác nhận lịch khảo sát, duyệt báo giá/thiết kế, nghiệm thu, thanh toán, đánh giá |
| `tuvan@nepnha.test` | Tư vấn viên | Tiếp nhận hồ sơ, lập lịch & ghi kết quả khảo sát, lập báo giá, phân công nhân sự, ghi nhận thu tiền |
| `thietke@nepnha.test` | Nhà thiết kế | Cập nhật tiến độ thiết kế, nộp các phiên bản bản vẽ thiết kế |
| `lapdat@nepnha.test` | Nhân viên lắp đặt | Cập nhật tiến độ thi công, báo cáo hoàn thành lắp đặt |
| `baotri@nepnha.test` | Kỹ thuật bảo trì | Kiểm tra và xử lý các yêu cầu bảo trì |
| `admin@nepnha.test` | Quản trị viên | Quản lý tài khoản/vai trò, danh mục dịch vụ, gói giải pháp, chiến dịch ưu đãi, voucher, báo cáo doanh thu |

---

## 3. Cấu trúc thư mục

```text
NepNha-Clean/
├── app/                # Xử lý nghiệp vụ, phân quyền (RBAC), kết nối CSDL (PDO)
├── bin/                # Script khởi chạy server local (serve.php) và cài đặt CSDL (install.php)
├── database/           # Schema SQL (sqlite.sql, mysql.sql, nepnha_web212_gd1.sql) và dữ liệu mẫu
├── public/             # Document Root (index.php, router.php, CSS/JS/Fonts/Images cục bộ)
├── storage/            # Cơ sở dữ liệu SQLite (app.sqlite) và thư mục lưu tệp tải lên
├── vendor/             # Thư viện PHPMailer (đã đóng gói sẵn)
├── views/              # Giao diện HTML/PHP của các phân hệ
├── config.example.php  # Cấu hình mẫu (cổng local, SQLite/MySQL, SMTP, VNPAY)
└── start.php           # Điểm khởi chạy nhanh ứng dụng
```

---

## 4. Chuyển sang MySQL (Tùy chọn khi cần chấm trên MySQL/XAMPP)

Mặc định dự án chạy sẵn bằng SQLite. Nếu muốn chuyển sang MySQL:
1. Tạo database trống tên `noithat_canho` (bảng mã `utf8mb4_unicode_ci`) trong phpMyAdmin / MySQL.
2. Nhập (Import) file `database/nepnha_web212_gd1.sql` (đã gồm đầy đủ 37 bảng, 69 khóa ngoại và dữ liệu mẫu).
3. Sao chép `config.example.php` thành `config.php`, đổi `'driver' => 'mysql'` và cập nhật thông tin `mysql_user`, `mysql_password` nếu có.
