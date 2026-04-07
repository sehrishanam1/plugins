<?php
/**
 * Widget: Feature Grid
 */

if (!defined('ABSPATH'))
    exit;

add_action('init', 'sg_feature_grid_register');

function sg_feature_grid_register()
{

    $widget_url = SG_BLOCKS_URL . 'widgets/feature-grid/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/feature-grid/';

    $js_ver = file_exists($widget_path . 'block.js') ? filemtime($widget_path . 'block.js') : '1.0';
    $css_ver = file_exists($widget_path . 'style.css') ? filemtime($widget_path . 'style.css') : '1.0';

    wp_register_script(
        'sg-feature-grid-editor',
        $widget_url . 'block.js',
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'),
        $js_ver,
        true
    );

    wp_register_style(
        'sg-feature-grid-style',
        $widget_url . 'style.css',
        array(),
        $css_ver
    );

    register_block_type('sg-blocks/feature-grid', array(
        'editor_script' => 'sg-feature-grid-editor',
        'editor_style' => 'sg-feature-grid-style',
        'style' => 'sg-feature-grid-style',
        'render_callback' => 'sg_feature_grid_render',
        'attributes' => array(
            // Section header
            'showBadge' => array('type' => 'boolean', 'default' => true),
            'badgeText' => array('type' => 'string', 'default' => 'Whats wrong with Your Current Website'),
            'showHeading' => array('type' => 'boolean', 'default' => true),
            'headingLine1' => array('type' => 'string', 'default' => 'Most Websites'),
            'headingLine2' => array('type' => 'string', 'default' => 'Leak Revenue'),
            'headingLine2Color' => array('type' => 'string', 'default' => '#00c8ff'),
            'showSubheading' => array('type' => 'boolean', 'default' => true),
            'subheading' => array('type' => 'string', 'default' => "If your website was built by a freelancer, a junior dev, or an offshore agency — it was probably designed to look good in a proposal."),
            // Grid
            'columns' => array('type' => 'number', 'default' => 3),
            'cards' => array('type' => 'string', 'default' => '[]'),
            // Style
            'bgColor' => array('type' => 'string', 'default' => '#000000'),
            'cardBgColor' => array('type' => 'string', 'default' => '#141414'),
            'cardBorderColor' => array('type' => 'string', 'default' => '#2a2a2a'),
            'innerWidth' => array('type' => 'string', 'default' => '1100px'),
            'iconColor' => array('type' => 'string', 'default' => '#00c8ff'),
        ),
    ));
}

