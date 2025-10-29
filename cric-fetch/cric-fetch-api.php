<?php
/*
Plugin Name: Cricket Score
Description: Displays live cricket scores from bdcrictime API in a horizontal slider layout.
Version: 1.3
Author: Your Name
*/
require_once plugin_dir_path(__FILE__) . 'flatsome-category-widget.php';
// require_once plugin_dir_path(__FILE__) . 'videos-cat.php';
require_once plugin_dir_path(__FILE__) . 'duplicate-posts.php';
if (!defined('ABSPATH')) exit; // Prevent direct access

// ✅ Helper functions (moved outside to avoid redeclaration)
if (!function_exists('lcs_get_nested')) {
    function lcs_get_nested(array $arr, $path, $default = null) {
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
    function lcs_safe($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('lcs_format_score')) {
    function lcs_format_score($match, $teamKey) {
        $scores_full = lcs_get_nested($match, "$teamKey.scores_full");
        $scores = lcs_get_nested($match, "$teamKey.scores");
        $overs = lcs_get_nested($match, "$teamKey.overs");
        if (!empty($scores_full)) return $scores_full;
        if (!empty($scores)) return !empty($overs) ? "$scores ($overs)" : $scores;
        return '—';
    }
}

// ✅ Enqueue CSS
function lcs_enqueue_assets() {
    wp_enqueue_style(
        'lcs-styles',
        plugin_dir_url(__FILE__) . 'assets/style.css',
        array(),
        '1.3',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'lcs_enqueue_assets');

// ✅ Shortcode
// ✅ Shortcode
function lcs_live_cricket_shortcode() {
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
    <div class="live-slider">
        <button class="slider-btn prev">&#10094;</button>
        <div class="live-track">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $match): ?>
                    <?php
//                     $title = lcs_get_nested($match, 'status_str', 'LIVE') . ' | ' .
//                              (lcs_get_nested($match, 'subtitle') ?: lcs_get_nested($match, 'match_number', '')) . ' | ' .
//                              (lcs_get_nested($match, 'competition.abbr') ?: lcs_get_nested($match, 'competition.title', ''));

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
<!--                         <div class="live-header"><?= lcs_safe($title) ?></div> -->
						<div class="live-header">
    <span class="match-status"><?= lcs_safe($status_str) ?></span>
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
        <button class="slider-btn next">&#10095;</button>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        function updateCountdown(elem) {
            const startTime = new Date(elem.dataset.start).getTime();
            const now = new Date().getTime();
            const diff = startTime - now;

            if (diff <= 0) {
                elem.textContent = "";
                return;
            }

            const d = Math.floor(diff / (1000 * 60 * 60 * 24));
            const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const m = Math.floor((diff / (1000 * 60)) % 60);
            const s = Math.floor((diff / 1000) % 60);

            elem.textContent = `${d.toString().padStart(2,'0')} D ${h.toString().padStart(2,'0')} H ${m.toString().padStart(2,'0')} M ${s.toString().padStart(2,'0')} S`;
        }

        document.querySelectorAll(".countdown-timer").forEach(timer => {
            updateCountdown(timer);
            setInterval(() => updateCountdown(timer), 1000);
        });

        const track = document.querySelector(".live-track");
        const prev = document.querySelector(".slider-btn.prev");
        const next = document.querySelector(".slider-btn.next");

        if (track && prev && next) {
            const cardWidth = 310;
            next.addEventListener("click", () => track.scrollBy({ left: cardWidth, behavior: "smooth" }));
            prev.addEventListener("click", () => track.scrollBy({ left: -cardWidth, behavior: "smooth" }));
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('live_cricket_score', 'lcs_live_cricket_shortcode');
