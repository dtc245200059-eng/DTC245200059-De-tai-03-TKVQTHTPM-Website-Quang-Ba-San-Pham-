<?php
/**
 * Page template for Website Quảng Bá Sản Phẩm Theme
 *
 * @package Website_Quang_Ba
 * @author Nông Quốc Tuấn - DTC245200059
 * @version 1.0.0
 */

get_header();
?>

<div class="page-wrapper">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="page-header">
                    <h1 class="page-title"><?php the_title(); ?></h1>
                </header>

                <div class="page-content" style="background: #ffffff; padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
                    <?php if (has_post_thumbnail()) : ?>
                        <div style="margin-bottom: 28px; border-radius: var(--radius-md); overflow: hidden;">
                            <?php the_post_thumbnail('full'); ?>
                        </div>
                    <?php endif; ?>

                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php
get_footer();
