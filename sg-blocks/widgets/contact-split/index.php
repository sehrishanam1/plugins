<?php
/**
 * Widget: Contact Split
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_contact_split_register' );

function sg_contact_split_register() {

    $widget_url  = SG_BLOCKS_URL  . 'widgets/contact-split/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/contact-split/';

    $js_ver  = file_exists( $widget_path . 'block.js'  ) ? filemtime( $widget_path . 'block.js'  ) : '1.0';
    $css_ver = file_exists( $widget_path . 'style.css' ) ? filemtime( $widget_path . 'style.css' ) : '1.0';

    wp_register_script(
        'sg-contact-split-editor',
        $widget_url . 'block.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
        $js_ver,
        true
    );

    wp_register_style(
        'sg-contact-split-style',
        $widget_url . 'style.css',
        array(),
        $css_ver
    );

    register_block_type( 'sg-blocks/contact-split', array(
        'editor_script'   => 'sg-contact-split-editor',
        'editor_style'    => 'sg-contact-split-style',
        'style'           => 'sg-contact-split-style',
        'render_callback' => 'sg_contact_split_render',
        'attributes'      => array(

            /* ── Layout ── */
            'bgColor'            => array( 'type' => 'string',  'default' => '#080808' ),
            'innerWidth'         => array( 'type' => 'string',  'default' => '1100px' ),
            'imagePosition'      => array( 'type' => 'string',  'default' => 'left' ),

            /* ── Left panel – image ── */
            'leftImage'          => array( 'type' => 'string',  'default' => '' ),
            'leftImageId'        => array( 'type' => 'integer', 'default' => 0 ),
            'leftBg'             => array( 'type' => 'string',  'default' => '#111111' ),
            'leftOverlayColor'   => array( 'type' => 'string',  'default' => 'rgba(0,0,0,0.35)' ),
            'leftOverlayEnable'  => array( 'type' => 'boolean', 'default' => true ),

            /* ── Right panel – header ── */
            'showBadge'          => array( 'type' => 'boolean', 'default' => false ),
            'badgeText'          => array( 'type' => 'string',  'default' => 'Contact Us' ),
            'showHeading'        => array( 'type' => 'boolean', 'default' => false ),
            'heading'            => array( 'type' => 'string',  'default' => 'Get in Touch' ),
            'showSubheading'     => array( 'type' => 'boolean', 'default' => false ),
            'subheading'         => array( 'type' => 'string',  'default' => 'Fill in the form and we will get back to you.' ),

            /* ── Form colors ── */
            'rightBg'            => array( 'type' => 'string',  'default' => '#080808' ),
            'labelColor'         => array( 'type' => 'string',  'default' => '#cccccc' ),
            'inputBg'            => array( 'type' => 'string',  'default' => '#111111' ),
            'inputBorderColor'   => array( 'type' => 'string',  'default' => '#2a2a2a' ),
            'inputTextColor'     => array( 'type' => 'string',  'default' => '#ffffff' ),
            'placeholderColor'   => array( 'type' => 'string',  'default' => '#555555' ),
            'checkboxBorder'     => array( 'type' => 'string',  'default' => '#444444' ),
            'checkboxLabelColor' => array( 'type' => 'string',  'default' => '#cccccc' ),

            /* ── Button ── */
            'btnText'            => array( 'type' => 'string',  'default' => 'Send My Free Proposal Request' ),
            'btnBg'              => array( 'type' => 'string',  'default' => '#00bcd4' ),
            'btnTextColor'       => array( 'type' => 'string',  'default' => '#000000' ),
            'btnBgHover'         => array( 'type' => 'string',  'default' => '#00acc1' ),

            /* ── Form fields JSON ── */
            /* Each field: { type, label, placeholder, required, width, options[] } */
            /* type: text | email | tel | textarea | checkboxgroup */
            /* width: full | half */
            'fields'             => array( 'type' => 'string',  'default' => '[]' ),

            /* ── Form action ── */
            'formAction'         => array( 'type' => 'string',  'default' => '' ),
            'formMethod'         => array( 'type' => 'string',  'default' => 'POST' ),
            'successMessage'     => array( 'type' => 'string',  'default' => 'Thank you! We\'ll be in touch soon.' ),
        ),
    ) );
}

