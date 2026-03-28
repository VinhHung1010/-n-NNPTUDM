<?php
require_once __DIR__ . '/../config/database.php';

class GoiY {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Ghi nhận lượt xem khóa học ──
    public function ghiNhanXem($id_nguoi_dung, $id_khoa_hoc, $thoi_gian = 0) {
        $stmt = $this->db->prepare("
            INSERT INTO lich_su_xem (id_nguoi_dung, id_khoa_hoc, thoi_gian_xem, ngay_xem)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE thoi_gian_xem = thoi_gian_xem + VALUES(thoi_gian_xem), ngay_xem = NOW()
        ");
        $stmt->bind_param("iii", $id_nguoi_dung, $id_khoa_hoc, $thoi_gian);
        return $stmt->execute();
    }

    // ── Lấy khóa học đã xem của user ──
    public function layKhoaHocDaXem($id_nguoi_dung, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT kh.*, lx.thoi_gian_xem, lx.ngay_xem, 
                   nd.ho_ten AS ten_giao_vien,
                   dm.ten_danh_muc,
                   (SELECT ROUND(AVG(diem_so), 1) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS diem_trung_binh,
                   (SELECT COUNT(*) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS so_danh_gia
            FROM lich_su_xem lx
            JOIN khoa_hoc kh ON kh.id = lx.id_khoa_hoc
            LEFT JOIN nguoi_dung nd ON nd.id = kh.id_nguoi_tao
            LEFT JOIN danh_muc dm ON dm.id = kh.id_danh_muc
            WHERE lx.id_nguoi_dung = ?
            ORDER BY lx.ngay_xem DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $id_nguoi_dung, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Gợi ý: Cùng danh mục với khóa đã xem ──
    public function goiYCungDanhMuc($id_nguoi_dung, $limit = 6) {
        // Lấy các danh mục user đã xem
        $stmt = $this->db->prepare("
            SELECT DISTINCT kh.id_danh_muc 
            FROM lich_su_xem lx
            JOIN khoa_hoc kh ON kh.id = lx.id_khoa_hoc
            WHERE lx.id_nguoi_dung = ?
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        $danh_mucs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($danh_mucs)) return [];

        $dm_ids = array_column($danh_mucs, 'id_danh_muc');
        $placeholders = implode(',', array_fill(0, count($dm_ids), '?'));

        // Lấy khóa học cùng danh mục mà chưa xem
        $sql = "
            SELECT kh.*, nd.ho_ten AS ten_giao_vien, dm.ten_danh_muc,
                   (SELECT ROUND(AVG(diem_so), 1) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS diem_trung_binh,
                   (SELECT COUNT(*) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS so_danh_gia,
                   (SELECT COUNT(*) FROM dang_ky_khoa_hoc WHERE id_khoa_hoc = kh.id AND trang_thai = 'da_xac_nhan') AS so_hoc_vien,
                   'cung_danh_muc' AS loai_goi_y,
                   80 + (SELECT COUNT(*) FROM lich_su_xem WHERE id_nguoi_dung = ? AND id_khoa_hoc IN (
                       SELECT id FROM khoa_hoc WHERE id_danh_muc = kh.id_danh_muc
                   )) * 5 AS diem_goi_y
            FROM khoa_hoc kh
            LEFT JOIN nguoi_dung nd ON nd.id = kh.id_nguoi_tao
            LEFT JOIN danh_muc dm ON dm.id = kh.id_danh_muc
            WHERE kh.id_danh_muc IN ($placeholders)
              AND kh.id NOT IN (
                  SELECT id_khoa_hoc FROM lich_su_xem WHERE id_nguoi_dung = ?
              )
              AND kh.id NOT IN (
                  SELECT id_khoa_hoc FROM dang_ky_khoa_hoc WHERE id_hoc_vien = ? AND trang_thai IN ('da_xac_nhan', 'cho_xu_ly')
              )
              AND kh.trang_thai = 'da_duyet'
            ORDER BY diem_goi_y DESC, kh.ngay_tao DESC
            LIMIT ?
        ";

        $params = array_merge([$id_nguoi_dung], $dm_ids, [$id_nguoi_dung, $id_nguoi_dung, $limit]);
        $types = str_repeat('i', count($params));
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Gợi ý: Cùng giáo viên ──
    public function goiYCungGiaoVien($id_nguoi_dung, $limit = 4) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT kh.id_nguoi_tao 
            FROM lich_su_xem lx
            JOIN khoa_hoc kh ON kh.id = lx.id_khoa_hoc
            WHERE lx.id_nguoi_dung = ?
        ");
        $stmt->bind_param("i", $id_nguoi_dung);
        $stmt->execute();
        $teachers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($teachers)) return [];

        $teacher_ids = array_column($teachers, 'id_nguoi_tao');
        $placeholders = implode(',', array_fill(0, count($teacher_ids), '?'));

        $sql = "
            SELECT kh.*, nd.ho_ten AS ten_giao_vien, dm.ten_danh_muc,
                   (SELECT ROUND(AVG(diem_so), 1) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS diem_trung_binh,
                   (SELECT COUNT(*) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS so_danh_gia,
                   (SELECT COUNT(*) FROM dang_ky_khoa_hoc WHERE id_khoa_hoc = kh.id AND trang_thai = 'da_xac_nhan') AS so_hoc_vien,
                   'cung_giao_vien' AS loai_goi_y,
                   75 AS diem_goi_y
            FROM khoa_hoc kh
            LEFT JOIN nguoi_dung nd ON nd.id = kh.id_nguoi_tao
            LEFT JOIN danh_muc dm ON dm.id = kh.id_danh_muc
            WHERE kh.id_nguoi_tao IN ($placeholders)
              AND kh.id NOT IN (
                  SELECT id_khoa_hoc FROM lich_su_xem WHERE id_nguoi_dung = ?
              )
              AND kh.id NOT IN (
                  SELECT id_khoa_hoc FROM dang_ky_khoa_hoc WHERE id_hoc_vien = ? AND trang_thai IN ('da_xac_nhan', 'cho_xu_ly')
              )
              AND kh.trang_thai = 'da_duyet'
            ORDER BY kh.ngay_tao DESC
            LIMIT ?
        ";

        $params = array_merge($teacher_ids, [$id_nguoi_dung, $id_nguoi_dung, $limit]);
        $types = str_repeat('i', count($params));
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Gợi ý: Khóa học phổ biến (có nhiều học viên & đánh giá cao) ──
    public function goiYPhoBien($id_nguoi_dung, $limit = 6) {
        $sql = "
            SELECT kh.*, nd.ho_ten AS ten_giao_vien, dm.ten_danh_muc,
                   (SELECT ROUND(AVG(diem_so), 1) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS diem_trung_binh,
                   (SELECT COUNT(*) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS so_danh_gia,
                   (SELECT COUNT(*) FROM dang_ky_khoa_hoc WHERE id_khoa_hoc = kh.id AND trang_thai = 'da_xac_nhan') AS so_hoc_vien,
                   'pho_bien' AS loai_goi_y,
                   ((SELECT COUNT(*) FROM dang_ky_khoa_hoc WHERE id_khoa_hoc = kh.id AND trang_thai = 'da_xac_nhan') * 5 +
                    (SELECT COALESCE(AVG(diem_so), 0) * 20 FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id)) AS diem_goi_y
            FROM khoa_hoc kh
            LEFT JOIN nguoi_dung nd ON nd.id = kh.id_nguoi_tao
            LEFT JOIN danh_muc dm ON dm.id = kh.id_danh_muc
            WHERE kh.id NOT IN (
                SELECT id_khoa_hoc FROM lich_su_xem WHERE id_nguoi_dung = ?
            )
            AND kh.id NOT IN (
                SELECT id_khoa_hoc FROM dang_ky_khoa_hoc WHERE id_hoc_vien = ? AND trang_thai IN ('da_xac_nhan', 'cho_xu_ly')
            )
            AND kh.trang_thai = 'da_duyet'
            HAVING diem_trung_binh >= 3.5 OR diem_trung_binh IS NULL
            ORDER BY diem_goi_y DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $id_nguoi_dung, $id_nguoi_dung, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Gợi ý: Khóa học mới nhất ──
    public function goiYMoiNhat($id_nguoi_dung, $limit = 4) {
        $sql = "
            SELECT kh.*, nd.ho_ten AS ten_giao_vien, dm.ten_danh_muc,
                   (SELECT ROUND(AVG(diem_so), 1) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS diem_trung_binh,
                   (SELECT COUNT(*) FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = kh.id) AS so_danh_gia,
                   (SELECT COUNT(*) FROM dang_ky_khoa_hoc WHERE id_khoa_hoc = kh.id AND trang_thai = 'da_xac_nhan') AS so_hoc_vien,
                   'moi_nhat' AS loai_goi_y,
                   60 AS diem_goi_y
            FROM khoa_hoc kh
            LEFT JOIN nguoi_dung nd ON nd.id = kh.id_nguoi_tao
            LEFT JOIN danh_muc dm ON dm.id = kh.id_danh_muc
            WHERE kh.id NOT IN (
                SELECT id_khoa_hoc FROM lich_su_xem WHERE id_nguoi_dung = ?
            )
            AND kh.id NOT IN (
                SELECT id_khoa_hoc FROM dang_ky_khoa_hoc WHERE id_hoc_vien = ? AND trang_thai IN ('da_xac_nhan', 'cho_xu_ly')
            )
            AND kh.trang_thai = 'da_duyet'
            ORDER BY kh.ngay_tao DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $id_nguoi_dung, $id_nguoi_dung, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Gợi ý tổng hợp: Kết hợp tất cả các loại ──
    public function goiYTongHop($id_nguoi_dung, $limit = 12) {
        $cung_danh_muc = $this->goiYCungDanhMuc($id_nguoi_dung, 6);
        $cung_giao_vien = $this->goiYCungGiaoVien($id_nguoi_dung, 4);
        $pho_bien = $this->goiYPhoBien($id_nguoi_dung, 6);
        $moi_nhat = $this->goiYMoiNhat($id_nguoi_dung, 4);

        $ket_qua = array_merge($cung_danh_muc, $cung_giao_vien, $pho_bien, $moi_nhat);

        // Loại bỏ trùng lặp
        $seen = [];
        $ket_qua = array_filter($ket_qua, function($item) use (&$seen) {
            if (isset($seen[$item['id']])) return false;
            $seen[$item['id']] = true;
            return true;
        });

        // Sắp xếp theo điểm gợi ý
        usort($ket_qua, function($a, $b) {
            return ($b['diem_goi_y'] ?? 0) <=> ($a['diem_goi_y'] ?? 0);
        });

        return array_slice($ket_qua, 0, $limit);
    }

    // ── Đánh giá khóa học ──
    public function danhGia($id_nguoi_dung, $id_khoa_hoc, $diem_so, $binh_luan = '') {
        $stmt = $this->db->prepare("
            INSERT INTO danh_gia_khoa_hoc (id_nguoi_dung, id_khoa_hoc, diem_so, binh_luan)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE diem_so = VALUES(diem_so), binh_luan = VALUES(binh_luan), ngay_danh_gia = NOW()
        ");
        $stmt->bind_param("iiis", $id_nguoi_dung, $id_khoa_hoc, $diem_so, $binh_luan);
        return $stmt->execute();
    }

    // ── Lấy đánh giá của user cho 1 khóa học ──
    public function layDanhGia($id_nguoi_dung, $id_khoa_hoc) {
        $stmt = $this->db->prepare("SELECT * FROM danh_gia_khoa_hoc WHERE id_nguoi_dung = ? AND id_khoa_hoc = ?");
        $stmt->bind_param("ii", $id_nguoi_dung, $id_khoa_hoc);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ── Lấy đánh giá của 1 khóa học ──
    public function layDanhGiaKhoaHoc($id_khoa_hoc, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT dg.*, nd.ho_ten, nd.anh_dai_dien
            FROM danh_gia_khoa_hoc dg
            JOIN nguoi_dung nd ON nd.id = dg.id_nguoi_dung
            WHERE dg.id_khoa_hoc = ?
            ORDER BY dg.ngay_danh_gia DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $id_khoa_hoc, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Lấy điểm TB và số đánh giá ──
    public function layThongKeDanhGia($id_khoa_hoc) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) AS so_danh_gia,
                ROUND(COALESCE(AVG(diem_so), 0), 1) AS diem_trung_binh,
                SUM(CASE WHEN diem_so = 5 THEN 1 ELSE 0 END) AS so_5_sao,
                SUM(CASE WHEN diem_so = 4 THEN 1 ELSE 0 END) AS so_4_sao,
                SUM(CASE WHEN diem_so = 3 THEN 1 ELSE 0 END) AS so_3_sao,
                SUM(CASE WHEN diem_so = 2 THEN 1 ELSE 0 END) AS so_2_sao,
                SUM(CASE WHEN diem_so = 1 THEN 1 ELSE 0 END) AS so_1_sao
            FROM danh_gia_khoa_hoc WHERE id_khoa_hoc = ?
        ");
        $stmt->bind_param("i", $id_khoa_hoc);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ── Lấy loại gợi ý label ──
    public function getLoaiLabel($loai) {
        $labels = [
            'cung_danh_muc' => 'Vì bạn quan tâm danh mục này',
            'cung_giao_vien' => 'Từ giáo viên bạn theo dõi',
            'tuong_tu' => 'Khóa học tương tự',
            'pho_bien' => 'Phổ biến nhất',
            'moi_nhat' => 'Khóa học mới'
        ];
        return $labels[$loai] ?? 'Gợi ý cho bạn';
    }

    public function getLoaiColor($loai) {
        $colors = [
            'cung_danh_muc' => '#4F46E5',
            'cung_giao_vien' => '#10B981',
            'tuong_tu' => '#8B5CF6',
            'pho_bien' => '#F59E0B',
            'moi_nhat' => '#3B82F6'
        ];
        return $colors[$loai] ?? '#64748B';
    }
}
?>
