<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/huy_hieu.php';

$page_title = 'Huy hiệu - ' . SITE_NAME;
$auth = new Auth();

if (!$auth->kiemTraDangNhap()) {
    header('Location: ' . VIEWS_URL . '/tai-khoan/dang-nhap.php');
    exit;
}

$nguoi_dung = $auth->layThongTinNguoiDung();
$hh_model = new HuyHieu();

// Lấy badges của user
$badges_dat = $hh_model->layCuaNguoiDung($nguoi_dung['id']);
$badges_chua_dat = $hh_model->layChuaDat($nguoi_dung['id']);
$so_badges = $hh_model->demCuaNguoiDung($nguoi_dung['id']);
$tong_badges = count($hh_model->layTatCa());
$leaderboard = $hh_model->layLeaderboard(10);

include __DIR__ . '/../../views/layouts/header.php';
?>

<style>
.badges-page { padding: 2rem 0; }
.badges-header {
    background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4F46E5 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    text-align: center;
}
.badges-header h1 {
    font-weight: 800;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}
.badges-header .badge-count {
    font-size: 3rem;
    font-weight: 800;
    line-height: 1;
}
.badges-header .badge-count-label {
    font-size: 1rem;
    opacity: 0.8;
}
.badge-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}
.badge-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
}
.badge-card.locked {
    opacity: 0.5;
    filter: grayscale(0.8);
}
.badge-card.locked:hover {
    transform: none;
    box-shadow: none;
}
.badge-card.locked .badge-icon-wrap {
    filter: grayscale(1);
}
.badge-icon-wrap {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    transition: all 0.3s;
}
.badge-icon-wrap.gold {
    background: linear-gradient(135deg, #F59E0B, #EAB308);
    color: white;
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4);
}
.badge-icon-wrap.silver {
    background: linear-gradient(135deg, #94A3B8, #64748B);
    color: white;
    box-shadow: 0 4px 20px rgba(100, 116, 139, 0.4);
}
.badge-icon-wrap.bronze {
    background: linear-gradient(135deg, #D97706, #B45309);
    color: white;
    box-shadow: 0 4px 20px rgba(217, 119, 6, 0.4);
}
.badge-icon-wrap.special {
    background: linear-gradient(135deg, #FFD700, #FFA500, #FF6347);
    color: white;
    box-shadow: 0 4px 20px rgba(255, 215, 0, 0.5);
    animation: glow 2s ease-in-out infinite alternate;
}
@keyframes glow {
    from { box-shadow: 0 4px 20px rgba(255, 215, 0, 0.5); }
    to { box-shadow: 0 4px 40px rgba(255, 215, 0, 0.8), 0 0 60px rgba(255, 215, 0, 0.4); }
}
.badge-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--secondary);
    margin-bottom: 0.5rem;
}
.badge-desc {
    font-size: 0.8rem;
    color: var(--muted);
    line-height: 1.4;
}
.badge-loai {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 50px;
    margin-top: 0.5rem;
}
.badge-date {
    font-size: 0.72rem;
    color: var(--muted);
    margin-top: 0.5rem;
}
.badge-locked-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--muted);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}
.leaderboard-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1rem;
}
.leaderboard-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}
.leaderboard-item:last-child { border-bottom: none; }
.leaderboard-rank {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.leaderboard-rank.rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: white; }
.leaderboard-rank.rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); color: white; }
.leaderboard-rank.rank-3 { background: linear-gradient(135deg, #CD7F32, #A0522D); color: white; }
.leaderboard-rank.rank-other { background: var(--light); color: var(--muted); }
.leaderboard-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
}
.section-title-badge {
    font-weight: 800;
    font-size: 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<div class="container badges-page">

    <!-- Header -->
    <div class="badges-header">
        <h1>
            <i class="fas fa-award me-2"></i>Huy hiệu & Thành tựu
        </h1>
        <p class="opacity-75 mb-3">Thu thập huy hiệu để thể hiện thành tích của bạn!</p>
        <div class="badge-count"><?php echo $so_badges; ?></div>
        <div class="badge-count-label">/ <?php echo $tong_badges; ?> Huy hiệu đã đạt được</div>
        <div class="progress mt-3 mx-auto" style="max-width: 300px; height: 10px; border-radius: 99px;">
            <div class="progress-bar bg-warning" style="width: <?php echo $tong_badges > 0 ? ($so_badges / $tong_badges) * 100 : 0; ?>%; border-radius: 99px;"></div>
        </div>
    </div>

    <!-- Leaderboard -->
    <?php if (!empty($leaderboard)): ?>
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto">
                <div class="leaderboard-card">
                    <h5 class="mb-3 fw-bold">
                        <i class="fas fa-trophy me-2" style="color: #F59E0B;"></i>
                        Bảng xếp hạng Huy hiệu
                    </h5>
                    <?php foreach ($leaderboard as $i => $item): ?>
                        <?php
                        $rank_class = $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-other'));
                        ?>
                        <div class="leaderboard-item">
                            <div class="leaderboard-rank <?php echo $rank_class; ?>">
                                <?php echo $i + 1; ?>
                            </div>
                            <div class="leaderboard-avatar">
                                <?php echo mb_substr($item['ho_ten'], 0, 1, 'UTF-8'); ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small"><?php echo htmlspecialchars($item['ho_ten']); ?></div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning text-dark fw-bold">
                                    <i class="fas fa-award me-1"></i><?php echo $item['so_huy_hieu']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Huy hiệu đã đạt -->
    <?php if (!empty($badges_dat)): ?>
        <h4 class="section-title-badge" style="color: #10B981;">
            <i class="fas fa-check-circle"></i> Huy hiệu đã đạt được
        </h4>
        <div class="row g-3 mb-5">
            <?php foreach ($badges_dat as $badge): ?>
                <?php
                $is_special = $badge['loai'] === 'special';
                $icon_class = $is_special ? 'special' : 'gold';
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="badge-card">
                        <div class="badge-icon-wrap <?php echo $icon_class; ?>">
                            <i class="fas <?php echo $badge['icon'] ?? 'fa-award'; ?>"></i>
                        </div>
                        <div class="badge-title"><?php echo htmlspecialchars($badge['ten']); ?></div>
                        <div class="badge-desc"><?php echo htmlspecialchars($badge['mo_ta'] ?? ''); ?></div>
                        <div class="badge-loai" style="background: <?php echo $hh_model->getLoaiColor($badge['loai']); ?>20; color: <?php echo $hh_model->getLoaiColor($badge['loai']); ?>;">
                            <?php echo $hh_model->getLoaiLabel($badge['loai']); ?>
                        </div>
                        <div class="badge-date">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('d/m/Y', strtotime($badge['ngay_dat'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Huy hiệu chưa đạt -->
    <?php if (!empty($badges_chua_dat)): ?>
        <h4 class="section-title-badge" style="color: var(--muted);">
            <i class="fas fa-lock"></i> Huy hiệu đang theo đuổi
        </h4>
        <div class="row g-3">
            <?php foreach ($badges_chua_dat as $badge): ?>
                <?php
                $is_special = $badge['loai'] === 'special';
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="badge-card locked">
                        <div class="badge-locked-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="badge-icon-wrap" style="background: var(--muted); color: white;">
                            <i class="fas <?php echo $badge['icon'] ?? 'fa-award'; ?>"></i>
                        </div>
                        <div class="badge-title"><?php echo htmlspecialchars($badge['ten']); ?></div>
                        <div class="badge-desc"><?php echo htmlspecialchars($badge['mo_ta'] ?? ''); ?></div>
                        <div class="badge-loai" style="background: var(--muted); color: white;">
                            <?php echo $hh_model->getLoaiLabel($badge['loai']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../../views/layouts/footer.php'; ?>
