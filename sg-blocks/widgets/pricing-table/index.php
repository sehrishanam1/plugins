<?php
/**
 * Widget: Pricing Table
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_pricing_table_register' );

function sg_pricing_table_register() {

    $widget_url  = SG_BLOCKS_URL  . 'widgets/pricing-table/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/pricing-table/';

    $js_ver  = file_exists( $widget_path . 'block.js'  ) ? filemtime( $widget_path . 'block.js'  ) : '1.0';
    $css_ver = file_exists( $widget_path . 'style.css' ) ? filemtime( $widget_path . 'style.css' ) : '1.0';

    wp_register_script(
        'sg-pricing-table-editor',
        $widget_url . 'block.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
        $js_ver,
        true
    );

    wp_register_style(
        'sg-pricing-table-style',
        $widget_url . 'style.css',
        array(),
        $css_ver
    );

    register_block_type( 'sg-blocks/pricing-table', array(
        'editor_script'   => 'sg-pricing-table-editor',
        'editor_style'    => 'sg-pricing-table-style',
        'style'           => 'sg-pricing-table-style',
        'render_callback' => 'sg_pricing_table_render',
        'attributes'      => array(
            // Section header
            'showBadge'       => array( 'type' => 'boolean', 'default' => true ),
            'badgeText'       => array( 'type' => 'string',  'default' => 'What We Build — Service Tiers' ),
            'showHeading'     => array( 'type' => 'boolean', 'default' => true ),
            'heading'         => array( 'type' => 'string',  'default' => 'Choose Your Website' ),
            'showSubheading'  => array( 'type' => 'boolean', 'default' => true ),
            'subheading'      => array( 'type' => 'string',  'default' => 'Both are custom-built. Zero templates. 100% yours.' ),
            // Footer note
            'showFooterNote'  => array( 'type' => 'boolean', 'default' => true ),
            'footerNoteLabel' => array( 'type' => 'string',  'default' => 'HOSTING:' ),
            'footerNoteText'  => array( 'type' => 'string',  'default' => 'AED 99/mo or AED 299/mo' ),
            // Background
            'bgColor'         => array( 'type' => 'string',  'default' => '#050505' ),
            'bgImage'         => array( 'type' => 'string',  'default' => '' ),
            'bgImageId'       => array( 'type' => 'integer', 'default' => 0 ),
            'bgImageSide'     => array( 'type' => 'string',  'default' => 'left' ),
            // Layout
            'innerWidth'      => array( 'type' => 'string',  'default' => '900px' ),
            // Plans repeater
            'plans'           => array( 'type' => 'string',  'default' => '[]' ),
        ),
    ) );
}

function sg_pricing_table_render( $attr ) {

    $plans = array();
    if ( ! empty( $attr['plans'] ) ) {
        $decoded = json_decode( $attr['plans'], true );
        if ( is_array( $decoded ) ) $plans = $decoded;
    }

    $bg_color    = esc_attr( $attr['bgColor'] );
    $inner_width = esc_attr( $attr['innerWidth'] );

    // Section bg style
    $section_style = 'background-color:' . $bg_color . ';';
    if ( ! empty( $attr['bgImage'] ) ) {
        $side = $attr['bgImageSide'] === 'right' ? 'right center' : 'left center';
        $section_style .= 'background-image:url(' . esc_url( $attr['bgImage'] ) . ');background-size:auto 100%;background-position:' . $side . ';background-repeat:no-repeat;';
    }

    ob_start();
    ?>
    <section class="sg-pt" style="<?php echo $section_style; ?>">
        <div class="sg-pt__inner" style="max-width:<?php echo $inner_width; ?>;margin:0 auto;">

            <?php if ( ! empty( $attr['showBadge'] ) && ! empty( $attr['badgeText'] ) ) : ?>
            <div class="sg-pt__header-top">
                <span class="sg-pt__badge"><?php echo esc_html( $attr['badgeText'] ); ?></span>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $attr['showHeading'] ) && ! empty( $attr['heading'] ) ) : ?>
            <h2 class="sg-pt__heading"><?php echo esc_html( $attr['heading'] ); ?></h2>
            <?php endif; ?>

            <?php if ( ! empty( $attr['showSubheading'] ) && ! empty( $attr['subheading'] ) ) : ?>
            <p class="sg-pt__subheading"><?php echo esc_html( $attr['subheading'] ); ?></p>
            <?php endif; ?>

            <?php if ( ! empty( $plans ) ) : ?>
            <div class="sg-pt__grid sg-pt__grid--count-<?php echo count( $plans ); ?>">
                <?php foreach ( $plans as $plan ) :
                    $price        = isset( $plan['price'] )       ? $plan['price']       : '';
                    $name         = isset( $plan['name'] )        ? $plan['name']        : '';
                    $desc         = isset( $plan['desc'] )        ? $plan['desc']        : '';
                    $features     = isset( $plan['features'] )    ? $plan['features']    : '';
                    $btn_text     = isset( $plan['btnText'] )     ? $plan['btnText']     : 'Get Started';
                    $btn_url      = isset( $plan['btnUrl'] )      ? $plan['btnUrl']      : '#';
                    $btn_target   = ! empty( $plan['btnNewTab'] ) ? '_blank'             : '_self';
                    $is_featured  = ! empty( $plan['featured'] );
                    $card_bg      = isset( $plan['cardBg'] )      ? $plan['cardBg']      : ( $is_featured ? '#0d9488' : '#1a1a1a' );
                    $card_border  = isset( $plan['cardBorder'] )  ? $plan['cardBorder']  : ( $is_featured ? '#0d9488' : '#2a2a2a' );
                    $price_color  = isset( $plan['priceColor'] )  ? $plan['priceColor']  : '#ffffff';
                    $check_color  = isset( $plan['checkColor'] )  ? $plan['checkColor']  : ( $is_featured ? 'rgba(255,255,255,0.8)' : '#555555' );

                    $feature_list = array_filter( array_map( 'trim', explode( "\n", $features ) ) );

                    $card_class = 'sg-pt__card';
                    if ( $is_featured ) $card_class .= ' sg-pt__card--featured';
                ?>
                <div class="<?php echo $card_class; ?>" style="background:<?php echo esc_attr($card_bg); ?>;border-color:<?php echo esc_attr($card_border); ?>;">

                    <?php if ( $price ) : ?>
                    <div class="sg-pt__price" style="color:<?php echo esc_attr($price_color); ?>;"><?php echo esc_html( $price ); ?></div>
                    <?php endif; ?>

                    <?php if ( $name ) : ?>
                    <h3 class="sg-pt__plan-name"><?php echo esc_html( $name ); ?></h3>
                    <?php endif; ?>

                    <?php if ( $desc ) : ?>
                    <p class="sg-pt__plan-desc"><?php echo esc_html( $desc ); ?></p>
                    <?php endif; ?>

                    <?php if ( ! empty( $feature_list ) ) : ?>
                    <ul class="sg-pt__features">
                        <?php foreach ( $feature_list as $feature ) : ?>
                        <li class="sg-pt__feature">
                            <span class="sg-pt__check" style="color:<?php echo esc_attr($check_color); ?>;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </span>
                            <span><?php echo esc_html( $feature ); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <?php if ( $btn_text ) : ?>
                    <div class="sg-pt__btn-wrap">
                        <a href="<?php echo esc_url( $btn_url ); ?>" target="<?php echo $btn_target; ?>" class="sg-pt__btn"><?php echo esc_html( $btn_text ); ?></a>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $attr['showFooterNote'] ) && ( ! empty( $attr['footerNoteLabel'] ) || ! empty( $attr['footerNoteText'] ) ) ) : ?>
            <div class="sg-pt__footer-note">
                <?php if ( ! empty( $attr['footerNoteLabel'] ) ) : ?>
                <span class="sg-pt__footer-label"><?php echo esc_html( $attr['footerNoteLabel'] ); ?></span>
                <?php endif; ?>
                <?php if ( ! empty( $attr['footerNoteText'] ) ) : ?>
                <span class="sg-pt__footer-text"><?php echo esc_html( $attr['footerNoteText'] ); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>
    <?php
    return ob_get_clean();
}
