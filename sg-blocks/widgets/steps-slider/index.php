<?php
/**
 * Widget: Steps Slider
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_steps_slider_register' );

function sg_steps_slider_register() {

    $widget_url  = SG_BLOCKS_URL  . 'widgets/steps-slider/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/steps-slider/';

    $js_ver  = file_exists( $widget_path . 'block.js'  ) ? filemtime( $widget_path . 'block.js'  ) : '1.0';
    $css_ver = file_exists( $widget_path . 'style.css' ) ? filemtime( $widget_path . 'style.css' ) : '1.0';
    $fe_ver  = file_exists( $widget_path . 'slider.js' ) ? filemtime( $widget_path . 'slider.js' ) : '1.0';

    wp_register_script(
        'sg-steps-slider-editor',
        $widget_url . 'block.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
        $js_ver,
        true
    );

    wp_register_script(
        'sg-steps-slider-frontend',
        $widget_url . 'slider.js',
        array(),
        $fe_ver,
        true
    );

    wp_register_style(
        'sg-steps-slider-style',
        $widget_url . 'style.css',
        array(),
        $css_ver
    );

    register_block_type( 'sg-blocks/steps-slider', array(
        'editor_script'   => 'sg-steps-slider-editor',
        'editor_style'    => 'sg-steps-slider-style',
        'style'           => 'sg-steps-slider-style',
        'script'          => 'sg-steps-slider-frontend',
        'render_callback' => 'sg_steps_slider_render',
        'attributes'      => array(
            // Left panel
            'badgeText'       => array( 'type' => 'string',  'default' => 'How We Build Your Website' ),
            'showBadge'       => array( 'type' => 'boolean', 'default' => true ),
            'headingLine1'    => array( 'type' => 'string',  'default' => 'From Brief to Live' ),
            'headingLine2'    => array( 'type' => 'string',  'default' => 'in 6 Steps.' ),
            'headingLine2Color' => array( 'type' => 'string', 'default' => '#00c8ff' ),
            'subtext'         => array( 'type' => 'string',  'default' => "No surprises. No chasing.\nYou stay focused on your business." ),
            'leftBgColor'     => array( 'type' => 'string',  'default' => '#0d1117' ),
            // Steps
            'steps'           => array( 'type' => 'string',  'default' => '[]' ),
            'stepBgColor'     => array( 'type' => 'string',  'default' => '#1e2028' ),
            'stepBgGradient'  => array( 'type' => 'boolean', 'default' => true ),
            'stepNumberColor' => array( 'type' => 'string',  'default' => '#888888' ),
            'stepTitleColor'  => array( 'type' => 'string',  'default' => '#ffffff' ),
            'stepDescColor'   => array( 'type' => 'string',  'default' => '#999999' ),
            // Slider settings
            'autoPlay'        => array( 'type' => 'boolean', 'default' => true ),
            'autoPlayDelay'   => array( 'type' => 'number',  'default' => 3000 ),
            'visibleCards'    => array( 'type' => 'number',  'default' => 3 ),
            'showArrows'      => array( 'type' => 'boolean', 'default' => true ),
            'arrowColor'      => array( 'type' => 'string',  'default' => '#ffffff' ),
            // Section
            'bgColor'         => array( 'type' => 'string',  'default' => '#000000' ),
            'innerWidth'      => array( 'type' => 'string',  'default' => '1200px' ),
        ),
    ) );
}

function sg_steps_slider_render( $attr ) {

    $steps = array();
    if ( ! empty( $attr['steps'] ) ) {
        $decoded = json_decode( $attr['steps'], true );
        if ( is_array( $decoded ) ) $steps = $decoded;
    }

    $uid = 'sg-ss-' . uniqid();

    $config = array(
        'autoPlay'      => ! empty( $attr['autoPlay'] ),
        'autoPlayDelay' => intval( $attr['autoPlayDelay'] ),
        'visibleCards'  => intval( $attr['visibleCards'] ),
        'showArrows'    => ! empty( $attr['showArrows'] ),
    );

    ob_start();
    ?>
    <section class="sg-ss" style="background-color:<?php echo esc_attr($attr['bgColor']); ?>;">
        <div class="sg-ss__inner" style="max-width:<?php echo esc_attr($attr['innerWidth']); ?>;margin:0 auto;">

            <!-- Left static panel -->
            <div class="sg-ss__left" style="background-color:<?php echo esc_attr($attr['leftBgColor']); ?>;">
                <?php if ( ! empty( $attr['showBadge'] ) && ! empty( $attr['badgeText'] ) ) : ?>
                <span class="sg-ss__badge"><?php echo esc_html( $attr['badgeText'] ); ?></span>
                <?php endif; ?>

                <h2 class="sg-ss__heading">
                    <?php if ( ! empty( $attr['headingLine1'] ) ) : ?>
                    <span class="sg-ss__heading-line1"><?php echo esc_html( $attr['headingLine1'] ); ?></span><br>
                    <?php endif; ?>
                    <?php if ( ! empty( $attr['headingLine2'] ) ) : ?>
                    <span class="sg-ss__heading-line2" style="color:<?php echo esc_attr($attr['headingLine2Color']); ?>"><?php echo esc_html( $attr['headingLine2'] ); ?></span>
                    <?php endif; ?>
                </h2>

                <?php if ( ! empty( $attr['subtext'] ) ) : ?>
                <p class="sg-ss__subtext"><?php echo nl2br( esc_html( $attr['subtext'] ) ); ?></p>
                <?php endif; ?>
            </div>

            <!-- Slider -->
            <div class="sg-ss__slider-wrap" id="<?php echo esc_attr($uid); ?>"
                 data-config="<?php echo esc_attr( json_encode( $config ) ); ?>">

                <?php if ( ! empty( $attr['showArrows'] ) ) : ?>
                <button class="sg-ss__arrow sg-ss__arrow--prev" aria-label="Previous" style="color:<?php echo esc_attr($attr['arrowColor']); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <?php endif; ?>

                <div class="sg-ss__viewport">
                    <div class="sg-ss__track" style="--visible:<?php echo intval($attr['visibleCards']); ?>;">
                        <?php foreach ( $steps as $i => $step ) :
                            $num   = str_pad( $i + 1, 2, '0', STR_PAD_LEFT );
                            $title = isset( $step['title'] ) ? $step['title'] : '';
                            $desc  = isset( $step['desc']  ) ? $step['desc']  : '';

                            $card_bg = '';
                            if ( ! empty( $attr['stepBgGradient'] ) ) {
                                $card_bg = 'background: linear-gradient(145deg, #2a2c35 0%, #1a1c24 100%);';
                            } else {
                                $card_bg = 'background-color:' . esc_attr( $attr['stepBgColor'] ) . ';';
                            }
                        ?>
                        <div class="sg-ss__card" style="<?php echo $card_bg; ?>">
                            <span class="sg-ss__num" style="color:<?php echo esc_attr($attr['stepNumberColor']); ?>"><?php echo $num; ?></span>
                            <?php if ( $title ) : ?>
                            <h3 class="sg-ss__card-title" style="color:<?php echo esc_attr($attr['stepTitleColor']); ?>"><?php echo esc_html( $title ); ?></h3>
                            <?php endif; ?>
                            <?php if ( $desc ) : ?>
                            <p class="sg-ss__card-desc" style="color:<?php echo esc_attr($attr['stepDescColor']); ?>"><?php echo esc_html( $desc ); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ( ! empty( $attr['showArrows'] ) ) : ?>
                <button class="sg-ss__arrow sg-ss__arrow--next" aria-label="Next" style="color:<?php echo esc_attr($attr['arrowColor']); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <?php endif; ?>

            </div><!-- .sg-ss__slider-wrap -->

        </div>
    </section>
    <?php
    return ob_get_clean();
}
