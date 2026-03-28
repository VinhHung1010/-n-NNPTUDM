-- Database: elearning_db
-- Tạo database nếu chưa tồn tại
CREATE DATABASE IF NOT EXISTS elearning_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE elearning_db;

-- Bảng người dùng
CREATE TABLE IF NOT EXISTS nguoi_dung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    vai_tro ENUM('hoc_vien', 'giao_vien', 'quan_tri') DEFAULT 'hoc_vien',
    anh_dai_dien VARCHAR(255) DEFAULT NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai ENUM('hoat_dong', 'khong_hoat_dong') DEFAULT 'hoat_dong'
) ENGINE=InnoDB;

-- Bảng danh mục khóa học
CREATE TABLE IF NOT EXISTS danh_muc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_danh_muc VARCHAR(100) NOT NULL,
    mo_ta TEXT,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng khóa học
CREATE TABLE IF NOT EXISTS khoa_hoc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_khoa_hoc VARCHAR(200) NOT NULL,
    mo_ta TEXT,
    hinh_anh VARCHAR(255),
    gia_tien DECIMAL(10,0) DEFAULT 0,
    id_nguoi_tao INT,
    id_danh_muc INT,
    trang_thai ENUM('ban_nhap', 'da_duyet', 'bi_an') DEFAULT 'ban_nhap',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_nguoi_tao) REFERENCES nguoi_dung(id) ON DELETE SET NULL,
    FOREIGN KEY (id_danh_muc) REFERENCES danh_muc(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Bảng bài học
CREATE TABLE IF NOT EXISTS bai_hoc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_khoa_hoc INT NOT NULL,
    tieu_de VARCHAR(200) NOT NULL,
    noi_dung TEXT,
    video_url VARCHAR(255),
    thu_tu INT DEFAULT 0,
    thoi_luong_phut INT DEFAULT 0,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_khoa_hoc) REFERENCES khoa_hoc(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng Quiz
CREATE TABLE IF NOT EXISTS quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_bai_hoc INT NOT NULL,
    tieu_de VARCHAR(200) NOT NULL,
    mo_ta TEXT,
    thoi_gian_phut INT DEFAULT 15,
    diem_toi_da INT DEFAULT 100,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_bai_hoc) REFERENCES bai_hoc(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng câu hỏi
CREATE TABLE IF NOT EXISTS cau_hoi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_quiz INT NOT NULL,
    noi_dung TEXT NOT NULL,
    FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng đáp án
CREATE TABLE IF NOT EXISTS dap_an (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cau_hoi INT NOT NULL,
    noi_dung TEXT NOT NULL,
    la_dap_an_dung BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_cau_hoi) REFERENCES cau_hoi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng kết quả Quiz
CREATE TABLE IF NOT EXISTS ket_qua_quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_hoc_vien INT NOT NULL,
    id_quiz INT NOT NULL,
    diem_so INT NOT NULL,
    so_cau_dung INT,
    thoi_gian_lam_bai INT,
    ngay_lam TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_hoc_vien) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng tiến độ học tập
CREATE TABLE IF NOT EXISTS tien_do_hoc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_hoc_vien INT NOT NULL,
    id_bai_hoc INT NOT NULL,
    da_hoan_thanh BOOLEAN DEFAULT FALSE,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_hoc_vien) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_bai_hoc) REFERENCES bai_hoc(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (id_hoc_vien, id_bai_hoc)
) ENGINE=InnoDB;

-- Bảng đăng ký khóa học
CREATE TABLE IF NOT EXISTS dang_ky_khoa_hoc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_hoc_vien INT NOT NULL,
    id_khoa_hoc INT NOT NULL,
    ngay_dang_ky TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai ENUM('cho_xu_ly', 'da_xac_nhan', 'da_huy') DEFAULT 'cho_xu_ly',
    FOREIGN KEY (id_hoc_vien) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_khoa_hoc) REFERENCES khoa_hoc(id) ON DELETE CASCADE,
    UNIQUE KEY unique_register (id_hoc_vien, id_khoa_hoc)
) ENGINE=InnoDB;

-- Bảng chứng chỉ hoàn thành khóa học
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_hoc_vien INT NOT NULL,
    id_khoa_hoc INT NOT NULL,
    ma_chung_chi VARCHAR(50) UNIQUE NOT NULL,
    ngay_cap TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_hoc_vien) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_khoa_hoc) REFERENCES khoa_hoc(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cert (id_hoc_vien, id_khoa_hoc)
) ENGINE=InnoDB;

-- Bảng đánh giá khóa học
CREATE TABLE IF NOT EXISTS danh_gia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_dung INT NOT NULL,
    id_khoa_hoc INT NOT NULL,
    diem_so INT NOT NULL CHECK (diem_so >= 1 AND diem_so <= 5),
    noi_dung TEXT,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_khoa_hoc) REFERENCES khoa_hoc(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (id_nguoi_dung, id_khoa_hoc)
) ENGINE=InnoDB;

