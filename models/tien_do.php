<?php
require_once __DIR__ . '/../config/database.php';

class TienDo {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Kiểm tra bài học đã hoàn thành chưa
     */
    public function daHoanThanh($hoc_vien_id, $bai_hoc_id) {
        $stmt = $this->db->prepare(
            "SELECT da_hoan_thanh FROM tien_do_hoc
             WHERE id_hoc_vien = ? AND id_bai_hoc = ?"
        );
        $stmt->bind_param("ii", $hoc_vien_id, $bai_hoc_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return $r ? (bool)$r['da_hoan_thanh'] : false;
    }

    /**
     * Đánh dấu hoàn thành bài học (upsert)
     */
    public function danhDauHoanThanh($hoc_vien_id, $bai_hoc_id, $trang_thai = true) {
        $stmt = $this->db->prepare(
            "INSERT INTO tien_do_hoc (id_hoc_vien, id_bai_hoc, da_hoan_thanh)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE da_hoan_thanh = VALUES(da_hoan_thanh),
                                   ngay_cap_nhat = CURRENT_TIMESTAMP"
        );
        $tt = $trang_thai ? 1 : 0;
        $stmt->bind_param("iii", $hoc_vien_id, $bai_hoc_id, $tt);
        return $stmt->execute();
    }

    /**
     * Xóa đánh dấu hoàn thành (hủy hoàn thành)
     */
    public function huyHoanThanh($hoc_vien_id, $bai_hoc_id) {
        return $this->danhDauHoanThanh($hoc_vien_id, $bai_hoc_id, false);
    }

    /**
     * Lấy tiến độ của 1 khóa học: mảng [bai_hoc_id => bool]
     */
    public function layTienDoKhoaHoc($hoc_vien_id, $khoa_hoc_id) {
        $stmt = $this->db->prepare(
            "SELECT td.id_bai_hoc, td.da_hoan_thanh
             FROM tien_do_hoc td
             JOIN bai_hoc bh ON bh.id = td.id_bai_hoc
             WHERE td.id_hoc_vien = ? AND bh.id_khoa_hoc = ?"
        );
        $stmt->bind_param("ii", $hoc_vien_id, $khoa_hoc_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['id_bai_hoc']] = (bool)$r['da_hoan_thanh'];
        }
        return $map;
    }

    /**
     * Tính % hoàn thành khóa học
     */
    public function tinhPhanTram($hoc_vien_id, $khoa_hoc_id) {
        $stmt = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = ?) AS tong_bai,
                (SELECT COUNT(*)
                 FROM tien_do_hoc td
                 JOIN bai_hoc bh ON bh.id = td.id_bai_hoc
                 WHERE td.id_hoc_vien = ? AND bh.id_khoa_hoc = ?
                   AND td.da_hoan_thanh = 1) AS da_hoan_thanh"
        );
        $stmt->bind_param("iii", $khoa_hoc_id, $hoc_vien_id, $khoa_hoc_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();

        $tong = (int)($r['tong_bai'] ?? 0);
        $da   = (int)($r['da_hoan_thanh'] ?? 0);

        if ($tong === 0) return 0;
        return round(($da / $tong) * 100);
    }

    /**
     * Kiểm tra HV đã hoàn thành toàn bộ KH chưa
     */
    public function daHoanThanhKhoaHoc($hoc_vien_id, $khoa_hoc_id) {
        $stmt = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM bai_hoc WHERE id_khoa_hoc = ?) AS tong_bai,
                (SELECT COUNT(*)
                 FROM tien_do_hoc td
                 JOIN bai_hoc bh ON bh.id = td.id_bai_hoc
                 WHERE td.id_hoc_vien = ? AND bh.id_khoa_hoc = ?
                   AND td.da_hoan_thanh = 1) AS da_hoan_thanh"
        );
        $stmt->bind_param("iii", $khoa_hoc_id, $hoc_vien_id, $khoa_hoc_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();

        $tong = (int)($r['tong_bai'] ?? 0);
        $da   = (int)($r['da_hoan_thanh'] ?? 0);

        return $tong > 0 && $da === $tong;
    }
}
?>
