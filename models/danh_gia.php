<?php
require_once __DIR__ . '/../config/database.php';

class DanhGia {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Thêm đánh giá mới (hoặc cập nhật nếu đã tồn tại)
    public function themHoacCapNhat($id_nguoi_dung, $id_khoa_hoc, $diem_so, $noi_dung = '') {
        // Kiểm tra đã có đánh giá chưa
        $da_co = $this->layTheoNguoiDung($id_nguoi_dung, $id_khoa_hoc);

        if ($da_co) {
            // Cập nhật đánh giá cũ
            $stmt = $this->db->prepare("
                UPDATE danh_gia SET diem_so = ?, noi_dung = ?, ngay_cap_nhat = CURRENT_TIMESTAMP
                WHERE id_nguoi_dung = ? AND id_khoa_hoc = ?
            ");
            $stmt->bind_param("isii", $diem_so, $noi_dung, $id_nguoi_dung, $id_khoa_hoc);
        } else {
            // Thêm đánh giá mới
            $stmt = $this->db->prepare("
                INSERT INTO danh_gia (id_nguoi_dung, id_khoa_hoc, diem_so, noi_dung)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiis", $id_nguoi_dung, $id_khoa_hoc, $diem_so, $noi_dung);
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cảm ơn bạn đã đánh giá!'];
        }
        return ['success' => false, 'message' => 'Có lỗi xảy ra.'];
    }

    // Lấy đánh giá của một người dùng cho một khóa học
    public function layTheoNguoiDung($id_nguoi_dung, $id_khoa_hoc) {
        $stmt = $this->db->prepare("
            SELECT dg.*, nd.ho_ten, nd.anh_dai_dien
            FROM danh_gia dg
            JOIN nguoi_dung nd ON dg.id_nguoi_dung = nd.id
            WHERE dg.id_nguoi_dung = ? AND dg.id_khoa_hoc = ?
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_khoa_hoc);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Lấy tất cả đánh giá của một khóa học
    public function layTheoKhoaHoc($id_khoa_hoc, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT dg.*, nd.ho_ten, nd.anh_dai_dien
            FROM danh_gia dg
            JOIN nguoi_dung nd ON dg.id_nguoi_dung = nd.id
            WHERE dg.id_khoa_hoc = ?
            ORDER BY dg.ngay_tao DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $id_khoa_hoc, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Đếm số đánh giá
    public function demDanhGia($id_khoa_hoc) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM danh_gia WHERE id_khoa_hoc = ?");
        $stmt->bind_param("i", $id_khoa_hoc);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }

    // Tính điểm trung bình
    public function tinhDiemTrungBinh($id_khoa_hoc) {
        $stmt = $this->db->prepare("
            SELECT AVG(diem_so) as diem_tb, COUNT(*) as so_luong
            FROM danh_gia WHERE id_khoa_hoc = ?
        ");
        $stmt->bind_param("i", $id_khoa_hoc);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return [
            'diem_tb' => round($result['diem_tb'] ?? 0, 1),
            'so_luong' => $result['so_luong'] ?? 0
        ];
    }

    // Xóa đánh giá
    public function xoa($id) {
        $stmt = $this->db->prepare("DELETE FROM danh_gia WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Đếm phân bố sao (1-5 sao)
    public function thongKeSao($id_khoa_hoc) {
        $stmt = $this->db->prepare("
            SELECT diem_so, COUNT(*) as count
            FROM danh_gia
            WHERE id_khoa_hoc = ?
            GROUP BY diem_so
            ORDER BY diem_so DESC
        ");
        $stmt->bind_param("i", $id_khoa_hoc);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Khởi tạo mảng 5 sao
        $thong_ke = [
            5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0
        ];
        foreach ($results as $r) {
            $thong_ke[$r['diem_so']] = $r['count'];
        }
        return $thong_ke;
    }
}
