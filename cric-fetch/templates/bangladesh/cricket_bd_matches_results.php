<?php
// Shortcode: [cric_matches]
function cric_matches_bd_matches_shortcode()
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

    echo '<div class="cric-matches-grid">';

    // Loop through all series
    foreach ($data['series'] as $series_data) {
        $overview = $series_data['overview'] ?? null;
        if (!$overview) continue;

        // Merge live and next matches
        $matches = array_merge($overview['completed_matches'] ?? [], $overview['completed_matches'] ?? []);

        foreach ($matches as $match) {
            $team1 = $match['team1'] ?? '';
            $team2 = $match['team2'] ?? '';
            $status = ucfirst($match['status'] ?? 'completed');
            $match_url = $match['match_url'] ?? '#';
            $start_result = $match['result'] ?? '';
            $team_flags = $match['team_images'] ?? [];


            echo '<div class="cric-match-card">';

            // Series Name
            $series_name_line = explode("\n", $series_data['series_name'])[0];
            echo '<div class="cric-match-head">';
            echo '<div class="cric-match-series">' . esc_html($series_name_line) . '</div>';

            // Match Header: Type, Number, Venue
            echo '<div class="cric-match-header">';
            echo esc_html($series_data['series_name']) . ' | ' . ($match['match_number'] ?? '') . ' | ' . ($match['venue'] ?? '');
            echo '</div>';
            echo '</div>';
            // Teams
            echo '<div class="cric-match-teams">';

            // Team 1
            echo '<div class="team team1">';
            if (!empty($team_flags[0]['src'])) {
                echo '<img src="' . esc_url($team_flags[0]['src']) . '" alt="' . esc_attr($team_flags[0]['alt']) . '" class="team-logo">';
            }
            echo '<span class="team-name">' . esc_html($team1) . '</span>';
            echo '</div>';
            echo '<div class="cric-match-status-main">';
            echo '<div class="cric-match-status ' . strtolower($status) . '">' . esc_html($status) . '</div>';
            echo '<div class="vs">-VS-</div>';
            echo '</div>';

            // Team 2
            echo '<div class="team team2">';
            if (!empty($team_flags[1]['src'])) {
                echo '<img src="' . esc_url($team_flags[1]['src']) . '" alt="' . esc_attr($team_flags[1]['alt']) . '" class="team-logo">';
            }
            echo '<span class="team-name">' . esc_html($team2) . '</span>';
            echo '</div>';

            echo '</div>'; // end cric-match-teams

            // Status


            // Date & start time
            if (!empty($start_result)) {
                echo '<div class="cric-match-date">' . esc_html($start_result) . '</div>';
            }

            // Match details link
            //            echo '<div class="cric-match-link"><a href="' . esc_url($match_url) . '" target="_blank">View Match Details</a></div>';
            //            echo '<div class="cric-match-link"><a href="' . esc_url($match_url) . '" target="_blank">View Match Details</a></div>';

            echo '</div>'; // end cric-match-card
        }
    }

    echo '</div>'; // end cric-matches-grid



    return ob_get_clean();
}
add_shortcode('bd_cric_matches_resultes', 'cric_matches_bd_matches_shortcode');