/* ─────────────────────────────────────────────
   Render
───────────────────────────────────────────── */
function sg_contact_split_render( $attr ) {

    $fields = array();
    if ( ! empty( $attr['fields'] ) ) {
        $d = json_decode( $attr['fields'], true );
        if ( is_array( $d ) ) $fields = $d;
    }

    $bg          = esc_attr( $attr['bgColor']          ?? '#080808' );
    $inner_w     = esc_attr( $attr['innerWidth']       ?? '1100px' );
    $img_pos     = $attr['imagePosition'] === 'right' ? 'right' : 'left';

    $left_image  = esc_url(  $attr['leftImage']        ?? '' );
    $left_bg     = esc_attr( $attr['leftBg']           ?? '#111111' );
    $ov_color    = esc_attr( $attr['leftOverlayColor'] ?? 'rgba(0,0,0,0.35)' );
    $ov_enable   = ! empty( $attr['leftOverlayEnable'] );

    $right_bg    = esc_attr( $attr['rightBg']          ?? '#080808' );
    $label_c     = esc_attr( $attr['labelColor']       ?? '#cccccc' );
    $input_bg    = esc_attr( $attr['inputBg']          ?? '#111111' );
    $input_bdr   = esc_attr( $attr['inputBorderColor'] ?? '#2a2a2a' );
    $input_txt   = esc_attr( $attr['inputTextColor']   ?? '#ffffff' );
    $ph_color    = esc_attr( $attr['placeholderColor'] ?? '#555555' );
    $cb_border   = esc_attr( $attr['checkboxBorder']   ?? '#444444' );
    $cb_label    = esc_attr( $attr['checkboxLabelColor'] ?? '#cccccc' );
    $btn_text    = esc_html( $attr['btnText']          ?? 'Send My Free Proposal Request' );
    $btn_bg      = esc_attr( $attr['btnBg']            ?? '#00bcd4' );
    $btn_clr     = esc_attr( $attr['btnTextColor']     ?? '#000000' );
    $btn_hover   = esc_attr( $attr['btnBgHover']       ?? '#00acc1' );
    $form_action = esc_url(  $attr['formAction']       ?? '' );
    $form_method = $attr['formMethod'] === 'GET' ? 'GET' : 'POST';
    $success_msg = esc_html( $attr['successMessage']   ?? "Thank you! We'll be in touch soon." );

    $unique_id = 'sg-cs-' . substr( md5( uniqid() ), 0, 8 );

    /* Left panel style */
    $left_style = 'background-color:' . $left_bg . ';';
    if ( $left_image ) {
        $left_style .= 'background-image:url(' . $left_image . ');background-size:cover;background-position:center;background-repeat:no-repeat;';
    }

    /* CSS vars for this instance */
    $css_vars = '--sg-cs-input-bg:' . $input_bg . ';'
              . '--sg-cs-input-bdr:' . $input_bdr . ';'
              . '--sg-cs-input-txt:' . $input_txt . ';'
              . '--sg-cs-ph:' . $ph_color . ';'
              . '--sg-cs-label:' . $label_c . ';'
              . '--sg-cs-cb-bdr:' . $cb_border . ';'
              . '--sg-cs-cb-lbl:' . $cb_label . ';'
              . '--sg-cs-btn-bg:' . $btn_bg . ';'
              . '--sg-cs-btn-txt:' . $btn_clr . ';'
              . '--sg-cs-btn-hover:' . $btn_hover . ';';

    ob_start();
?>
<section class="sg-cs" id="<?php echo esc_attr($unique_id); ?>" style="background:<?php echo $bg; ?>;">
    <div class="sg-cs__inner" style="max-width:<?php echo $inner_w; ?>;">

        <!-- Left: image panel -->
        <div class="sg-cs__panel sg-cs__panel--image sg-cs__panel--<?php echo $img_pos; ?>" style="<?php echo $left_style; ?>">
            <?php if ( $left_image && $ov_enable ) : ?>
            <div class="sg-cs__img-overlay" style="background:<?php echo $ov_color; ?>;"></div>
            <?php endif; ?>
        </div>

        <!-- Right: form panel -->
        <div class="sg-cs__panel sg-cs__panel--form" style="background:<?php echo $right_bg; ?>; <?php echo $css_vars; ?>">

            <?php if ( ! empty( $attr['showBadge'] ) && ! empty( $attr['badgeText'] ) ) : ?>
            <div class="sg-cs__badge-wrap">
                <span class="sg-cs__badge"><?php echo esc_html( $attr['badgeText'] ); ?></span>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $attr['showHeading'] ) && ! empty( $attr['heading'] ) ) : ?>
            <h2 class="sg-cs__heading"><?php echo esc_html( $attr['heading'] ); ?></h2>
            <?php endif; ?>

            <?php if ( ! empty( $attr['showSubheading'] ) && ! empty( $attr['subheading'] ) ) : ?>
            <p class="sg-cs__subheading"><?php echo esc_html( $attr['subheading'] ); ?></p>
            <?php endif; ?>

            <?php if ( ! empty( $fields ) ) : ?>
            <form class="sg-cs__form" action="<?php echo $form_action ?: '#'; ?>" method="<?php echo $form_method; ?>" id="<?php echo esc_attr($unique_id); ?>-form" novalidate>
                <?php wp_nonce_field( 'sg_contact_split_submit', 'sg_cs_nonce' ); ?>
                <div class="sg-cs__grid">
                    <?php foreach ( $fields as $field ) :
                        $ftype   = $field['type']        ?? 'text';
                        $flabel  = $field['label']       ?? '';
                        $fph     = $field['placeholder'] ?? '';
                        $freq    = ! empty( $field['required'] );
                        $fwidth  = ( $field['width'] ?? 'full' ) === 'half' ? 'half' : 'full';
                        $fname   = sanitize_key( strtolower( str_replace( ' ', '_', $flabel ) ) ) ?: 'field_' . rand(100,999);
                        $fid     = $unique_id . '-' . $fname;
                    ?>
                    <div class="sg-cs__field sg-cs__field--<?php echo $fwidth; ?>">
                        <?php if ( $flabel ) : ?>
                        <label class="sg-cs__label" for="<?php echo esc_attr($fid); ?>"><?php echo esc_html($flabel); ?><?php if ($freq) echo '<span class="sg-cs__req">*</span>'; ?></label>
                        <?php endif; ?>

                        <?php if ( $ftype === 'textarea' ) : ?>
                            <textarea
                                class="sg-cs__input sg-cs__textarea"
                                id="<?php echo esc_attr($fid); ?>"
                                name="<?php echo esc_attr($fname); ?>"
                                placeholder="<?php echo esc_attr($fph); ?>"
                                <?php if ($freq) echo 'required'; ?>
                                rows="5"
                            ></textarea>

                        <?php elseif ( $ftype === 'checkboxgroup' ) :
                            $options = $field['options'] ?? array();
                        ?>
                            <div class="sg-cs__checkbox-group">
                                <?php foreach ( $options as $opt ) :
                                    $opt_val = sanitize_key( $opt );
                                    $opt_id  = $fid . '-' . $opt_val;
                                ?>
                                <label class="sg-cs__cb-label" for="<?php echo esc_attr($opt_id); ?>">
                                    <input type="checkbox" id="<?php echo esc_attr($opt_id); ?>" name="<?php echo esc_attr($fname); ?>[]" value="<?php echo esc_attr($opt); ?>" class="sg-cs__cb-input">
                                    <span class="sg-cs__cb-box"></span>
                                    <span class="sg-cs__cb-text"><?php echo esc_html($opt); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>

                        <?php else : ?>
                            <input
                                type="<?php echo esc_attr($ftype); ?>"
                                class="sg-cs__input"
                                id="<?php echo esc_attr($fid); ?>"
                                name="<?php echo esc_attr($fname); ?>"
                                placeholder="<?php echo esc_attr($fph); ?>"
                                <?php if ($freq) echo 'required'; ?>
                            >
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="sg-cs__submit-wrap">
                    <button type="submit" class="sg-cs__btn"><?php echo $btn_text; ?></button>
                </div>

                <div class="sg-cs__success" id="<?php echo esc_attr($unique_id); ?>-success" style="display:none;">
                    <?php echo $success_msg; ?>
                </div>
            </form>
            <?php else : ?>
            <p style="color:#555;font-size:13px;text-align:center;padding:40px 0;">← Add form fields from the editor sidebar</p>
            <?php endif; ?>

        </div>
    </div>
</section>
<script>
(function(){
    var form = document.getElementById('<?php echo esc_js($unique_id); ?>-form');
    if(!form) return;
    form.addEventListener('submit', function(e){
        <?php if ( ! $form_action || $form_action === '#' ) : ?>
        e.preventDefault();
        var success = document.getElementById('<?php echo esc_js($unique_id); ?>-success');
        if(success){ form.style.display='none'; success.style.display='block'; }
        <?php endif; ?>
    });
})();
</script>
<?php
    return ob_get_clean();
}
