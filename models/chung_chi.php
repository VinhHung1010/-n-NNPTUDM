<?php
require_once __DIR__ . '/../config/database.php';

class ChungChi {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Tạo mã chứng chỉ ngẫu nhiên 12 ký tự
     */
    private function taoMa() {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $ma = 'CERT-';
        for ($i = 0; $i < 8; $i++) {
            $ma .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $ma;
    }

    /**
     * Kiểm tra đã có chứng chỉ chưa
     */
    public function lay($hoc_vien_id, $khoa_hoc_id) {
        $stmt = $this->db->prepare(
            "SELECT c.*, kh.ten_khoa_hoc, kh.mo_ta AS mo_ta_khoa,
                    nd.ho_ten AS ten_hoc_vien, nd.email AS email_hv,
                    gv.ho_ten AS ten_giao_vien
             FROM certificates c
             JOIN khoa_hoc kh ON c.id_khoa_hoc = kh.id
             JOIN nguoi_dung nd ON c.id_hoc_vien = nd.id
             LEFT JOIN nguoi_dung gv ON kh.id_nguoi_tao = gv.id
             WHERE c.id_hoc_vien = ? AND c.id_khoa_hoc = ?"
        );
        $stmt->bind_param("ii", $hoc_vien_id, $khoa_hoc_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Tạo / lấy chứng chỉ
     */
    public function tao($hoc_vien_id, $khoa_hoc_id) {
        // Đã có → trả về
        $ton_tai = $this->lay($hoc_vien_id, $khoa_hoc_id);
        if ($ton_tai) return $ton_tai;

        $ma = $this->taoMa();
        $stmt = $this->db->prepare(
            "INSERT INTO certificates (id_hoc_vien, id_khoa_hoc, ma_chung_chi)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iis", $hoc_vien_id, $khoa_hoc_id, $ma);
        $stmt->execute();

        return $this->lay($hoc_vien_id, $khoa_hoc_id);
    }

    /**
     * Lấy tất cả chứng chỉ của 1 học viên
     */
    public function layTatCa($hoc_vien_id) {
        $stmt = $this->db->prepare(
            "SELECT c.*, kh.ten_khoa_hoc, kh.hinh_anh, dm.ten_danh_muc,
                    gv.ho_ten AS ten_giao_vien
             FROM certificates c
             JOIN khoa_hoc kh ON c.id_khoa_hoc = kh.id
             LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
             LEFT JOIN nguoi_dung gv ON kh.id_nguoi_tao = gv.id
             WHERE c.id_hoc_vien = ?
             ORDER BY c.ngay_cap DESC"
        );
        $stmt->bind_param("i", $hoc_vien_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Kiểm tra chứng chỉ theo mã
     */
    public function layTheoMa($ma) {
        $stmt = $this->db->prepare(
            "SELECT c.*, kh.ten_khoa_hoc, kh.mo_ta AS mo_ta_khoa,
                    nd.ho_ten AS ten_hoc_vien, nd.email AS email_hv,
                    gv.ho_ten AS ten_giao_vien
             FROM certificates c
             JOIN khoa_hoc kh ON c.id_khoa_hoc = kh.id
             JOIN nguoi_dung nd ON c.id_hoc_vien = nd.id
             LEFT JOIN nguoi_dung gv ON kh.id_nguoi_tao = gv.id
             WHERE c.ma_chung_chi = ?"
        );
        $stmt->bind_param("s", $ma);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
