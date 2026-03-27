<?php
require_once __DIR__ . '/../bootstrap.php';
require_once dirname(__DIR__) . '/../models/danh_muc.php';

$dm = new DanhMuc();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $id > 0;

if ($is_edit) {
    $d = $dm->layTheoId($id);
    if (!$d) {
        header('Location: index.php');
        exit;
    }
    $page_title = 'Sửa Danh mục';
} else {
    $d = ['ten_danh_muc' => '', 'mo_ta' => ''];
    $page_title = 'Thêm Danh mục';
}

$thong_bao = '';
$old = ['ten_danh_muc' => '', 'mo_ta' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['ten_danh_muc'] = trim($_POST['ten_danh_muc'] ?? '');
    $old['mo_ta']        = trim($_POST['mo_ta'] ?? '');

    if (empty($old['ten_danh_muc'])) {
        $thong_bao = '<div class="alert alert-danger">Tên danh mục không được để trống.</div>';
    } else {
        if ($is_edit) {
            $dm->sua($id, $old['ten_danh_muc'], $old['mo_ta']);
            $thong_bao = '<div class="alert alert-success">Cập nhật danh mục thành công.</div>';
            $d = $dm->layTheoId($id); // refresh
        } else {
            $dm->them($old['ten_danh_muc'], $old['mo_ta']);
            header('Location: index.php?them=1');
            exit;
        }
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
            <a href="index.php" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
        </p>
    </div>
</div>

<?php echo $thong_bao; ?>

<div class="card stat-card" style="max-width: 640px;">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="ten_danh_muc" class="form-label fw-semibold">
                    Tên danh mục <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="ten_danh_muc" name="ten_danh_muc"
                       value="<?php echo htmlspecialchars($is_edit ? $d['ten_danh_muc'] : $old['ten_danh_muc']); ?>"
                       placeholder="VD: Lập trình Web" required maxlength="100">
            </div>

            <div class="mb-4">
                <label for="mo_ta" class="form-label fw-semibold">Mô tả</label>
                <textarea class="form-control" id="mo_ta" name="mo_ta" rows="3"
                          placeholder="Mô tả ngắn về danh mục (không bắt buộc)"><?php
                    echo htmlspecialchars($is_edit ? $d['mo_ta'] : $old['mo_ta']);
                ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i><?php echo $is_edit ? 'Lưu thay đổi' : 'Thêm danh mục'; ?>
                </button>
                <a href="index.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/layout_end.php'; ?>
