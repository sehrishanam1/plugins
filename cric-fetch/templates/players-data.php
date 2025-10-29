<?php
/**
 * Shortcode: [players_data_widget country="bd"]
 * Fetches player data from API and shows searchable list
 */

function cric_fetch_players_data_widget($atts) {
    $atts = shortcode_atts(array(
        'country' => 'bd',
    ), $atts, 'players_data_widget');

    $api_url = 'https://bdcrictime.com/api/get-playerdata?country=' . esc_attr($atts['country']);
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return '<p>Unable to fetch players data right now.</p>';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['response']['items'])) {
        return '<p>No players found.</p>';
    }

    $players = $data['response']['items'];

    ob_start();
    ?>
    <div class="players-data-widget" style="background:#fff;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.1);overflow:hidden;">
        <div style="background:#006A4E;color:#fff;padding:10px 15px;font-weight:bold;border-top-left-radius:8px;border-top-right-radius:8px;">
            Players Data
        </div>

        <div style="padding:10px 15px;border-bottom:1px solid #ddd;">
            <input type="text" id="playerSearch" placeholder="Search Player..." style="width:100%;padding:8px 35px 8px 10px;border:1px solid #ccc;border-radius:20px;background:url('https://cdn-icons-png.flaticon.com/512/622/622669.png') no-repeat right 10px center/18px auto;">
        </div>

        <table id="playersTable" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8f8f8;text-align:left;">
                    <th style="padding:8px 12px;border-bottom:1px solid #ddd;">Players Name</th>
                    <th style="padding:8px 12px;border-bottom:1px solid #ddd;width:80px;">Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($players as $player) :
                    $img = !empty($player['thumb_url']) ? esc_url($player['thumb_url']) : 'https://api.bdcrictime.com/players/' . $player['pid'] . '.png';
                    $rating = isset($player['fantasy_player_rating']) ? esc_html($player['fantasy_player_rating']) : '-';
                ?>
                <tr class="player-row" style="border-bottom:1px solid #eee;">
                    <td style="padding:8px 12px;display:flex;align-items:center;gap:8px;">
                        <img src="<?php echo $img; ?>" alt="<?php echo esc_attr($player['title']); ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                        <?php echo esc_html($player['title']); ?>
                    </td>
                    <td style="padding:8px 12px;"><?php echo $rating; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('playerSearch');
        const rows = document.querySelectorAll('#playersTable .player-row');

        searchInput.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            rows.forEach(row => {
                const name = row.textContent.toLowerCase();
                row.style.display = name.includes(term) ? '' : 'none';
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('players_data_widget', 'cric_fetch_players_data_widget');
