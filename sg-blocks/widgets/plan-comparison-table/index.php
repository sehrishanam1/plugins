<?php
/**
 * Widget: Plan Comparison Table
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_plan_comparison_register' );

function sg_plan_comparison_register() {

    $widget_url  = SG_BLOCKS_URL  . 'widgets/plan-comparison-table/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/plan-comparison-table/';

    $js_ver  = file_exists( $widget_path . 'block.js'  ) ? filemtime( $widget_path . 'block.js'  ) : '1.0';
    $css_ver = file_exists( $widget_path . 'style.css' ) ? filemtime( $widget_path . 'style.css' ) : '1.0';

    wp_register_script(
        'sg-plan-comparison-editor',
        $widget_url . 'block.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
        $js_ver,
        true
    );

    wp_register_style(
        'sg-plan-comparison-style',
        $widget_url . 'style.css',
        array(),
        $css_ver
    );

    register_block_type( 'sg-blocks/plan-comparison-table', array(
        'editor_script'   => 'sg-plan-comparison-editor',
        'editor_style'    => 'sg-plan-comparison-style',
        'style'           => 'sg-plan-comparison-style',
        'render_callback' => 'sg_plan_comparison_render',
        'attributes'      => array(
            // Header
            'showBadge'        => array( 'type' => 'boolean', 'default' => true ),
            'badgeText'        => array( 'type' => 'string',  'default' => 'Pricing Breakdown' ),
            'headingBefore'    => array( 'type' => 'string',  'default' => 'Find the Plan' ),
            'headingHighlight' => array( 'type' => 'string',  'default' => "That's Best for You" ),
            'highlightColor'   => array( 'type' => 'string',  'default' => '#e0445e' ),
            // Layout / Colors
            'bgColor'          => array( 'type' => 'string',  'default' => '#0a0a0a' ),
            'labelColBg'       => array( 'type' => 'string',  'default' => '#0d9488' ),
            'labelColText'     => array( 'type' => 'string',  'default' => '#ffffff' ),
            'headerTextColor'  => array( 'type' => 'string',  'default' => '#ffffff' ),
            'rowBgEven'        => array( 'type' => 'string',  'default' => '#141414' ),
            'rowBgOdd'         => array( 'type' => 'string',  'default' => '#0f0f0f' ),
            'rowTextColor'     => array( 'type' => 'string',  'default' => '#cccccc' ),
            'dividerColor'     => array( 'type' => 'string',  'default' => '#222222' ),
            'checkColor'       => array( 'type' => 'string',  'default' => '#e0445e' ),
            'innerWidth'       => array( 'type' => 'string',  'default' => '860px' ),
            // Footer note
            'showFooterNote'   => array( 'type' => 'boolean', 'default' => true ),
            'footerNoteText'   => array( 'type' => 'string',  'default' => '* Hosting available from AED 99/mo' ),
            // Plans JSON  (name, price, priceSuffix, btnText, btnUrl, btnNewTab)
            'plans'            => array( 'type' => 'string',  'default' => '[]' ),
            // Sections JSON (label, rows[])
            // rows: { label, values[] }  value: '' | 'check' | 'dash' | any string
            'sections'         => array( 'type' => 'string',  'default' => '[]' ),
        ),
    ) );
}

/* ─────────────────────────────────────────────
   Render
───────────────────────────────────────────── */
function sg_plan_comparison_render( $attr ) {

    $plans = array();
    if ( ! empty( $attr['plans'] ) ) {
        $d = json_decode( $attr['plans'], true );
        if ( is_array( $d ) ) $plans = $d;
    }

    $sections = array();
    if ( ! empty( $attr['sections'] ) ) {
        $d = json_decode( $attr['sections'], true );
        if ( is_array( $d ) ) $sections = $d;
    }

    $bg           = esc_attr( $attr['bgColor']         ?? '#0a0a0a' );
    $label_bg     = esc_attr( $attr['labelColBg']      ?? '#0d9488' );
    $label_text   = esc_attr( $attr['labelColText']    ?? '#ffffff' );
    $header_text  = esc_attr( $attr['headerTextColor'] ?? '#ffffff' );
    $row_bg_even  = esc_attr( $attr['rowBgEven']       ?? '#141414' );
    $row_bg_odd   = esc_attr( $attr['rowBgOdd']        ?? '#0f0f0f' );
    $row_text     = esc_attr( $attr['rowTextColor']    ?? '#cccccc' );
    $divider      = esc_attr( $attr['dividerColor']    ?? '#222222' );
    $check_color  = esc_attr( $attr['checkColor']      ?? '#e0445e' );
    $inner_width  = esc_attr( $attr['innerWidth']      ?? '860px' );
    $hi_color     = esc_attr( $attr['highlightColor']  ?? '#e0445e' );

    $col_count    = count( $plans );

    ob_start();
?>
<section class="sg-pct" style="background:<?php echo $bg; ?>;">
    <div class="sg-pct__inner" style="max-width:<?php echo $inner_width; ?>;">

        <?php if ( ! empty( $attr['showBadge'] ) && ! empty( $attr['badgeText'] ) ) : ?>
        <div class="sg-pct__header-top">
            <span class="sg-pct__badge"><?php echo esc_html( $attr['badgeText'] ); ?></span>
        </div>
        <?php endif; ?>

        <?php
        $before    = $attr['headingBefore']    ?? '';
        $highlight = $attr['headingHighlight'] ?? '';
        if ( $before || $highlight ) :
        ?>
        <h2 class="sg-pct__heading">
            <?php if ( $before ) : ?>
            <span class="sg-pct__heading-plain" style="color:<?php echo $header_text; ?>;"><?php echo esc_html( $before ); ?> </span>
            <?php endif; ?>
            <?php if ( $highlight ) : ?>
            <span class="sg-pct__heading-hi" style="color:<?php echo $hi_color; ?>;"><?php echo esc_html( $highlight ); ?></span>
            <?php endif; ?>
        </h2>
        <?php endif; ?>

        <?php if ( ! empty( $plans ) && ! empty( $sections ) ) : ?>
        <div class="sg-pct__table-wrap" style="--sg-pct-divider:<?php echo $divider; ?>; --sg-pct-check:<?php echo $check_color; ?>;">
            <table class="sg-pct__table">
                <!-- Header row -->
                <thead>
                    <tr>
                        <th class="sg-pct__th sg-pct__th--label" style="background:<?php echo $label_bg; ?>;color:<?php echo $label_text; ?>;">
                            Compare Plan Details
                        </th>
                        <?php foreach ( $plans as $plan ) :
                            $pname   = esc_html( $plan['name']        ?? '' );
                            $price   = esc_html( $plan['price']       ?? '' );
                            $suffix  = esc_html( $plan['priceSuffix'] ?? '' );
                        ?>
                        <th class="sg-pct__th sg-pct__th--plan" style="color:<?php echo $header_text; ?>;">
                            <span class="sg-pct__plan-name"><?php echo $pname; ?></span>
                            <?php if ( $price ) : ?>
                            <span class="sg-pct__plan-price" style="color:<?php echo $hi_color; ?>;">
                                <?php echo $price; ?>
                                <?php if ( $suffix ) : ?><span class="sg-pct__plan-price-suffix" style="color:<?php echo $header_text; ?>;"><?php echo $suffix; ?></span><?php endif; ?>
                            </span>
                            <?php endif; ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $global_row = 0;
                foreach ( $sections as $section ) :
                    $section_label = $section['label'] ?? '';
                    $rows          = $section['rows']  ?? array();
                ?>
                    <!-- Section heading row -->
                    <?php if ( $section_label ) : ?>
                    <tr class="sg-pct__section-row" style="background:<?php echo $row_bg_odd; ?>;">
                        <td class="sg-pct__section-label" colspan="<?php echo $col_count + 1; ?>" style="color:<?php echo $row_text; ?>;">
                            <?php echo esc_html( $section_label ); ?>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <?php foreach ( $rows as $row ) :
                        $row_label  = $row['label']  ?? '';
                        $row_values = $row['values'] ?? array();
                        $row_bg     = ( $global_row % 2 === 0 ) ? $row_bg_even : $row_bg_odd;
                        $global_row++;
                    ?>
                    <tr class="sg-pct__row" style="background:<?php echo $row_bg; ?>;">
                        <td class="sg-pct__cell sg-pct__cell--label" style="background:<?php echo $label_bg; ?>;color:<?php echo $label_text; ?>;">
                            <?php echo esc_html( $row_label ); ?>
                        </td>
                        <?php foreach ( $plans as $pi => $plan ) :
                            $val = $row_values[ $pi ] ?? '';
                        ?>
                        <td class="sg-pct__cell sg-pct__cell--value" style="color:<?php echo $row_text; ?>;">
                            <?php if ( $val === 'check' ) : ?>
                                <span class="sg-pct__icon sg-pct__icon--check">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                            <?php elseif ( $val === 'dash' || $val === '' ) : ?>
                                <span class="sg-pct__icon sg-pct__icon--dash">—</span>
                            <?php else : ?>
                                <span class="sg-pct__text-value"><?php echo esc_html( $val ); ?></span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>

                <?php endforeach; ?>
                </tbody>
                <!-- CTA row -->
                <tfoot>
                    <tr class="sg-pct__cta-row" style="background:<?php echo $bg; ?>;">
                        <td class="sg-pct__cell" style="background:<?php echo $label_bg; ?>;"></td>
                        <?php foreach ( $plans as $plan ) :
                            $btn_text   = esc_html( $plan['btnText']  ?? 'Get Started' );
                            $btn_url    = esc_url(  $plan['btnUrl']   ?? '#' );
                            $btn_target = ! empty( $plan['btnNewTab'] ) ? '_blank' : '_self';
                        ?>
                        <td class="sg-pct__cell sg-pct__cta-cell">
                            <a href="<?php echo $btn_url; ?>" target="<?php echo $btn_target; ?>" class="sg-pct__btn"><?php echo $btn_text; ?></a>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $attr['showFooterNote'] ) && ! empty( $attr['footerNoteText'] ) ) : ?>
        <p class="sg-pct__footer-note"><?php echo esc_html( $attr['footerNoteText'] ); ?></p>
        <?php endif; ?>

    </div>
</section>
<?php
    return ob_get_clean();
}
