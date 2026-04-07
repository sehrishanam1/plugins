<?php
/**
 * Widget: FAQ Section
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_faq_section_register' );

function sg_faq_section_register() {

    $widget_url  = SG_BLOCKS_URL  . 'widgets/faq-section/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/faq-section/';

    $js_ver  = file_exists( $widget_path . 'block.js'  ) ? filemtime( $widget_path . 'block.js'  ) : '1.0';
    $css_ver = file_exists( $widget_path . 'style.css' ) ? filemtime( $widget_path . 'style.css' ) : '1.0';

    wp_register_script(
        'sg-faq-section-editor',
        $widget_url . 'block.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
        $js_ver,
        true
    );

    wp_register_style(
        'sg-faq-section-style',
        $widget_url . 'style.css',
        array(),
        $css_ver
    );

    register_block_type( 'sg-blocks/faq-section', array(
        'editor_script'   => 'sg-faq-section-editor',
        'editor_style'    => 'sg-faq-section-style',
        'style'           => 'sg-faq-section-style',
        'render_callback' => 'sg_faq_section_render',
        'attributes'      => array(
            // Header
            'showBadge'        => array( 'type' => 'boolean', 'default' => true ),
            'badgeText'        => array( 'type' => 'string',  'default' => 'FAQs' ),
            'heading'          => array( 'type' => 'string',  'default' => 'Frequently Asked Questions' ),
            'subheading'       => array( 'type' => 'string',  'default' => 'Questions We Get Asked Before They Hit "Submit"' ),
            'showSubheading'   => array( 'type' => 'boolean', 'default' => true ),
            // Colors
            'bgColor'          => array( 'type' => 'string',  'default' => '#0a0a0a' ),
            'bgImage'          => array( 'type' => 'string',  'default' => '' ),
            'bgImageId'        => array( 'type' => 'integer', 'default' => 0 ),
            'bgOverlayColor'   => array( 'type' => 'string',  'default' => 'rgba(10,10,10,0.82)' ),
            'bgOverlayEnable'  => array( 'type' => 'boolean', 'default' => true ),
            'headingColor'     => array( 'type' => 'string',  'default' => '#ffffff' ),
            'subheadingColor'  => array( 'type' => 'string',  'default' => '#888888' ),
            'badgeBorderColor' => array( 'type' => 'string',  'default' => '#333333' ),
            'badgeTextColor'   => array( 'type' => 'string',  'default' => '#aaaaaa' ),
            'itemBg'           => array( 'type' => 'string',  'default' => '#141414' ),
            'itemBorderColor'  => array( 'type' => 'string',  'default' => '#222222' ),
            'questionColor'    => array( 'type' => 'string',  'default' => '#ffffff' ),
            'answerColor'      => array( 'type' => 'string',  'default' => '#888888' ),
            'iconColor'        => array( 'type' => 'string',  'default' => '#666666' ),
            'iconActiveColor'  => array( 'type' => 'string',  'default' => '#ffffff' ),
            // Layout
            'innerWidth'       => array( 'type' => 'string',  'default' => '680px' ),
            // FAQs JSON
            'faqs'             => array( 'type' => 'string',  'default' => '[]' ),
        ),
    ) );
}

/* ─────────────────────────────────────────────
   Render
───────────────────────────────────────────── */
function sg_faq_section_render( $attr ) {

    $faqs = array();
    if ( ! empty( $attr['faqs'] ) ) {
        $d = json_decode( $attr['faqs'], true );
        if ( is_array( $d ) ) $faqs = $d;
    }

    $bg_color        = esc_attr( $attr['bgColor']          ?? '#0a0a0a' );
    $bg_image        = esc_url(  $attr['bgImage']          ?? '' );
    $overlay_color   = esc_attr( $attr['bgOverlayColor']   ?? 'rgba(10,10,10,0.82)' );
    $overlay_enable  = ! empty( $attr['bgOverlayEnable'] );
    $heading_color   = esc_attr( $attr['headingColor']     ?? '#ffffff' );
    $sub_color       = esc_attr( $attr['subheadingColor']  ?? '#888888' );
    $badge_border    = esc_attr( $attr['badgeBorderColor'] ?? '#333333' );
    $badge_text_c    = esc_attr( $attr['badgeTextColor']   ?? '#aaaaaa' );
    $item_bg         = esc_attr( $attr['itemBg']           ?? '#141414' );
    $item_border     = esc_attr( $attr['itemBorderColor']  ?? '#222222' );
    $q_color         = esc_attr( $attr['questionColor']    ?? '#ffffff' );
    $a_color         = esc_attr( $attr['answerColor']      ?? '#888888' );
    $icon_color      = esc_attr( $attr['iconColor']        ?? '#666666' );
    $icon_active     = esc_attr( $attr['iconActiveColor']  ?? '#ffffff' );
    $inner_width     = esc_attr( $attr['innerWidth']       ?? '680px' );

    /* Build section inline style */
    $section_style = 'background-color:' . $bg_color . ';';
    if ( $bg_image ) {
        $section_style .= 'background-image:url(' . $bg_image . ');background-size:cover;background-position:center center;background-repeat:no-repeat;';
    }

    $unique_id = 'sg-faq-' . substr( md5( uniqid() ), 0, 8 );

    ob_start();
?>
<section class="sg-faq" id="<?php echo esc_attr( $unique_id ); ?>" style="<?php echo $section_style; ?>">

    <?php if ( $bg_image && $overlay_enable ) : ?>
    <div class="sg-faq__overlay" style="background:<?php echo $overlay_color; ?>;"></div>
    <?php endif; ?>

    <div class="sg-faq__inner" style="max-width:<?php echo $inner_width; ?>;">

        <?php if ( ! empty( $attr['showBadge'] ) && ! empty( $attr['badgeText'] ) ) : ?>
        <div class="sg-faq__badge-wrap">
            <span class="sg-faq__badge" style="border-color:<?php echo $badge_border; ?>;color:<?php echo $badge_text_c; ?>;"><?php echo esc_html( $attr['badgeText'] ); ?></span>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $attr['heading'] ) ) : ?>
        <h2 class="sg-faq__heading" style="color:<?php echo $heading_color; ?>;"><?php echo esc_html( $attr['heading'] ); ?></h2>
        <?php endif; ?>

        <?php if ( ! empty( $attr['showSubheading'] ) && ! empty( $attr['subheading'] ) ) : ?>
        <p class="sg-faq__subheading" style="color:<?php echo $sub_color; ?>;"><?php echo esc_html( $attr['subheading'] ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $faqs ) ) : ?>
        <div class="sg-faq__list" style="--sg-faq-item-bg:<?php echo $item_bg; ?>;--sg-faq-item-border:<?php echo $item_border; ?>;--sg-faq-q-color:<?php echo $q_color; ?>;--sg-faq-a-color:<?php echo $a_color; ?>;--sg-faq-icon:<?php echo $icon_color; ?>;--sg-faq-icon-active:<?php echo $icon_active; ?>;">
            <?php foreach ( $faqs as $index => $faq ) :
                $question = isset( $faq['question'] ) ? $faq['question'] : '';
                $answer   = isset( $faq['answer'] )   ? $faq['answer']   : '';
                $open_by_default = ! empty( $faq['openByDefault'] );
                $item_id  = $unique_id . '-item-' . $index;
                if ( ! $question ) continue;
            ?>
            <div class="sg-faq__item<?php echo $open_by_default ? ' sg-faq__item--open' : ''; ?>" id="<?php echo esc_attr( $item_id ); ?>">
                <button class="sg-faq__question" aria-expanded="<?php echo $open_by_default ? 'true' : 'false'; ?>" onclick="sgFaqToggle(this)">
                    <span class="sg-faq__question-text"><?php echo esc_html( $question ); ?></span>
                    <span class="sg-faq__icon" aria-hidden="true">
                        <svg class="sg-faq__icon-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </span>
                </button>
                <div class="sg-faq__answer" <?php echo $open_by_default ? '' : 'hidden'; ?>>
                    <div class="sg-faq__answer-inner">
                        <?php echo wp_kses_post( nl2br( $answer ) ); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>
<script>
(function(){
    function sgFaqToggle(btn){
        var item   = btn.closest('.sg-faq__item');
        var answer = item.querySelector('.sg-faq__answer');
        var isOpen = item.classList.contains('sg-faq__item--open');
        if(isOpen){
            item.classList.remove('sg-faq__item--open');
            btn.setAttribute('aria-expanded','false');
            answer.setAttribute('hidden','');
        } else {
            item.classList.add('sg-faq__item--open');
            btn.setAttribute('aria-expanded','true');
            answer.removeAttribute('hidden');
        }
    }
    window.sgFaqToggle = window.sgFaqToggle || sgFaqToggle;
})();
</script>
<?php
    return ob_get_clean();
}
