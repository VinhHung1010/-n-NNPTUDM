<?php
if (!isset($page_title)) {
    $page_title = SITE_NAME;
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/auth.php';
require_once __DIR__ . '/../../models/thong_bao.php';
$auth = new Auth();
$nguoi_dung = $auth->layThongTinNguoiDung();
$thong_bao_model = new ThongBao();
$so_thong_bao_chua_doc = 0;
$ds_thong_bao = [];
if ($nguoi_dung) {
    $so_thong_bao_chua_doc = $thong_bao_model->demChuaDoc($nguoi_dung['id']);
    $ds_thong_bao = $thong_bao_model->layChuaDoc($nguoi_dung['id']);
}

// Active nav helper
$current_script = basename($_SERVER['SCRIPT_NAME']);
$current_path = $_SERVER['SCRIPT_NAME'] ?? '';
function isActive($pattern, $path = null) {
    $path = $path ?? $_SERVER['SCRIPT_NAME'];
    return strpos($path, $pattern) !== false ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-dark: #4338CA;
            --primary-light: #EEF2FF;
            --secondary: #0F172A;
            --accent: #F59E0B;
            --success: #10B981;
            --danger: #EF4444;
            --light: #F8FAFC;
            --muted: #64748B;
            --border: #E2E8F0;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light);
            color: var(--secondary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main { flex: 1; }

        /* ── Navbar ── */
        .navbar-elearn {
            background: var(--secondary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            padding: 0.75rem 0;
        }
        .navbar-elearn .navbar-brand {
            font-weight: 800;
            font-size: 1.3rem;
            color: #fff !important;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .navbar-elearn .brand-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .navbar-elearn .nav-link {
            color: rgba(255,255,255,0.75) !important;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 0.9rem !important;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .navbar-elearn .nav-link:hover,
        .navbar-elearn .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.1);
        }
        .navbar-elearn .nav-link.btn-nav-primary {
            background: var(--primary);
            color: #fff !important;
        }
        .navbar-elearn .nav-link.btn-nav-primary:hover {
            background: var(--primary-dark);
        }
        .navbar-elearn .nav-link.btn-nav-outline {
            border: 1.5px solid rgba(255,255,255,0.4);
            color: rgba(255,255,255,0.9) !important;
        }
        .navbar-elearn .nav-link.btn-nav-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: #fff;
        }
        .navbar-elearn .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            padding: 0.5rem;
            min-width: 200px;
        }
        .navbar-elearn .dropdown-item {
            border-radius: 8px;
            padding: 0.5rem 0.9rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .navbar-elearn .dropdown-item:hover {
            background: var(--primary-light);
            color: var(--primary);
        }
        .navbar-elearn .dropdown-divider { margin: 0.4rem 0; }
        .navbar-toggler {
            border: 1.5px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            padding: 6px 10px;
        }
        .navbar-toggler:focus { box-shadow: none; }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255,255,255,0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* ── Cards ── */
        .card {
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform 0.25s, box-shadow 0.25s;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(79,70,229,0.12);
        }
        .card-img-top {
            height: 185px;
            object-fit: cover;
            border-radius: 16px 16px 0 0;
        }
        .course-card .card-body { padding: 1.2rem; }
        .course-card .card-title {
            font-weight: 700;
            font-size: 0.95rem;
            line-height: 1.4;
            color: var(--secondary);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .course-card .card-text {
            font-size: 0.82rem;
            color: var(--muted);
        }
        .badge-category {
            background: var(--primary);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }
        .price-tag {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--danger);
        }
        .price-free {
            font-size: 1rem;
            font-weight: 700;
            color: var(--success);
        }

        /* ── Footer ── */
        .footer-elearn {
            background: var(--secondary);
            color: rgba(255,255,255,0.7);
            padding: 3rem 0 1.5rem;
            margin-top: auto;
        }
        .footer-elearn h5 {
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        .footer-elearn a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }
        .footer-elearn a:hover { color: #fff; }
        .footer-elearn .list-unstyled li { margin-bottom: 0.4rem; }
        .footer-brand {
            font-weight: 800;
            font-size: 1.1rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0.75rem;
        }
        .social-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            transition: all 0.2s;
            text-decoration: none;
        }
        .social-icon:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-2px);
        }

        /* ── Hero ── */
        .hero-section {
            background: linear-gradient(135deg, #1E1B4B 0%, #312E81 40%, #4F46E5 100%);
            color: #fff;
            padding: 72px 0 64px;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(79,70,229,0.3) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(245,158,11,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-section h1 {
            font-weight: 800;
            font-size: 2.4rem;
            line-height: 1.2;
        }
        .hero-section .lead {
            font-size: 1.1rem;
            opacity: 0.85;
            font-weight: 400;
        }
        .hero-search {
            max-width: 520px;
            margin: 0 auto;
            background: rgba(255,255,255,0.12);
            border-radius: 50px;
            padding: 6px;
            display: flex;
            border: 1.5px solid rgba(255,255,255,0.2);
        }
        .hero-search input {
            flex: 1;
            border: none;
            background: transparent;
            color: #fff;
            padding: 10px 20px;
            font-size: 0.95rem;
            outline: none;
        }
        .hero-search input::placeholder { color: rgba(255,255,255,0.55); }
        .hero-search button {
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 600;
            background: var(--accent);
            border: none;
            color: #fff;
            font-size: 0.9rem;
        }
        .hero-search button:hover { background: #D97706; }

        /* ── Misc ── */
        .section-title {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--secondary);
        }
        .badge {
            font-weight: 600;
            font-size: 0.75rem;
        }
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .btn-success { background: var(--success); border-color: var(--success); }
        .btn-success:hover { background: #059669; border-color: #059669; }
        .btn-accent {
            background: var(--accent);
            border: none;
            color: #fff;
            font-weight: 600;
        }
        .btn-accent:hover { background: #D97706; color: #fff; }
        .breadcrumb {
            font-size: 0.85rem;
        }
        .breadcrumb a { color: var(--primary); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .list-group-item { border-color: var(--border); }

        /* Stat cards */
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .hero-section h1 { font-size: 1.7rem; }
            .hero-section { padding: 48px 0 40px; }
        }

        /* ── Notifications ── */
        .badge-notification {
            position: absolute;
            top: -4px;
            right: -6px;
            background: #EF4444;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            border: 2px solid var(--secondary);
        }
        .notification-dropdown {
            width: 360px !important;
            max-height: 480px;
            padding: 0 !important;
            overflow: hidden;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--light);
        }
        .notification-header h6 {
            font-weight: 700;
            color: var(--secondary);
        }
        .mark-all-read {
            font-size: 0.78rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .mark-all-read:hover { text-decoration: underline; }
        .notification-list {
            max-height: 360px;
            overflow-y: auto;
        }
        .notification-item {
            display: flex;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        .notification-item:hover { background: var(--light); }
        .notification-item.unread { background: #EEF2FF; }
        .notification-item.unread:hover { background: #E0E7FF; }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .notification-content { flex: 1; min-width: 0; }
        .notification-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 2px;
            line-height: 1.3;
        }
        .notification-text {
            font-size: 0.78rem;
            color: var(--muted);
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .notification-time {
            font-size: 0.72rem;
            color: var(--muted);
        }
        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: var(--muted);
        }
        .notification-empty i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: 0.4;
        }
        .notification-empty p {
            font-size: 0.9rem;
            margin: 0;
        }
        .notification-footer {
            display: block;
            text-align: center;
            padding: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            border-top: 1px solid var(--border);
            background: var(--light);
        }
        .notification-footer:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
        }
        @media (max-width: 768px) {
            .notification-dropdown { width: 300px !important; }
        }
    </style>
</head>
<body>

<!-- ═══ NAVBAR ═══ -->
<nav class="navbar navbar-expand-lg navbar-elearn">
    <div class="container">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>/index.php">
            <span class="brand-icon"><i class="fas fa-graduation-cap"></i></span>
            E-Learning
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('index.php') && !isset($_GET['danh_muc']) && !isset($_GET['tu_khoa']) ? 'active' : ''; ?>"
                       href="<?php echo SITE_URL; ?>/index.php">
                        <i class="fas fa-home me-1"></i>Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('khoa-hoc'); ?>"
                       href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">
                        <i class="fas fa-book me-1"></i>Khóa học
                    </a>
                </li>

                <?php if ($nguoi_dung): ?>
                    <!-- Notification Bell -->
                    <li class="nav-item dropdown" id="notificationDropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationBell">
                            <i class="fas fa-bell"></i>
                            <?php if ($so_thong_bao_chua_doc > 0): ?>
                                <span class="position-absolute badge-notification" id="notificationBadge"><?php echo $so_thong_bao_chua_doc > 9 ? '9+' : $so_thong_bao_chua_doc; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="notificationMenu">
                            <div class="notification-header">
                                <h6 class="mb-0">Thông báo</h6>
                                <?php if ($so_thong_bao_chua_doc > 0): ?>
                                    <a href="#" class="mark-all-read" id="markAllRead">Đánh dấu tất cả đã đọc</a>
                                <?php endif; ?>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <?php if (empty($ds_thong_bao)): ?>
                                    <div class="notification-empty">
                                        <i class="fas fa-bell-slash"></i>
                                        <p>Không có thông báo nào</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($ds_thong_bao as $tb): ?>
                                        <a href="<?php echo $tb['duong_dan'] ? $tb['duong_dan'] : '#'; ?>" 
                                           class="notification-item <?php echo $tb['da_doc'] ? '' : 'unread'; ?>"
                                           data-id="<?php echo $tb['id']; ?>">
                                            <div class="notification-icon" style="background: <?php echo $thong_bao_model->getMauByLoai($tb['loai']); ?>20;">
                                                <i class="fas <?php echo $thong_bao_model->getIconByLoai($tb['loai']); ?>"></i>
                                            </div>
                                            <div class="notification-content">
                                                <p class="notification-title"><?php echo htmlspecialchars($tb['tieu_de']); ?></p>
                                                <p class="notification-text"><?php echo htmlspecialchars($tb['noi_dung'] ?? ''); ?></p>
                                                <span class="notification-time"><?php echo $thong_bao_model->getTimeAgo($tb['ngay_tao']); ?></span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo VIEWS_URL; ?>/thong-bao/index.php" class="notification-footer">
                                Xem tất cả thông báo
                            </a>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/home/index.php'); ?>"
                           href="<?php echo VIEWS_URL; ?>/home/index.php">
                            <i class="fas fa-chart-line me-1"></i>Tiến độ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/hoi-dap/'); ?>"
                           href="<?php echo VIEWS_URL; ?>/hoi-dap/index.php">
                            <i class="fas fa-comments me-1"></i>Hỏi đáp
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/huy-hieu/'); ?>"
                           href="<?php echo VIEWS_URL; ?>/huy-hieu/index.php">
                            <i class="fas fa-award me-1"></i>Huy hiệu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/yeu-thich/'); ?>"
                           href="<?php echo VIEWS_URL; ?>/yeu-thich/index.php">
                            <i class="fas fa-heart me-1"></i>Yêu thích
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-circle-user me-1"></i><?php echo htmlspecialchars($nguoi_dung['ho_ten']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (($nguoi_dung['vai_tro'] ?? '') === 'quan_tri'): ?>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/index.php">
                                    <i class="fas fa-shield-halved me-2 text-primary"></i>Trang quản trị
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <?php if (($nguoi_dung['vai_tro'] ?? '') === 'giao_vien'): ?>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/teacher/index.php">
                                    <i class="fas fa-chalkboard-teacher me-2 text-success"></i>Khu vực giảng viên
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo VIEWS_URL; ?>/chung-chi/xem.php">
                                <i class="fas fa-award me-2 text-warning"></i>Chứng chỉ của tôi
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo VIEWS_URL; ?>/tai-khoan/ho-so.php">
                                <i class="fas fa-id-card me-2 text-primary"></i>Hồ sơ
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo VIEWS_URL; ?>/tai-khoan/doi-mat-khau.php">
                                <i class="fas fa-key me-2 text-primary"></i>Đổi mật khẩu
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-xuat.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn-nav-outline" href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-nhap.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-nav-primary" href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-ky.php">
                            <i class="fas fa-user-plus me-1"></i>Đăng ký
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main>
