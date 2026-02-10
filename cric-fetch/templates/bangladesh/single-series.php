<?php
if (!defined('ABSPATH')) exit;
get_header();

$series_id = get_query_var('cricket_series_id');
$json_file = 'https://ban-cricket.stagecode.online/data';
$json_data = file_get_contents($json_file);

if ($json_data === false) {
    echo '<p>Unable to fetch data.</p>';
    exit;
}

$data = json_decode($json_data, true);

if ($data === null) {
    echo '<p>Invalid JSON format.</p>';
    exit;
}

$series_data = null;
if (!empty($data['series'])) {
    foreach ($data['series'] as $series) {
        if (($series['series_id'] ?? '') == $series_id) {
            $series_data = $series;
            break;
        }
    }
}

function extract_team2($full_text)
{
    $parts = explode("\n", trim($full_text));
    return isset($parts[2]) ? trim($parts[2]) : '';
}
?>


<div class="container" style="display:flex; gap:30px;">
    <main class="site-main" style="flex:1;">

        <div class="bd-cricket-series-grid-wrap bd-cricket-series-single">
            <div class="bd-cricket-series-grid">
                <?php foreach ($data['series'] as $series):

                    $series_name = trim($series['series_name'] ?? '');
                    $current_series_id = trim($series['series_id'] ?? '');
                    if (!$series_name || !$current_series_id) continue;


                    $bangladesh_terms = [
                        'Bangladesh',
                        'Dhaka',
                        'Khulna',
                        'Sylhet',
                        'Chittagong',
                        'Rangpur',
                        'Rajshahi',
                        'Barisal',
                        "Cox's Bazar",
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

                    $parts = explode("\n", $series_name);
                    $main_name = trim($parts[0]);
                    if (!$main_name) continue;

                    $series_date_text = trim($series['series_date_range'] ?? '');
                    $slug = sanitize_title($main_name);
                    $custom_url = site_url("/series/{$current_series_id}/{$slug}/");

                ?>
                   <a class="bd-cricket-series-card <?php echo $current_series_id == $series_id ? 'active-series' : ''; ?>"
                        href="<?php echo esc_url($custom_url); ?>">
                        <div class="bd-series-name"><?php echo esc_html($main_name); ?></div>
                        <?php if ($series_date_text): ?>
                            <div class="bd-series-dates"><?php echo esc_html($series_date_text); ?></div>
                        <?php endif; ?>
                    </a>

                <?php endforeach; ?>
            </div>
        </div>















        <div class="bd-cricket-tabs">
            <!-- Tab buttons -->
            <div class="bd-tabs-header">
                <button class="bd-tab-btn active" data-tab="overview">Overview</button>
                <button class="bd-tab-btn" data-tab="fixtures">Fixtures</button>
                <button class="bd-tab-btn" data-tab="results">Results</button>
                <button class="bd-tab-btn" data-tab="top-players">Top Players</button>
                <button class="bd-tab-btn" data-tab="standings">Standings</button>
            </div>
            <?php
            $overview = $series_data['overview'] ?? [];
            $live = $overview['live_matches'] ?? [];
            $next = $overview['next_matches'] ?? [];
            $completed = $overview['completed_matches'] ?? [];
            ?>
            <!-- Tab content -->
            <div class="bd-tabs-content">

                <!-- OVERVIEW TAB -->
                <div class="bd-tab-content active" id="overview">
                    <?php if ($series_data): ?>
                        <h1><?php echo esc_html(explode("\n", $series_data['series_name'])[0]); ?></h1>


                        <!-- Series Matches (current Live/Next/Completed content) -->
                        <div class="series-matches-wrapper">

                            <?php if (!empty($live)): ?>
                                <div class="match-section-custom">
                                    <h3 class="section-title">Live Matches</h3>
                                    <div class="matches-section">
                                        <div class="matches-table">
                                            <?php foreach ($live as $match):
                                                $team1 = $match['team1'] ?? '';
                                                $team2 = extract_team2($match['full_text'] ?? '');
                                                $full_text = $match['full_text'] ?? '';
                                                $parts = explode("\n", trim($full_text));
                                                $status = end($parts); // get text after the last newline

                                                $url = $match['match_url'] ?? '#';
                                                $team1_flag = $match['team1_flag'] ?? '';
                                                $team2_flag = $match['team2_flag'] ?? '';
                                            ?>
                                                <div class="match-row">
                                                    <div class="match-row">
                                                        <div class="match-content">
                                                            <?php
                                                            // ✅ Safely build image tags if available
                                                            $img_team1 = '';
                                                            $img_team2 = '';

                                                            if (!empty($match['team1_flag'])) {
                                                                $img_team1 = '<img src="' . esc_url($match['team1_flag']) . '" alt="' . esc_attr($match['team1_flag_alt'] ?? $team1) . '" class="team-logo">';
                                                            }

                                                            if (!empty($match['team2_flag'])) {
                                                                $img_team2 = '<img src="' . esc_url($match['team2_flag']) . '" alt="' . esc_attr($match['team2_flag_alt'] ?? $team2) . '" class="team-logo">';
                                                            }
                                                            ?>

                                                            <span class="teams">
                                                                <?php echo $img_team1 . esc_html($team1) . '&nbsp;&nbsp;-vs-&nbsp;&nbsp;' . $img_team2 . esc_html($team2); ?>
                                                            </span>
                                                            <span class="separator">|</span>
                                                            <span class="match-status" style="background: white;"><?php echo esc_html(ucfirst($status)); ?></span>
                                                        </div>
                                                    </div>

                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($next)): ?>
                                <div class="match-section-custom">
                                    <h3 class="section-title">Next Matches</h3>
                                    <div class="matches-section">
                                        <div class="matches-table">
                                            <?php foreach ($next as $match):
                                                $team1 = $match['team1'] ?? '';
                                                $team2 = extract_team2($match['full_text'] ?? '');
                                                // $time = $match['match_time'] ?? ($match['status'] ?? '');
                                                $full_text = $match['full_text'] ?? '';
                                                $parts = explode("\n", trim($full_text));
                                                $time = end($parts) ?: ($match['match_time'] ?? $match['status'] ?? '');

                                                $url = $match['match_url'] ?? '#';
                                                $team_flags = $match['team_images'] ?? [];
                                                $img_team1 = '<img src="' . esc_url($team_flags[0]['src']) . '" alt="' . esc_attr($team_flags[0]['alt']) . '" class="team-logo">';
                                                $img_team2 = '<img src="' . esc_url($team_flags[0]['src']) . '" alt="' . esc_attr($team_flags[1]['alt']) . '" class="team-logo">';
                                            ?>
                                                <div class="match-row">
                                                    <div class="match-content">
                                                        <span class="teams"><?php echo $img_team1 . $team1 . '&nbsp;&nbsp;-vs-&nbsp;&nbsp;'  . $img_team2 . $team2; ?></span>
                                                        <span class="separator">|</span>
                                                        <span class="match-result"><?php echo esc_html($time); ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($completed)): ?>
                                <div class="match-section-custom">
                                    <h3 class="section-title">Completed Matches</h3>
                                    <div class="matches-section">
                                        <div class="matches-table">
                                            <?php foreach ($completed as $match):
                                                $team1 = $match['team1'] ?? '';
                                                $team2 = extract_team2($match['full_text'] ?? '');
                                                // $result = $match['result'] ?? '';
                                                $full_text = $match['full_text'] ?? '';
                                                $parts = explode("\n", trim($full_text));
                                                $result = end($parts) ?: ($match['result'] ?? '');

                                                $url = $match['match_url'] ?? '#';
                                                $team_flags = $match['team_images'] ?? [];

                                                // ✅ Safely handle missing or incomplete team images
                                                $img_team1 = '';
                                                $img_team2 = '';

                                                if (!empty($team_flags[0]['src'])) {
                                                    $img_team1 = '<img src="' . esc_url($team_flags[0]['src']) . '" alt="' . esc_attr($team_flags[0]['alt'] ?? $team1) . '" class="team-logo">';
                                                }

                                                if (!empty($team_flags[1]['src'])) {
                                                    $img_team2 = '<img src="' . esc_url($team_flags[1]['src']) . '" alt="' . esc_attr($team_flags[1]['alt'] ?? $team2) . '" class="team-logo">';
                                                }
                                            ?>
                                                <div class="match-row">
                                                    <div class="match-content">
                                                        <span class="teams">
                                                            <?php echo $img_team1 . esc_html($team1) . '&nbsp;&nbsp;-vs-&nbsp;&nbsp;' . $img_team2 . esc_html($team2); ?>
                                                        </span>
                                                        <span class="separator">|</span>
                                                        <span class="match-result"><?php echo esc_html($result); ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>


                        </div>

                        <!-- Optional includes -->
                        <?php
                        $series_top_file = plugin_dir_path(__FILE__) . 'series-players-top.php';
                        if (file_exists($series_top_file)) include $series_top_file;

                        $points_table_file = plugin_dir_path(__FILE__) . 'points-table.php';
                        if (file_exists($points_table_file)) include $points_table_file;
                        ?>

                    <?php else: ?>
                        <p>No data found for this series.</p>
                    <?php endif; ?>
                </div>

                <!-- FIXTURES TAB -->
                <div class="bd-tab-content" id="fixtures">
                    <?php
                    // Make sure $next exists and is an array
                    if (
                        (!isset($next) || !is_array($next) || count($next) === 0)
                        && (!isset($live) || !is_array($live) || count($live) === 0)
                    ): ?>
                        <div class="match-section-custom" style="text-align:center; margin:30px 0;">
                            <h3 class="section-title">Scheduled and Live matches</h3>
                            <p style="font-weight:bold; color:#555;">No Scheduled or Live matches ahead.</p>
                        </div>
                    <?php else: ?>
                        <div class="match-section-custom">
                            <h3 class="section-title">Scheduled and Live matches</h3>
                            <div style="display: grid; grid-template-columns: 49% 49%; gap:20px;">
                                <?php foreach ($live as $match):
                                    $team1 = $match['team1'] ?? '';
                                    $team2 = $match['team2'] ?? '';
                                    $result = $match['result'] ?? '';
                                    $status = ucfirst($match['status'] ?? 'completed');
                                    $team1_flag = $match['team1_flag'] ?? '';
                                    $team2_flag = $match['team2_flag'] ?? '';
                                    $team1_alt = $match['team1_flag_alt'] ?? $team1;
                                    $team2_alt = $match['team2_flag_alt'] ?? $team2;
                                    $img_team1 = !empty($team1_flag)
                                        ? '<img src="' . esc_url($team1_flag) . '" alt="' . esc_attr($team1_alt) . '" class="team-logo2 team-logo">'
                                        : '';

                                    $img_team2 = !empty($team2_flag)
                                        ? '<img src="' . esc_url($team2_flag) . '" alt="' . esc_attr($team2_alt) . '" class="team-logo2 team-logo">'
                                        : '';
                                ?>
                                    <div class="completed-match-card" style="border:1px solid #ccc; margin-bottom:20px; text-align:center; border-radius:6px; background:#f8f8f8;">
                                        <div class="match-header" style="font-weight:bold; font-size:14px; color:#fff; background:#008080; padding:5px 10px; display:flex; justify-content:space-between;">
                                            <span><?php echo esc_html($match['full_text'] ? explode("\n", $match['full_text'])[0] : ''); ?></span>
                                            <span><?php echo 'T20'; ?></span>
                                            <span><?php echo 'Match'; ?></span>
                                        </div>

                                        <div class="match-body" style="display:flex; justify-content:center; align-items:center; gap:30px; margin:15px 0;">
                                            <div class="team" style="text-align:center;">
                                                <?php echo $img_team1; ?>
                                                <div style="font-weight:bold; margin-top:5px;"><?php echo esc_html($team1_alt); ?></div>
                                                <div><?php echo esc_html($match['team1_score'] ?? ''); ?></div>
                                            </div>

                                            <div style="text-align:center;">
                                                <?php
                                                // Normalize status string
                                                $status_clean = strtolower(trim($status));

                                                // Determine CSS class safely
                                                if ($status_clean === 'live') {
                                                    $status_class = 'live';
                                                } elseif ($status_clean === 'upcoming') {
                                                    $status_class = 'upcoming';
                                                } else {
                                                    $status_class = 'completed';
                                                }
                                                ?>

                                                <div class="cric-match-status <?php echo esc_attr($status_class); ?>">
                                                    <?php echo esc_html(ucfirst($status_clean)); ?>
                                                </div>

                                                <div style="font-weight:bold;">-VS-</div>
                                            </div>

                                            <div class="team" style="text-align:center;">
                                                <?php echo $img_team2; ?>
                                                <div style="font-weight:bold; margin-top:5px;"><?php echo esc_html($team2_alt); ?></div>
                                                <div><?php echo esc_html($match['team2_score'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                        <span class="match-result"><?php echo esc_html($time); ?></span>
                                        <div class="match-result" style="margin-bottom:10px; font-weight:bold; color:green;"><?php echo esc_html($result); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="display: grid; grid-template-columns: 49% 49%; gap:20px;">
                                <?php foreach ($next as $match):
                                    $team1 = $match['team1'] ?? '';
                                    $team2 = $match['team2'] ?? '';
                                    $result = $match['result'] ?? '';
                                    $status = ucfirst($match['status'] ?? 'completed');
                                    $team_flags = $match['team_images'] ?? [];

                                    $img_team1 = !empty($team_flags[0]['src'])
                                        ? '<img src="' . esc_url($team_flags[0]['src']) . '" alt="' . esc_attr($team_flags[0]['alt'] ?? $team1) . '" class="team-logo2 team-logo">'
                                        : '';
                                    $team1_alt = !empty($team_flags[0]['alt']) ? $team_flags[0]['alt'] : $team1;

                                    $img_team2 = !empty($team_flags[1]['src'])
                                        ? '<img src="' . esc_url($team_flags[1]['src']) . '" alt="' . esc_attr($team_flags[1]['alt'] ?? $team2) . '" class="team-logo2 team-logo">'
                                        : '';
                                    $team2_alt = !empty($team_flags[1]['alt']) ? $team_flags[1]['alt'] : $team2;
                                ?>
                                    <div class="completed-match-card" style="border:1px solid #ccc; margin-bottom:20px; text-align:center; border-radius:6px; background:#f8f8f8;">
                                        <div class="match-header" style="font-weight:bold; font-size:14px; color:#fff; background:#008080; padding:5px 10px; display:flex; justify-content:space-between;">
                                            <span><?php echo esc_html($match['full_text'] ? explode("\n", $match['full_text'])[0] : ''); ?></span>
                                            <span><?php echo 'T20'; ?></span>
                                            <span><?php echo 'Match'; ?></span>
                                        </div>

                                        <div class="match-body" style="display:flex; justify-content:center; align-items:center; gap:30px; margin:15px 0;">
                                            <div class="team" style="text-align:center;">
                                                <?php echo $img_team1; ?>
                                                <div style="font-weight:bold; margin-top:5px;"><?php echo esc_html($team1_alt); ?></div>
                                                <div><?php echo esc_html($match['team1_score'] ?? ''); ?></div>
                                            </div>

                                            <div style="text-align:center;">
                                                <?php
                                                // Normalize status string
                                                $status_clean = strtolower(trim($status));

                                                // Determine CSS class safely
                                                if ($status_clean === 'live') {
                                                    $status_class = 'live';
                                                } elseif ($status_clean === 'Scheduled') {
                                                    $status_class = 'upcoming';
                                                } else {
                                                    $status_class = 'completed';
                                                }
                                                ?>

                                                <div class="cric-match-status <?php echo esc_attr($status_class); ?>">
                                                    <?php echo esc_html(ucfirst($status_clean)); ?>
                                                </div>
                                                <div style="font-weight:bold;">-VS-</div>
                                            </div>

                                            <div class="team" style="text-align:center;">
                                                <?php echo $img_team2; ?>
                                                <div style="font-weight:bold; margin-top:5px;"><?php echo esc_html($team2_alt); ?></div>
                                                <div><?php echo esc_html($match['team2_score'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                        <span class="match-result"><?php echo esc_html($time); ?></span>
                                        <div class="match-result" style="margin-bottom:10px; font-weight:bold; color:green;"><?php echo esc_html($result); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- RESULTS TAB -->
                <div class="bd-tab-content" id="results">
                    <?php if (!empty($completed)): ?>
                        <div class="match-section-custom">
                            <h3 class="section-title">Results for Matches</h3>
                            <div style="display: grid; grid-template-columns: 49% 49%; gap: 20px;">
                                <?php foreach ($completed as $match):
                                    $team1 = $match['team1'] ?? '';
                                    $team2 = $match['team2'] ?? '';
                                    $result = $match['result'] ?? '';
                                    $status = ucfirst($match['status'] ?? 'completed');
                                    $match_url = $match['match_url'] ?? '#';
                                    $team_flags = $match['team_images'] ?? [];

                                    $img_team1 = !empty($team_flags[0]['src']) ? '<img src="' . esc_url($team_flags[0]['src']) . '" alt="' . esc_attr($team_flags[0]['alt'] ?? $team1) . '" class="team-logo2 team-logo">' : '';
                                    // Extract alt text safely
                                    $team1_alt = !empty($team_flags[0]['alt']) ? $team_flags[0]['alt'] : $team1;
                                    $img_team2 = !empty($team_flags[1]['src']) ? '<img src="' . esc_url($team_flags[1]['src']) . '" alt="' . esc_attr($team_flags[1]['alt'] ?? $team2) . '" class="team-logo2 team-logo">' : '';
                                    $team2_alt = !empty($team_flags[1]['alt']) ? $team_flags[1]['alt'] : $team2;
                                ?>
                                    <div class="completed-match-card" style="border:1px solid #ccc; margin-bottom:20px; text-align:center; border-radius:6px; background:#f8f8f8;">
                                        <div class="match-header" style="font-weight:bold; font-size:14px; color:#fff; background:#008080; padding:5px 10px; display:flex; justify-content:space-between;">
                                            <span><?php echo esc_html($match['full_text'] ? explode("\n", $match['full_text'])[0] : ''); ?></span>
                                            <span><?php echo 'T20'; // You can customize or fetch format 
                                                    ?></span>
                                            <span><?php echo 'Match'; ?></span>
                                        </div>

                                        <div class="match-body" style="display:flex; justify-content:center; align-items:center; gap:30px; margin:15px 0;">
                                            <div class="team" style="text-align:center;">
                                                <?php echo $img_team1; ?>
                                                <div style="font-weight:bold; margin-top:5px;"><?php echo esc_html($team1_alt); ?></div>
                                                <div><?php echo esc_html($match['team1_score'] ?? ''); ?></div>
                                            </div>
                                            <div style="">
                                                <div class="cric-match-status completed"><?php echo $status ?></div><br />
                                                <div style="font-weight:bold;">-VS-</div>
                                            </div>

                                            <div class="team" style="text-align:center;">
                                                <?php echo $img_team2; ?>
                                                <div style="font-weight:bold; margin-top:5px;"><?php echo esc_html($team2_alt); ?></div>
                                                <div><?php echo esc_html($match['team2_score'] ?? ''); ?></div>
                                            </div>
                                        </div>

                                        <div class="match-result" style="margin-bottom:10px; font-weight:bold; color:green;"><?php echo esc_html($result); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- TOP PLAYERS TAB -->
                <div class="bd-tab-content" id="top-players">
                    <?php $series_top_file = plugin_dir_path(__FILE__) . 'series-players-top.php';
                    if (file_exists($series_top_file)) include $series_top_file; ?>
                </div>

                <!-- STANDINGS TAB -->
                <div class="bd-tab-content" id="standings">
                    <?php
                    $points_table_file = plugin_dir_path(__FILE__) . 'points-table.php';

                    if (file_exists($points_table_file)) {
                        // Include the file and capture output
                        ob_start();
                        include $points_table_file;
                        $points_table_content = ob_get_clean();

                        // Check if the included file returned any table rows
                        if (!empty($points_table_content) && strpos($points_table_content, '<tr') !== false) {
                            echo $points_table_content; // Output table if data exists
                        } else {
                            // Show friendly message if no data
                    ?>
                            <div class="points-table-section">
                                <h3 class="section-title">Points Table</h3>
                                <p style="color:#555; font-weight:500; margin-top:10px;">
                                    Points table data is not available at the moment. Please check back later for updates.
                                </p>
                            </div>
                        <?php
                        }
                    } else {
                        // File not found
                        ?>
                        <div class="points-table-section">
                            <h3 class="section-title">Points Table</h3>
                            <p style="color:#c00; font-weight:500; margin-top:10px;">
                                Points table file is missing. Please contact the site administrator.
                            </p>
                        </div>
                    <?php
                    }
                    ?>

                </div>

            </div>
        </div>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('.bd-tab-btn');
                const contents = document.querySelectorAll('.bd-tab-content');

                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        tabs.forEach(t => t.classList.remove('active'));
                        tab.classList.add('active');

                        const target = tab.getAttribute('data-tab');
                        contents.forEach(c => {
                            if (c.id === target) c.classList.add('active');
                            else c.classList.remove('active');
                        });
                    });
                });
            });
        </script>
    </main>
    <aside class="sidebar" style="width:320px;">
        <?php get_sidebar(); ?>
    </aside>
</div>

<?php get_footer(); ?>