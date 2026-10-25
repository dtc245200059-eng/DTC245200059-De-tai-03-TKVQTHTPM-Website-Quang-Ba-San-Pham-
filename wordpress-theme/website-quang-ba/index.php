<?php
/**
 * Main template file for Website Quảng Bá Sản Phẩm Theme
 *
 * @package Website_Quang_Ba
 * @author Nông Quốc Tuấn - DTC245200059
 * @version 1.0.0
 */

get_header();
?>

<div class="page-wrapper">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">
                <?php
                if (is_home() && !is_front_page()) {
                    single_post_title();
                } elseif (is_archive()) {
                    the_archive_title();
                } elseif (is_search()) {
                    printf(esc_html__('Kết quả tìm kiếm cho: %s', 'website-quang-ba'), '<span>' . get_search_query() . '</span>');
                } else {
                    esc_html_e('Tin Tức & Hoạt Động', 'website-quang-ba');
                }
                ?>
            </h1>
        </header>

        <div class="page-content">
            <?php if (have_posts()) : ?>
                <div class="products-grid" style="margin-bottom: 40px;">
                    <?php while (have_posts()) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('product-card'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="product-media">
                                    <?php the_post_thumbnail('medium_large', array('class' => 'product-image', 'loading' => 'lazy')); ?>
                                </div>
                            <?php endif; ?>

                            <div class="product-body">
                                <div class="product-category"><?php echo get_the_date(); ?></div>
                                <h2 class="product-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <div class="product-summary">
                                    <?php the_excerpt(); ?>
                                </div>
                                <div class="product-footer">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-secondary btn-sm">
                                        <?php esc_html_e('Đọc Tiếp', 'website-quang-ba'); ?> &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <div class="pagination-wrapper" style="text-align: center; margin: 40px 0;">
                    <?php
                    the_posts_pagination(array(
                        'mid_size'  => 2,
                        'prev_text' => __('&larr; Trang Trước', 'website-quang-ba'),
                        'next_text' => __('Trang Sau &rarr;', 'website-quang-ba'),
                    ));
                    ?>
                </div>

            <?php else : ?>
                <div style="background: #ffffff; padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); text-align: center;">
                    <h2 style="font-size: 1.5rem; margin-bottom: 12px;"><?php esc_html_e('Chưa Có Bài Viết Nào', 'website-quang-ba'); ?></h2>
                    <p style="color: var(--color-text-muted); margin-bottom: 24px;">
                        <?php esc_html_e('Hiện tại chưa có nội dung bài viết nào trong chuyên mục này.', 'website-quang-ba'); ?>
                    </p>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                        <?php esc_html_e('Quay Lại Trang Chủ', 'website-quang-ba'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
get_footer();
