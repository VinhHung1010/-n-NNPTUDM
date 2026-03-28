
</main>

<!-- ═══ FOOTER ═══ -->
<footer class="footer-elearn">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">
                    <span style="width:36px;height:36px;background:var(--primary);border-radius:10px;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-graduation-cap" style="color:#fff"></i>
                    </span>
                    E-Learning Việt Nam
                </div>
                <p style="font-size:0.875rem;line-height:1.7;margin-bottom:1.2rem">
                    Nền tảng học trực tuyến hàng đầu Việt Nam. Học mọi lúc, mọi nơi với các khóa học chất lượng cao từ giảng viên uy tín.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2 col-md-3">
                <h5>Khám phá</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo SITE_URL; ?>/index.php">Trang chủ</a></li>
                    <li><a href="<?php echo VIEWS_URL; ?>/khoa-hoc/index.php">Khóa học</a></li>
                    <li><a href="<?php echo VIEWS_URL; ?>/home/index.php">Tiến độ học</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2 col-md-3">
                <h5>Tài khoản</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-nhap.php">Đăng nhập</a></li>
                    <li><a href="<?php echo VIEWS_URL; ?>/tai-khoan/dang-ky.php">Đăng ký</a></li>
                    <li><a href="<?php echo VIEWS_URL; ?>/tai-khoan/ho-so.php">Hồ sơ</a></li>
                    <li><a href="<?php echo VIEWS_URL; ?>/tai-khoan/doi-mat-khau.php">Đổi mật khẩu</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h5>Liên hệ</h5>
                <ul class="list-unstyled" style="font-size:0.875rem">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt me-2" style="width:16px"></i>
                        123 Điện Biên Phủ, Quận 3, TP.HCM
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2" style="width:16px"></i>
                        contact@elearning.vn
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2" style="width:16px"></i>
                        028 1234 5678
                    </li>
                    <li>
                        <i class="fas fa-clock me-2" style="width:16px"></i>
                        Thứ 2 - Thứ 6: 8:00 - 18:00
                    </li>
                </ul>
            </div>
        </div>

        <hr style="border-color:rgba(255,255,255,0.1);margin:2rem 0 1.5rem">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <p class="mb-0" style="font-size:0.82rem">
                &copy; <?php echo date('Y'); ?> E-Learning Việt Nam. Tất cả quyền được bảo lưu.
            </p>
            <div class="d-flex gap-3" style="font-size:0.82rem">
                <a href="#">Chính sách bảo mật</a>
                <a href="#">Điều khoản sử dụng</a>
                <a href="#">Hỗ trợ</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Notification AJAX handlers
document.addEventListener('DOMContentLoaded', function() {
    // Mark single notification as read
    document.querySelectorAll('.notification-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            const notifId = this.dataset.id;
            if (notifId && !this.classList.contains('read')) {
                fetch('<?php echo SITE_URL; ?>/api/thong-bao.php?action=mark_read&id=' + notifId)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            updateNotificationBadge(-1);
                            this.classList.remove('unread');
                        }
                    })
                    .catch(console.error);
            }
        });
    });

    // Mark all as read
    const markAllBtn = document.getElementById('markAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('<?php echo SITE_URL; ?>/api/thong-bao.php?action=mark_all_read')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.notification-item.unread').forEach(item => {
                            item.classList.remove('unread');
                        });
                        updateNotificationBadge(0);
                        const badge = document.getElementById('notificationBadge');
                        if (badge) badge.remove();
                        markAllBtn.remove();
                    }
                })
                .catch(console.error);
        });
    }

    function updateNotificationBadge(change) {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            let count = parseInt(badge.textContent);
            if (change === 0) {
                badge.remove();
            } else {
                count += change;
                if (count <= 0) {
                    badge.remove();
                } else {
                    badge.textContent = count > 9 ? '9+' : count;
                }
            }
        }
    }
});
</script>
</body>
</html>
