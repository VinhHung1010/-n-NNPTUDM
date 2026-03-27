<?php
$page_title = 'Chi tiết Đăng ký';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/dang_ky.php';

$dk_model = new DangKy();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$d = $dk_model->layTheoId($id);

if (!$d) {
    header('Location: index.php');
    exit;
}

$thong_bao = '';

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'xac_nhan':
            $dk_model->capNhatTrangThai($id, 'da_xac_nhan');
            $thong_bao = '<div class="alert alert-success">Đã xác nhận đăng ký.</div>';
            $d['trang_thai'] = 'da_xac_nhan';
            break;
        case 'huy':
            $dk_model->capNhatTrangThai($id, 'da_huy');
            $thong_bao = '<div class="alert alert-warning">Đã hủy đăng ký.</div>';
            $d['trang_thai'] = 'da_huy';
            break;
        case 'cho_xu_ly':
            $dk_model->capNhatTrangThai($id, 'cho_xu_ly');
            $thong_bao = '<div class="alert alert-info">Đã chuyển sang chờ xử lý.</div>';
            $d['trang_thai'] = 'cho_xu_ly';
            break;
        case 'xoa':
            if ($dk_model->xoa($id)) {
                header('Location: index.php');
                exit;
            } else {
                $thong_bao = '<div class="alert alert-danger">Xóa thất bại.</div>';
            }
            break;
    }
}

$tt_labels = [
    'cho_xu_ly'   => ['Chờ xử lý', 'warning text-dark', 'clock'],
    'da_xac_nhan' => ['Đã xác nhận', 'success', 'check-circle'],
    'da_huy'      => ['Đã hủy', 'secondary', 'x-circle'],
];
[$tt_text, $tt_color, $tt_icon] = $tt_labels[$d['trang_thai']] ?? ['—', 'secondary', 'circle'];

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>Chi tiết Đăng ký</h1>
        <p class="text-muted small mb-0">
            <a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quay lại danh sách</a>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($d['trang_thai'] === 'cho_xu_ly'): ?>
            <a href="?id=<?php echo $id; ?>&action=xac_nhan" class="btn btn-success">
                <i class="fas fa-check me-1"></i>Xác nhận
            </a>
            <a href="?id=<?php echo $id; ?>&action=huy" class="btn btn-outline-danger"
               onclick="return confirm('Hủy đăng ký này?');">
                <i class="fas fa-xmark me-1"></i>Hủy
            </a>
        <?php elseif ($d['trang_thai'] === 'da_xac_nhan'): ?>
            <a href="?id=<?php echo $id; ?>&action=huy" class="btn btn-outline-dark">
                <i class="fas fa-ban me-1"></i>Hủy xác nhận
            </a>
        <?php elseif ($d['trang_thai'] === 'da_huy'): ?>
            <a href="?id=<?php echo $id; ?>&action=cho_xu_ly" class="btn btn-outline-primary">
                <i class="fas fa-rotate-left me-1"></i>Chờ xử lý lại
            </a>
        <?php endif; ?>
        <a href="?id=<?php echo $id; ?>&action=xoa" class="btn btn-outline-danger"
           onclick="return confirm('Xóa vĩnh viễn đăng ký này?');">
            <i class="fas fa-trash me-1"></i>Xóa
        </a>
    </div>
</div>

<?php echo $thong_bao; ?>

