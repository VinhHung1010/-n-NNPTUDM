<?php
if (!isset($page_title)) {
    $page_title = 'Quản trị';
}
$admin_base = VIEWS_URL . '/admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> — <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --admin-sidebar: #1a1d29;
            --admin-sidebar-hover: #252936;
            --admin-accent: #5b8def;
            --admin-body: #f0f2f5;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background: var(--admin-body);
            min-height: 100vh;
        }
        .admin-sidebar {
            background: var(--admin-sidebar);
            min-height: 100vh;
            color: #e8eaed;
        }
        .admin-sidebar .brand {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            font-weight: 700;
            color: #fff;
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 0.65rem 1rem;
            border-radius: 8px;
            margin: 0.15rem 0.5rem;
        }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
            background: var(--admin-sidebar-hover);
            color: #fff;
        }
        .admin-sidebar .nav-link i { width: 1.35rem; }
        .admin-main { padding: 1.5rem; }
        .admin-topbar {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 1.5rem;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .stat-card .icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row g-0">
        <nav class="col-lg-2 col-md-3 admin-sidebar p-0 d-flex flex-column">
            <div class="brand">
                <i class="fas fa-shield-halved me-2 text-primary"></i>Quản trị
            </div>
            <ul class="nav flex-column py-2 flex-grow-1">
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo $admin_base; ?>/index.php">
                        <i class="fas fa-gauge-high me-2"></i>Tổng quan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">
                        <i class="fas fa-book me-2"></i>Khóa học (site)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo VIEWS_URL; ?>/home/index.php">
                        <i class="fas fa-house me-2"></i>Trang chủ công khai
                    </a>
                </li>
            </ul>
            <div class="p-3 border-top border-secondary border-opacity-25 small text-white-50">
                <?php echo htmlspecialchars($nguoi_dung_admin['ho_ten'] ?? ''); ?>
            </div>
        </nav>
        <div class="col-lg-10 col-md-9 admin-main">
