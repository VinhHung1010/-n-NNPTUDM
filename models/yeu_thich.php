<?php
require_once __DIR__ . '/../config/database.php';

class YeuThich {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Thêm/yêu thích khóa học
    public function them($id_nguoi_dung, $id_khoa_hoc) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO yeu_thich (id_nguoi_dung, id_khoa_hoc)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_khoa_hoc);
        return $stmt->execute();
    }

    // Xóa bỏ yêu thích
    public function xoa($id_nguoi_dung, $id_khoa_hoc) {
        $stmt = $this->db->prepare("
            DELETE FROM yeu_thich WHERE id_nguoi_dung = ? AND id_khoa_hoc = ?
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_khoa_hoc);
        return $stmt->execute();
    }

    // Kiểm tra đã yêu thích chưa
    public function daYeuThich($id_nguoi_dung, $id_khoa_hoc) {
        $stmt = $this->db->prepare("
            SELECT id FROM yeu_thich WHERE id_nguoi_dung = ? AND id_khoa_hoc = ?
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_khoa_hoc);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // Lấy danh sách khóa học yêu thích của người dùng
    public function layDanhSach($id_nguoi_dung) {
        $stmt = $this->db->prepare("
            SELECT kh.*, nd.ho_ten AS ten_giao_vien, dm.ten_danh_muc,
                   (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = kh.id) AS so_bai_hoc,
                   yt.ngay_tao AS ngay_yeu_thich
            FROM yeu_thich yt
            JOIN khoa_hoc kh ON yt.id_khoa_hoc = kh.id
            LEFT JOIN nguoi_dung nd ON kh.id_nguoi_tao = nd.id
            LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
            WHERE yt.id_nguoi_dung = ?
            ORDER BY yt.ngay_tao DESC
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Lấy danh sách ID khóa học đã yêu thích (để hiển thị icon nhanh)
    public function layIdsYeuThich($id_nguoi_dung) {
        $stmt = $this->db->prepare("
            SELECT id_khoa_hoc FROM yeu_thich WHERE id_nguoi_dung = ?
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return array_column($result, 'id_khoa_hoc');
    }

    // Đếm số khóa học yêu thích
    public function dem($id_nguoi_dung) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM yeu_thich WHERE id_nguoi_dung = ?
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }

    // Toggle - thêm nếu chưa có, xóa nếu đã có
    public function toggle($id_nguoi_dung, $id_khoa_hoc) {
        if ($this->daYeuThich($id_nguoi_dung, $id_khoa_hoc)) {
            $this->xoa($id_nguoi_dung, $id_khoa_hoc);
            return ['action' => 'removed', 'is_favorited' => false];
        } else {
            $this->them($id_nguoi_dung, $id_khoa_hoc);
            return ['action' => 'added', 'is_favorited' => true];
        }
    }
}
