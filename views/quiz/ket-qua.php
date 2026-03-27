<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/quiz.php';
require_once __DIR__ . '/../../models/khoa_hoc.php';

$page_title = 'Kết quả Quiz - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$quiz_model = new Quiz();
$nguoi_dung = $auth->layThongTinNguoiDung();
$ket_qua_list = $quiz_model->layKetQua($nguoi_dung['id']);

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Kết quả các bài Quiz</h2>

    <?php if (empty($ket_qua_list)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            Bạn chưa làm bài Quiz nào.
        </div>
    <?php else: ?>
        <div class="card shadow">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>STT</th>
                            <th>Tên Quiz</th>
                            <th>Bài học</th>
                            <th>Điểm</th>
                            <th>Số câu đúng</th>
                            <th>Thời gian làm</th>
                            <th>Ngày làm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ket_qua_list as $index => $kq): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo $kq['tieu_de']; ?></strong></td>
                                <td><?php echo $kq['ten_bai_hoc']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        $tyle = ($kq['diem_so'] / 100) * 100;
                                        echo ($tyle >= 80) ? 'success' : (($tyle >= 50) ? 'warning' : 'danger');
                                    ?> fs-6">
                                        <?php echo $kq['diem_so']; ?>
                                    </span>
                                </td>
                                <td><?php echo $kq['so_cau_dung']; ?></td>
                                <td><?php echo gmdate("i:s", $kq['thoi_gian_lam_bai']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($kq['ngay_lam'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
