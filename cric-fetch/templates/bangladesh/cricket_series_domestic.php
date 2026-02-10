<?php
if (!defined('ABSPATH')) exit;

function show_bd_cricket_series()
{
    // Fetch JSON
    $json_file = 'https://ban-cricket.stagecode.online/data';
    $json_data = @file_get_contents($json_file);

    if ($json_data === false) {
        return '<p style="color:#c00;">Unable to fetch cricket data from remote source.</p>';
    }

    $data = json_decode($json_data, true);

    if (!$data || !isset($data['series']) || !is_array($data['series'])) {
        return '<p style="color:#c00;">Invalid cricket data (unable to parse JSON).</p>';
    }

    ob_start(); ?>
    
    <div class="bd-cricket-series-grid-wrap">
        <div class="bd-cricket-series-grid">

            <?php foreach ($data['series'] as $series):

                $series_name = trim($series['series_name'] ?? '');
                $series_id   = trim($series['series_id']   ?? '');

                if (!$series_name || !$series_id) continue;

                // Filter only Bangladesh-related
                $bangladesh_terms = [
                    'Bangladesh',
                    'Dhaka', 'Khulna', 'Sylhet', 'Chittagong',
                    'Rangpur', 'Rajshahi', 'Barisal', "Cox's Bazar",
                    'Premier League'
                ];

                $is_bd_related = false;
                foreach ($bangladesh_terms as $term) {
                    if (stripos($series_name, $term) !== false) {
                        $is_bd_related = true;
                        break;
                    }
                }

                if (!$is_bd_related) continue;

                // Split name before newline
                $parts = explode("\n", $series_name);
                $main_name = trim($parts[0]);

                if (!$main_name) continue;

                // ⬇️ USE DATE RANGE DIRECTLY FROM JSON
                $series_date_text = trim($series['series_date_range'] ?? '');

                // SEO URL
                $slug = sanitize_title($main_name);
                $custom_url = site_url("/series/{$series_id}/{$slug}/");

            ?>

                <a class="bd-cricket-series-card" href="<?php echo esc_url($custom_url); ?>">
                    <div class="bd-series-name"><?php echo esc_html($main_name); ?></div>

                    <?php if ($series_date_text): ?>
                        <div class="bd-series-dates"><?php echo esc_html($series_date_text); ?></div>
                    <?php endif; ?>
                </a>

            <?php endforeach; ?>

        </div>
    </div>

<?php
    return ob_get_clean();
}

add_shortcode('bd_cricket_series', 'show_bd_cricket_series');
