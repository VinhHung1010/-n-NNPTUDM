<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function dangKy($ho_ten, $email, $mat_khau, $xac_nhan_mat_khau) {
        if ($mat_khau !== $xac_nhan_mat_khau) {
            return ['success' => false, 'message' => 'Mật khẩu xác nhận không khớp!'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không hợp lệ!'];
        }

        if (strlen($mat_khau) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự!'];
        }

        // Kiểm tra email đã tồn tại chưa
        $stmt = $this->db->prepare("SELECT id FROM nguoi_dung WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email đã được đăng ký!'];
        }

        // Mã hóa mật khẩu
        $mat_khau_hash = password_hash($mat_khau, PASSWORD_DEFAULT);

        // Thêm người dùng mới
        $stmt = $this->db->prepare("INSERT INTO nguoi_dung (ho_ten, email, mat_khau, vai_tro) VALUES (?, ?, ?, 'hoc_vien')");
        $stmt->bind_param("sss", $ho_ten, $email, $mat_khau_hash);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đăng ký thành công! Vui lòng đăng nhập.'];
        }

        return ['success' => false, 'message' => 'Đăng ký thất bại!'];
    }

    public function dangNhap($email, $mat_khau) {
        $stmt = $this->db->prepare("SELECT id, ho_ten, email, mat_khau, vai_tro, trang_thai FROM nguoi_dung WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!'];
        }

        $user = $result->fetch_assoc();

        if ($user['trang_thai'] === 'khong_hoat_dong') {
            return ['success' => false, 'message' => 'Tài khoản đã bị khóa!'];
        }

        if (!password_verify($mat_khau, $user['mat_khau'])) {
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!'];
        }

        // Đăng nhập thành công - lưu session
        $_SESSION['nguoi_dung'] = [
            'id' => $user['id'],
            'ho_ten' => $user['ho_ten'],
            'email' => $user['email'],
            'vai_tro' => $user['vai_tro']
        ];

        return ['success' => true, 'message' => 'Đăng nhập thành công!', 'user' => $user];
    }

    public function dangXuat() {
        session_destroy();
        return ['success' => true, 'message' => 'Đăng xuất thành công!'];
    }

    public function kiemTraDangNhap() {
        return isset($_SESSION['nguoi_dung']);
    }

    public function layThongTinNguoiDung() {
        if ($this->kiemTraDangNhap()) {
            return $_SESSION['nguoi_dung'];
        }
        return null;
    }

    public function laQuanTri() {
        if (!$this->kiemTraDangNhap()) {
            return false;
        }
        $u = $this->layThongTinNguoiDung();
        return ($u['vai_tro'] ?? '') === 'quan_tri';
    }

    public function laGiaoVien() {
        if (!$this->kiemTraDangNhap()) {
            return false;
        }
        $u = $this->layThongTinNguoiDung();
        return ($u['vai_tro'] ?? '') === 'giao_vien';
    }

    public function layNguoiDungTheoId($id) {
        $stmt = $this->db->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function capNhatHoSo($id, $ho_ten, $mat_khau_moi = null) {
        if ($mat_khau_moi) {
            $mat_khau_hash = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE nguoi_dung SET ho_ten = ?, mat_khau = ? WHERE id = ?");
            $stmt->bind_param("ssi", $ho_ten, $mat_khau_hash, $id);
        } else {
            $stmt = $this->db->prepare("UPDATE nguoi_dung SET ho_ten = ? WHERE id = ?");
            $stmt->bind_param("si", $ho_ten, $id);
        }

        return $stmt->execute();
    }

    public function doiMatKhau($id, $mat_khau_cu, $mat_khau_moi) {
        $user = $this->layNguoiDungTheoId($id);
        if (!$user) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản!'];
        }

        if (!password_verify($mat_khau_cu, $user['mat_khau'])) {
            return ['success' => false, 'message' => 'Mật khẩu cũ không đúng!'];
        }

        $mat_khau_hash = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?");
        $stmt->bind_param("si", $mat_khau_hash, $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công!'];
        }

        return ['success' => false, 'message' => 'Đổi mật khẩu thất bại!'];
    }
}
?>
