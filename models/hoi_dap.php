<?php
require_once __DIR__ . '/../config/database.php';

class HoiDap {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Câu hỏi ──

    public function taoCauHoi($id_nguoi_hoi, $id_bai_hoc, $tieu_de, $noi_dung) {
        $stmt = $this->db->prepare("
            INSERT INTO hoi_dap (id_nguoi_hoi, id_bai_hoc, tieu_de, noi_dung) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $id_nguoi_hoi, $id_bai_hoc, $tieu_de, $noi_dung);
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    public function layCauHoiTheoBaiHoc($id_bai_hoc, $limit = 20, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT h.*, nd.ho_ten, nd.vai_tro,
                   (SELECT COUNT(*) FROM tra_loi WHERE id_cau_hoi = h.id) AS so_tra_loi
            FROM hoi_dap h
            JOIN nguoi_dung nd ON nd.id = h.id_nguoi_hoi
            WHERE h.id_bai_hoc = ?
            ORDER BY h.ngay_tao DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $id_bai_hoc, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layCauHoiCuaNguoiDung($id_nguoi_dung, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT h.*, bh.tieu_de AS ten_bai_hoc, kh.ten_khoa_hoc,
                   (SELECT COUNT(*) FROM tra_loi WHERE id_cau_hoi = h.id) AS so_tra_loi
            FROM hoi_dap h
            JOIN bai_hoc bh ON bh.id = h.id_bai_hoc
            JOIN khoa_hoc kh ON kh.id = bh.id_khoa_hoc
            WHERE h.id_nguoi_hoi = ?
            ORDER BY h.ngay_tao DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTatCa($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT h.*, nd.ho_ten, nd.vai_tro,
                   bh.tieu_de AS ten_bai_hoc, kh.ten_khoa_hoc, kh.id AS id_khoa_hoc,
                   (SELECT COUNT(*) FROM tra_loi WHERE id_cau_hoi = h.id) AS so_tra_loi
            FROM hoi_dap h
            JOIN nguoi_dung nd ON nd.id = h.id_nguoi_hoi
            JOIN bai_hoc bh ON bh.id = h.id_bai_hoc
            JOIN khoa_hoc kh ON kh.id = bh.id_khoa_hoc
            ORDER BY h.ngay_tao DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTheoId($id) {
        $stmt = $this->db->prepare("
            SELECT h.*, nd.ho_ten, nd.vai_tro,
                   bh.tieu_de AS ten_bai_hoc, bh.id_khoa_hoc,
                   kh.ten_khoa_hoc, kh.id AS id_khoa_hoc
            FROM hoi_dap h
            JOIN nguoi_dung nd ON nd.id = h.id_nguoi_hoi
            JOIN bai_hoc bh ON bh.id = h.id_bai_hoc
            JOIN khoa_hoc kh ON kh.id = bh.id_khoa_hoc
            WHERE h.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function tangLuotXem($id) {
        $stmt = $this->db->prepare("UPDATE hoi_dap SET luot_xem = luot_xem + 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function demCauHoi() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM hoi_dap");
        return $result->fetch_assoc()['total'];
    }

    public function xoaCauHoi($id) {
        $stmt = $this->db->prepare("DELETE FROM hoi_dap WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ── Câu trả lời ──

    public function themTraLoi($id_cau_hoi, $id_nguoi_tra_loi, $noi_dung) {
        $stmt = $this->db->prepare("
            INSERT INTO tra_loi (id_cau_hoi, id_nguoi_tra_loi, noi_dung) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $id_cau_hoi, $id_nguoi_tra_loi, $noi_dung);
        if ($stmt->execute()) {
            // Cập nhật trạng thái câu hỏi
            $update = $this->db->prepare("UPDATE hoi_dap SET trang_thai = 'da_tra_loi' WHERE id = ?");
            $update->bind_param("i", $id_cau_hoi);
            $update->execute();
            return $stmt->insert_id;
        }
        return false;
    }

    public function layTraLoi($id_cau_hoi) {
        $stmt = $this->db->prepare("
            SELECT tl.*, nd.ho_ten, nd.vai_tro
            FROM tra_loi tl
            JOIN nguoi_dung nd ON nd.id = tl.id_nguoi_tra_loi
            WHERE tl.id_cau_hoi = ?
            ORDER BY tl.la_cau_tra_tot_nhat DESC, tl.so_thich DESC, tl.ngay_tao ASC
        ");
        $stmt->bind_param("i", $id_cau_hoi);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function danhDauTraLoiTotNhat($id_cau_hoi, $id_tra_loi) {
        // Bỏ đánh dấu tất cả
        $reset = $this->db->prepare("UPDATE tra_loi SET la_cau_tra_tot_nhat = FALSE WHERE id_cau_hoi = ?");
        $reset->bind_param("i", $id_cau_hoi);
        $reset->execute();
        // Đánh dấu cái mới
        $set = $this->db->prepare("UPDATE tra_loi SET la_cau_tra_tot_nhat = TRUE WHERE id = ?");
        $set->bind_param("i", $id_tra_loi);
        return $set->execute();
    }

    public function xoaTraLoi($id) {
        $stmt = $this->db->prepare("DELETE FROM tra_loi WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ── Thích câu hỏi ──

    public function thichCauHoi($id_nguoi_dung, $id_cau_hoi) {
        $stmt = $this->db->prepare("
            INSERT INTO thich_cau_hoi (id_nguoi_dung, id_cau_hoi) VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_cau_hoi);
        if ($stmt->execute()) {
            $this->db->query("UPDATE hoi_dap SET luot_thich = luot_thich + 1 WHERE id = $id_cau_hoi");
            return true;
        }
        return false;
    }

    public function boThichCauHoi($id_nguoi_dung, $id_cau_hoi) {
        $stmt = $this->db->prepare("DELETE FROM thich_cau_hoi WHERE id_nguoi_dung = ? AND id_cau_hoi = ?");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_cau_hoi);
        if ($stmt->execute()) {
            $this->db->query("UPDATE hoi_dap SET luot_thich = GREATEST(luot_thich - 1, 0) WHERE id = $id_cau_hoi");
            return true;
        }
        return false;
    }

    public function daThichCauHoi($id_nguoi_dung, $id_cau_hoi) {
        $stmt = $this->db->prepare("
            SELECT id FROM thich_cau_hoi WHERE id_nguoi_dung = ? AND id_cau_hoi = ?
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_cau_hoi);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // ── Thích câu trả lời ──

    public function thichTraLoi($id_nguoi_dung, $id_tra_loi) {
        $stmt = $this->db->prepare("
            INSERT INTO thich_tra_loi (id_nguoi_dung, id_tra_loi) VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_tra_loi);
        if ($stmt->execute()) {
            $this->db->query("UPDATE tra_loi SET so_thich = so_thich + 1 WHERE id = $id_tra_loi");
            return true;
        }
        return false;
    }

    public function boThichTraLoi($id_nguoi_dung, $id_tra_loi) {
        $stmt = $this->db->prepare("DELETE FROM thich_tra_loi WHERE id_nguoi_dung = ? AND id_tra_loi = ?");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_tra_loi);
        if ($stmt->execute()) {
            $this->db->query("UPDATE tra_loi SET so_thich = GREATEST(so_thich - 1, 0) WHERE id = $id_tra_loi");
            return true;
        }
        return false;
    }

    public function daThichTraLoi($id_nguoi_dung, $id_tra_loi) {
        $stmt = $this->db->prepare("
            SELECT id FROM thich_tra_loi WHERE id_nguoi_dung = ? AND id_tra_loi = ?
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_tra_loi);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function layIdNguoiHoi($id_cau_hoi) {
        $stmt = $this->db->prepare("SELECT id_nguoi_hoi FROM hoi_dap WHERE id = ?");
        $stmt->bind_param("i", $id_cau_hoi);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['id_nguoi_hoi'] : null;
    }

    public function layIdBaiHoc($id_cau_hoi) {
        $stmt = $this->db->prepare("SELECT id_bai_hoc FROM hoi_dap WHERE id = ?");
        $stmt->bind_param("i", $id_cau_hoi);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['id_bai_hoc'] : null;
    }
}
?>
