<?php
/*
Plugin Name: Cricket Live Score Slider
Description: Displays live cricket scores from bdcrictime API inside a Slick Slider.
Version: 1.0
Author: Sehrish Anam
*/

if (!defined('ABSPATH')) exit;

// Enqueue slick and custom styles/scripts
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_style('slick-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
    wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', ['jquery'], null, true);

    wp_add_inline_style('slick-css', "
        .live-score-slider { display: flex; flex-wrap: nowrap; }
        .match-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            padding: 15px;
            margin: 10px;
            width: 280px;
        }
        .match-status { font-weight: bold; color: #e53935; }
        .team-row { display: flex; align-items: center; margin: 5px 0; }
        .team-row img { width: 25px; height: 25px; border-radius: 50%; margin-right: 8px; }
        .team-name { font-weight: 600; }
        .team-score { margin-left: auto; font-weight: 700; }
        .match-info { font-size: 13px; margin-top: 5px; }
        .slick-prev:before, .slick-next:before { color: #fff; }
        .match-footer a { font-size: 13px; margin-right: 8px; color: #0073aa; text-decoration: none; }
        .match-footer a:hover { text-decoration: underline; }
    ");
});

// Shortcode
add_shortcode('live_score_slider', function() {
    ob_start(); ?>

    <div class="live-score-slider">
        <?php
        $response = wp_remote_get('https://bdcrictime.com/api/get-live-score-slider?filtered=1');
        if (is_wp_error($response)) {
            echo '<p>Unable to fetch match data.</p>';
        } else {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($data['response']['items'])) {
                foreach ($data['response']['items'] as $match) {
                    $statusClass = strtolower($match['status_str']);
                    $teamA = $match['teama'];
                    $teamB = $match['teamb'];
                    ?>
                    <div class="match-card <?php echo esc_attr($statusClass); ?>">
                        <div class="match-status">
                            <?php echo esc_html(strtoupper($match['status_str'])); ?>
                        </div>
                        <div class="match-title">
                            <?php echo esc_html($match['subtitle']); ?> • <?php echo esc_html($match['short_title']); ?>
                        </div>
                        <div class="team-row">
                            <img src="<?php echo esc_url($teamA['logo_url']); ?>" alt="">
                            <span class="team-name"><?php echo esc_html($teamA['short_name']); ?></span>
                            <span class="team-score"><?php echo esc_html($teamA['scores'] ?? ''); ?></span>
                        </div>
                        <div class="team-row">
                            <img src="<?php echo esc_url($teamB['logo_url']); ?>" alt="">
                            <span class="team-name"><?php echo esc_html($teamB['short_name']); ?></span>
                            <span class="team-score"><?php echo esc_html($teamB['scores'] ?? ''); ?></span>
                        </div>
                        <div class="match-info"><?php echo esc_html($match['status_note']); ?></div>
                        <div class="match-footer">
                            <a href="#">Live</a>
                            <a href="#">Commentary</a>
                            <a href="#">Statistics</a>
                            <a href="#">Info</a>
                        </div>
                    </div>
                <?php }
            } else {
                echo '<p>No live matches available.</p>';
            }
        }
        ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.live-score-slider').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            arrows: true,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 4000,
            responsive: [
                { breakpoint: 1024, settings: { slidesToShow: 3 } },
                { breakpoint: 768, settings: { slidesToShow: 2 } },
                { breakpoint: 480, settings: { slidesToShow: 1 } }
            ]
        });
    });
    </script>

    <?php
    return ob_get_clean();
});
