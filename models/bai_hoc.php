<?php
require_once __DIR__ . '/../config/database.php';

class BaiHoc {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function layTatCa() {
        $stmt = $this->db->prepare("
            SELECT bh.*, kh.ten_khoa_hoc
            FROM bai_hoc bh
            LEFT JOIN khoa_hoc kh ON bh.id_khoa_hoc = kh.id
            ORDER BY kh.ten_khoa_hoc, bh.thu_tu
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTatCaAdmin() {
        $stmt = $this->db->prepare("
            SELECT bh.*, kh.ten_khoa_hoc, kh.trang_thai AS trang_thai_khoa_hoc,
                   (SELECT COUNT(*) FROM quiz WHERE id_bai_hoc = bh.id) AS so_quiz
            FROM bai_hoc bh
            LEFT JOIN khoa_hoc kh ON bh.id_khoa_hoc = kh.id
            ORDER BY kh.ten_khoa_hoc, bh.thu_tu
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layKhoaHocDuyet() {
        $stmt = $this->db->prepare("
            SELECT id, ten_khoa_hoc FROM khoa_hoc
            WHERE trang_thai = 'da_duyet'
            ORDER BY ten_khoa_hoc
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layThuTuLonNhat($khoa_hoc_id) {
        $stmt = $this->db->prepare("SELECT MAX(thu_tu) AS max_order FROM bai_hoc WHERE id_khoa_hoc = ?");
        $stmt->bind_param("i", $khoa_hoc_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return (int)($r['max_order'] ?? 0);
    }

    public function layTheoKhoaHoc($khoa_hoc_id) {
        $sql = "SELECT * FROM bai_hoc WHERE id_khoa_hoc = ? ORDER BY thu_tu ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $khoa_hoc_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTheoId($id) {
        $stmt = $this->db->prepare("SELECT bh.*, kh.ten_khoa_hoc FROM bai_hoc bh 
                                    LEFT JOIN khoa_hoc kh ON bh.id_khoa_hoc = kh.id 
                                    WHERE bh.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function them($id_khoa_hoc, $tieu_de, $noi_dung, $video_url, $thu_tu, $thoi_luong_phut) {
        $sql = "INSERT INTO bai_hoc (id_khoa_hoc, tieu_de, noi_dung, video_url, thu_tu, thoi_luong_phut) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isssii", $id_khoa_hoc, $tieu_de, $noi_dung, $video_url, $thu_tu, $thoi_luong_phut);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm bài học thành công!', 'id' => $stmt->insert_id];
        }
        return ['success' => false, 'message' => 'Thêm bài học thất bại!'];
    }

    public function sua($id, $tieu_de, $noi_dung, $video_url, $thu_tu, $thoi_luong_phut) {
        $sql = "UPDATE bai_hoc SET tieu_de = ?, noi_dung = ?, video_url = ?, thu_tu = ?, thoi_luong_phut = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssiii", $tieu_de, $noi_dung, $video_url, $thu_tu, $thoi_luong_phut, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật bài học thành công!'];
        }
        return ['success' => false, 'message' => 'Cập nhật bài học thất bại!'];
    }

    public function xoa($id) {
        $sql = "DELETE FROM bai_hoc WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa bài học thành công!'];
        }
        return ['success' => false, 'message' => 'Xóa bài học thất bại!'];
    }

    public function demBaiHoc($khoa_hoc_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as dem FROM bai_hoc WHERE id_khoa_hoc = ?");
        $stmt->bind_param("i", $khoa_hoc_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['dem'];
    }

    public function tinhTongThoiLuong($khoa_hoc_id) {
        $stmt = $this->db->prepare("SELECT SUM(thoi_luong_phut) as tong FROM bai_hoc WHERE id_khoa_hoc = ?");
        $stmt->bind_param("i", $khoa_hoc_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['tong'] ?? 0;
    }
}
?>
