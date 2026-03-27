<?php
$page_title = 'Quản lý Quiz - Giáo viên';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/models/quiz.php';
require_once dirname(__DIR__) . '/models/bai_hoc.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';

$quiz_model = new Quiz();
$bh_model   = new BaiHoc();
$kh_model   = new KhoaHoc();
$thong_bao = '';

// Xử lý xóa
if (isset($_GET['action']) && $_GET['action'] === 'xoa' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $q = $quiz_model->layTheoId($id);
    if ($q) {
        $khoa = $kh_model->layTheoId($q['id_khoa_hoc'] ?? 0);
        if ($khoa && (int)$khoa['id_nguoi_tao'] === (int)$nguoi_dung['id']) {
            $r = $quiz_model->xoa($id);
            $thong_bao = $r['success']
                ? '<div class="alert alert-success">Đã xóa quiz.</div>'
                : '<div class="alert alert-danger">' . htmlspecialchars($r['message']) . '</div>';
        }
    }
}

// Lọc
$loc_kh = isset($_GET['khoa_hoc']) ? (int)$_GET['khoa_hoc'] : 0;
$loc_bh = isset($_GET['bai_hoc']) ? (int)$_GET['bai_hoc'] : 0;
$khoa_list = $kh_model->layKhoaHocCuaGiaoVien($nguoi_dung['id']);
$ds_quiz = $quiz_model->layTatCa();

if ($loc_kh > 0) {
    $ds_quiz = array_filter($ds_quiz, fn($q) => (int)($q['id_khoa_hoc'] ?? 0) === $loc_kh);
}
if ($loc_bh > 0) {
    $ds_quiz = array_filter($ds_quiz, fn($q) => (int)($q['id_bai_hoc'] ?? 0) === $loc_bh);
}

// Bài học cho dropdown
$bai_hoc_list = [];
if ($loc_kh > 0) {
    $bh_all = $bh_model->layTatCa();
    $bai_hoc_list = array_filter($bh_all, fn($b) => (int)($b['id_khoa_hoc'] ?? 0) === $loc_kh);
}

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="tv-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-circle-question me-2 text-success"></i>Quản lý Quiz</h1>
        <p class="text-muted small mb-0"><?php echo count($ds_quiz); ?> quiz</p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <select class="form-select form-select-sm" onchange="location.href='index.php?khoa_hoc='+this.value+'&bai_hoc=0'"
                style="min-width:180px">
            <option value="0">-- Tất cả khóa --</option>
            <?php foreach ($khoa_list as $k): ?>
                <option value="<?php echo $k['id']; ?>" <?php if ($loc_kh == $k['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($loc_kh > 0): ?>
        <select class="form-select form-select-sm" onchange="location.href='index.php?khoa_hoc=<?php echo $loc_kh; ?>&bai_hoc='+this.value"
                style="min-width:180px">
            <option value="0">-- Tất cả bài --</option>
            <?php foreach ($bai_hoc_list as $b): ?>
                <option value="<?php echo $b['id']; ?>" <?php if ($loc_bh == $b['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($b['tieu_de']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <a href="form.php?bai_hoc=<?php echo $loc_bh > 0 ? $loc_bh : ''; ?>"
           class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i>Thêm Quiz
        </a>
        <?php endif; ?>
    </div>
</div>

<?php echo $thong_bao; ?>

<?php if (empty($khoa_list)): ?>
    <div class="card tv-stat-card text-center py-5">
        <div class="card-body">
            <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Bạn chưa có khóa học nào.</h4>
            <a href="<?php echo SITE_URL; ?>/teacher/khoa-hoc/them-moi.php" class="btn btn-success mt-2">
                <i class="fas fa-plus me-1"></i>Tạo khóa học trước
            </a>
        </div>
    </div>
<?php elseif (empty($ds_quiz)): ?>
    <div class="card tv-stat-card text-center py-5">
        <div class="card-body">
            <i class="fas fa-circle-question fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Chưa có Quiz nào.</h4>
            <?php if ($loc_kh > 0): ?>
                <a href="form.php" class="btn btn-success mt-2"><i class="fas fa-plus me-1"></i>Thêm Quiz đầu tiên</a>
            <?php else: ?>
                <p class="text-muted">Chọn khóa học ở trên để thêm Quiz.</p>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="card tv-stat-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Tiêu đề Quiz</th>
                            <th>Bài học</th>
                            <th>Câu hỏi</th>
                            <th>Thời gian</th>
                            <th class="pe-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ds_quiz as $i => $q): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($q['tieu_de']); ?></td>
                                <td class="small text-muted"><?php echo htmlspecialchars($q['ten_bai_hoc'] ?? '-'); ?></td>
                                <td>
                                    <?php if ((int)$q['so_cau_hoi'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $q['so_cau_hoi']; ?> câu</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Chưa có câu hỏi</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo $q['thoi_gian_phut']; ?>p</td>
                                <td class="pe-4 text-center">
                                    <a href="form.php?id=<?php echo $q['id']; ?>" class="btn btn-sm btn-outline-success me-1">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="?action=xoa&id=<?php echo $q['id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Xóa quiz này?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
