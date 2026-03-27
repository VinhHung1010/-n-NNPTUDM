<?php
require_once __DIR__ . '/../config/database.php';

class Quiz {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function layTatCa() {
        $stmt = $this->db->prepare("
            SELECT q.*, bh.tieu_de AS ten_bai_hoc, kh.ten_khoa_hoc,
                   (SELECT COUNT(*) FROM cau_hoi WHERE id_quiz = q.id) AS so_cau_hoi
            FROM quiz q
            LEFT JOIN bai_hoc bh ON q.id_bai_hoc = bh.id
            LEFT JOIN khoa_hoc kh ON bh.id_khoa_hoc = kh.id
            ORDER BY q.ngay_tao DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTheoBaiHoc($bai_hoc_id) {
        $sql = "SELECT * FROM quiz WHERE id_bai_hoc = ? ORDER BY ngay_tao DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $bai_hoc_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTheoId($id) {
        $stmt = $this->db->prepare("SELECT q.*, bh.tieu_de as ten_bai_hoc, kh.ten_khoa_hoc 
                                    FROM quiz q 
                                    LEFT JOIN bai_hoc bh ON q.id_bai_hoc = bh.id
                                    LEFT JOIN khoa_hoc kh ON bh.id_khoa_hoc = kh.id
                                    WHERE q.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function them($id_bai_hoc, $tieu_de, $mo_ta, $thoi_gian_phut, $diem_toi_da) {
        $sql = "INSERT INTO quiz (id_bai_hoc, tieu_de, mo_ta, thoi_gian_phut, diem_toi_da) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("issii", $id_bai_hoc, $tieu_de, $mo_ta, $thoi_gian_phut, $diem_toi_da);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm quiz thành công!', 'id' => $stmt->insert_id];
        }
        return ['success' => false, 'message' => 'Thêm quiz thất bại!'];
    }

    public function sua($id, $tieu_de, $mo_ta, $thoi_gian_phut, $diem_toi_da) {
        $sql = "UPDATE quiz SET tieu_de = ?, mo_ta = ?, thoi_gian_phut = ?, diem_toi_da = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssiii", $tieu_de, $mo_ta, $thoi_gian_phut, $diem_toi_da, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật quiz thành công!'];
        }
        return ['success' => false, 'message' => 'Cập nhật quiz thất bại!'];
    }

    public function xoa($id) {
        $sql = "DELETE FROM quiz WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa quiz thành công!'];
        }
        return ['success' => false, 'message' => 'Xóa quiz thất bại!'];
    }

    public function layCauHoi($quiz_id) {
        $sql = "SELECT * FROM cau_hoi WHERE id_quiz = ? ORDER BY id";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layCauHoiChiTiet($quiz_id) {
        $cau_hoi = $this->layCauHoi($quiz_id);
        foreach ($cau_hoi as &$ch) {
            $ch['dap_an'] = $this->layDapAn($ch['id']);
        }
        return $cau_hoi;
    }

    public function layDapAn($cau_hoi_id) {
        $sql = "SELECT * FROM dap_an WHERE id_cau_hoi = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $cau_hoi_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function themCauHoi($id_quiz, $noi_dung, $dap_an) {
        // Thêm câu hỏi
        $stmt = $this->db->prepare("INSERT INTO cau_hoi (id_quiz, noi_dung) VALUES (?, ?)");
        $stmt->bind_param("is", $id_quiz, $noi_dung);
        $stmt->execute();
        $cau_hoi_id = $stmt->insert_id;

        // Thêm các đáp án
        foreach ($dap_an as $index => $da) {
            $la_dung = ($index === 0) ? true : false; // Mặc định đáp án đầu tiên là đúng
            $stmt = $this->db->prepare("INSERT INTO dap_an (id_cau_hoi, noi_dung, la_dap_an_dung) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $cau_hoi_id, $da, $la_dung);
            $stmt->execute();
        }

        return ['success' => true, 'message' => 'Thêm câu hỏi thành công!'];
    }

    public function suaCauHoi($id, $noi_dung) {
        $stmt = $this->db->prepare("UPDATE cau_hoi SET noi_dung = ? WHERE id = ?");
        $stmt->bind_param("si", $noi_dung, $id);
        return $stmt->execute();
    }

    public function xoaCauHoi($id) {
        $stmt = $this->db->prepare("DELETE FROM cau_hoi WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function themDapAn($id_cau_hoi, $noi_dung, $la_dung) {
        $stmt = $this->db->prepare("INSERT INTO dap_an (id_cau_hoi, noi_dung, la_dap_an_dung) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $id_cau_hoi, $noi_dung, $la_dung);
        return $stmt->execute();
    }

    public function xoaDapAn($id) {
        $stmt = $this->db->prepare("DELETE FROM dap_an WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function datDapAnDung($id_cau_hoi, $dap_an_dung_id) {
        $this->db->query("UPDATE dap_an SET la_dap_an_dung = 0 WHERE id_cau_hoi = $id_cau_hoi");
        $stmt = $this->db->prepare("UPDATE dap_an SET la_dap_an_dung = 1 WHERE id = ?");
        $stmt->bind_param("i", $dap_an_dung_id);
        return $stmt->execute();
    }

    public function nopBai($id_hoc_vien, $id_quiz, $dap_an_chon, $thoi_gian_lam_bai) {
        $quiz = $this->layTheoId($id_quiz);
        $cau_hoi = $this->layCauHoi($id_quiz);
        
        $diem = 0;
        $so_cau_dung = 0;
        $tong_cau = count($cau_hoi);

        foreach ($cau_hoi as $ch) {
            $dap_an_dung = $this->db->query("SELECT id FROM dap_an WHERE id_cau_hoi = {$ch['id']} AND la_dap_an_dung = 1")->fetch_assoc();
            
            if (isset($dap_an_chon[$ch['id']]) && $dap_an_chon[$ch['id']] == $dap_an_dung['id']) {
                $so_cau_dung++;
            }
        }

        $diem = round(($so_cau_dung / $tong_cau) * $quiz['diem_toi_da']);

        // Lưu kết quả
        $stmt = $this->db->prepare("INSERT INTO ket_qua_quiz (id_hoc_vien, id_quiz, diem_so, so_cau_dung, thoi_gian_lam_bai) 
                                    VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiii", $id_hoc_vien, $id_quiz, $diem, $so_cau_dung, $thoi_gian_lam_bai);
        $stmt->execute();

        return [
            'success' => true,
            'diem_so' => $diem,
            'so_cau_dung' => $so_cau_dung,
            'tong_cau' => $tong_cau
        ];
    }

    public function layKetQua($hoc_vien_id, $quiz_id = null) {
        if ($quiz_id) {
            $sql = "SELECT kq.*, q.tieu_de, bh.tieu_de as ten_bai_hoc 
                    FROM ket_qua_quiz kq 
                    LEFT JOIN quiz q ON kq.id_quiz = q.id 
                    LEFT JOIN bai_hoc bh ON q.id_bai_hoc = bh.id
                    WHERE kq.id_hoc_vien = ? AND kq.id_quiz = ?
                    ORDER BY kq.ngay_lam DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $hoc_vien_id, $quiz_id);
        } else {
            $sql = "SELECT kq.*, q.tieu_de, bh.tieu_de as ten_bai_hoc 
                    FROM ket_qua_quiz kq 
                    LEFT JOIN quiz q ON kq.id_quiz = q.id 
                    LEFT JOIN bai_hoc bh ON q.id_bai_hoc = bh.id
                    WHERE kq.id_hoc_vien = ?
                    ORDER BY kq.ngay_lam DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $hoc_vien_id);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
