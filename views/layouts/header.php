<?php
if (!isset($page_title)) {
    $page_title = SITE_NAME;
}
$auth = new Auth();
$nguoi_dung = $auth->layThongTinNguoiDung();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90d9;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --light-bg: #f8f9fa;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--light-bg);
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff !important;
        }
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s;
        }
        .nav-link:hover {
            color: #fff !important;
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .card-img-top {
            height: 180px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
        }
        .course-card .card-body {
            padding: 1.25rem;
        }
        .course-card .card-title {
            font-weight: 600;
            color: var(--secondary-color);
            min-height: 48px;
        }
        .badge-category {
            background-color: var(--primary-color);
            font-size: 0.75rem;
        }
        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-color);
        }
        .price-free {
            color: var(--success-color);
        }
        footer {
            background: var(--secondary-color);
            color: #fff;
            padding: 30px 0;
            margin-top: 50px;
        }
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            padding: 80px 0;
            margin-bottom: 40px;
        }
        .hero-section h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }
        .search-box {
            max-width: 500px;
            margin: 0 auto;
        }
        .search-box input {
            border-radius: 25px;
            padding: 12px 20px;
            border: none;
        }
        .search-box button {
            border-radius: 25px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo HOME_URL; ?>">
                <i class="fas fa-graduation-cap me-2"></i>E-LEARNING
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo HOME_URL; ?>">
                            <i class="fas fa-home me-1"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>/khoa-hoc/index.php">
                            <i class="fas fa-book me-1"></i> Khóa học
                        </a>
                    </li>
                    <?php if ($nguoi_dung): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_PATH; ?>/tien-do/index.php">
                                <i class="fas fa-chart-line me-1"></i> Tiến độ
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo $nguoi_dung['ho_ten']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/tai-khoan/ho-so.php">
                                    <i class="fas fa-id-card me-2"></i>Hồ sơ
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/tai-khoan/doi-mat-khau.php">
                                    <i class="fas fa-key me-2"></i>Đổi mật khẩu
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_PATH; ?>/tai-khoan/dang-xuat.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_PATH; ?>/tai-khoan/dang-nhap.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_PATH; ?>/tai-khoan/dang-ky.php">
                                <i class="fas fa-user-plus me-1"></i> Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main>
