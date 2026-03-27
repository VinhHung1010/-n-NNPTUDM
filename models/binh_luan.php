<?php
require_once __DIR__ . '/../config/database.php';

class BinhLuan {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        // 若本地库未执行 schema 升级，自动创建评论表，避免页面致命错误
        $this->damBaoBangTonTai();
    }

    /**
     * 确保 binh_luan 表存在（与 database/schema.sql 结构一致）
     */
    private function damBaoBangTonTai() {
        static $daKiemTra = false;
        if ($daKiemTra) {
            return;
        }
        $daKiemTra = true;

        $res = $this->db->query("SHOW TABLES LIKE 'binh_luan'");
        if ($res && $res->num_rows > 0) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS binh_luan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_bai_hoc INT NOT NULL,
            id_nguoi_dung INT NOT NULL,
            noi_dung TEXT NOT NULL,
            ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_bai_hoc) REFERENCES bai_hoc(id) ON DELETE CASCADE,
            FOREIGN KEY (id_nguoi_dung) REFERENCES nguoi_dung(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$this->db->query($sql)) {
            trigger_error('Không thể tạo bảng binh_luan: ' . $this->db->error, E_USER_WARNING);
        }
    }

    public function layTheoBaiHoc($bai_hoc_id) {
        $stmt = $this->db->prepare("
            SELECT bl.*, nd.ho_ten, nd.anh_dai_dien, nd.vai_tro
            FROM binh_luan bl
            LEFT JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
            WHERE bl.id_bai_hoc = ?
            ORDER BY bl.ngay_tao DESC
        ");
        $stmt->bind_param("i", $bai_hoc_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function layTheoId($id) {
        $stmt = $this->db->prepare("
            SELECT bl.*, nd.ho_ten, nd.anh_dai_dien
            FROM binh_luan bl
            LEFT JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
            WHERE bl.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function them($bai_hoc_id, $nguoi_dung_id, $noi_dung) {
        $sql = "INSERT INTO binh_luan (id_bai_hoc, id_nguoi_dung, noi_dung) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iis", $bai_hoc_id, $nguoi_dung_id, $noi_dung);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm bình luận thành công!', 'id' => $stmt->insert_id];
        }
        return ['success' => false, 'message' => 'Thêm bình luận thất bại!'];
    }

    public function sua($id, $noi_dung) {
        $sql = "UPDATE binh_luan SET noi_dung = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $noi_dung, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật bình luận thành công!'];
        }
        return ['success' => false, 'message' => 'Cập nhật bình luận thất bại!'];
    }

    public function xoa($id) {
        $sql = "DELETE FROM binh_luan WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa bình luận thành công!'];
        }
        return ['success' => false, 'message' => 'Xóa bình luận thất bại!'];
    }

    public function demBinhLuan($bai_hoc_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as dem FROM binh_luan WHERE id_bai_hoc = ?");
        $stmt->bind_param("i", $bai_hoc_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['dem'];
    }
}
?>
