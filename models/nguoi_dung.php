<?php
require_once __DIR__ . '/../config/database.php';

class NguoiDung {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function layTatCa() {
        $stmt = $this->db->prepare("
            SELECT id, ho_ten, email, vai_tro, trang_thai, ngay_tao,
                   (SELECT COUNT(*) FROM dang_ky_khoa_hoc WHERE id_hoc_vien = nguoi_dung.id) AS so_khoa_hoc_dang_ky
            FROM nguoi_dung
            ORDER BY ngay_tao DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTheoId($id) {
        $stmt = $this->db->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function khoa($id) {
        $stmt = $this->db->prepare("UPDATE nguoi_dung SET trang_thai = 'khong_hoat_dong' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function moKhoa($id) {
        $stmt = $this->db->prepare("UPDATE nguoi_dung SET trang_thai = 'hoat_dong' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
