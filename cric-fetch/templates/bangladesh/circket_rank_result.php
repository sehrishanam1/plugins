<?php
// Add shortcode: [cric_points_table]
function cric_points_table_shortcode()
{
    ob_start();

    $json_file = 'https://ban-cricket.stagecode.online/data';

    // Get data from the URL
    $json_data = file_get_contents($json_file);

    // Check if data was fetched successfully
    if ($json_data === false) {
        echo '<p>Unable to fetch data.</p>';
        exit;
    }

    // Convert JSON string into PHP array
    $data = json_decode($json_data, true);

    // Check if JSON was valid
    if ($data === null) {
        echo '<p>Invalid JSON format.</p>';
        exit;
    }

    // Loop through all series
    foreach ($data['series'] as $series_data) {
        $overview = $series_data['overview'] ?? null;
        if (empty($overview['points_table'])) {
            continue; // skip series without points table
        }

        $points = $overview['points_table'];
?>
        <div class="points-table-section">
            <h3 class="section-title">
                <?php
                $series_name = $series_data['series_name'];
                $first_line = explode("\n", $series_name)[0]; // Get text before \n
                echo esc_html($first_line);
                ?>
            </h3>

            <div class="points-table-wrapper">
                <div class="points-table-ctn">
                    <div class="points-table-inner">
                        <table class="points-table">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Team</th>
                                    <th>Matches</th>
                                    <th>Won</th>
                                    <th>Lost</th>
                                    <th>N/R</th>
                                    <th>Tied</th>
                                    <th>Net RR</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($points as $team): ?>
                                    <tr>
                                        <td><?php echo esc_html($team['position']); ?></td>
                                        <td class="team-cell">
                                            <?php if (!empty($team['team_flag'])): ?>
                                                <img src="<?php echo esc_url($team['team_flag']); ?>"
                                                    alt="<?php echo esc_attr($team['team_flag_alt']); ?>"
                                                    class="team-logo">
                                            <?php endif; ?>
                                            <span class="team-name"><?php echo esc_html($team['team']); ?></span>
                                        </td>
                                        <td><?php echo esc_html($team['matches']); ?></td>
                                        <td><?php echo esc_html($team['won']); ?></td>
                                        <td><?php echo esc_html($team['lost']); ?></td>
                                        <td><?php echo esc_html($team['nr']); ?></td>
                                        <td><?php echo esc_html($team['tied']); ?></td>
                                        <td><?php echo esc_html($team['net_rr']); ?></td>
                                        <td class="points-cell"><b><?php echo esc_html($team['points']); ?></b></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
<?php
    } // end foreach series

    return ob_get_clean();
}
add_shortcode('cricket_points_table', 'cric_points_table_shortcode');
