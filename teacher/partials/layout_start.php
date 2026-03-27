<?php
if (!isset($page_title)) $page_title = 'Giáo viên';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> — <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --tv-sidebar: #065f46;
            --tv-sidebar-hover: #047857;
            --tv-body: #f0fdf4;
            --tv-accent: #10B981;
            --tv-accent2: #059669;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--tv-body);
            min-height: 100vh;
            margin: 0;
        }
        .tv-sidebar {
            background: var(--tv-sidebar);
            min-height: 100vh;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            width: 240px;
            z-index: 1000;
            overflow-y: auto;
        }
        .tv-sidebar .brand {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            font-weight: 800;
            font-size: 1.05rem;
        }
        .tv-sidebar .brand i { color: #6ee7b7; }
        .tv-sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 0.65rem 1rem;
            border-radius: 8px;
            margin: 0.15rem 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .tv-sidebar .nav-link:hover,
        .tv-sidebar .nav-link.active {
            background: var(--tv-sidebar-hover);
            color: #fff;
        }
        .tv-sidebar .nav-link i { width: 1.35rem; }
        .tv-sidebar .sidebar-divider {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 0.5rem 1rem;
        }
        .tv-main { margin-left: 240px; padding: 1.5rem; }
        .tv-topbar {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 1.5rem;
        }
        .tv-stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .tv-stat-card .icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        @media (max-width: 768px) {
            .tv-sidebar { width: 0; overflow: hidden; }
            .tv-main { margin-left: 0; }
        }
    </style>
</head>
<body>
    <nav class="tv-sidebar">
        <div class="brand">
            <i class="fas fa-chalkboard-teacher me-2"></i>Giáo viên
        </div>
        <ul class="nav flex-column py-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? 'active' : ''; ?>"
                   href="<?php echo SITE_URL; ?>/teacher/index.php">
                    <i class="fas fa-gauge-high me-2"></i>Tổng quan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['SCRIPT_NAME'], 'khoa-hoc') !== false ? 'active' : ''; ?>"
                   href="<?php echo SITE_URL; ?>/teacher/khoa-hoc/index.php">
                    <i class="fas fa-book me-2"></i>Khóa học
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['SCRIPT_NAME'], 'bai-hoc') !== false ? 'active' : ''; ?>"
                   href="<?php echo SITE_URL; ?>/teacher/bai-hoc/index.php">
                    <i class="fas fa-file-lines me-2"></i>Bài học
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['SCRIPT_NAME'], 'quiz') !== false ? 'active' : ''; ?>"
                   href="<?php echo SITE_URL; ?>/teacher/quiz/index.php">
                    <i class="fas fa-circle-question me-2"></i>Quiz
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['SCRIPT_NAME'], 'hoc-vien') !== false ? 'active' : ''; ?>"
                   href="<?php echo SITE_URL; ?>/teacher/hoc-vien/index.php">
                    <i class="fas fa-user-graduate me-2"></i>Học viên
                </a>
            </li>

            <div class="sidebar-divider"></div>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>/views/home/index.php">
                    <i class="fas fa-house me-2"></i>Trang học viên
                </a>
            </li>
            <?php if (($nguoi_dung['vai_tro'] ?? '') === 'quan_tri'): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/index.php">
                    <i class="fas fa-shield-halved me-2"></i>Trang quản trị
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="p-3 border-top border-secondary border-opacity-25 small text-white-50 mt-auto">
            <i class="fas fa-chalkboard-teacher me-1"></i>
            <?php echo htmlspecialchars($nguoi_dung['ho_ten'] ?? ''); ?>
        </div>
    </nav>

    <div class="tv-main">
