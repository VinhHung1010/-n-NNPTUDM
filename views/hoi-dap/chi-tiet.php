<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/hoi_dap.php';

$page_title = 'Chi tiết Hỏi đáp - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$qd_model = new HoiDap();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: ' . VIEWS_URL . '/hoi-dap/index.php');
    exit;
}

$cau_hoi = $qd_model->layTheoId($id);
if (!$cau_hoi) {
    header('Location: ' . VIEWS_URL . '/hoi-dap/index.php');
    exit;
}

$qd_model->tangLuotXem($id);
$tra_loi_list = $qd_model->layTraLoi($id);

include __DIR__ . '/../../views/layouts/header.php';
?>

<style>
.qa-detail-page { padding: 2rem 0; }
.qa-question-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.qa-answer-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.qa-answer-card.best-answer {
    border: 2px solid #D97706;
    background: #FFFBEB;
}
.qa-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
}
.qa-meta {
    font-size: 0.8rem;
    color: var(--muted);
}
.qa-action-btn {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.qa-reply-form {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
    margin-top: 1.5rem;
}
</style>

<div class="container qa-detail-page">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/chi-tiet.php?id=<?php echo $cau_hoi['id_khoa_hoc']; ?>"><?php echo htmlspecialchars($cau_hoi['ten_khoa_hoc']); ?></a></li>
            <li class="breadcrumb-item"><a href="<?php echo VIEWS_URL; ?>/bai-hoc/chi-tiet.php?id=<?php echo $cau_hoi['id_bai_hoc']; ?>"><?php echo htmlspecialchars($cau_hoi['ten_bai_hoc']); ?></a></li>
            <li class="breadcrumb-item active">Hỏi đáp</li>
        </ol>
    </nav>

    <!-- Câu hỏi -->
    <?php
    $role_color = $cau_hoi['vai_tro'] === 'quan_tri' ? 'danger' : ($cau_hoi['vai_tro'] === 'giao_vien' ? 'success' : 'primary');
    $role_label = $cau_hoi['vai_tro'] === 'giao_vien' ? 'GV' : ($cau_hoi['vai_tro'] === 'quan_tri' ? 'Admin' : 'HV');
    $da_thich_q = $qd_model->daThichCauHoi($nguoi_dung['id'], $cau_hoi['id']);
    ?>
    <div class="qa-question-card">
        <div class="d-flex gap-3">
            <div class="qa-avatar" style="background: var(--primary);">
                <?php echo mb_substr($cau_hoi['ho_ten'], 0, 1, 'UTF-8'); ?>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <strong><?php echo htmlspecialchars($cau_hoi['ho_ten']); ?></strong>
                        <span class="badge bg-<?php echo $role_color; ?> ms-1" style="font-size: 0.65rem;"><?php echo $role_label; ?></span>
                        <span class="text-muted ms-2 qa-meta">
                            <i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($cau_hoi['ngay_tao'])); ?>
                        </span>
                    </div>
                    <?php if ($cau_hoi['trang_thai'] === 'da_tra_loi'): ?>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Đã trả lời</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Chờ trả lời</span>
                    <?php endif; ?>
                </div>

                <h4 class="mb-3" style="font-weight: 700; color: var(--secondary);">
                    <?php echo htmlspecialchars($cau_hoi['tieu_de']); ?>
                </h4>

                <div class="mb-3" style="font-size: 1rem; line-height: 1.7; color: var(--secondary);">
                    <?php echo nl2br(htmlspecialchars($cau_hoi['noi_dung'])); ?>
                </div>

                <div class="d-flex gap-3 flex-wrap align-items-center">
                    <button class="qa-action-btn <?php echo $da_thich_q ? 'btn-primary' : 'btn-outline-secondary'; ?> qa-like-btn"
                            data-id="<?php echo $cau_hoi['id']; ?>" data-type="question">
                        <i class="fas fa-thumbs-up me-1"></i>
                        Thích <span class="like-count"><?php echo $cau_hoi['luot_thich']; ?></span>
                    </button>
                    <span class="qa-meta">
                        <i class="fas fa-eye me-1"></i><?php echo $cau_hoi['luot_xem']; ?> lượt xem
                    </span>
                    <span class="qa-meta">
                        <i class="fas fa-comment me-1"></i><?php echo count($tra_loi_list); ?> câu trả lời
                    </span>
                    <?php if ($cau_hoi['id_nguoi_hoi'] == $nguoi_dung['id']): ?>
                        <button class="qa-action-btn btn-outline-danger ms-auto" id="btnDeleteQuestion">
                            <i class="fas fa-trash me-1"></i>Xóa câu hỏi
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách câu trả lời -->
    <h5 class="mb-3" style="font-weight: 700;">
        <i class="fas fa-reply me-2" style="color: var(--accent);"></i>
        <?php echo count($tra_loi_list); ?> Câu trả lời
    </h5>

    <?php if (empty($tra_loi_list)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-comment-slash fa-3x mb-3"></i>
            <h5>Chưa có câu trả lời nào</h5>
            <p>Hãy là người đầu tiên trả lời câu hỏi này!</p>
        </div>
    <?php else: ?>
        <?php foreach ($tra_loi_list as $tl): ?>
            <?php
            $role_color2 = $tl['vai_tro'] === 'quan_tri' ? 'danger' : ($tl['vai_tro'] === 'giao_vien' ? 'success' : 'primary');
            $role_label2 = $tl['vai_tro'] === 'giao_vien' ? 'GV' : ($tl['vai_tro'] === 'quan_tri' ? 'Admin' : 'HV');
            $da_thich_tl = $qd_model->daThichTraLoi($nguoi_dung['id'], $tl['id']);
            ?>
            <div class="qa-answer-card <?php echo $tl['la_cau_tra_tot_nhat'] ? 'best-answer' : ''; ?>" id="answer-<?php echo $tl['id']; ?>">
                <div class="d-flex gap-3">
                    <div class="qa-avatar" style="background: var(--muted); width: 36px; height: 36px; font-size: 0.85rem;">
                        <?php echo mb_substr($tl['ho_ten'], 0, 1, 'UTF-8'); ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <strong class="small"><?php echo htmlspecialchars($tl['ho_ten']); ?></strong>
                                <span class="badge bg-<?php echo $role_color2; ?> ms-1" style="font-size: 0.6rem;"><?php echo $role_label2; ?></span>
                                <span class="text-muted ms-2 qa-meta"><?php echo date('d/m/Y H:i', strtotime($tl['ngay_tao'])); ?></span>
                            </div>
                            <?php if ($tl['la_cau_tra_tot_nhat']): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i>Câu trả lời hay nhất
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-2" style="font-size: 0.95rem; line-height: 1.7;">
                            <?php echo nl2br(htmlspecialchars($tl['noi_dung'])); ?>
                        </div>

                        <div class="d-flex gap-3 flex-wrap align-items-center">
                            <button class="qa-action-btn <?php echo $da_thich_tl ? 'btn-primary' : 'btn-outline-secondary'; ?> qa-like-btn"
                                    data-id="<?php echo $tl['id']; ?>" data-type="answer">
                                <i class="fas fa-thumbs-up me-1"></i><?php echo $tl['so_thich']; ?>
                            </button>
                            <?php if ($cau_hoi['id_nguoi_hoi'] == $nguoi_dung['id'] && !$tl['la_cau_tra_tot_nhat']): ?>
                                <button class="qa-action-btn btn-outline-warning qa-mark-best" 
                                        data-question="<?php echo $cau_hoi['id']; ?>" data-answer="<?php echo $tl['id']; ?>">
                                    <i class="fas fa-star me-1"></i>Đánh dấu hay nhất
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Form trả lời -->
    <div class="qa-reply-form">
        <h5 class="mb-3" style="font-weight: 700;">
            <i class="fas fa-pen me-2 text-primary"></i>Viết câu trả lời của bạn
        </h5>
        <form id="formReply">
            <input type="hidden" name="id_cau_hoi" value="<?php echo $cau_hoi['id']; ?>">
            <div class="mb-3">
                <textarea name="noi_dung" class="form-control" rows="5"
                          placeholder="Viết câu trả lời chi tiết và rõ ràng..." required></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="alert alert-info small mb-0 py-2">
                    <i class="fas fa-lightbulb me-1"></i>
                    Trả lời chi tiết và chính xác để nhận được đánh dấu "Câu trả lời hay nhất"!
                </div>
                <button type="submit" class="btn btn-primary" id="btnSubmitReply">
                    <i class="fas fa-paper-plane me-1"></i>Gửi câu trả lời
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const SITE_URL = '<?php echo SITE_URL; ?>';

    // Submit reply
    const formReply = document.getElementById('formReply');
    if (formReply) {
        formReply.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(formReply);
            const btn = document.getElementById('btnSubmitReply');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang gửi...';

            fetch(SITE_URL + '/api/hoi-dap.php?action=create_answer', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    formReply.reset();
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            })
            .catch(err => alert('Có lỗi: ' + err))
            .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi câu trả lời'; });
        });
    }

    // Like buttons
    document.querySelectorAll('.qa-like-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            fetch(SITE_URL + '/api/hoi-dap.php?action=like&id=' + id + '&type=' + type)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (type === 'question') {
                            const countSpan = this.querySelector('.like-count');
                            if (data.liked) {
                                this.classList.remove('btn-outline-secondary');
                                this.classList.add('btn-primary');
                                countSpan.textContent = parseInt(countSpan.textContent) + 1;
                            } else {
                                this.classList.remove('btn-primary');
                                this.classList.add('btn-outline-secondary');
                                countSpan.textContent = Math.max(0, parseInt(countSpan.textContent) - 1);
                            }
                        } else {
                            const current = parseInt(this.textContent.trim()) || 0;
                            if (data.liked) {
                                this.classList.remove('btn-outline-secondary');
                                this.classList.add('btn-primary');
                                this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>' + (current + 1);
                            } else {
                                this.classList.remove('btn-primary');
                                this.classList.add('btn-outline-secondary');
                                this.innerHTML = '<i class="fas fa-thumbs-up me-1"></i>' + Math.max(0, current - 1);
                            }
                        }
                    }
                })
                .catch(console.error);
        });
    });

    // Mark best answer
    document.querySelectorAll('.qa-mark-best').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const qid = this.dataset.question;
            const aid = this.dataset.answer;
            if (!confirm('Đánh dấu đây là câu trả lời hay nhất?')) return;
            const formData = new FormData();
            formData.append('id_cau_hoi', qid);
            formData.append('id_tra_loi', aid);
            fetch(SITE_URL + '/api/hoi-dap.php?action=mark_best_answer', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message);
            })
            .catch(console.error);
        });
    });

    // Delete question
    const btnDelQ = document.getElementById('btnDeleteQuestion');
    if (btnDelQ) {
        btnDelQ.addEventListener('click', function() {
            if (!confirm('Xóa câu hỏi này?')) return;
            fetch(SITE_URL + '/api/hoi-dap.php?action=delete_question&id=<?php echo $cau_hoi['id']; ?>')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '<?php echo VIEWS_URL; ?>/hoi-dap/index.php';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(console.error);
        });
    }
});
</script>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
