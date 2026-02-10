<?php
if (!defined('ABSPATH')) exit;

// Ensure we have $series_data available
if (empty($series_data)) return;
$players = isset($series_data['overview']['players_on_top']) && is_array($series_data['overview']['players_on_top'])
    ? $series_data['overview']['players_on_top']
    : [];


// ✅ Only render the section if at least one category (most_wickets, most_runs, etc.) has data
$has_any_data = (
    !empty($players['most_wickets']) ||
    !empty($players['most_runs']) ||
    !empty($players['most_sixes']) ||
    !empty($players['highest_score']) ||
    !empty($players['best_figures'])
);

if (!$has_any_data) return;
?>

<div class="players-on-top-section">
    <h3 class="section-title">Players on Top</h3>

    <div class="players-grid">

        <!-- Most Wickets -->
        <?php if (!empty($players['most_wickets'])): ?>
            <div class="player-card">
                <h4 class="card-title">Most Wickets</h4>
                <?php
                $top = $players['most_wickets'][0] ?? null;
                if ($top): ?>
                    <div class="player-top">
                        <?php if (!empty($top['player_image'])): ?>
                            <img src="<?php echo esc_url($top['player_image']); ?>"
                                alt="<?php echo esc_attr($top['name']); ?>"
                                class="player-img">
                        <?php endif; ?>
                        <div class="player-info">
                            <strong><?php echo esc_html($top['name']); ?></strong>
                            <p><span class="title-win">Wickets:</span><br /> <span class="number"><?php echo esc_html($top['wickets'] ?? $top['value'] ?? '0'); ?></span></p>
                        </div>
                    </div>
                <?php endif; ?>
                <ul class="player-list">
                    <li>
                        <span class="pos-title">POS</span>
                        <span class="name-title">Name</span>
                        <span class="val-title">Wickets</span>
                    </li>
                    <?php foreach (array_slice($players['most_wickets'], 1, 4) as $pos => $player):
                    ?>
                        <li>
                            <span class="pos"><?php echo $pos + 2; ?>.</span>
                            <span class="name"><?php echo esc_html($player['name']); ?></span>
                            <span class="val"><?php echo esc_html($player['wickets'] ?? $player['value'] ?? ''); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Most Runs -->
        <?php if (!empty($players['most_runs'])): ?>
            <div class="player-card">
                <h4 class="card-title">Most Runs</h4>
                <?php
                $top = $players['most_runs'][0] ?? null;
                if ($top): ?>
                    <div class="player-top">
                        <?php if (!empty($top['player_image'])): ?>
                            <img src="<?php echo esc_url($top['player_image']); ?>"
                                alt="<?php echo esc_attr($top['name']); ?>"
                                class="player-img">
                        <?php endif; ?>
                        <div class="player-info">
                            <strong><?php echo esc_html($top['name']); ?></strong>
                            <p><span class="title-win">Runs:</span><br /> <span class="number"><?php echo esc_html($top['runs'] ?? $top['value'] ?? '0'); ?></span></p>
                        </div>
                    </div>
                <?php endif; ?>
                <ul class="player-list">
                    <li>
                        <span class="pos-title">POS</span>
                        <span class="name-title">Name</span>
                        <span class="val-title">Wickets</span>
                    </li>
                    <?php foreach (array_slice($players['most_runs'], 1, 4) as $pos => $player): ?>
                        <li>
                            <span class="pos"><?php echo $pos + 2; ?>.</span>
                            <span class="name"><?php echo esc_html($player['name']); ?></span>
                            <span class="val"><?php echo esc_html($player['runs'] ?? $player['value'] ?? ''); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif;  ?>

        <!-- Most Sixes -->
        <?php if (!empty($players['most_sixes'])): ?>
            <div class="player-card">
                <h4 class="card-title">Most Sixes</h4>
                <?php
                $top = $players['most_sixes'][0] ?? null;
                if ($top): ?>
                    <div class="player-top">
                        <?php if (!empty($top['player_image'])): ?>
                            <img src="<?php echo esc_url($top['player_image']); ?>"
                                alt="<?php echo esc_attr($top['name']); ?>"
                                class="player-img">
                        <?php endif; ?>
                        <div class="player-info">
                            <strong><?php echo esc_html($top['name']); ?></strong>
                            <span class="player-stat">
                                <p><span class="title-win"><?php echo isset($top['sixes']) ? 'Sixes' : (isset($top['wickets']) ? 'Wickets' : 'Runs'); ?></span>
                                    <br />
                                    <span class="number"><?php echo esc_html($top['sixes'] ?? $top['wickets'] ?? $top['value'] ?? '0'); ?></span>
                                </p>
                            </span>
                        </div>
                    </div>

                <?php endif; ?>
                <ul class="player-list">
                    <li>
                        <span class="pos-title">POS</span>
                        <span class="name-title">Name</span>
                        <span class="val-title">Wickets</span>
                    </li>
                    <?php foreach (array_slice($players['most_sixes'], 1, 4) as $pos => $player): ?>
                        <li>
                            <span class="pos"><?php echo $pos + 2; ?>.</span>
                            <span class="name"><?php echo esc_html($player['name']); ?></span>
                            <span class="val"><?php echo esc_html($player['sixes'] ?? $player['value'] ?? ''); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Highest Score -->
        <?php if (!empty($players['highest_score'])): ?>
            <div class="player-card">
                <h4 class="card-title">Highest Score</h4>
                <?php
                $top = $players['highest_score'][0] ?? null;
                if ($top): ?>
                    <div class="player-top">
                        <?php if (!empty($top['player_image'])): ?>
                            <img src="<?php echo esc_url($top['player_image']); ?>" alt="<?php echo esc_attr($top['name']); ?>" class="player-img">
                        <?php endif; ?>
                        <div class="player-info">
                            <strong><?php echo esc_html($top['name']); ?></strong>
                            <span class="player-stat">
                                <p>Runs<br /><span class="stat-value"><?php echo esc_html($top['score']); ?></span> </p>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <ul class="player-list">
                    <li>
                        <span class="pos-title">POS</span>
                        <span class="name-title">Name</span>
                        <span class="val-title">Wickets</span>
                    </li>
                    <?php foreach (array_slice($players['highest_score'], 1, 4) as $pos => $player): ?>
                        <li>
                            <span class="pos"><?php echo $pos + 2; ?>.</span>
                            <span class="name"><?php echo esc_html($player['name']); ?></span>
                            <span class="val"><?php echo esc_html($player['score']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($players['best_figures'])): ?>
            <div class="player-card">
                <h4 class="card-title">Best Figures</h4>

                <?php
                $top = $players['best_figures'][0] ?? null;
                if ($top): ?>
                    <div class="player-top">
                        <?php if (!empty($top['player_image'])): ?>
                            <img src="<?php echo esc_url($top['player_image']); ?>"
                                alt="<?php echo esc_attr($top['name']); ?>"
                                class="player-img">
                        <?php endif; ?>
                        <div class="player-info">
                            <strong><?php echo esc_html($top['name']); ?></strong>
                            <span class="player-stat">
                                <p>Runs<br /><span class="stat-value"><span class="stat-value"><?php echo esc_html($top['figures']); ?></span>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <ul class="player-list">
                    <li>
                        <span class="pos-title">POS</span>
                        <span class="name-title">Name</span>
                        <span class="val-title">Wickets</span>
                    </li>
                    <?php foreach (array_slice($players['best_figures'], 1, 4) as $pos => $player): ?>
                        <li>
                            <span class="pos"><?php echo $pos + 2; ?>.</span>
                            <span class="name"><?php echo esc_html($player['name']); ?></span>
                            <span class="val"><?php echo esc_html($player['figures']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>
</div>