<?php
/*
Plugin Name: Cricket Score
Description: Displays live cricket scores from bdcrictime API in a horizontal slider layout.
Version: 1.3
Author: Sehrish Anam
*/

require_once plugin_dir_path(__FILE__) . 'templates/flatsome-category-widget.php';
require_once plugin_dir_path(__FILE__) . 'templates/players-data.php';
require_once plugin_dir_path(__FILE__) . 'templates/duplicate-posts.php';
require_once plugin_dir_path(__FILE__) . 'templates/recent-series.php';
require_once plugin_dir_path(__FILE__) . 'templates/women-series.php';
require_once plugin_dir_path(__FILE__) . 'templates/latest-news.php';
require_once plugin_dir_path(__FILE__) . 'templates/popular-posts.php';
require_once plugin_dir_path(__FILE__) . 'templates/featured-pics.php';
require_once plugin_dir_path(__FILE__) . 'templates/icc-rankings-widget.php';
require_once plugin_dir_path(__FILE__) . 'templates/newsletter.php';
require_once plugin_dir_path(__FILE__) . 'templates/bangladesh/cricket_series_domestic.php';
require_once plugin_dir_path(__FILE__) . 'templates/bangladesh/circket_rank_result.php';
require_once plugin_dir_path(__FILE__) . 'templates/bangladesh/cricket_matche_scheduled.php';
require_once plugin_dir_path(__FILE__) . 'templates/bangladesh/cricket_bd_matches_results.php';
if (!defined('ABSPATH')) exit; // Prevent direct access

// ✅ Helper functions (moved outside to avoid redeclaration)
if (!function_exists('lcs_get_nested')) {
    function lcs_get_nested(array $arr, $path, $default = null)
    {
        if (is_string($path)) $path = explode('.', $path);
        $cur = $arr;
        foreach ($path as $p) {
            if (!is_array($cur) || !array_key_exists($p, $cur)) return $default;
            $cur = $cur[$p];
        }
        return $cur;
    }
}

if (!function_exists('lcs_safe')) {
    function lcs_safe($value)
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('lcs_format_score')) {
    function lcs_format_score($match, $teamKey)
    {
        $scores_full = lcs_get_nested($match, "$teamKey.scores_full");
        $scores = lcs_get_nested($match, "$teamKey.scores");
        $overs = lcs_get_nested($match, "$teamKey.overs");
        if (!empty($scores_full)) return $scores_full;
        if (!empty($scores)) return !empty($overs) ? "$scores ($overs)" : $scores;
        return '';
    }
}

// ✅ Enqueue CSS
function lcs_enqueue_assets() {
    // jQuery (always load it first)
    wp_enqueue_script('jquery');

    // Owl Carousel CSS/JS
    wp_enqueue_style('owl-carousel-css', '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css');
    wp_enqueue_style('owl-carousel-theme-css', '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css');
    wp_enqueue_script('owl-carousel-js', '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array('jquery'), '2.3.4', true);

    // Plugin CSS & JS
    wp_enqueue_style('lcs-styles', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_style('lcs-styles-custom', plugin_dir_url(__FILE__) . 'assets/custom.css');
    wp_enqueue_script('lcs-live-js', plugin_dir_url(__FILE__) . 'assets/live-score.js', array('jquery', 'owl-carousel-js'), '1.3', true);
}


add_action('wp_enqueue_scripts', 'lcs_enqueue_assets');


function lcs_live_cricket_shortcode()
{
    // Only show on homepage/front page
    if (!(is_front_page() || is_home())) {
        return ''; // Empty output for other pages
    }

    ob_start();

    // --- Fetch data
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://bdcrictime.com/api/get-live-score-slider?filtered=1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
    ));
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        $errorMsg = 'cURL Error: ' . curl_error($curl);
        $response = json_encode(['error' => $errorMsg]);
    }
    curl_close($curl);

    // --- Decode safely
    $data = json_decode($response, true);
    if (!is_array($data)) {
        $data = ['response' => ['items' => []]];
    }

    // --- If API fails
    if (isset($data['error'])) {
        echo '<div class="no-match">⚠️ ' . esc_html($data['error']) . '</div>';
        return ob_get_clean();
    }

    $items = lcs_get_nested($data, 'response.items', []);
