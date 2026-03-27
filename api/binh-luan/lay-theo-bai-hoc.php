<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/binh_luan.php';
require_once __DIR__ . '/../../models/auth.php';

$bai_hoc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bai_hoc_id <= 0) {
    echo '<div class="alert alert-danger py-2">ID bài học không hợp lệ!</div>';
    exit;
}

$auth = new Auth();
$nguoi_dung = $auth->layThongTinNguoiDung();
$bl_model = new BinhLuan();
$binh_luan_list = $bl_model->layTheoBaiHoc($bai_hoc_id);

if (empty($binh_luan_list)): ?>
    <div class="text-center text-muted py-4" id="no-comments">
        <i class="fas fa-comment-dots fa-2x mb-2 opacity-25"></i>
        <p class="mb-0">Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
    </div>
<?php else: ?>
    <div class="list-group list-group-flush">
        <?php foreach ($binh_luan_list as $bl): ?>
            <div class="list-group-item px-0 py-3 border-0 border-bottom" id="comment-<?php echo $bl['id']; ?>">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <?php if (!empty($bl['anh_dai_dien'])): ?>
                            <img src="<?php echo htmlspecialchars($bl['anh_dai_dien']); ?>" 
                                 class="rounded-circle" width="42" height="42" alt="Avatar">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                 style="width:42px;height:42px;font-weight:700;font-size:1rem">
                                <?php echo mb_substr($bl['ho_ten'], 0, 1, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                            <div>
                                <span class="fw-semibold"><?php echo htmlspecialchars($bl['ho_ten']); ?></span>
                                <?php if ($bl['vai_tro'] === 'quan_tri'): ?>
                                    <span class="badge bg-danger ms-1" style="font-size:0.65rem">Quản trị</span>
                                <?php elseif ($bl['vai_tro'] === 'giao_vien'): ?>
                                    <span class="badge bg-success ms-1" style="font-size:0.65rem">Giáo viên</span>
                                <?php endif; ?>
                                <span class="text-muted ms-2" style="font-size:0.8rem">
                                    <i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($bl['ngay_tao'])); ?>
                                </span>
                            </div>
                            <?php if ($nguoi_dung && ($nguoi_dung['id'] == $bl['id_nguoi_dung'] || $nguoi_dung['vai_tro'] === 'quan_tri')): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if ($nguoi_dung['id'] == $bl['id_nguoi_dung']): ?>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" 
                                                   onclick="editComment(<?php echo $bl['id']; ?>, '<?php echo htmlspecialchars(addslashes($bl['noi_dung'])); ?>')">
                                                    <i class="fas fa-edit me-2 text-primary"></i>Sửa
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0)" 
                                               onclick="deleteComment(<?php echo $bl['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>Xóa
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="mb-0 mt-1 text-secondary" style="font-size:0.95rem;line-height:1.6">
                            <?php echo nl2br(htmlspecialchars($bl['noi_dung'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
