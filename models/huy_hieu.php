<?php
require_once __DIR__ . '/../config/database.php';

class HuyHieu {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Lấy danh sách tất cả badges ──
    public function layTatCa() {
        $stmt = $this->db->prepare("SELECT * FROM huy_hieu ORDER BY loai, diem_threshold");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Lấy badge theo ID ──
    public function layTheoId($id) {
        $stmt = $this->db->prepare("SELECT * FROM huy_hieu WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ── Lấy badge theo mã ──
    public function layTheoMa($ma) {
        $stmt = $this->db->prepare("SELECT * FROM huy_hieu WHERE ma = ?");
        $stmt->bind_param("s", $ma);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ── Lấy badges của người dùng ──
    public function layCuaNguoiDung($id_nguoi_dung) {
        $stmt = $this->db->prepare("
            SELECT hh.*, ndh.ngay_dat
            FROM nguoi_dung_huy_hieu ndh
            JOIN huy_hieu hh ON hh.id = ndh.id_huy_hieu
            WHERE ndh.id_nguoi_dung = ?
            ORDER BY ndh.ngay_dat DESC
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Đếm badges của người dùng ──
    public function demCuaNguoiDung($id_nguoi_dung) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM nguoi_dung_huy_hieu WHERE id_nguoi_dung = ?
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    // ── Kiểm tra user đã có badge chưa ──
    public function daCo($id_nguoi_dung, $id_huy_hieu) {
        $stmt = $this->db->prepare("
            SELECT id FROM nguoi_dung_huy_hieu 
            WHERE id_nguoi_dung = ? AND id_huy_hieu = ?
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_huy_hieu);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // ── Trao badge cho user ──
    public function trao($id_nguoi_dung, $id_huy_hieu) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO nguoi_dung_huy_hieu (id_nguoi_dung, id_huy_hieu) VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_huy_hieu);
        return $stmt->execute();
    }

    // ── Lấy badges chưa đạt được của user ──
    public function layChuaDat($id_nguoi_dung) {
        $stmt = $this->db->prepare("
            SELECT hh.* FROM huy_hieu hh
            WHERE hh.id NOT IN (
                SELECT id_huy_hieu FROM nguoi_dung_huy_hieu WHERE id_nguoi_dung = ?
            )
            ORDER BY hh.loai, hh.diem_threshold
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Cập nhật streak cho user ──
    public function capNhatStreak($id_nguoi_dung) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT DATE(ngay_cap_nhat) as ngay 
            FROM tien_do_hoc 
            WHERE id_hoc_vien = ? AND da_hoan_thanh = TRUE
            ORDER BY ngay DESC
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        $days = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($days)) return 0;

        $streak = 1;
        for ($i = 1; $i < count($days); $i++) {
            $prev = strtotime($days[$i - 1]['ngay']);
            $curr = strtotime($days[$i]['ngay']);
            $diff = ($prev - $curr) / 86400;
            if ($diff == 1) {
                $streak++;
            } else {
                break;
            }
        }
        return $streak;
    }

    // ── Kiểm tra và trao badges tự động ──
    public function kiemTraVaTrao($id_nguoi_dung) {
        $badges_trao = [];

        // Đếm khóa học đã hoàn thành
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT id_khoa_hoc) as so_khoa FROM dang_ky_khoa_hoc 
            WHERE id_hoc_vien = ? AND trang_thai = 'da_xac_nhan'
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        $so_khoa = $stmt->get_result()->fetch_assoc()['so_khoa'];

        // Badge khóa học
        $badges_khoa = [
            'first_course' => 1,
            'course_5' => 5,
            'course_10' => 10,
        ];
        foreach ($badges_khoa as $ma => $threshold) {
            if ($so_khoa >= $threshold) {
                $badge = $this->layTheoMa($ma);
                if ($badge && !$this->daCo($id_nguoi_dung, $badge['id'])) {
                    $this->trao($id_nguoi_dung, $badge['id']);
                    $badges_trao[] = $badge;
                }
            }
        }

        // Đếm quiz đạt 100 điểm
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as so_quiz FROM ket_qua_quiz 
            WHERE id_hoc_vien = ? AND diem_so = 100
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        $so_quiz_100 = $stmt->get_result()->fetch_assoc()['so_quiz'];

        // Badge quiz
        $badges_quiz = [
            'quiz_first' => 1,
            'quiz_master' => 5,
            'quiz_legend' => 10,
        ];
        foreach ($badges_quiz as $ma => $threshold) {
            if ($so_quiz_100 >= $threshold) {
                $badge = $this->layTheoMa($ma);
                if ($badge && !$this->daCo($id_nguoi_dung, $badge['id'])) {
                    $this->trao($id_nguoi_dung, $badge['id']);
                    $badges_trao[] = $badge;
                }
            }
        }

        // Đếm câu trả lời Q&A
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as so_tra_loi FROM tra_loi WHERE id_nguoi_tra_loi = ?
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        $so_tra_loi = $stmt->get_result()->fetch_assoc()['so_tra_loi'];

        $badges_qa = [
            'qa_helper' => 5,
            'qa_mentor' => 20,
        ];
        foreach ($badges_qa as $ma => $threshold) {
            if ($so_tra_loi >= $threshold) {
                $badge = $this->layTheoMa($ma);
                if ($badge && !$this->daCo($id_nguoi_dung, $badge['id'])) {
                    $this->trao($id_nguoi_dung, $badge['id']);
                    $badges_trao[] = $badge;
                }
            }
        }

        // Badge streak
        $streak = $this->capNhatStreak($id_nguoi_dung);
        $badges_streak = [
            'streak_3' => 3,
            'streak_7' => 7,
            'streak_30' => 30,
        ];
        foreach ($badges_streak as $ma => $threshold) {
            if ($streak >= $threshold) {
                $badge = $this->layTheoMa($ma);
                if ($badge && !$this->daCo($id_nguoi_dung, $badge['id'])) {
                    $this->trao($id_nguoi_dung, $badge['id']);
                    $badges_trao[] = $badge;
                }
            }
        }

        return $badges_trao;
    }

    // ── Lấy icon theo loại ──
    public function getLoaiLabel($loai) {
        $labels = [
            'khoa_hoc' => 'Khóa học',
            'quiz' => 'Bài kiểm tra',
            'qa' => 'Hỏi đáp',
            'streak' => 'Streak',
            'special' => 'Đặc biệt'
        ];
        return $labels[$loai] ?? 'Khác';
    }

    public function getLoaiColor($loai) {
        $colors = [
            'khoa_hoc' => '#4F46E5',
            'quiz' => '#3B82F6',
            'qa' => '#06B6D4',
            'streak' => '#F97316',
            'special' => '#FFD700'
        ];
        return $colors[$loai] ?? '#64748B';
    }

    // ── Lấy leaderboard ──
    public function layLeaderboard($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT nd.id, nd.ho_ten, COUNT(ndh.id) as so_huy_hieu
            FROM nguoi_dung nd
            LEFT JOIN nguoi_dung_huy_hieu ndh ON ndh.id_nguoi_dung = nd.id
            WHERE nd.vai_tro != 'quan_tri'
            GROUP BY nd.id
            ORDER BY so_huy_hieu DESC, nd.ho_ten ASC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
