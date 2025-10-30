<?php
add_shortcode('icc_team_rankings', 'bdcrictime_icc_team_rankings_shortcode');

function bdcrictime_icc_team_rankings_shortcode() {

    $api_url = 'https://bdcrictime.com/api/get-iccranks';
    $response = wp_remote_get($api_url, array('timeout' => 15));
    $teams = array('odis' => [], 't20s' => [], 'tests' => []);

    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        if (!empty($decoded['response']['ranks']['teams'])) {
            $teams = $decoded['response']['ranks']['teams'];
        }
    }

    ob_start(); ?>

    <div class="icc-rankings-widget">
        <div class="widget-header"><h3>ICC Team Rankings</h3></div>

        <div class="tabs">
            <button class="tab-btn active" data-tab="odis">ODI</button>
            <button class="tab-btn" data-tab="t20s">T20</button>
            <button class="tab-btn" data-tab="tests">TEST</button>
        </div>

        <div id="rankings-content" class="rankings-content"></div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let data = <?php echo json_encode($teams); ?>;
        const contentDiv = document.getElementById("rankings-content");
        const tabButtons = document.querySelectorAll(".tab-btn");
        let currentTab = "odis";

        function renderTab(tab) {
            const list = data[tab];
            if (!list || list.length === 0) {
                contentDiv.innerHTML = "<p style='text-align:center;color:#777;'>No data available.</p>";
                return;
            }

            let html = `
                <table class="rankings-table">
                    <thead><tr><th>Pos</th><th>Team</th><th>Rating</th></tr></thead>
                    <tbody>
            `;
            list.slice(0, 5).forEach(item => {
                html += `
                    <tr>
                        <td>${item.rank}</td>
                        <td>
                            <img src="https://flagcdn.com/24x18/${item.team.toLowerCase().slice(0,2)}.png" class="team-flag">
                            ${item.team}
                        </td>
                        <td>${item.rating}</td>
                    </tr>`;
            });
            html += "</tbody></table>";
            contentDiv.innerHTML = html;
        }

        tabButtons.forEach(btn => {
            btn.addEventListener("click", () => {
                tabButtons.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentTab = btn.dataset.tab;
                renderTab(currentTab);
            });
        });

        renderTab(currentTab);

        // Optional auto-refresh
        setInterval(() => {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=refresh_icc_team_data')
                .then(res => res.json())
                .then(newData => {
                    data = newData;
                    renderTab(currentTab);
                });
        }, 10000);
    });
    </script>
    <?php

    return ob_get_clean();
}

add_action('wp_ajax_refresh_icc_team_data', 'refresh_icc_team_data_callback');
add_action('wp_ajax_nopriv_refresh_icc_team_data', 'refresh_icc_team_data_callback');

function refresh_icc_team_data_callback() {
    $api_url = 'https://bdcrictime.com/api/get-iccranks';
    $response = wp_remote_get($api_url, array('timeout' => 15));
    $teams = array('odis' => [], 't20s' => [], 'tests' => []);

    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        if (!empty($decoded['response']['ranks']['teams'])) {
            $teams = $decoded['response']['ranks']['teams'];
        }
    }

    wp_send_json($teams);
}
?>