-- Bảng yêu thích khóa học
CREATE TABLE IF NOT EXISTS yeu_thich (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_dung INT NOT NULL,
    id_khoa_hoc INT NOT NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (id_khoa_hoc) REFERENCES khoa_hoc(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (id_nguoi_dung, id_khoa_hoc)
) ENGINE=InnoDB;

-- Bảng thông báo
CREATE TABLE IF NOT EXISTS thong_bao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nguoi_nhan INT NOT NULL,
    tieu_de VARCHAR(255) NOT NULL,
    noi_dung TEXT,
    loai ENUM('dang_ky', 'duyet_khoa', 'tu_choi_khoa', 'hoan_thanh_khoa', 'chung_chi', 'quiz', 'he_thong') DEFAULT 'he_thong',
    duong_dan VARCHAR(255) DEFAULT NULL,
    da_doc BOOLEAN DEFAULT FALSE,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_nguoi_nhan) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    INDEX idx_nguoi_nhan (id_nguoi_nhan),
    INDEX idx_da_doc (da_doc),
    INDEX idx_ngay_tao (ngay_tao DESC)
) ENGINE=InnoDB;

-- Dữ liệu mẫu: Danh mục
INSERT INTO danh_muc (ten_danh_muc, mo_ta) VALUES
('Lập trình Web', 'Các khóa học về lập trình web'),
('Lập trình Mobile', 'Các khóa học về lập trình di động'),
('Python', 'Ngôn ngữ lập trình Python'),
('AI & Machine Learning', 'Trí tuệ nhân tạo và học máy'),
('Thiết kế đồ họa', 'Các khóa học thiết kế');

-- Dữ liệu mẫu: Người dùng (mật khẩu: 123456)
INSERT INTO nguoi_dung (ho_ten, email, mat_khau, vai_tro) VALUES
('Admin', 'admin@elearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'quan_tri'),
('Giáo viên Demo', 'giaovien@elearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'giao_vien');

-- Dữ liệu mẫu: Khóa học
INSERT INTO khoa_hoc (ten_khoa_hoc, mo_ta, hinh_anh, gia_tien, id_nguoi_tao, id_danh_muc, trang_thai) VALUES
('HTML & CSS Cơ bản', 'Khóa học dành cho người mới bắt đầu học lập trình web', 'html-css.jpg', 0, 2, 1, 'da_duyet'),
('JavaScript Nâng cao', 'Tìm hiểu sâu về JavaScript ES6+', 'javascript.jpg', 500000, 2, 1, 'da_duyet'),
('Python cho người mới', 'Học Python từ con số 0', 'python.jpg', 0, 2, 3, 'da_duyet');

-- Dữ liệu mẫu: Bài học
INSERT INTO bai_hoc (id_khoa_hoc, tieu_de, noi_dung, thu_tu, thoi_luong_phut) VALUES
(1, 'Giới thiệu HTML', 'HTML là ngôn ngữ đánh dấu siêu văn bản...', 1, 30),
(1, 'Cấu trúc HTML', 'Cấu trúc cơ bản của một trang web...', 2, 45),
(1, 'Giới thiệu CSS', 'CSS dùng để tạo kiểu cho trang web...', 3, 25),
(2, 'Biến và Kiểu dữ liệu', 'Tìm hiểu về biến trong JavaScript...', 1, 40),
(2, 'Hàm trong JavaScript', 'Cách khai báo và sử dụng hàm...', 2, 50);

-- Dữ liệu mẫu: Quiz
INSERT INTO quiz (id_bai_hoc, tieu_de, mo_ta, thoi_gian_phut, diem_toi_da) VALUES
(1, 'Kiểm tra HTML cơ bản', 'Ôn tập kiến thức HTML', 15, 100),
(2, 'Kiểm tra cấu trúc HTML', 'Bài kiểm tra về cấu trúc', 10, 100);

-- Dữ liệu mẫu: Câu hỏi
INSERT INTO cau_hoi (id_quiz, noi_dung) VALUES
(1, 'HTML là viết tắt của gì?'),
(1, 'Tag nào dùng để tạo tiêu đề lớn nhất?'),
(1, 'Thuộc tính nào dùng để thêm hình ảnh?'),
(2, 'Tag nào là thẻ gốc của trang HTML?'),
(2, 'Tag nào dùng để phân chia nội dung?');

-- Dữ liệu mẫu: Đáp án
INSERT INTO dap_an (id_cau_hoi, noi_dung, la_dap_an_dung) VALUES
(1, 'Hyper Text Markup Language', TRUE),
(1, 'High Tech Modern Language', FALSE),
(1, 'Home Tool Markup Language', FALSE),
(2, '<h1>', TRUE),
(2, '<h6>', FALSE),
(2, '<header>', FALSE),
(3, 'src', TRUE),
(3, 'href', FALSE),
(3, 'alt', FALSE),
(4, '<html>', TRUE),
(4, '<body>', FALSE),
(4, '<head>', FALSE),
(5, '<div>', TRUE),
(5, '<span>', FALSE),
(5, '<p>', TRUE);
