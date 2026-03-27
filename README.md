# E-LEARNING Project

Website học trực tuyến E-LEARNING được xây dựng bằng PHP và MySQL.

## Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- XAMPP / WAMP / MAMP
- Web Server (Apache/Nginx)

## Cài đặt

### 1. Cài đặt XAMPP

1. Tải và cài đặt [XAMPP](https://www.apachefriends.org/)
2. Bật Apache và MySQL trong XAMPP Control Panel

### 2. Cài đặt Project

1. Clone hoặc copy project vào thư mục `htdocs`:
   ```
   C:\xampp\htdocs\DANNPTUDM
   ```

2. Mở phpMyAdmin: http://localhost/phpmyadmin

3. Import file `database/schema.sql` vào phpMyAdmin:
   - Chọn tab "Import"
   - Chọn file `database/schema.sql`
   - Click "Go"

### 3. Cấu hình

Mở file `config/config.php` và kiểm tra các cấu hình:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elearning_db');
define('SITE_URL', 'http://localhost/DANNPTUDM');
```

### 4. Truy cập Website

Mở trình duyệt và truy cập: http://localhost/DANNPTUDM/

## Tài khoản Demo

| Vai trò | Email | Mật khẩu |
|---------|-------|----------|
| Quản trị viên | admin@elearning.com | password |
| Giáo viên | giaovien@elearning.com | password |

## Các chức năng đã xây dựng

### 1. Đăng ký / Đăng nhập
- Đăng ký tài khoản mới
- Đăng nhập với email/password
- Đăng xuất
- Quản lý hồ sơ cá nhân
- Đổi mật khẩu

### 2. Quản lý Khóa học
- Xem danh sách khóa học
- Tìm kiếm khóa học
- Lọc theo danh mục
- Thêm/Sửa/Xóa khóa học (Admin)

### 3. Chi tiết Khóa học
- Xem thông tin chi tiết khóa học
- Xem danh sách bài học
- Thời lượng khóa học

### 4. Quản lý Bài học
- Xem nội dung bài học
- Hỗ trợ video bài học
- Điều hướng bài học (trước/sau)
- Danh sách bài học sidebar

### 5. Hệ thống Quiz
- Làm bài quiz trắc nghiệm
- Đếm ngược thời gian
- Chấm điểm tự động
- Hiển thị kết quả
- Lịch sử làm bài

## Cấu trúc Project

```
DANNPTUDM/
├── config/
│   ├── config.php       # Cấu hình chính
│   └── database.php     # Kết nối database
├── database/
│   └── schema.sql       # Cấu trúc database
├── models/
│   ├── auth.php         # Model xác thực
│   ├── khoa_hoc.php     # Model khóa học
│   ├── bai_hoc.php      # Model bài học
│   └── quiz.php         # Model quiz
├── views/
│   ├── layouts/
│   │   ├── header.php   # Header chung
│   │   └── footer.php   # Footer chung
│   ├── home/
│   │   └── index.php    # Trang chủ
│   ├── tai-khoan/
│   │   ├── dang-ky.php  # Trang đăng ký
│   │   ├── dang-nhap.php # Trang đăng nhập
│   │   ├── dang-xuat.php # Đăng xuất
│   │   ├── ho-so.php    # Hồ sơ cá nhân
│   │   └── doi-mat-khau.php # Đổi mật khẩu
│   ├── khoa-hoc/
│   │   ├── index.php    # Danh sách khóa học
│   │   ├── chi-tiet.php # Chi tiết khóa học
│   │   ├── them-moi.php # Thêm khóa học
│   │   ├── sua.php      # Sửa khóa học
│   │   └── xoa.php      # Xóa khóa học
│   ├── bai-hoc/
│   │   └── chi-tiet.php # Chi tiết bài học
│   └── quiz/
│       ├── lam-bai.php  # Làm bài quiz
│       └── ket-qua.php  # Kết quả quiz
├── index.php            # File index chính
├── .gitignore           # Git ignore
└── README.md            # File README
```

## Hướng dẫn sử dụng Git

### Clone project về máy
```bash
git clone https://github.com/VinhHung1010/-n-NNPTUDM.git
```

### Làm việc với nhánh
```bash
# Tạo nhánh mới cho chức năng của bạn
git checkout -b feature/ten-chuc-nang

# Commit thay đổi
git add .
git commit -m "Mô tả chức năng"

# Push lên nhánh mới
git push -u origin feature/ten-chuc-nang
```

## Phát triển thêm

Các thành viên khác có thể phát triển thêm các chức năng:
- Quản lý danh mục
- Quản lý người dùng (Admin)
- Tiến độ học tập
- Chứng chỉ hoàn thành
- Bình luận/Đánh giá khóa học
- Thanh toán trực tuyến
- Thông báo email

## License

MIT License
