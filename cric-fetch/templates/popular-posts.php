<?php
function lcs_popular_posts_shortcode($atts) {
    ob_start();

    // Shortcode attributes
    $atts = shortcode_atts(array(
        'number' => 5,
    ), $atts, 'popular_posts');

    // Helper function to get recent posts
    function lcs_get_recent_posts($days, $limit, $random = false) {
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => $limit,
            'date_query'     => array(
                array(
                    'after' => date('Y-m-d', strtotime("-$days days")),
                    'inclusive' => true,
                ),
            ),
            'orderby'        => $random ? 'rand' : 'date',
            'order'          => 'DESC',
        );
        return new WP_Query($args);
    }
    ?>

    <div class="lcs-popular-widget">
        <div class="lcs-widget-header">Most Popular</div>

        <div class="lcs-tabs">
            <button class="lcs-tab active" data-target="today">Today</button>
            <button class="lcs-tab" data-target="week">Last 7 Days</button>
            <button class="lcs-tab" data-target="month">Last 30 Days</button>
        </div>

        <!-- Today -->
        <div class="lcs-tab-content active" id="lcs-tab-today">
            <?php $today = lcs_get_recent_posts(1, $atts['number']);
            $i = 1;
            if ($today->have_posts()) : ?>
                <ul class="lcs-post-list">
                    <?php while ($today->have_posts()) : $today->the_post(); ?>
                        <li>
                            <span class="lcs-post-rank"><?php echo $i++; ?></span>
                            <a href="<?php the_permalink(); ?>" class="lcs-post-title"><?php the_title(); ?></a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No posts found.</p>
            <?php endif; wp_reset_postdata(); ?>
        </div>

        <!-- Week (Randomized) -->
        <div class="lcs-tab-content" id="lcs-tab-week">
            <?php $week = lcs_get_recent_posts(7, $atts['number'], true);
            $i = 1;
            if ($week->have_posts()) : ?>
                <ul class="lcs-post-list">
                    <?php while ($week->have_posts()) : $week->the_post(); ?>
                        <li>
                            <span class="lcs-post-rank"><?php echo $i++; ?></span>
                            <a href="<?php the_permalink(); ?>" class="lcs-post-title"><?php the_title(); ?></a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No posts found.</p>
            <?php endif; wp_reset_postdata(); ?>
        </div>

        <!-- Month (Randomized) -->
        <div class="lcs-tab-content" id="lcs-tab-month">
            <?php $month = lcs_get_recent_posts(30, $atts['number'], true);
            $i = 1;
            if ($month->have_posts()) : ?>
                <ul class="lcs-post-list">
                    <?php while ($month->have_posts()) : $month->the_post(); ?>
                        <li>
                            <span class="lcs-post-rank"><?php echo $i++; ?></span>
                            <a href="<?php the_permalink(); ?>" class="lcs-post-title"><?php the_title(); ?></a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No posts found.</p>
            <?php endif; wp_reset_postdata(); ?>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function(){
        const tabs = document.querySelectorAll(".lcs-tab");
        const contents = document.querySelectorAll(".lcs-tab-content");

        tabs.forEach(tab => {
            tab.addEventListener("click", function(){
                tabs.forEach(t => t.classList.remove("active"));
                contents.forEach(c => c.classList.remove("active"));
                tab.classList.add("active");
                document.querySelector("#lcs-tab-" + tab.dataset.target).classList.add("active");
            });
        });
    });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('popular_posts', 'lcs_popular_posts_shortcode');
