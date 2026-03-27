<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class ThanhToan {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        // 确保支付表存在，避免未迁移数据库时崩溃
        $this->damBaoBangTonTai();
    }

    /**
     * 若 giao_dich_thanh_toan 不存在则创建（与 schema.sql 一致）
     */
    private function damBaoBangTonTai() {
        static $ok = false;
        if ($ok) {
            return;
        }
        $ok = true;
        $res = $this->db->query("SHOW TABLES LIKE 'giao_dich_thanh_toan'");
        if ($res && $res->num_rows > 0) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS giao_dich_thanh_toan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_hoc_vien INT NOT NULL,
            id_khoa_hoc INT NOT NULL,
            so_tien DECIMAL(12,0) NOT NULL,
            ma_giao_dich VARCHAR(64) NOT NULL,
            trang_thai ENUM('cho_thanh_toan', 'thanh_cong', 'that_bai', 'huy') DEFAULT 'cho_thanh_toan',
            kenh VARCHAR(32) DEFAULT 'demo',
            ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_hoc_vien) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
            FOREIGN KEY (id_khoa_hoc) REFERENCES khoa_hoc(id) ON DELETE CASCADE,
            UNIQUE KEY uk_ma_giao_dich (ma_giao_dich),
            INDEX idx_hv_kh (id_hoc_vien, id_khoa_hoc)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$this->db->query($sql)) {
            trigger_error('Không thể tạo bảng giao_dich_thanh_toan: ' . $this->db->error, E_USER_WARNING);
        }
    }

    private function taoMaGiaoDich() {
        return 'ORD-' . strtoupper(bin2hex(random_bytes(8)));
    }

    public function layTheoMa($ma) {
        $stmt = $this->db->prepare("SELECT * FROM giao_dich_thanh_toan WHERE ma_giao_dich = ?");
        $stmt->bind_param("s", $ma);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Giao dịch đang chờ của học viên + khóa (dùng lại nếu còn hiệu lực, tránh spam bản ghi)
     */
    public function layGiaoDichChoGanNhat($hoc_vien_id, $khoa_hoc_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM giao_dich_thanh_toan
            WHERE id_hoc_vien = ? AND id_khoa_hoc = ? AND trang_thai = 'cho_thanh_toan'
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->bind_param("ii", $hoc_vien_id, $khoa_hoc_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Tạo giao dịch mới (chờ thanh toán)
     */
    public function taoGiaoDich($hoc_vien_id, $khoa_hoc_id, $so_tien) {
        $so_tien = (float)$so_tien;
        if ($so_tien <= 0) {
            return ['success' => false, 'message' => 'Khóa học không có phí thanh toán.'];
        }
        $ma = $this->taoMaGiaoDich();
        $kenh = defined('THANH_TOAN_KENH') ? THANH_TOAN_KENH : 'demo';
        $stmt = $this->db->prepare("
            INSERT INTO giao_dich_thanh_toan (id_hoc_vien, id_khoa_hoc, so_tien, ma_giao_dich, trang_thai, kenh)
            VALUES (?, ?, ?, ?, 'cho_thanh_toan', ?)
        ");
        $stmt->bind_param("iidss", $hoc_vien_id, $khoa_hoc_id, $so_tien, $ma, $kenh);
        if ($stmt->execute()) {
            return ['success' => true, 'ma_giao_dich' => $ma, 'id' => $stmt->insert_id];
        }
        return ['success' => false, 'message' => 'Không tạo được giao dịch.'];
    }

    /**
     * Hoàn tất thanh toán (kênh demo — mô phỏng callback cổng thanh toán)
     */
    public function hoanTatThanhToanDemo($ma_giao_dich, $hoc_vien_id) {
        $gd = $this->layTheoMa($ma_giao_dich);
        if (!$gd) {
            return ['success' => false, 'message' => 'Không tìm thấy giao dịch.'];
        }
        if ((int)$gd['id_hoc_vien'] !== (int)$hoc_vien_id) {
            return ['success' => false, 'message' => 'Giao dịch không thuộc tài khoản của bạn.'];
        }
        if ($gd['trang_thai'] !== 'cho_thanh_toan') {
            return ['success' => false, 'message' => 'Giao dịch đã được xử lý trước đó.'];
        }
        $stmt = $this->db->prepare("
            UPDATE giao_dich_thanh_toan SET trang_thai = 'thanh_cong' WHERE id = ? AND trang_thai = 'cho_thanh_toan'
        ");
        $id = (int)$gd['id'];
        $stmt->bind_param("i", $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            return ['success' => true, 'giao_dich' => $gd];
        }
        return ['success' => false, 'message' => 'Không cập nhật được trạng thái thanh toán.'];
    }
}
