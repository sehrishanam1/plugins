<?php
add_shortcode('icc_rankings', 'bdcrictime_icc_rankings_shortcode');

function bdcrictime_icc_rankings_shortcode() {

    // Fetch data from API
    $api_url = 'https://bdcrictime.com/api/get-iccranks';
    $response = wp_remote_get($api_url, array('timeout' => 15));
    $data = array();

    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        if (isset($decoded['response']['ranks']['teams'])) {
            $teams = $decoded['response']['ranks']['teams'];
            $data = array(
                'odi'  => array_slice($teams['odis'], 0, 10),
                't20'  => array_slice($teams['t20s'], 0, 10),
                'test' => array_slice($teams['tests'], 0, 10),
            );
        }
    }

    ob_start(); ?>

    <div class="icc-rankings-widget">
        <div class="widget-header"><h3>ICC Team Rankings</h3></div>

        <div class="tabs">
            <button class="tab-btn active" data-tab="odi">ODI</button>
            <button class="tab-btn" data-tab="t20">T20</button>
            <button class="tab-btn" data-tab="test">TEST</button>
        </div>

        <div id="rankings-content" class="rankings-content"></div>
        
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const data = <?php echo json_encode($data); ?>;
            const contentDiv = document.getElementById("rankings-content");
            const tabButtons = document.querySelectorAll(".tab-btn");

            function renderTab(tab) {
                const list = data[tab];
                if (!list) {
                    contentDiv.innerHTML = "<p style='text-align:center;color:#777;'>No data available.</p>";
                    return;
                }
                let html = `
                    <table class="rankings-table">
                        <thead><tr><th>Pos</th><th>Team</th><th>Rating</th></tr></thead><tbody>
                `;
                list.slice(0, 5).forEach(item => {
                    html += `
                        <tr>
                            <td>${item.rank}</td>
                            <td>
                                <img src="https://flagcdn.com/16x12/${item.team.toLowerCase().slice(0,2)}.png" class="team-flag">
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
                    renderTab(btn.dataset.tab);
                });
            });

            renderTab("odi");
        });
    </script>

    <?php
    return ob_get_clean();
}
?>
