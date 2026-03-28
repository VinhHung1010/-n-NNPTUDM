<?php
require_once __DIR__ . '/../config/database.php';

class ThongBao {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function guiThongBao($id_nguoi_nhan, $tieu_de, $noi_dung, $loai = 'he_thong', $duong_dan = null) {
        $stmt = $this->db->prepare("
            INSERT INTO thong_bao (id_nguoi_nhan, tieu_de, noi_dung, loai, duong_dan) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $id_nguoi_nhan, $tieu_de, $noi_dung, $loai, $duong_dan);
        return $stmt->execute();
    }

    public function layTatCa($id_nguoi_nhan, $limit = 20, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM thong_bao 
            WHERE id_nguoi_nhan = ?
            ORDER BY ngay_tao DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $id_nguoi_nhan, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layChuaDoc($id_nguoi_nhan) {
        $stmt = $this->db->prepare("
            SELECT * FROM thong_bao 
            WHERE id_nguoi_nhan = ? AND da_doc = FALSE
            ORDER BY ngay_tao DESC
        ");
        $stmt->bind_param("i", $id_nguoi_nhan);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function demChuaDoc($id_nguoi_nhan) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as so_luong FROM thong_bao 
            WHERE id_nguoi_nhan = ? AND da_doc = FALSE
        ");
        $stmt->bind_param("i", $id_nguoi_nhan);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['so_luong'];
    }

    public function danhDauDaDoc($id) {
        $stmt = $this->db->prepare("UPDATE thong_bao SET da_doc = TRUE WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function danhDauTatCaDaDoc($id_nguoi_nhan) {
        $stmt = $this->db->prepare("UPDATE thong_bao SET da_doc = TRUE WHERE id_nguoi_nhan = ? AND da_doc = FALSE");
        $stmt->bind_param("i", $id_nguoi_nhan);
        return $stmt->execute();
    }

    public function xoa($id) {
        $stmt = $this->db->prepare("DELETE FROM thong_bao WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function xoaTatCa($id_nguoi_nhan) {
        $stmt = $this->db->prepare("DELETE FROM thong_bao WHERE id_nguoi_nhan = ?");
        $stmt->bind_param("i", $id_nguoi_nhan);
        return $stmt->execute();
    }

    public function layTheoId($id) {
        $stmt = $this->db->prepare("SELECT * FROM thong_bao WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getIconByLoai($loai) {
        $icons = [
            'dang_ky' => 'fa-user-plus text-primary',
            'duyet_khoa' => 'fa-check-circle text-success',
            'tu_choi_khoa' => 'fa-times-circle text-danger',
            'hoan_thanh_khoa' => 'fa-trophy text-warning',
            'chung_chi' => 'fa-award text-warning',
            'quiz' => 'fa-file-alt text-info',
            'he_thong' => 'fa-bell text-secondary'
        ];
        return $icons[$loai] ?? 'fa-bell text-secondary';
    }

    public function getMauByLoai($loai) {
        $colors = [
            'dang_ky' => '#4F46E5',
            'duyet_khoa' => '#10B981',
            'tu_choi_khoa' => '#EF4444',
            'hoan_thanh_khoa' => '#F59E0B',
            'chung_chi' => '#F59E0B',
            'quiz' => '#3B82F6',
            'he_thong' => '#64748B'
        ];
        return $colors[$loai] ?? '#64748B';
    }

    public function getTimeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Vừa xong';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' phút trước';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' giờ trước';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' ngày trước';
        } else {
            return date('d/m/Y', $timestamp);
        }
    }

    public function getLoaiText($loai) {
        $texts = [
            'dang_ky' => 'Đăng ký',
            'duyet_khoa' => 'Duyệt khóa học',
            'tu_choi_khoa' => 'Từ chối khóa học',
            'hoan_thanh_khoa' => 'Hoàn thành khóa học',
            'chung_chi' => 'Chứng chỉ',
            'quiz' => 'Bài kiểm tra',
            'he_thong' => 'Hệ thống'
        ];
        return $texts[$loai] ?? 'Thông báo';
    }
}
?>