<div class="row g-4">

    <!-- ══ Thông tin học viên ══ -->
    <div class="col-lg-4">
        <div class="card stat-card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-user-graduate me-1 text-primary"></i>Thông tin Học viên
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div style="width:72px;height:72px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;margin:0 auto;color:#fff;font-size:1.8rem">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="mb-2">
                    <strong>Họ tên:</strong>
                    <?php echo htmlspecialchars($d['ten_hoc_vien']); ?>
                </div>
                <div class="mb-2">
                    <strong>Email:</strong>
                    <a href="mailto:<?php echo htmlspecialchars($d['email_hoc_vien']); ?>">
                        <?php echo htmlspecialchars($d['email_hoc_vien']); ?>
                    </a>
                </div>
                <div class="mb-2">
                    <strong>Vai trò:</strong>
                    <?php
                    $vai_tro_labels = [
                        'hoc_vien'  => '<span class="badge bg-primary">Học viên</span>',
                        'giao_vien' => '<span class="badge bg-warning text-dark">Giáo viên</span>',
                        'quan_tri'   => '<span class="badge bg-danger">Quản trị</span>',
                    ];
                    echo $vai_tro_labels[$d['vai_tro_hv']] ?? $d['vai_tro_hv'];
                    ?>
                </div>
                <div class="mb-2">
                    <strong>Ngày đăng ký:</strong>
                    <?php echo date('d/m/Y H:i', strtotime($d['ngay_dang_ky'])); ?>
                </div>
                <div>
                    <strong>Trạng thái:</strong>
                    <span class="badge bg-<?php echo $tt_color; ?>">
                        <i class="fas fa-<?php echo $tt_icon; ?> me-1"></i><?php echo $tt_text; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Thông tin khóa học ══ -->
    <div class="col-lg-8">
        <div class="card stat-card mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-book me-1 text-primary"></i>Thông tin Khóa học
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-3">
                        <?php
                        $hinh = !empty($d['hinh_anh'])
                            ? $d['hinh_anh']
                            : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=200&h=120&fit=crop';
                        ?>
                        <img src="<?php echo $hinh; ?>" class="img-fluid rounded" style="object-fit:cover;height:90px;width:100%"
                             onerror="this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=200&h=120&fit=crop'">
                    </div>
                    <div class="col-sm-9">
                        <div class="mb-2">
                            <strong>Khóa học:</strong>
                            <a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $d['id_khoa_hoc']; ?>" target="_blank">
                                <?php echo htmlspecialchars($d['ten_khoa_hoc']); ?>
                            </a>
                        </div>
                        <div class="mb-2">
                            <strong>Danh mục:</strong> <?php echo htmlspecialchars($d['ten_danh_muc'] ?? '-'); ?>
                        </div>
                        <div class="mb-2">
                            <strong>Giáo viên:</strong> <?php echo htmlspecialchars($d['ten_giao_vien'] ?? '-'); ?>
                        </div>
                        <div>
                            <strong>Giá:</strong>
                            <?php
                            if ((int)$d['gia_khoa_hoc'] === 0) {
                                echo '<span class="text-success fw-semibold">Miễn phí</span>';
                            } else {
                                echo number_format($d['gia_khoa_hoc'], 0, ',', '.') . 'đ';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($d['mo_ta'])): ?>
                    <hr>
                    <div class="small text-muted">
                        <strong>Mô tả:</strong><br>
                        <?php echo nl2br(htmlspecialchars($d['mo_ta'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thao tác nhanh -->
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-bolt me-1 text-warning"></i>Thao tác nhanh
            </div>
            <div class="card-body d-flex flex-wrap gap-2">
                <?php if ($d['trang_thai'] === 'cho_xu_ly'): ?>
                    <a href="?id=<?php echo $id; ?>&action=xac_nhan" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Xác nhận đăng ký
                    </a>
                    <a href="?id=<?php echo $id; ?>&action=huy" class="btn btn-outline-danger"
                       onclick="return confirm('Hủy đăng ký?');">
                        <i class="fas fa-xmark me-1"></i>Hủy đăng ký
                    </a>
                <?php elseif ($d['trang_thai'] === 'da_xac_nhan'): ?>
                    <a href="?id=<?php echo $id; ?>&action=huy" class="btn btn-outline-dark">
                        <i class="fas fa-ban me-1"></i>Hủy xác nhận
                    </a>
                <?php elseif ($d['trang_thai'] === 'da_huy'): ?>
                    <a href="?id=<?php echo $id; ?>&action=cho_xu_ly" class="btn btn-outline-primary">
                        <i class="fas fa-rotate-left me-1"></i>Chuyển về chờ xử lý
                    </a>
                <?php endif; ?>

                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i>Danh sách
                </a>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
