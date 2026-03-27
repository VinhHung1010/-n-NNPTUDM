<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/auth.php';

$auth = new Auth();
if ($auth->laQuanTri()) {
    header('Location: ' . VIEWS_URL . '/admin/index.php');
    exit;
}

header('Location: ./views/home/index.php');
exit;
?>
