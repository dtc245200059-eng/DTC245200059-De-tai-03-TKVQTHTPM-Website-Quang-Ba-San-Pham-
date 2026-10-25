<?php
/**
 * Footer template for Website Quảng Bá Sản Phẩm Theme
 *
 * @package Website_Quang_Ba
 * @author Nông Quốc Tuấn - DTC245200059
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
</main><!-- #main-content -->

<footer class="site-footer" id="site-footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">
                <!-- Cột 1: Giới thiệu Thương hiệu & Đồ án -->
                <div class="footer-branding">
                    <div class="footer-logo">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.svg'); ?>" alt="<?php bloginfo('name'); ?>" width="200" height="44">
                    </div>
                    <p class="footer-desc">
                        Nền tảng website quảng bá thiết bị và giải pháp công nghệ thông minh thế hệ mới, tối ưu hóa trải nghiệm người dùng trên nền tảng hạ tầng container hóa hiện đại.
                    </p>
                    <p class="footer-desc" style="color: #94a3b8; font-size: 0.8125rem;">
                        <strong>Đề tài số 03:</strong> Triển khai và Quản trị Hệ thống Phần mềm<br>
                        <strong>Sinh viên thực hiện:</strong> Nông Quốc Tuấn (DTC245200059 - KTPM K23C)
                    </p>
                </div>

                <!-- Cột 2: Liên kết điều hướng nhanh -->
                <div class="footer-col">
                    <h3 class="footer-title"><?php esc_html_e('Điều Hướng', 'website-quang-ba'); ?></h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo is_front_page() ? '#hero' : esc_url(home_url('/#hero')); ?>" class="footer-link"><?php esc_html_e('Trang Chủ', 'website-quang-ba'); ?></a></li>
                        <li><a href="<?php echo is_front_page() ? '#san-pham' : esc_url(home_url('/#san-pham')); ?>" class="footer-link"><?php esc_html_e('Sản Phẩm Tiêu Biểu', 'website-quang-ba'); ?></a></li>
                        <li><a href="<?php echo is_front_page() ? '#tinh-nang' : esc_url(home_url('/#tinh-nang')); ?>" class="footer-link"><?php esc_html_e('Tính Năng Vượt Trội', 'website-quang-ba'); ?></a></li>
                        <li><a href="<?php echo is_front_page() ? '#ve-chung-toi' : esc_url(home_url('/#ve-chung-toi')); ?>" class="footer-link"><?php esc_html_e('Về Chúng Tôi', 'website-quang-ba'); ?></a></li>
                        <li><a href="<?php echo is_front_page() ? '#lien-he' : esc_url(home_url('/#lien-he')); ?>" class="footer-link"><?php esc_html_e('Liên Hệ & Báo Giá', 'website-quang-ba'); ?></a></li>
                    </ul>
                </div>

                <!-- Cột 3: Thông tin liên hệ & Demo -->
                <div class="footer-col">
                    <h3 class="footer-title"><?php esc_html_e('Hỗ Trợ Demo', 'website-quang-ba'); ?></h3>
                    <ul class="footer-links">
                        <li><span style="color: #cbd5e1;"><?php esc_html_e('Tên miền truy cập:', 'website-quang-ba'); ?></span> https://shop.local/</li>
                        <li><span style="color: #cbd5e1;"><?php esc_html_e('Bí danh cục bộ:', 'website-quang-ba'); ?></span> https://localhost/</li>
                        <li><span style="color: #cbd5e1;"><?php esc_html_e('Email demo:', 'website-quang-ba'); ?></span> contact@shop.local</li>
                        <li><span style="color: #cbd5e1;"><?php esc_html_e('Địa điểm:', 'website-quang-ba'); ?></span> Trường ĐH CNTT & Truyền Thông (ICTU)</li>
                    </ul>
                </div>

                <!-- Cột 4: Trạng thái Hạ tầng DevOps (Đặc thù đồ án) -->
                <div class="footer-col">
                    <h3 class="footer-title"><?php esc_html_e('Hạ Tầng DevOps', 'website-quang-ba'); ?></h3>
                    <div class="footer-badges-list">
                        <span class="tech-badge"><span class="tech-badge-dot"></span>Docker Compose</span>
                        <span class="tech-badge"><span class="tech-badge-dot"></span>Nginx Reverse Proxy</span>
                        <span class="tech-badge"><span class="tech-badge-dot"></span>HTTPS / TLS 1.3</span>
                        <span class="tech-badge"><span class="tech-badge-dot"></span>Prometheus Telemetry</span>
                        <span class="tech-badge"><span class="tech-badge-dot"></span>Grafana Dashboards</span>
                        <span class="tech-badge"><span class="tech-badge-dot"></span>Grafana Loki LogQL</span>
                        <span class="tech-badge"><span class="tech-badge-dot"></span>HAProxy Socket Proxy</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thanh bản quyền chân trang -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner">
                <div>
                    &copy; <?php echo date('Y'); ?> <strong>TechSmart</strong> — <?php bloginfo('name'); ?>. <?php esc_html_e('Mọi quyền được bảo lưu.', 'website-quang-ba'); ?>
                </div>
                <div style="color: #94a3b8;">
                    <?php esc_html_e('Dữ liệu mẫu phục vụ đồ án học phần môn Triển khai và Quản trị Hệ thống Phần mềm.', 'website-quang-ba'); ?>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- -----------------------------------------------------------------------
     PRODUCT QUICK VIEW MODAL (Tương tác xem nhanh thông số kỹ thuật)
------------------------------------------------------------------------ -->
<div class="modal-backdrop" id="product-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="modal-title">
    <div class="modal-container">
        <button class="modal-close-btn" id="modal-close-btn" aria-label="<?php esc_attr_e('Đóng hộp thoại', 'website-quang-ba'); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="modal-header">
            <div class="modal-category" id="modal-category"></div>
            <h2 class="modal-title" id="modal-title"></h2>
            <div class="modal-price" id="modal-price"></div>
        </div>
        <p class="modal-desc" id="modal-desc"></p>

        <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 12px; color: var(--color-slate-900);">
            <?php esc_html_e('Thông Số Kỹ Thuật Chi Tiết', 'website-quang-ba'); ?>
        </h3>
        <table class="modal-specs-table">
            <tbody id="modal-specs-body">
                <!-- Nội dung thông số kỹ thuật được render bằng JavaScript -->
            </tbody>
        </table>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="#lien-he" class="btn btn-primary" onclick="document.getElementById('product-modal').classList.remove('is-visible'); document.body.style.overflow='';">
                <?php esc_html_e('Đăng Ký Tư Vấn Sản Phẩm Này', 'website-quang-ba'); ?>
            </a>
        </div>
    </div>
</div>

<!-- Toast Container (Thông báo phản hồi tức thì) -->
<div class="toast-container" id="toast-container" aria-live="polite"></div>

<?php wp_footer(); ?>
</body>
</html>
