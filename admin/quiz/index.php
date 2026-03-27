<?php
$page_title = 'Quản lý Quiz';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/quiz.php';

$quiz = new Quiz();
$thong_bao = '';

// ── Xử lý xóa ──
if (isset($_GET['action']) && $_GET['action'] === 'xoa' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $q = $quiz->layTheoId($id);
    if ($q) {
        $result = $quiz->xoa($id);
        $thong_bao = $result['success']
            ? '<div class="alert alert-success">Đã xóa quiz thành công.</div>'
            : '<div class="alert alert-danger">' . htmlspecialchars($result['message']) . '</div>';
    }
}

// ── Lấy danh sách ──
$ds = $quiz->layTatCa();
$tong = count($ds);

// Thống kê nhanh
$co_cau_hoi  = count(array_filter($ds, fn($q) => (int)$q['so_cau_hoi'] > 0));
$chua_cau_hoi = $tong - $co_cau_hoi;

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-0"><i class="fas fa-circle-question me-2 text-primary"></i>Quản lý Quiz</h1>
        <p class="text-muted small mb-0">Tổng: <?php echo $tong; ?> quiz</p>
    </div>
    <a href="form.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Thêm Quiz
    </a>
</div>

<?php echo $thong_bao; ?>

<!-- Thống kê -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-circle-question"></i></div>
                <div>
                    <div class="text-muted small">Tổng Quiz</div>
                    <div class="fs-4 fw-semibold"><?php echo $tong; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="text-muted small">Có câu hỏi</div>
                    <div class="fs-4 fw-semibold"><?php echo $co_cau_hoi; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                    <div class="text-muted small">Chưa có câu hỏi</div>
                    <div class="fs-4 fw-semibold"><?php echo $chua_cau_hoi; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng danh sách -->
<div class="card stat-card">
    <div class="card-body p-0">
        <?php if (empty($ds)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-circle-question fa-3x mb-3"></i>
                <p class="mb-0">Chưa có quiz nào. <a href="form.php">Thêm quiz đầu tiên</a></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Tiêu đề Quiz</th>
                            <th>Bài học / Khóa học</th>
                            <th>Câu hỏi</th>
                            <th>Thời gian</th>
                            <th>Điểm tối đa</th>
                            <th class="pe-4 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ds as $i => $q): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($q['tieu_de']); ?></div>
                                    <?php if ($q['mo_ta']): ?>
                                        <div class="small text-muted"><?php echo htmlspecialchars($q['mo_ta']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small"><?php echo htmlspecialchars($q['ten_bai_hoc'] ?? '-'); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($q['ten_khoa_hoc'] ?? '-'); ?></div>
                                </td>
                                <td>
                                    <?php if ((int)$q['so_cau_hoi'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $q['so_cau_hoi']; ?> câu</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Chưa có câu hỏi</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?php echo $q['thoi_gian_phut']; ?> phút</td>
                                <td class="text-muted small"><?php echo $q['diem_toi_da']; ?></td>
                                <td class="pe-4 text-center">
                                    <a href="form.php?id=<?php echo $q['id']; ?>" class="btn btn-outline-primary btn-sm me-1">
                                        <i class="fas fa-pen me-1"></i>Sửa
                                    </a>
                                    <a href="?action=xoa&id=<?php echo $q['id']; ?>"
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Xóa quiz &quot;<?php echo htmlspecialchars($q['tieu_de']); ?>&quot;?');">
                                        <i class="fas fa-trash me-1"></i>Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
