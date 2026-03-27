<?php
require_once __DIR__ . '/../config/database.php';

class DangKy {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function layTatCa($loc_tt = '') {
        $sql = "SELECT dk.*,
                       kh.ten_khoa_hoc, kh.gia_tien AS gia_khoa_hoc,
                       hv.ho_ten AS ten_hoc_vien, hv.email AS email_hoc_vien,
                       gv.ho_ten AS ten_giao_vien,
                       dm.ten_danh_muc
                FROM dang_ky_khoa_hoc dk
                JOIN khoa_hoc kh ON dk.id_khoa_hoc = kh.id
                JOIN nguoi_dung hv ON dk.id_hoc_vien = hv.id
                LEFT JOIN nguoi_dung gv ON kh.id_nguoi_tao = gv.id
                LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
                ORDER BY dk.ngay_dang_ky DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $ds = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if ($loc_tt !== '') {
            $ds = array_filter($ds, fn($r) => $r['trang_thai'] === $loc_tt);
        }
        return array_values($ds);
    }

    public function layTheoId($id) {
        $stmt = $this->db->prepare("
            SELECT dk.*,
                   kh.ten_khoa_hoc, kh.gia_tien, kh.mo_ta, kh.hinh_anh,
                   hv.ho_ten AS ten_hoc_vien, hv.email AS email_hoc_vien,
                   hv.vai_tro AS vai_tro_hv,
                   gv.ho_ten AS ten_giao_vien,
                   dm.ten_danh_muc
            FROM dang_ky_khoa_hoc dk
            JOIN khoa_hoc kh ON dk.id_khoa_hoc = kh.id
            JOIN nguoi_dung hv ON dk.id_hoc_vien = hv.id
            LEFT JOIN nguoi_dung gv ON kh.id_nguoi_tao = gv.id
            LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
            WHERE dk.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function capNhatTrangThai($id, $trang_thai) {
        $allowed = ['cho_xu_ly', 'da_xac_nhan', 'da_huy'];
        if (!in_array($trang_thai, $allowed)) return false;
        $stmt = $this->db->prepare("UPDATE dang_ky_khoa_hoc SET trang_thai = ? WHERE id = ?");
        $stmt->bind_param("si", $trang_thai, $id);
        return $stmt->execute();
    }

    public function xoa($id) {
        $stmt = $this->db->prepare("DELETE FROM dang_ky_khoa_hoc WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function thongKe() {
        $all = $this->layTatCa();
        return [
            'tong'       => count($all),
            'cho_xu_ly' => count(array_filter($all, fn($r) => $r['trang_thai'] === 'cho_xu_ly')),
            'da_xac_nhan' => count(array_filter($all, fn($r) => $r['trang_thai'] === 'da_xac_nhan')),
            'da_huy'    => count(array_filter($all, fn($r) => $r['trang_thai'] === 'da_huy')),
        ];
    }
}
?>
