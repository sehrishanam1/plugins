<?php
/**
 * Plugin Widget: Recent Series
 */

function cric_fetch_recent_series_widget() {
    // API URL
    $api_url = 'https://bdcrictime.com/api/get-recent-series';

    // Get the data
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return '<p>Unable to fetch recent series right now.</p>';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['response']['items'])) {
        return '<p>No recent series found.</p>';
    }

    $items = $data['response']['items'];

    // Start HTML output
    ob_start();
    ?>
    <div class="recent-series-widget" style="background:#fff;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.1);overflow:hidden;">
        <div style="background:#006A4E;color:#fff;padding:10px 15px;font-weight:bold;border-top-left-radius:8px;border-top-right-radius:8px;">
            Recent Series
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8f8f8;text-align:left;">
                    <th style="padding:8px 12px;border-bottom:1px solid #ddd;width:40%;">Date</th>
                    <th style="padding:8px 12px;border-bottom:1px solid #ddd;">Series Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $series) : 
                    if ($index >= 7) break; // Show only first 7 series like your example

                    // Format dates
                    $start = date('d M', strtotime($series['datestart']));
                    $end   = date('d M', strtotime($series['dateend']));
                ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:8px 12px;color:#333;"><?php echo esc_html($start . ' - ' . $end); ?></td>
                        <td style="padding:8px 12px;color:#006A4E;font-weight:500;"><?php echo esc_html($series['title']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('recent_series_widget', 'cric_fetch_recent_series_widget');
