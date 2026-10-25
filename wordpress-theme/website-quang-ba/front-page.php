<?php
/**
 * Front Page Template for Website Quảng Bá Sản Phẩm Theme
 *
 * @package Website_Quang_Ba
 * @author Nông Quốc Tuấn - DTC245200059
 * @version 1.0.0
 */

get_header();

$sample_products = website_quang_ba_get_sample_products();
?>

<!-- ===========================================================================
     SECTION 1: HERO SHOWCASE BANNER
============================================================================ -->
<section class="hero-section" id="hero">
    <div class="container">
        <div class="hero-grid">
            <!-- Nội dung Hero bên trái -->
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    <span><?php esc_html_e('Đề Tài Số 03 — Hệ Sinh Thái Thiết Bị Số', 'website-quang-ba'); ?></span>
                </div>
                <h1 class="hero-title">
                    <?php esc_html_e('Hệ Sinh Thái Thiết Bị &', 'website-quang-ba'); ?>
                    <span class="hero-title-highlight"><?php esc_html_e('Công Nghệ Thông Minh', 'website-quang-ba'); ?></span>
                </h1>
                <p class="hero-lead">
                    <?php esc_html_e('Nền tảng quảng bá và trải nghiệm các thiết bị IoT, điều khiển thông minh và bảo mật biên thế hệ mới. Vận hành ổn định, an toàn và tối ưu trên nền tảng hạ tầng DevOps container hóa.', 'website-quang-ba'); ?>
                </p>
                <div class="hero-cta-group">
                    <a href="#san-pham" class="btn btn-primary">
                        <span><?php esc_html_e('Khám Phá Sản Phẩm', 'website-quang-ba'); ?></span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                    <a href="#tinh-nang" class="btn btn-secondary">
                        <span><?php esc_html_e('Tính Năng Vượt Trội', 'website-quang-ba'); ?></span>
                    </a>
                </div>
                <div class="hero-trust-bar">
                    <div class="hero-trust-item">
                        <svg class="hero-trust-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span><?php esc_html_e('Kiến Trúc Cô Lập Mạng', 'website-quang-ba'); ?></span>
                    </div>
                    <div class="hero-trust-item">
                        <svg class="hero-trust-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span><?php esc_html_e('Định Hướng Chuẩn Mở IoT', 'website-quang-ba'); ?></span>
                    </div>
                    <div class="hero-trust-item">
                        <svg class="hero-trust-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span><?php esc_html_e('Giám Sát Prometheus & Loki', 'website-quang-ba'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Hình ảnh mô phỏng Hero bên phải -->
            <div class="hero-visual">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-device.svg'); ?>" alt="<?php esc_attr_e('Mô phỏng thiết bị công nghệ thông minh', 'website-quang-ba'); ?>" class="hero-illustration" width="540" height="420">
            </div>
        </div>
    </div>
</section>

<!-- ===========================================================================
     SECTION 2: FEATURED PRODUCTS SHOWCASE