?>
    <div id="live-cricket-container">
        <div class="live-slider">
            <div class="live-track owl-carousel owl-theme">
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $match): ?>
                        <?php
                        $status_str = lcs_get_nested($match, 'status_str', 'LIVE');
                        $subtitle   = lcs_get_nested($match, 'subtitle') ?: lcs_get_nested($match, 'match_number', '');
                        $competition = lcs_get_nested($match, 'competition.abbr') ?: lcs_get_nested($match, 'competition.title', '');


                        $teama = [
                            'name' => lcs_get_nested($match, 'teama.name', 'Team A'),
                            'logo' => lcs_get_nested($match, 'teama.logo_url'),
                            'score' => lcs_format_score($match, 'teama'),
                        ];
                        $teamb = [
                            'name' => lcs_get_nested($match, 'teamb.name', 'Team B'),
                            'logo' => lcs_get_nested($match, 'teamb.logo_url'),
                            'score' => lcs_format_score($match, 'teamb'),
                        ];

                        $status_note = lcs_get_nested($match, 'status_note') ?: lcs_get_nested($match, 'live', '');
                        $date_start = lcs_get_nested($match, 'date_start_ist') ?: lcs_get_nested($match, 'date_start');
                        $countdown_id = uniqid('countdown_');
                        $match_time = !empty($date_start) ? date('j M Y, g:i A', strtotime($date_start)) : '';
                        $placeholderLogo = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="100%" height="100%" fill="%23e6eef9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%23707b8a" font-size="10">No+Logo</text></svg>';
                        ?>

                        <div class="live-card">

                            <div class="live-header">
                                <span class="match-status match-status-<?= lcs_safe($status_str) ?>"><?= lcs_safe($status_str) ?></span>
                                <?php if (!empty($subtitle)): ?>
                                    <span class="match-subtitle"><?= lcs_safe($subtitle) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($competition)): ?>
                                    <span class="match-competition"><?= lcs_safe($competition) ?></span>
                                <?php endif; ?>
                            </div>


                            <div class="live-body">
                                <div class="team-row">
                                    <div class="team-info">
                                        <img class="team-logo" src="<?= lcs_safe($teama['logo'] ?: $placeholderLogo) ?>" alt="<?= lcs_safe($teama['name']) ?>">
                                        <div class="team-name"><?= lcs_safe($teama['name']) ?></div>
                                    </div>
                                    <div class="team-score"><?= lcs_safe($teama['score']) ?></div>
                                </div>
                                <div class="team-row">
                                    <div class="team-info">
                                        <img class="team-logo" src="<?= lcs_safe($teamb['logo'] ?: $placeholderLogo) ?>" alt="<?= lcs_safe($teamb['name']) ?>">
                                        <div class="team-name"><?= lcs_safe($teamb['name']) ?></div>
                                    </div>
                                    <div class="team-score"><?= lcs_safe($teamb['score']) ?></div>
                                </div>
                                <?php if (!empty($status_note)): ?>
                                    <div class="status-note"><?= lcs_safe($status_note) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($match_time)): ?>
                                <div id="<?= $countdown_id ?>" class="countdown-timer" data-start="<?= lcs_safe($date_start) ?>"></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-match">No live matches found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const apiUrl = "https://bdcrictime.com/api/get-live-score-slider?filtered=1";

            async function fetchLiveScores() {
                try {
                    const response = await fetch(apiUrl, {
                        cache: "no-store"
                    });
                    const data = await response.json();

                    const items = data?.response?.items || [];
                    const track = document.querySelector(".live-track");
                    if (!track) return;

                    // Clear previous items
                    track.innerHTML = "";

                    if (items.length === 0) {
                        track.innerHTML = `<div class="no-match">No live matches found.</div>`;
                        return;
                    }

                    // Build cards dynamically
                    items.forEach(match => {
                        const teama = match.teama || {};
                        const teamb = match.teamb || {};
                        const status_str = match.status_str || "LIVE";
                        const subtitle = match.subtitle || match.match_number || "";
                        const competition = match.competition?.abbr || match.competition?.title || "";
                        const status_note = match.status_note || match.live || "";
                        const date_start = match.date_start_ist || match.date_start || "";
                        const placeholderLogo = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="100%" height="100%" fill="%23e6eef9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%23707b8a" font-size="10">No+Logo</text></svg>';

                        const cardHTML = `
                    <div class="live-card">
                        <div class="live-header">
                            <span class="match-status">${status_str}</span>
                            ${subtitle ? `<span class="match-subtitle">${subtitle}</span>` : ""}
                            ${competition ? `<span class="match-competition">${competition}</span>` : ""}
                        </div>
                        <div class="live-body">
                            <div class="team-row">
                                <div class="team-info">
                                    <img class="team-logo" src="${teama.logo_url || placeholderLogo}" alt="${teama.short_name || 'Team A'}">
                                    <div class="team-name"><?= lcs_safe($teama['name']) ?></div>
                                </div>
                                <div class="team-score">${teama.scores_full || teama.scores || ""}</div>
                            </div>
                            <div class="team-row">
                                <div class="team-info">
                                    <img class="team-logo" src="${teamb.logo_url || placeholderLogo}" alt="${teamb.short_name || 'Team B'}">
                                    <div class="team-name">${teamb.short_name || "Team B"}</div>
                                </div>
                                <div class="team-score">${teamb.scores_full || teamb.scores || ""}</div>
                            </div>
                            ${status_note ? `<div class="status-note">${status_note}</div>` : ""}
                        </div>
                    </div>
                `;

                        track.insertAdjacentHTML("beforeend", cardHTML);
                    });
                } catch (err) {
                    console.error("Error fetching live scores:", err);
                }
            }

        });

    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('live_cricket_score', 'lcs_live_cricket_shortcode');

