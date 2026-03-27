<?php
require_once __DIR__ . '/../config/database.php';

class DanhMuc {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function layTatCa() {
        $stmt = $this->db->prepare("
            SELECT dm.*,
                   (SELECT COUNT(*) FROM khoa_hoc WHERE id_danh_muc = dm.id) AS so_khoa_hoc
            FROM danh_muc dm
            ORDER BY dm.ngay_tao DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTheoId($id) {
        $stmt = $this->db->prepare("SELECT * FROM danh_muc WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function them($ten_danh_muc, $mo_ta = '') {
        $stmt = $this->db->prepare("INSERT INTO danh_muc (ten_danh_muc, mo_ta) VALUES (?, ?)");
        $stmt->bind_param("ss", $ten_danh_muc, $mo_ta);
        return $stmt->execute();
    }

    public function sua($id, $ten_danh_muc, $mo_ta = '') {
        $stmt = $this->db->prepare("UPDATE danh_muc SET ten_danh_muc = ?, mo_ta = ? WHERE id = ?");
        $stmt->bind_param("ssi", $ten_danh_muc, $mo_ta, $id);
        return $stmt->execute();
    }

    public function xoa($id) {
        // Kiểm tra có khóa học đang dùng không
        $stmt = $this->db->prepare("SELECT COUNT(*) AS c FROM khoa_hoc WHERE id_danh_muc = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_assoc()['c'];
        if ($count > 0) {
            return ['success' => false, 'message' => "Có $count khóa học đang sử dụng danh mục này. Không thể xóa."];
        }
        $stmt2 = $this->db->prepare("DELETE FROM danh_muc WHERE id = ?");
        $stmt2->bind_param("i", $id);
        return ['success' => $stmt2->execute(), 'message' => ''];
    }
}
