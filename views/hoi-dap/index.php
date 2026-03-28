<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/hoi_dap.php';

$page_title = 'Hỏi đáp - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$qd_model = new HoiDap();

// Filter
$filter = $_GET['filter'] ?? 'all';
$id_bai_hoc = isset($_GET['bai_hoc']) ? intval($_GET['bai_hoc']) : 0;

if ($id_bai_hoc > 0) {
    $cau_hoi_list = $qd_model->layCauHoiTheoBaiHoc($id_bai_hoc, 50);
    $tieu_de_loc = 'Câu hỏi của bài học này';
} elseif ($filter === 'my') {
    $cau_hoi_list = $qd_model->layCauHoiCuaNguoiDung($nguoi_dung['id'], 50);
    $tieu_de_loc = 'Câu hỏi của tôi';
} else {
    $cau_hoi_list = $qd_model->layTatCa(50);
    $tieu_de_loc = 'Tất cả câu hỏi';
}

$tong_cau_hoi = $qd_model->demCauHoi();

include __DIR__ . '/../../views/layouts/header.php';
?>

<style>
.qa-list-page { padding: 2rem 0; }
.qa-page-header {
    background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4F46E5 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
}
.qa-filter-bar {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.qa-filter-btn {
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    border: 1.5px solid var(--border);
    background: white;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.qa-filter-btn:hover,
.qa-filter-btn.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}
.qa-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 12px;
    transition: all 0.2s;
    cursor: pointer;
}
.qa-card:hover {
    box-shadow: 0 4px 16px rgba(79,70,229,0.1);
    transform: translateY(-1px);
    border-color: var(--primary);
}
.qa-card.unanswered {
    border-left: 4px solid #F59E0B;
}
.qa-card.answered {
    border-left: 4px solid #10B981;
}
.qa-card-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.qa-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--muted);
}
.qa-empty i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}
</style>

<div class="container qa-list-page">

    <div class="qa-page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="fas fa-comments me-2"></i>Hỏi đáp & Thảo luận
                </h2>
                <p class="mb-0 opacity-75"><?php echo $tong_cau_hoi; ?> câu hỏi trên hệ thống</p>
            </div>
        </div>
    </div>

    <div class="qa-filter-bar">
        <a href="index.php" class="qa-filter-btn <?php echo $filter === 'all' && $id_bai_hoc <= 0 ? 'active' : ''; ?>">
            <i class="fas fa-globe me-1"></i>Tất cả
        </a>
        <a href="index.php?filter=my" class="qa-filter-btn <?php echo $filter === 'my' ? 'active' : ''; ?>">
            <i class="fas fa-user me-1"></i>Của tôi
        </a>
        <a href="index.php?filter=unanswered" class="qa-filter-btn <?php echo $filter === 'unanswered' ? 'active' : ''; ?>">
            <i class="fas fa-clock me-1"></i>Chưa trả lời
        </a>
        <a href="index.php?filter=answered" class="qa-filter-btn <?php echo $filter === 'answered' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle me-1"></i>Đã trả lời
        </a>
    </div>

    <?php if (empty($cau_hoi_list)): ?>
        <div class="qa-empty">
            <i class="fas fa-comment-dots"></i>
            <h4>Không có câu hỏi nào</h4>
            <p>Hãy là người đầu tiên đặt câu hỏi!</p>
        </div>
    <?php else: ?>
        <?php foreach ($cau_hoi_list as $ch): ?>
            <?php
            $role_color = $ch['vai_tro'] === 'quan_tri' ? 'danger' : ($ch['vai_tro'] === 'giao_vien' ? 'success' : 'primary');
            $role_label = $ch['vai_tro'] === 'giao_vien' ? 'GV' : ($ch['vai_tro'] === 'quan_tri' ? 'Admin' : 'HV');
            $is_answered = $ch['trang_thai'] === 'da_tra_loi';
            ?>
            <a href="chi-tiet.php?id=<?php echo $ch['id']; ?>" class="qa-card <?php echo $is_answered ? 'answered' : 'unanswered'; ?> text-decoration-none d-block">
                <div class="d-flex gap-3">
                    <div class="qa-card-avatar">
                        <?php echo mb_substr($ch['ho_ten'], 0, 1, 'UTF-8'); ?>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong class="small" style="color: var(--secondary);">
                                    <?php echo htmlspecialchars($ch['ho_ten']); ?>
                                </strong>
                                <span class="badge bg-<?php echo $role_color; ?>" style="font-size: 0.6rem; padding: 2px 6px;">
                                    <?php echo $role_label; ?>
                                </span>
                                <span class="text-muted" style="font-size: 0.72rem;">
                                    · <?php echo date('d/m/Y', strtotime($ch['ngay_tao'])); ?>
                                </span>
                            </div>
                            <?php if ($is_answered): ?>
                                <span class="badge bg-success" style="font-size: 0.65rem;">
                                    <i class="fas fa-check-circle me-1"></i>Đã trả lời
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">
                                    <i class="fas fa-clock me-1"></i>Chờ trả lời
                                </span>
                            <?php endif; ?>
                        </div>

                        <h6 class="mb-1 fw-bold" style="color: var(--secondary); font-size: 0.95rem;">
                            <?php echo htmlspecialchars($ch['tieu_de']); ?>
                        </h6>

                        <p class="mb-2 text-muted" style="font-size: 0.82rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars($ch['noi_dung']); ?>
                        </p>

                        <?php if (isset($ch['ten_bai_hoc'])): ?>
                            <div class="small text-muted mb-1">
                                <i class="fas fa-file-lines me-1"></i>
                                <?php echo htmlspecialchars($ch['ten_bai_hoc']); ?>
                                <?php if (isset($ch['ten_khoa_hoc'])): ?>
                                    · <i class="fas fa-book me-1"></i><?php echo htmlspecialchars($ch['ten_khoa_hoc']); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex gap-3 flex-wrap" style="font-size: 0.78rem; color: var(--muted);">
                            <span><i class="fas fa-thumbs-up me-1"></i><?php echo $ch['luot_thich']; ?></span>
                            <span><i class="fas fa-comment me-1"></i><?php echo $ch['so_tra_loi']; ?> câu trả lời</span>
                            <span><i class="fas fa-eye me-1"></i><?php echo $ch['luot_xem']; ?> lượt xem</span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