function lcs_get_live_cricket()
{
    check_ajax_referer('lcs_nonce', 'security');

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://bdcrictime.com/api/get-live-score-slider?filtered=1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
    ));
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        wp_send_json_error(['error' => 'cURL Error: ' . curl_error($curl)]);
    }
    curl_close($curl);

    $data = json_decode($response, true);
    if (!is_array($data)) {
        wp_send_json_error(['error' => 'Invalid API response']);
    }

    ob_start();
    $items = isset($data['response']['items']) ? $data['response']['items'] : [];
    if (empty($items)) {
        echo '<div class="no-match">No live matches found.</div>';
    } else {
        foreach ($items as $match) {
            $teama = $match['teama'] ?? [];
            $teamb = $match['teamb'] ?? [];
    ?>
            <div class="live-card">
                <div class="live-header">
                    <span class="match-status match-status-<?= esc_html($match['status_str'] ?? 'LIVE') ?>"><?= esc_html($match['status_str'] ?? 'LIVE') ?></span>
                    <span class="match-subtitle"><?= esc_html($match['subtitle'] ?? '') ?></span>
                    <span class="match-competition"><?= esc_html($match['competition']['abbr'] ?? '') ?></span>
                </div>
                <div class="live-body">
                    <div class="team-row">
                        <div class="team-info">
                            <img class="team-logo" src="<?= esc_url($teama['logo_url'] ?? '') ?>" alt="">
                            <div class="team-name"><?= lcs_safe($teama['name']) ?></div>
                        </div>
                        <div class="team-score"><?= esc_html($teama['scores_full'] ?? '') ?></div>
                    </div>
                    <div class="team-row">
                        <div class="team-info">
                            <img class="team-logo" src="<?= esc_url($teamb['logo_url'] ?? '') ?>" alt="">
                            <div class="team-name"><?= esc_html($teamb['name'] ?? '') ?></div>
                        </div>
                        <div class="team-score"><?= esc_html($teamb['scores_full'] ?? '') ?></div>
                    </div>
                    <div class="status-note"><?= esc_html($match['status_note'] ?? '') ?></div>
                </div>
            </div>
<?php
        }
    }
    $html = ob_get_clean();

    echo $html;
    wp_die(); // Required for AJAX
}


// AJAX Handler
add_action('wp_ajax_lcs_get_live_cricket', 'lcs_get_live_cricket');
add_action('wp_ajax_nopriv_lcs_get_live_cricket', 'lcs_get_live_cricket');





// === Custom rewrite for cricket series ===
function bdcric_rewrite_rules() {
    add_rewrite_rule(
        '^series/([^/]+)/([^/]+)/?',
        'index.php?cricket_series_id=$matches[1]&cricket_series_slug=$matches[2]',
        'top'
    );
}
add_action('init', 'bdcric_rewrite_rules');

function bdcric_query_vars($vars) {
    $vars[] = 'cricket_series_id';
    $vars[] = 'cricket_series_slug';
    return $vars;
}
add_filter('query_vars', 'bdcric_query_vars');

// === Load custom single template ===
function bdcric_template_redirect() {
    if (get_query_var('cricket_series_id')) {
        $template = plugin_dir_path(__FILE__) . 'templates/bangladesh/single-series.php';
        if (file_exists($template)) {
            include $template;
            exit;
        }
    }
}
add_action('template_redirect', 'bdcric_template_redirect');