============================================================================ -->
<section class="products-section section-padding" id="san-pham">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow"><?php esc_html_e('Danh Mục Sản Phẩm', 'website-quang-ba'); ?></span>
            <h2 class="section-title"><?php esc_html_e('Sản Phẩm Tiêu Biểu & Đột Phá', 'website-quang-ba'); ?></h2>
            <p class="section-desc">
                <?php esc_html_e('Các thiết bị thông minh được thiết kế theo tiêu chuẩn công nghiệp hiện đại (Dữ liệu mẫu phục vụ trình diễn đồ án).', 'website-quang-ba'); ?>
            </p>
        </div>

        <!-- Bộ lọc danh mục sản phẩm (Category Filter Tabs) -->
        <div class="filter-bar" role="tablist" aria-label="<?php esc_attr_e('Lọc theo danh mục', 'website-quang-ba'); ?>">
            <button class="filter-btn active" data-filter="all" role="tab" aria-selected="true"><?php esc_html_e('Tất Cả', 'website-quang-ba'); ?></button>
            <button class="filter-btn" data-filter="thiet-bi-trung-tam" role="tab" aria-selected="false"><?php esc_html_e('Thiết Bị Trung Tâm', 'website-quang-ba'); ?></button>
            <button class="filter-btn" data-filter="cam-bien" role="tab" aria-selected="false"><?php esc_html_e('Cảm Biến', 'website-quang-ba'); ?></button>
            <button class="filter-btn" data-filter="bao-mat-mang" role="tab" aria-selected="false"><?php esc_html_e('Bảo Mật & Mạng', 'website-quang-ba'); ?></button>
            <button class="filter-btn" data-filter="camera-an-ninh" role="tab" aria-selected="false"><?php esc_html_e('Camera An Ninh', 'website-quang-ba'); ?></button>
            <button class="filter-btn" data-filter="nguon-phu-kien" role="tab" aria-selected="false"><?php esc_html_e('Nguồn & Phụ Kiện', 'website-quang-ba'); ?></button>
        </div>

        <!-- Lưới sản phẩm (Product Grid) -->
        <div class="products-grid">
            <?php foreach ($sample_products as $product) : ?>
                <article class="product-card"
                         data-category="<?php echo esc_attr($product['category_slug']); ?>"
                         data-name="<?php echo esc_attr($product['name']); ?>"
                         data-category-name="<?php echo esc_attr($product['category']); ?>"
                         data-price="<?php echo esc_attr($product['price']); ?>"
                         data-desc="<?php echo esc_attr($product['description']); ?>"
                         data-specs='<?php echo esc_attr(json_encode($product['specs'], JSON_UNESCAPED_UNICODE)); ?>'>

                    <div class="product-media">
                        <span class="product-badge <?php echo esc_attr($product['badge_type']); ?>">
                            <?php echo esc_html($product['badge']); ?>
                        </span>
                        <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>" class="product-image" loading="lazy" width="240" height="160">
                    </div>

                    <div class="product-body">
                        <div class="product-category"><?php echo esc_html($product['category']); ?></div>
                        <h3 class="product-title"><?php echo esc_html($product['name']); ?></h3>
                        <p class="product-summary"><?php echo esc_html($product['summary']); ?></p>

                        <!-- Xem nhanh 3 thông số kỹ thuật chính -->
                        <div class="product-specs-preview">
                            <?php
                            $count = 0;
                            foreach ($product['specs'] as $spec_key => $spec_val) :
                                if ($count++ >= 3) break;
                            ?>
                                <span class="spec-chip"><?php echo esc_html($spec_val); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="product-footer">
                            <div class="product-price">
                                <span class="price-label"><?php esc_html_e('Giá Niêm Yết', 'website-quang-ba'); ?></span>
                                <span class="price-value"><?php echo esc_html($product['price']); ?></span>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm btn-quick-view" aria-label="<?php echo esc_attr(sprintf(__('Xem chi tiết %s', 'website-quang-ba'), $product['name'])); ?>">
                                <?php esc_html_e('Xem Chi Tiết', 'website-quang-ba'); ?>
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===========================================================================
     SECTION 3: FEATURES & HIGHLIGHTS
