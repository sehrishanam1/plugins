<?php
/*
 * Plugin Widget: ICC Women's World Cup Standings
 */

if (!defined('ABSPATH')) exit;

// ====================================================
// 1️⃣ MAIN FUNCTION — Fetch + Display API Data
// ====================================================
function sa_render_series_standings_widget($series_id = 129650) {
    $api_url = "https://bdcrictime.com/api/get-series-standing?series_id={$series_id}";

    $response = wp_remote_get($api_url, array('timeout' => 15));
    if (is_wp_error($response)) {
        return '<p>Unable to load data.</p>';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['response']['standings'][0]['standings'])) {
        return '<p>No standings data available.</p>';
    }

    $standings = $data['response']['standings'][0]['standings'];
    $round_name = $data['response']['standings'][0]['round']['name'] ?? 'Recent Series';

    ob_start();
    ?>
    <div class="series-standings-widget">
        <div class="widget-header">
            <h3><?php echo esc_html($round_name); ?></h3>
        </div>
        <table class="standings-table">
            <thead>
                <tr>
                    <th>Pos</th>
                    <th>Team</th>
                    <th>M</th>
                    <th>W</th>
                    <th>L</th>
                    <th>T</th>
                    <th>P</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $pos = 1;
                foreach ($standings as $team) : 
                    $t = $team['team'];
                    ?>
                    <tr>
                        <td><?php echo $pos++; ?></td>
                        <td class="team-cell">
                            <img src="<?php echo esc_url($t['logo_url']); ?>" alt="<?php echo esc_attr($t['title']); ?>">
                            <?php echo esc_html($t['title']); ?>
                        </td>
                        <td><?php echo esc_html($team['played']); ?></td>
                        <td><?php echo esc_html($team['win']); ?></td>
                        <td><?php echo esc_html($team['loss']); ?></td>
                        <td><?php echo esc_html($team['draw']); ?></td>
                        <td><?php echo esc_html($team['points']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// ====================================================
// 2️⃣ SHORTCODE
// ====================================================
function sa_series_standing_shortcode($atts) {
    $atts = shortcode_atts(array(
        'series_id' => '129650',
    ), $atts, 'series_standings');

    return sa_render_series_standings_widget($atts['series_id']);
}
add_shortcode('series_standings', 'sa_series_standing_shortcode');

// ====================================================
// 3️⃣ INLINE STYLES
// ====================================================
function sa_series_standings_styles() {
    ?>
    <style>
        
    </style>
    <?php
}
add_action('wp_footer', 'sa_series_standings_styles');
