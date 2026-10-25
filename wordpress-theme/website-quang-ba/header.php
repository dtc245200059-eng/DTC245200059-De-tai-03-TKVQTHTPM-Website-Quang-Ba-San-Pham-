<?php
/**
 * Header template for Website Quảng Bá Sản Phẩm Theme
 *
 * @package Website_Quang_Ba
 * @author Nông Quốc Tuấn - DTC245200059
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main-content">
    <?php esc_html_e('Chuyển đến nội dung chính', 'website-quang-ba'); ?>
</a>

<header class="site-header" id="site-header">
    <div class="container">
        <div class="header-inner">
            <!-- Logo & Thương hiệu -->
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo-link" rel="home">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.svg'); ?>" alt="<?php bloginfo('name'); ?>" class="site-logo-svg" width="220" height="48">
                    </a>
                <?php endif; ?>
            </div>

            <!-- Nút bật menu trên Mobile -->
            <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="<?php esc_attr_e('Mở menu điều hướng', 'website-quang-ba'); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <!-- Điều hướng Menu chính -->
            <nav class="site-nav" id="primary-nav" aria-label="<?php esc_attr_e('Điều hướng chính', 'website-quang-ba'); ?>">
                <?php
                if (has_nav_menu('primary-menu')) :
                    wp_nav_menu(array(
                        'theme_location' => 'primary-menu',
                        'container'      => false,
                        'menu_class'     => 'nav-menu',
                        'fallback_cb'    => false,
                        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    ));
                else :
                    // Fallback menu mặc định hướng đến các section của trang chủ
                ?>
                    <ul class="nav-menu">
                        <li><a href="<?php echo is_front_page() ? '#hero' : esc_url(home_url('/#hero')); ?>" class="nav-link"><?php esc_html_e('Trang Chủ', 'website-quang-ba'); ?></a></li>
                        <li><a href="<?php echo is_front_page() ? '#san-pham' : esc_url(home_url('/#san-pham')); ?>" class="nav-link"><?php esc_html_e('Sản Phẩm', 'website-quang-ba'); ?></a></li>
                        <li><a href="<?php echo is_front_page() ? '#tinh-nang' : esc_url(home_url('/#tinh-nang')); ?>" class="nav-link"><?php esc_html_e('Tính Năng', 'website-quang-ba'); ?></a></li>
                        <li><a href="<?php echo is_front_page() ? '#ve-chung-toi' : esc_url(home_url('/#ve-chung-toi')); ?>" class="nav-link"><?php esc_html_e('Về Chúng Tôi', 'website-quang-ba'); ?></a></li>
                        <li><a href="<?php echo is_front_page() ? '#lien-he' : esc_url(home_url('/#lien-he')); ?>" class="nav-link"><?php esc_html_e('Liên Hệ', 'website-quang-ba'); ?></a></li>
                    </ul>
                <?php endif; ?>
            </nav>

            <!-- Nút Hành động CTA góc Header -->
            <div class="header-actions">
                <a href="<?php echo is_front_page() ? '#lien-he' : esc_url(home_url('/#lien-he')); ?>" class="btn btn-primary btn-sm">
                    <span><?php esc_html_e('Tư Vấn Ngay', 'website-quang-ba'); ?></span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</header>
<main id="main-content" class="site-main">
