<?php
function lcs_latest_news_shortcode($atts) {
    ob_start();

    // Shortcode attributes
    $atts = shortcode_atts(array(
        'posts_per_page' => 10,
        'see_all_url' => site_url('/blog')
    ), $atts, 'latest_news');

    // Query latest posts
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $atts['posts_per_page'],
        'post_status' => 'publish'
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) :
        ?>
        <div class="lcs-latest-news">
            <div class="lcs-news-header">Latest News</div>
            <ul class="lcs-news-list">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <li class="lcs-news-item">
                        <a href="<?php the_permalink(); ?>" class="lcs-news-title"><?php the_title(); ?></a>
                        <div class="lcs-news-time">
                            <span class="lcs-clock-icon">🕒</span>
                            <?php
                            $post_time = get_post_time('U', true);
                            $time_diff = current_time('timestamp') - $post_time;
                            $hours = floor($time_diff / 3600);
                            $minutes = floor(($time_diff % 3600) / 60);

                            if ($hours < 1) {
                                echo $minutes . ' minutes ago';
                            } elseif ($hours < 24) {
                                echo $hours . ' hours ' . $minutes . ' minutes ago';
                            } else {
                                $days = floor($hours / 24);
                                $remaining_hours = $hours % 24;
                                echo $days . ' days ' . $remaining_hours . ' hours ago';
                            }
                            ?>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
            <div class="lcs-see-all">
                <a href="<?php echo esc_url($atts['see_all_url']); ?>" class="lcs-see-all-btn">See All</a>
            </div>
        </div>
        <?php
    endif;

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('latest_news', 'lcs_latest_news_shortcode');
