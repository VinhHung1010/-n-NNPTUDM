<?php
$page_title = 'Thông báo - E-Learning';
require_once __DIR__ . '/../layouts/header.php';

$auth = new Auth();
$nguoi_dung = $auth->layThongTinNguoiDung();

if (!$nguoi_dung) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$thong_bao_model = new ThongBao();
$ds_thong_bao = $thong_bao_model->layTatCa($nguoi_dung['id'], 50);
$so_chua_doc = $thong_bao_model->demChuaDoc($nguoi_dung['id']);
?>
<style>
.notification-page {
    padding: 2rem 0;
}
.notification-page .page-header {
    background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4F46E5 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
}
.notification-page .page-header h2 {
    font-weight: 800;
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}
.notification-page .page-header p {
    opacity: 0.8;
    font-size: 0.9rem;
    margin: 0;
}
.notification-page .filter-bar {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.notification-page .filter-btn {
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    border: 1.5px solid var(--border);
    background: white;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
}
.notification-page .filter-btn:hover,
.notification-page .filter-btn.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}
.notification-page .notif-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
    transition: all 0.2s;
    cursor: pointer;
}
.notification-page .notif-card:hover {
    box-shadow: 0 4px 16px rgba(79,70,229,0.1);
    transform: translateY(-1px);
}
.notification-page .notif-card.unread {
    background: #EEF2FF;
    border-color: #C7D2FE;
}
.notification-page .notif-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.notification-page .notif-body { flex: 1; min-width: 0; }
.notification-page .notif-title {
    font-weight: 700;
    font-size: 0.92rem;
    color: var(--secondary);
    margin-bottom: 4px;
}
.notification-page .notif-text {
    font-size: 0.85rem;
    color: var(--muted);
    margin-bottom: 6px;
    line-height: 1.5;
}
.notification-page .notif-meta {
    display: flex;
    gap: 12px;
    align-items: center;
    font-size: 0.78rem;
    color: var(--muted);
}
.notification-page .notif-actions {
    display: flex;
    gap: 6px;
    margin-top: 6px;
    opacity: 0;
    transition: opacity 0.2s;
}
.notification-page .notif-card:hover .notif-actions { opacity: 1; }
.notification-page .notif-action-btn {
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.notification-page .btn-delete {
    background: #FEE2E2;
    color: #EF4444;
}
.notification-page .btn-delete:hover { background: #FECACA; }
.notification-page .empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--muted);
}
.notification-page .empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}
.notification-page .empty-state h4 {
    font-weight: 700;
    color: var(--secondary);
    margin-bottom: 0.5rem;
}
.notification-page .empty-state p { font-size: 0.9rem; }
</style>

<div class="container notification-page">
    <div class="page-header">
        <h2><i class="fas fa-bell me-2"></i>Thông báo</h2>
        <p><?php echo $so_chua_doc > 0 ? "Bạn có $so_chua_doc thông báo chưa đọc" : "Tất cả thông báo của bạn"; ?></p>
    </div>

    <div class="filter-bar">
        <button class="filter-btn active" data-filter="all">Tất cả</button>
        <button class="filter-btn" data-filter="duyet_khoa">Duyệt khóa học</button>
        <button class="filter-btn" data-filter="hoan_thanh_khoa">Hoàn thành</button>
        <button class="filter-btn" data-filter="chung_chi">Chứng chỉ</button>
        <button class="filter-btn" data-filter="quiz">Bài kiểm tra</button>
        <button class="filter-btn" data-filter="he_thong">Hệ thống</button>
    </div>

    <?php if (empty($ds_thong_bao)): ?>
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h4>Không có thông báo nào</h4>
            <p>Các thông báo sẽ xuất hiện ở đây khi có cập nhật mới.</p>
        </div>
    <?php else: ?>
        <div id="notificationList">
            <?php foreach ($ds_thong_bao as $tb): ?>
                <div class="notif-card <?php echo $tb['da_doc'] ? '' : 'unread'; ?>" 
                     data-id="<?php echo $tb['id']; ?>"
                     data-loai="<?php echo $tb['loai']; ?>"
                     data-url="<?php echo $tb['duong_dan'] ? $tb['duong_dan'] : '#'; ?>">
                    <div class="notif-icon" style="background: <?php echo $thong_bao_model->getMauByLoai($tb['loai']); ?>20;">
                        <i class="fas <?php echo $thong_bao_model->getIconByLoai($tb['loai']); ?>" style="color: <?php echo $thong_bao_model->getMauByLoai($tb['loai']); ?>;"></i>
                    </div>
                    <div class="notif-body">
                        <p class="notif-title"><?php echo htmlspecialchars($tb['tieu_de']); ?></p>
                        <p class="notif-text"><?php echo htmlspecialchars($tb['noi_dung'] ?? ''); ?></p>
                        <div class="notif-meta">
                            <span><i class="fas fa-tag me-1"></i><?php echo $thong_bao_model->getLoaiText($tb['loai']); ?></span>
                            <span><i class="fas fa-clock me-1"></i><?php echo $thong_bao_model->getTimeAgo($tb['ngay_tao']); ?></span>
                            <?php if (!$tb['da_doc']): ?>
                                <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 6px;">Mới</span>
                            <?php endif; ?>
                        </div>
                        <div class="notif-actions">
                            <button class="notif-action-btn btn-delete" data-delete="<?php echo $tb['id']; ?>">
                                <i class="fas fa-trash me-1"></i>Xóa
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const SITE_URL = '<?php echo SITE_URL; ?>';

    // Click on notification card to mark as read and navigate
    document.querySelectorAll('.notif-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) return;
            const id = this.dataset.id;
            const url = this.dataset.url;
            if (!this.classList.contains('read') && id) {
                fetch(SITE_URL + '/api/thong-bao.php?action=mark_read&id=' + id)
                    .then(r => r.json())
                    .then(() => {
                        this.classList.remove('unread');
                        this.classList.add('read');
                        const badge = this.querySelector('.badge');
                        if (badge) badge.remove();
                    })
                    .catch(console.error);
            }
            if (url && url !== '#') {
                window.location.href = url;
            }
        });
    });

    // Delete notification
    document.querySelectorAll('[data-delete]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.delete;
            if (!confirm('Xóa thông báo này?')) return;
            fetch(SITE_URL + '/api/thong-bao.php?action=delete&id=' + id)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.notif-card[data-id="' + id + '"]').remove();
                        location.reload();
                    }
                })
                .catch(console.error);
        });
    });

    // Filter notifications
    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('.notif-card').forEach(function(card) {
                if (filter === 'all' || card.dataset.loai === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