============================================================================ -->
<section class="features-section section-padding" id="tinh-nang">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow"><?php esc_html_e('Ưu Thế Vượt Trội', 'website-quang-ba'); ?></span>
            <h2 class="section-title"><?php esc_html_e('Tại Sao Chọn Giải Pháp Của Chúng Tôi?', 'website-quang-ba'); ?></h2>
            <p class="section-desc">
                <?php esc_html_e('Kiến trúc công nghệ hướng tới sự bền bỉ, an toàn dữ liệu và khả năng mở rộng linh hoạt.', 'website-quang-ba'); ?>
            </p>
        </div>

        <div class="features-grid">
            <!-- Thẻ tính năng 1 -->
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h3 class="feature-title"><?php esc_html_e('Bảo Mật Hệ Thống Đa Tầng', 'website-quang-ba'); ?></h3>
                <p class="feature-desc">
                    <?php esc_html_e('Bảo vệ hệ thống với reverse proxy Nginx, mã hóa HTTPS SSL/TLS tự ký, phân tách mạng nội bộ và thu hẹp Linux capabilities.', 'website-quang-ba'); ?>
                </p>
            </div>

            <!-- Thẻ tính năng 2 -->
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 12h8"></path>
                        <path d="M12 8v8"></path>
                    </svg>
                </div>
                <h3 class="feature-title"><?php esc_html_e('Định Hướng Chuẩn Mở IoT', 'website-quang-ba'); ?></h3>
                <p class="feature-desc">
                    <?php esc_html_e('Mô phỏng kiến trúc kết nối mở, thuận tiện cho việc định hướng tích hợp với các nền tảng điều khiển thông minh phổ biến (Dữ liệu demo).', 'website-quang-ba'); ?>
                </p>
            </div>

            <!-- Thẻ tính năng 3 -->
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <h3 class="feature-title"><?php esc_html_e('Giám Sát & Telemetry Đã Tích Hợp', 'website-quang-ba'); ?></h3>
                <p class="feature-desc">
                    <?php esc_html_e('Hạ tầng đã tích hợp sẵn bộ thu thập chỉ số Prometheus, bảng điều khiển đồ họa Grafana và hệ thống truy vấn nhật ký tập trung Grafana Loki.', 'website-quang-ba'); ?>
                </p>
            </div>

            <!-- Thẻ tính năng 4 -->
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                    </svg>
                </div>
                <h3 class="feature-title"><?php esc_html_e('Mô Hình Xử Lý Cục Bộ On-Device', 'website-quang-ba'); ?></h3>
                <p class="feature-desc">
                    <?php esc_html_e('Định hướng kiến trúc xử lý dữ liệu và tự động hóa trực tiếp tại thiết bị biên, tăng cường tính chủ động và bảo vệ riêng tư (Dữ liệu demo).', 'website-quang-ba'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ===========================================================================
     SECTION 4: ABOUT / BRAND STORY
============================================================================ -->
<section class="about-section section-padding" id="ve-chung-toi">
    <div class="container">
        <div class="about-grid">
            <!-- Cột trái: Câu chuyện thương hiệu -->
            <div class="about-content">
                <span class="section-eyebrow"><?php esc_html_e('Về Chúng Tôi', 'website-quang-ba'); ?></span>
                <h2 class="section-title"><?php esc_html_e('Tiên Phong Công Nghệ Kết Nối Thông Minh', 'website-quang-ba'); ?></h2>
                <p class="about-text">
                    <?php esc_html_e('Dự án được xây dựng với mục tiêu mang đến giải pháp công nghệ hiện đại, kết hợp hài hòa giữa tính thẩm mỹ phần cứng và độ tin cậy của phần mềm hệ thống.', 'website-quang-ba'); ?>
                </p>
                <div class="about-pillars">
                    <div class="pillar-item">
                        <div class="pillar-bullet">1</div>
                        <div>
                            <h4 class="pillar-title"><?php esc_html_e('Nghiên Cứu & Phát Triển Chuẩn Mực', 'website-quang-ba'); ?></h4>
                            <p class="pillar-desc"><?php esc_html_e('Mỗi dòng sản phẩm được thử nghiệm nghiêm ngặt về độ bền nhiệt, khả năng kháng nhiễu và độ bảo mật firmware.', 'website-quang-ba'); ?></p>
                        </div>
                    </div>
                    <div class="pillar-item">
                        <div class="pillar-bullet">2</div>
                        <div>
                            <h4 class="pillar-title"><?php esc_html_e('Trải Nghiệm Người Dùng Tối Giản', 'website-quang-ba'); ?></h4>
                            <p class="pillar-desc"><?php esc_html_e('Giao diện trực quan, quy trình thiết lập đơn giản và thân thiện cho người dùng trải nghiệm mô hình hệ thống.', 'website-quang-ba'); ?></p>
                        </div>
                    </div>
                    <div class="pillar-item">
                        <div class="pillar-bullet">3</div>
                        <div>
                            <h4 class="pillar-title"><?php esc_html_e('Hạ Tầng Vận Hành DevOps Chuẩn Hóa', 'website-quang-ba'); ?></h4>
                            <p class="pillar-desc"><?php esc_html_e('Triển khai tự động hóa qua Docker Compose, cô lập mạng Docker API an toàn và kiểm soát tài nguyên tối ưu.', 'website-quang-ba'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Card đồ họa tóm tắt năng lực -->
            <div class="about-banner-side">
                <div class="about-card-banner">
                    <span class="about-banner-badge"><?php esc_html_e('Hạ Tầng Đồ Án', 'website-quang-ba'); ?></span>
                    <h3 class="about-banner-title"><?php esc_html_e('Hệ Thống Container & Giám Sát Tập Trung', 'website-quang-ba'); ?></h3>
                    <p class="about-banner-text">
                        <?php esc_html_e('Kiến trúc container hóa kết hợp giải pháp mã nguồn mở WordPress, MySQL và Nginx Reverse Proxy, được cấu hình kiểm soát tài nguyên và giám sát qua Prometheus & Loki.', 'website-quang-ba'); ?>
                    </p>
                    <div class="about-banner-stats">
                        <div>
                            <div class="banner-stat-num">12</div>
                            <div class="banner-stat-label"><?php esc_html_e('Dịch vụ container', 'website-quang-ba'); ?></div>
                        </div>
                        <div>
                            <div class="banner-stat-num">4</div>
                            <div class="banner-stat-label"><?php esc_html_e('Phân vùng mạng', 'website-quang-ba'); ?></div>
                        </div>
                        <div>
                            <div class="banner-stat-num">Demo</div>
                            <div class="banner-stat-label"><?php esc_html_e('Dữ liệu mô phỏng', 'website-quang-ba'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===========================================================================
     SECTION 5: CONTACT & CTA INQUIRY FORM
============================================================================ -->
<section class="contact-section section-padding" id="lien-he">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow"><?php esc_html_e('Kết Nối Với Chúng Tôi', 'website-quang-ba'); ?></span>
            <h2 class="section-title"><?php esc_html_e('Đăng Ký Nhận Tư Vấn & Báo Giá', 'website-quang-ba'); ?></h2>
            <p class="section-desc">
                <?php esc_html_e('Để lại thông tin để nhận tài liệu kỹ thuật chi tiết và demo giải pháp phù hợp với nhu cầu của bạn.', 'website-quang-ba'); ?>
            </p>
        </div>

        <div class="contact-card-wrapper">
            <!-- Form liên hệ bên trái -->
            <div class="contact-form-side">
                <form id="demo-contact-form" action="#" method="POST" novalidate>
                    <div class="form-group">
                        <label for="contact-name" class="form-label"><?php esc_html_e('Họ và Tên (*)', 'website-quang-ba'); ?></label>
                        <input type="text" id="contact-name" name="name" class="form-control" placeholder="<?php esc_attr_e('Ví dụ: Nguyễn Văn An', 'website-quang-ba'); ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="contact-email" class="form-label"><?php esc_html_e('Địa Chỉ Email (*)', 'website-quang-ba'); ?></label>
                            <input type="email" id="contact-email" name="email" class="form-control" placeholder="<?php esc_attr_e('email@domain.com', 'website-quang-ba'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="contact-phone" class="form-label"><?php esc_html_e('Số Điện Thoại (*)', 'website-quang-ba'); ?></label>
                            <input type="tel" id="contact-phone" name="phone" class="form-control" placeholder="<?php esc_attr_e('0912 345 678', 'website-quang-ba'); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact-product" class="form-label"><?php esc_html_e('Dòng Sản Phẩm Quan Tâm', 'website-quang-ba'); ?></label>
                        <select id="contact-product" name="product" class="form-control">
                            <option value="hub-pro"><?php esc_html_e('Smart Hub Pro X1 (Thiết Bị Trung Tâm)', 'website-quang-ba'); ?></option>
                            <option value="sensor"><?php esc_html_e('UltraSensor IoT Node S2 (Cảm Biến Đa Năng)', 'website-quang-ba'); ?></option>
                            <option value="gateway"><?php esc_html_e('CyberEdge Gateway G3 (Bảo Mật & Mạng)', 'website-quang-ba'); ?></option>
                            <option value="camera"><?php esc_html_e('VisionAI Cam 4K Ultra (Camera Trí Tuệ Nhân Tạo)', 'website-quang-ba'); ?></option>
                            <option value="ups"><?php esc_html_e('PowerGuard Mini UPS (Bộ Lưu Điện Thông Minh)', 'website-quang-ba'); ?></option>
                            <option value="tracker"><?php esc_html_e('SmartTag Track R2 (Định Vị UWB Chính Xác)', 'website-quang-ba'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contact-msg" class="form-label"><?php esc_html_e('Nội Dung Yêu Cầu / Câu Hỏi', 'website-quang-ba'); ?></label>
                        <textarea id="contact-msg" name="message" class="form-control" placeholder="<?php esc_attr_e('Chia sẻ nhu cầu triển khai thực tế của bạn...', 'website-quang-ba'); ?>"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <span><?php esc_html_e('Gửi Thử Nghiệm (Demo Giao Diện)', 'website-quang-ba'); ?></span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Khối thông tin liên hệ bên phải -->
            <div class="contact-info-side">
                <div>
                    <h3 class="info-title"><?php esc_html_e('Thông Tin Liên Hệ', 'website-quang-ba'); ?></h3>
                    <p class="info-desc">
                        <?php esc_html_e('Chúng tôi sẵn sàng hỗ trợ kỹ thuật và trình diễn trực tiếp các kịch bản kết nối thiết bị trong đồ án.', 'website-quang-ba'); ?>
                    </p>

                    <ul class="info-list">
                        <li class="info-item">
                            <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <div>
                                <div class="info-text-primary"><?php esc_html_e('Địa Điểm Triển Khai', 'website-quang-ba'); ?></div>
                                <div class="info-text-sub"><?php esc_html_e('Trường ĐH Công Nghệ Thông Tin & Truyền Thông — ĐH Thái Nguyên', 'website-quang-ba'); ?></div>
                            </div>
                        </li>
                        <li class="info-item">
                            <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <div>
                                <div class="info-text-primary"><?php esc_html_e('Email Hỗ Trợ Đồ Án', 'website-quang-ba'); ?></div>
                                <div class="info-text-sub">nongquoctuan@dtc245200059.edu.vn</div>
                            </div>
                        </li>
                        <li class="info-item">
                            <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <div>
                                <div class="info-text-primary"><?php esc_html_e('Cổng Thông Tin Bảo Mật', 'website-quang-ba'); ?></div>
                                <div class="info-text-sub">HTTPS (TLS 1.3) / Nginx Reverse Proxy / shop.local</div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="demo-note-box">
                    <strong><?php esc_html_e('Lưu ý đồ án (Demo UI):', 'website-quang-ba'); ?></strong>
                    <?php esc_html_e('Form liên hệ trên là thành phần mô phỏng giao diện. Do hệ thống chưa tích hợp backend tiếp nhận hay dịch vụ gửi email, dữ liệu sau khi nhập sẽ không được gửi hoặc lưu trữ tại máy chủ.', 'website-quang-ba'); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