function sg_feature_grid_render($attr)
{

    $cards = array();
    if (!empty($attr['cards'])) {
        $decoded = json_decode($attr['cards'], true);
        if (is_array($decoded))
            $cards = $decoded;
    }

    $columns = max(1, min(6, intval($attr['columns'])));
    $inner_width = esc_attr($attr['innerWidth']);
    $bg = esc_attr($attr['bgColor']);
    $card_bg = esc_attr($attr['cardBgColor']);
    $card_border = esc_attr($attr['cardBorderColor']);
    $icon_color = esc_attr($attr['iconColor']);
    $h2_color = esc_attr($attr['headingLine2Color']);

    ob_start();
?>
    <section class="sg-fg" style="background-color:<?php echo $bg; ?>;">
        <div class="sg-fg__inner" style="max-width:<?php echo $inner_width; ?>;margin:0 auto;">

            <?php if (!empty($attr['showBadge']) && !empty($attr['badgeText'])): ?>
            <div class="sg-fg__header-top">
                <span class="sg-fg__badge"><?php echo esc_html($attr['badgeText']); ?></span>
            </div>
            <?php
    endif; ?>

            <?php if ((!empty($attr['showHeading']) && (!empty($attr['headingLine1']) || !empty($attr['headingLine2'])))): ?>
            <h2 class="sg-fg__heading">
                <?php if (!empty($attr['headingLine1'])): ?>
                <span class="sg-fg__heading-line1"><?php echo esc_html($attr['headingLine1']); ?></span>
                <?php
        endif; ?>
                <?php if (!empty($attr['headingLine2'])): ?>
                <span class="sg-fg__heading-line2" style="color:<?php echo $h2_color; ?>"><?php echo esc_html($attr['headingLine2']); ?></span>
                <?php
        endif; ?>
            </h2>
            <?php
    endif; ?>

            <?php if (!empty($attr['showSubheading']) && !empty($attr['subheading'])): ?>
            <p class="sg-fg__subheading"><?php echo esc_html($attr['subheading']); ?></p>
            <?php
    endif; ?>

            <?php if (!empty($cards)): ?>
            <div class="sg-fg__grid sg-fg__grid--cols-<?php echo $columns; ?>">
                <?php foreach ($cards as $card):
            $card_icon_url = isset($card['iconUrl']) ? $card['iconUrl'] : '';
            $card_icon_svg = isset($card['iconSvg']) ? $card['iconSvg'] : '';
            $card_title = isset($card['title']) ? $card['title'] : '';
            $card_desc = isset($card['desc']) ? $card['desc'] : '';
            $card_icon_col = isset($card['iconColor']) ? $card['iconColor'] : $icon_color;
            $custom_bg = isset($card['bgColor']) ? $card['bgColor'] : $card_bg;
            $custom_border = isset($card['borderColor']) ? $card['borderColor'] : $card_border;
?>
                <div class="sg-fg__card" style="background:<?php echo esc_attr($custom_bg); ?>;border-color:<?php echo esc_attr($custom_border); ?>;">
                    <?php if ($card_icon_url || $card_icon_svg): ?>
                    <div class="sg-fg__card-icon" style="color:<?php echo esc_attr($card_icon_col); ?>;">
                        <?php if ($card_icon_url): ?>
                        <img src="<?php echo esc_url($card_icon_url); ?>" alt="" style="height:36px;width:auto;" />
                        <?php
                elseif ($card_icon_svg): ?>
                        <?php echo wp_kses($card_icon_svg, sg_fg_allowed_svg_tags()); ?>
                        <?php
                endif; ?>
                    </div>
                    <?php
            endif; ?>

                    <?php if ($card_title): ?>
                    <h3 class="sg-fg__card-title"><?php echo esc_html($card_title); ?></h3>
                    <?php
            endif; ?>

                    <?php if ($card_desc): ?>
                    <p class="sg-fg__card-desc"><?php echo esc_html($card_desc); ?></p>
                    <?php
            endif; ?>
                </div>
                <?php
        endforeach; ?>
            </div>
            <?php
    endif; ?>

        </div>
    </section>
    <?php
    return ob_get_clean();
}

function sg_fg_allowed_svg_tags()
{
    return array(
        'svg' => array('xmlns' => array(), 'viewbox' => array(), 'viewBox' => array(), 'width' => array(), 'height' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array(), 'class' => array(), 'aria-hidden' => array()),
        'path' => array('d' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array(), 'stroke-linecap' => array(), 'stroke-linejoin' => array()),
        'circle' => array('cx' => array(), 'cy' => array(), 'r' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array()),
        'rect' => array('x' => array(), 'y' => array(), 'width' => array(), 'height' => array(), 'rx' => array(), 'fill' => array(), 'stroke' => array()),
        'line' => array('x1' => array(), 'y1' => array(), 'x2' => array(), 'y2' => array(), 'stroke' => array(), 'stroke-width' => array(), 'stroke-linecap' => array()),
        'polyline' => array('points' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array(), 'stroke-linecap' => array(), 'stroke-linejoin' => array()),
        'polygon' => array('points' => array(), 'fill' => array(), 'stroke' => array()),
        'g' => array('fill' => array(), 'stroke' => array(), 'transform' => array()),
    );
}

add_filter('upload_mimes', 'sg_fg_allow_svg');
function sg_fg_allow_svg($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}

add_filter('wp_check_filetype_and_ext', 'sg_fg_fix_svg_filetype', 10, 4);
function sg_fg_fix_svg_filetype($data, $file, $filename, $mimes)
{
    $filetype = wp_check_filetype($filename, $mimes);
    return [
        'ext' => $filetype['ext'],
        'type' => $filetype['type'],
        'proper_filename' => $data['proper_filename'],
    ];
}