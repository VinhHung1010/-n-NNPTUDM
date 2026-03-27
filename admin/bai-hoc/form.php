<?php
$page_title = 'Thêm Bài học';
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/bai_hoc.php';
require_once dirname(__DIR__) . '/../models/khoa_hoc.php';

$bh = new BaiHoc();
$kh = new KhoaHoc();

$id        = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit   = $id > 0;
$khoa_hocs = $kh->layTatCaAdmin();
$thong_bao = '';

if ($is_edit) {
    $b = $bh->layTheoId($id);
    if (!$b) { header('Location: index.php'); exit; }
    $page_title  = 'Sửa Bài học';
    $form_data    = [
        'tieu_de'         => $b['tieu_de'],
        'noi_dung'        => $b['noi_dung'],
        'video_url'       => $b['video_url'],
        'thu_tu'          => $b['thu_tu'],
        'thoi_luong_phut' => $b['thoi_luong_phut'],
        'id_khoa_hoc'     => $b['id_khoa_hoc'],
    ];
} else {
    $form_data = [
        'tieu_de'         => '',
        'noi_dung'        => '',
        'video_url'       => '',
        'thu_tu'          => '',
        'thoi_luong_phut' => 15,
        'id_khoa_hoc'     => isset($_GET['khoa_hoc']) ? (int) $_GET['khoa_hoc'] : '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['tieu_de']         = trim($_POST['tieu_de'] ?? '');
    $form_data['noi_dung']        = trim($_POST['noi_dung'] ?? '');
    $form_data['video_url']       = trim($_POST['video_url'] ?? '');
    $form_data['thu_tu']          = (int)($_POST['thu_tu'] ?? 1);
    $form_data['thoi_luong_phut'] = (int)($_POST['thoi_luong_phut'] ?? 15);
    $form_data['id_khoa_hoc']     = (int)($_POST['id_khoa_hoc'] ?? 0);

    $errors = [];
    if (empty($form_data['tieu_de'])) {
        $errors[] = 'Tiêu đề bài học không được để trống.';
    }
    if ($form_data['id_khoa_hoc'] <= 0) {
        $errors[] = 'Vui lòng chọn khóa học.';
    }
    if ($form_data['thu_tu'] < 0) {
        $errors[] = 'Thứ tự phải >= 0.';
    }
    if ($form_data['thoi_luong_phut'] <= 0) {
        $errors[] = 'Thời lượng phải > 0.';
    }

    if (empty($errors)) {
        if ($is_edit) {
            $result = $bh->sua($id, $form_data['tieu_de'], $form_data['noi_dung'],
                                $form_data['video_url'], $form_data['thu_tu'],
                                $form_data['thoi_luong_phut']);
            if ($result['success']) {
                $thong_bao = '<div class="alert alert-success">Cập nhật bài học thành công!</div>';
            } else {
                $thong_bao = '<div class="alert alert-danger">' . $result['message'] . '</div>';
            }
        } else {
            $result = $bh->them($form_data['id_khoa_hoc'], $form_data['tieu_de'],
                                $form_data['noi_dung'], $form_data['video_url'],
                                $form_data['thu_tu'], $form_data['thoi_luong_phut']);
            if ($result['success']) {
                header('Location: index.php?them=1');
                exit;
            } else {
                $thong_bao = '<div class="alert alert-danger">' . $result['message'] . '</div>';
            }
        }
    } else {
        $thong_bao = '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
    }
}

include __DIR__ . '/../partials/layout_start.php';
?>

<div class="admin-topbar">
    <div>
        <h1 class="h4 mb-0">
            <i class="fas fa-<?php echo $is_edit ? 'pen' : 'plus'; ?> me-2 text-primary"></i>
            <?php echo $page_title; ?>
        </h1>
        <p class="text-muted small mb-0">
            <a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quay lại danh sách</a>
        </p>
    </div>
</div>

<?php echo $thong_bao; ?>

<div class="row g-4">
    <!-- ===== Form ===== -->
    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-info-circle me-1 text-primary"></i>Thông tin Bài học
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Khóa học <span class="text-danger">*</span></label>
                        <select name="id_khoa_hoc" class="form-select" required>
                            <option value="">-- Chọn khóa học --</option>
                            <?php
                            $current_cat = '';
                            foreach ($khoa_hocs as $k):
                                $label = ($k['ten_danh_muc'] ?? 'Khác') . ' — ' . $k['trang_thai'];
                                if ($label !== $current_cat) {
                                    if ($current_cat !== '') echo '</optgroup>';
                                    $label_dm = $k['ten_danh_muc'] ?? 'Khác';
                                    echo '<optgroup label="' . htmlspecialchars($label_dm) . '">';
                                    $current_cat = $label;
                                }
                            ?>
                                <option value="<?php echo $k['id']; ?>"
                                    <?php if ($form_data['id_khoa_hoc'] == $k['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($k['ten_khoa_hoc']); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_cat !== '') echo '</optgroup>'; ?>
                        </select>
                        <div class="form-text">Chỉ hiển thị khóa học đã duyệt.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiêu đề Bài học <span class="text-danger">*</span></label>
                        <input type="text" name="tieu_de" class="form-control"
                               value="<?php echo htmlspecialchars($form_data['tieu_de']); ?>"
                               placeholder="VD: Giới thiệu HTML" required maxlength="200">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nội dung</label>
                        <textarea name="noi_dung" class="form-control" rows="8"
                                  placeholder="Nội dung chi tiết bài học..."><?php
                                  echo htmlspecialchars($form_data['noi_dung']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Video URL</label>
                        <input type="url" name="video_url" class="form-control"
                               value="<?php echo htmlspecialchars($form_data['video_url']); ?>"
                               placeholder="https://youtube.com/watch?v=...">
                        <div class="form-text">Dán link YouTube/Vimeo để nhúng video vào bài học.</div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                            <input type="number" name="thu_tu" class="form-control" min="0"
                                   value="<?php echo $form_data['thu_tu']; ?>">
                            <div class="form-text">Số nhỏ hiển thị trước.</div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Thời lượng (phút)</label>
                            <input type="number" name="thoi_luong_phut" class="form-control" min="1"
                                   value="<?php echo $form_data['thoi_luong_phut']; ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i><?php echo $is_edit ? 'Lưu thay đổi' : 'Thêm Bài học'; ?>
                        </button>
                        <a href="index.php" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== Trợ giúp ===== -->
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-lightbulb me-1 text-warning"></i>Hướng dẫn
            </div>
            <div class="card-body">
                <ul class="mb-0 small text-muted ps-3">
                    <li class="mb-2">Bài học phải thuộc một khóa học đã được <strong>duyệt</strong>.</li>
                    <li class="mb-2">Thứ tự nhỏ → hiển thị trước trong danh sách bài học.</li>
                    <li class="mb-2">Video URL hỗ trợ YouTube, Vimeo hoặc link trực tiếp (.mp4).</li>
                    <li class="mb-2">Sau khi thêm bài học, bạn có thể tạo quiz cho bài học đó trong <a href="<?php echo SITE_URL; ?>/admin/quiz/form.php">Quản lý Quiz</a>.</li>
                    <li>Nội dung bài học hỗ trợ định dạng văn bản thuần túy.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
