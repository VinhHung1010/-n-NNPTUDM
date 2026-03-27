<?php
$page_title = 'Thêm Bài học - Giáo viên';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/models/bai_hoc.php';
require_once dirname(__DIR__) . '/models/khoa_hoc.php';

$bh = new BaiHoc();
$kh = new KhoaHoc();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$khoa_list = $kh->layKhoaHocCuaGiaoVien($nguoi_dung['id']);
$thong_bao = '';

if ($is_edit) {
    $b = $bh->layTheoId($id);
    if (!$b) { header('Location: index.php'); exit; }
    // Verify ownership
    $khoa_check = $kh->layTheoId($b['id_khoa_hoc']);
    if (!$khoa_check || (int)$khoa_check['id_nguoi_tao'] !== (int)$nguoi_dung['id']) {
        header('Location: index.php'); exit;
    }
    $page_title = 'Sửa Bài học';
    $form_data = [
        'tieu_de' => $b['tieu_de'],
        'noi_dung' => $b['noi_dung'],
        'video_url' => $b['video_url'],
        'thu_tu' => $b['thu_tu'],
        'thoi_luong_phut' => $b['thoi_luong_phut'],
        'id_khoa_hoc' => $b['id_khoa_hoc'],
    ];
} else {
    $form_data = [
        'tieu_de' => '', 'noi_dung' => '', 'video_url' => '',
        'thu_tu' => '', 'thoi_luong_phut' => 15,
        'id_khoa_hoc' => isset($_GET['khoa_hoc']) ? (int)$_GET['khoa_hoc'] : '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['tieu_de'] = trim($_POST['tieu_de'] ?? '');
    $form_data['noi_dung'] = trim($_POST['noi_dung'] ?? '');
    $form_data['video_url'] = trim($_POST['video_url'] ?? '');
    $form_data['thu_tu'] = (int)($_POST['thu_tu'] ?? 1);
    $form_data['thoi_luong_phut'] = (int)($_POST['thoi_luong_phut'] ?? 15);
    $form_data['id_khoa_hoc'] = (int)($_POST['id_khoa_hoc'] ?? 0);

    $errors = [];
    if (empty($form_data['tieu_de'])) $errors[] = 'Tiêu đề bài học không được để trống.';
    if ($form_data['id_khoa_hoc'] <= 0) $errors[] = 'Vui lòng chọn khóa học.';

    if (empty($errors)) {
        if ($is_edit) {
            $bh->sua($id, $form_data['tieu_de'], $form_data['noi_dung'],
                      $form_data['video_url'], $form_data['thu_tu'], $form_data['thoi_luong_phut']);
            $thong_bao = '<div class="alert alert-success">Cập nhật bài học thành công!</div>';
        } else {
            // Auto set thu_tu if empty
            if ($form_data['thu_tu'] <= 0) {
                $form_data['thu_tu'] = $bh->layThuTuLonNhat($form_data['id_khoa_hoc']) + 1;
            }
            $bh->them($form_data['id_khoa_hoc'], $form_data['tieu_de'],
                       $form_data['noi_dung'], $form_data['video_url'],
                       $form_data['thu_tu'], $form_data['thoi_luong_phut']);
            header('Location: index.php?khoa_hoc=' . $form_data['id_khoa_hoc'] . '?added=1');
            exit;
        }
    } else {
        $thong_bao = '<div class="alert alert-danger">' . implode(' ', $errors) . '</div>';
    }
}

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="tv-topbar">
    <div>
        <h1 class="h4 mb-0">
            <i class="fas fa-<?php echo $is_edit ? 'pen' : 'plus'; ?> me-2 text-success"></i>
            <?php echo $page_title; ?>
        </h1>
        <p class="text-muted small mb-0">
            <a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
        </p>
    </div>
</div>

<?php echo $thong_bao; ?>

<div class="card tv-stat-card" style="max-width:720px">
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-semibold">Tiêu đề bài học <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="tieu_de" required
                           value="<?php echo htmlspecialchars($form_data['tieu_de']); ?>"
                           placeholder="VD: Giới thiệu HTML cơ bản">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Khóa học <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_khoa_hoc" required>
                        <option value="">-- Chọn khóa học --</option>
                        <?php foreach ($khoa_list as $k): ?>
                            <option value="<?php echo $k['id']; ?>"
                                <?php if ($form_data['id_khoa_hoc'] == $k['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nội dung bài học</label>
                <textarea class="form-control" name="noi_dung" rows="8"
                          placeholder="Nhập nội dung bài học..."><?php
                          echo htmlspecialchars($form_data['noi_dung']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Link Video</label>
                <input type="url" class="form-control" name="video_url"
                       value="<?php echo htmlspecialchars($form_data['video_url']); ?>"
                       placeholder="https://www.youtube.com/embed/...">
                <div class="form-text">Dán link YouTube Embed hoặc link video trực tiếp.</div>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-semibold">Thứ tự</label>
                    <input type="number" class="form-control" name="thu_tu" min="1"
                           value="<?php echo $form_data['thu_tu']; ?>">
                </div>
                <div class="col-6 mb-4">
                    <label class="form-label fw-semibold">Thời lượng (phút)</label>
                    <input type="number" class="form-control" name="thoi_luong_phut" min="1"
                           value="<?php echo $form_data['thoi_luong_phut']; ?>">
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-secondary flex-fill">Hủy</a>
                <button type="submit" class="btn btn-success flex-fill">
                    <i class="fas fa-save me-1"></i><?php echo $is_edit ? 'Lưu thay đổi' : 'Thêm bài học'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
