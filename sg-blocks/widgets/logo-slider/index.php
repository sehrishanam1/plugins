<?php
/**
 * Widget: Logo Slider
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_logo_slider_register' );

function sg_logo_slider_register() {

    $widget_url  = SG_BLOCKS_URL  . 'widgets/logo-slider/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/logo-slider/';

    $js_ver  = file_exists( $widget_path . 'block.js'  ) ? filemtime( $widget_path . 'block.js'  ) : '1.0';
    $css_ver = file_exists( $widget_path . 'style.css' ) ? filemtime( $widget_path . 'style.css' ) : '1.0';

    wp_register_script(
        'sg-logo-slider-editor',
        $widget_url . 'block.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
        $js_ver,
        true
    );

    wp_register_style(
        'sg-logo-slider-style',
        $widget_url . 'style.css',
        array(),
        $css_ver
    );

    register_block_type( 'sg-blocks/logo-slider', array(
        'editor_script'   => 'sg-logo-slider-editor',
        'editor_style'    => 'sg-logo-slider-style',
        'style'           => 'sg-logo-slider-style',
        'render_callback' => 'sg_logo_slider_render',
        'attributes'      => array(
            'title'           => array( 'type' => 'string',  'default' => 'Trusted by' ),
            'showTitle'       => array( 'type' => 'boolean', 'default' => true ),
            'showBorder'      => array( 'type' => 'boolean', 'default' => true ),
            'logos'           => array( 'type' => 'string',  'default' => '[]' ),
            'speed'           => array( 'type' => 'number',  'default' => 30 ),
            'logoHeight'      => array( 'type' => 'number',  'default' => 32 ),
            'bgColor'         => array( 'type' => 'string',  'default' => '#111111' ),
            'borderColor'     => array( 'type' => 'string',  'default' => '#2a2a2a' ),
            'logoOpacity'     => array( 'type' => 'number',  'default' => 50 ),
        ),
    ) );
}

function sg_logo_slider_render( $attr ) {

    $logos = array();
    if ( ! empty( $attr['logos'] ) ) {
        $decoded = json_decode( $attr['logos'], true );
        if ( is_array( $decoded ) ) {
            $logos = $decoded;
        }
    }

    if ( empty( $logos ) ) {
        return '';
    }

    $uid          = 'sg-ls-' . uniqid();
    $speed        = intval( $attr['speed'] );
    $logo_height  = intval( $attr['logoHeight'] );
    $bg_color     = esc_attr( $attr['bgColor'] );
    $border_color = esc_attr( $attr['borderColor'] );
    $opacity      = floatval( $attr['logoOpacity'] ) / 100;

    // Build logo items HTML — duplicated for seamless infinite loop
    $items_html = '';
    foreach ( $logos as $logo ) {
        $url = isset( $logo['url'] ) ? $logo['url'] : '';
        $alt = isset( $logo['alt'] ) ? $logo['alt'] : '';
        if ( ! $url ) continue;
        $items_html .= '<div class="sg-ls__item"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" style="height:' . $logo_height . 'px;opacity:' . $opacity . ';" /></div>';
    }

    // Duplicate track for seamless loop
    $track_html = $items_html . $items_html;

    // Calculate animation duration based on number of logos and speed setting
    // More logos = longer duration so speed feels consistent
    $logo_count = count( $logos );
    $duration   = max( 5, ( $logo_count * $speed ) / 5 );

    ob_start();
    ?>
    <div class="sg-ls" id="<?php echo esc_attr( $uid ); ?>" style="background-color:<?php echo $bg_color; ?>;<?php echo ! empty( $attr['showBorder'] ) ? 'border-top:1px solid ' . $border_color . ';border-bottom:1px solid ' . $border_color . ';' : ''; ?>">

        <?php if ( ! empty( $attr['showTitle'] ) && ! empty( $attr['title'] ) ) : ?>
        <div class="sg-ls__title"><?php echo esc_html( $attr['title'] ); ?></div>
        <?php endif; ?>

        <div class="sg-ls__viewport">
            <div class="sg-ls__track" style="animation-duration:<?php echo $duration; ?>s;">
                <?php echo $track_html; ?>
            </div>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
