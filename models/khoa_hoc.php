<?php
require_once __DIR__ . '/../config/database.php';

class KhoaHoc {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function layTatCa($trang_thai = 'da_duyet') {
        $sql = "SELECT kh.*, nd.ho_ten as ten_giao_vien, dm.ten_danh_muc,
                (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = kh.id) as so_bai_hoc
                FROM khoa_hoc kh
                LEFT JOIN nguoi_dung nd ON kh.id_nguoi_tao = nd.id
                LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
                WHERE kh.trang_thai = ? OR ? IS NULL
                ORDER BY kh.ngay_tao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $trang_thai, $trang_thai);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTheoId($id) {
        $sql = "SELECT kh.*, nd.ho_ten as ten_giao_vien, dm.ten_danh_muc
                FROM khoa_hoc kh
                LEFT JOIN nguoi_dung nd ON kh.id_nguoi_tao = nd.id
                LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
                WHERE kh.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function layTheoDanhMuc($danh_muc_id) {
        $sql = "SELECT kh.*, nd.ho_ten as ten_giao_vien, dm.ten_danh_muc,
                (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = kh.id) as so_bai_hoc
                FROM khoa_hoc kh
                LEFT JOIN nguoi_dung nd ON kh.id_nguoi_tao = nd.id
                LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
                WHERE kh.id_danh_muc = ? AND kh.trang_thai = 'da_duyet'
                ORDER BY kh.ngay_tao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $danh_muc_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function them($ten_khoa_hoc, $mo_ta, $hinh_anh, $gia_tien, $id_danh_muc, $id_nguoi_tao) {
        $sql = "INSERT INTO khoa_hoc (ten_khoa_hoc, mo_ta, hinh_anh, gia_tien, id_danh_muc, id_nguoi_tao, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, 'ban_nhap')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssdiis", $ten_khoa_hoc, $mo_ta, $hinh_anh, $gia_tien, $id_danh_muc, $id_nguoi_tao);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm khóa học thành công!', 'id' => $stmt->insert_id];
        }
        return ['success' => false, 'message' => 'Thêm khóa học thất bại!'];
    }

    public function sua($id, $ten_khoa_hoc, $mo_ta, $hinh_anh, $gia_tien, $id_danh_muc, $trang_thai) {
        $sql = "UPDATE khoa_hoc SET ten_khoa_hoc = ?, mo_ta = ?, hinh_anh = ?, gia_tien = ?, id_danh_muc = ?, trang_thai = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssddisi", $ten_khoa_hoc, $mo_ta, $hinh_anh, $gia_tien, $id_danh_muc, $trang_thai, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật khóa học thành công!'];
        }
        return ['success' => false, 'message' => 'Cập nhật khóa học thất bại!'];
    }

    public function xoa($id) {
        $sql = "DELETE FROM khoa_hoc WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa khóa học thành công!'];
        }
        return ['success' => false, 'message' => 'Xóa khóa học thất bại!'];
    }

    public function timKiem($tu_khoa) {
        $tu_khoa = "%{$tu_khoa}%";
        $sql = "SELECT kh.*, nd.ho_ten as ten_giao_vien, dm.ten_danh_muc,
                (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = kh.id) as so_bai_hoc
                FROM khoa_hoc kh
                LEFT JOIN nguoi_dung nd ON kh.id_nguoi_tao = nd.id
                LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
                WHERE kh.ten_khoa_hoc LIKE ? AND kh.trang_thai = 'da_duyet'
                ORDER BY kh.ngay_tao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $tu_khoa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layDanhMuc() {
        $sql = "SELECT * FROM danh_muc ORDER BY ten_danh_muc";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function layTatCaAdmin() {
        $sql = "SELECT kh.*, nd.ho_ten AS ten_giao_vien, dm.ten_danh_muc,
                       (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = kh.id) AS so_bai_hoc,
                       (SELECT COUNT(*) FROM dang_ky_khoa_hoc WHERE id_khoa_hoc = kh.id) AS so_hoc_vien
                FROM khoa_hoc kh
                LEFT JOIN nguoi_dung nd ON kh.id_nguoi_tao = nd.id
                LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
                ORDER BY kh.ngay_tao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function capNhatTrangThai($id, $trang_thai) {
        $allowed = ['ban_nhap', 'da_duyet', 'bi_an'];
        if (!in_array($trang_thai, $allowed)) return false;
        $stmt = $this->db->prepare("UPDATE khoa_hoc SET trang_thai = ? WHERE id = ?");
        $stmt->bind_param("si", $trang_thai, $id);
        return $stmt->execute();
    }

    public function layBaiHoc($khoa_hoc_id) {
        $stmt = $this->db->prepare("
            SELECT bh.*,
                   (SELECT COUNT(*) FROM quiz WHERE id_bai_hoc = bh.id) AS so_quiz
            FROM bai_hoc bh
            WHERE bh.id_khoa_hoc = ?
            ORDER BY bh.thu_tu
        ");
        $stmt->bind_param("i", $khoa_hoc_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layHocVien($khoa_hoc_id) {
        $stmt = $this->db->prepare("
            SELECT nd.*, dk.ngay_dang_ky, dk.trang_thai AS trang_thai_dk
            FROM dang_ky_khoa_hoc dk
            JOIN nguoi_dung nd ON dk.id_hoc_vien = nd.id
            WHERE dk.id_khoa_hoc = ?
            ORDER BY dk.ngay_dang_ky DESC
        ");
        $stmt->bind_param("i", $khoa_hoc_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layKhoaHocCuaHocVien($hoc_vien_id) {
        $stmt = $this->db->prepare("
            SELECT kh.*, dk.ngay_dang_ky AS ngay_dang_ky_khoa, dk.trang_thai AS trang_thai_dk,
                   nd.ho_ten AS ten_giao_vien, dm.ten_danh_muc,
                   (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = kh.id) AS so_bai_hoc
            FROM dang_ky_khoa_hoc dk
            JOIN khoa_hoc kh ON dk.id_khoa_hoc = kh.id
            LEFT JOIN nguoi_dung nd ON kh.id_nguoi_tao = nd.id
            LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
            WHERE dk.id_hoc_vien = ?
            ORDER BY dk.ngay_dang_ky DESC
        ");
        $stmt->bind_param("i", $hoc_vien_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function daDangKy($hoc_vien_id, $khoa_hoc_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS c FROM dang_ky_khoa_hoc
            WHERE id_hoc_vien = ? AND id_khoa_hoc = ?
        ");
        $stmt->bind_param("ii", $hoc_vien_id, $khoa_hoc_id);
        $stmt->execute();
        return (int)($stmt->get_result()->fetch_assoc()['c']) > 0;
    }

    public function dangKy($hoc_vien_id, $khoa_hoc_id) {
        $stmt = $this->db->prepare("
            INSERT INTO dang_ky_khoa_hoc (id_hoc_vien, id_khoa_hoc, trang_thai)
            VALUES (?, ?, 'cho_xu_ly')
        ");
        $stmt->bind_param("ii", $hoc_vien_id, $khoa_hoc_id);
        return $stmt->execute();
    }
}
?>
