<?php
/**
 * Anexly — Plan Selector Shortcode
 *
 * Works with BOTH:
 *  - WooCommerce taxonomy attributes  (pa_plan  / attribute_pa_plan / slug)
 *  - WooCommerce custom attributes    (Plan     / attribute_plan    / Value)
 *
 * Usage:
 *   [anexly_plan_selector]
 *   [anexly_plan_selector product_id="42"]
 *   [anexly_plan_selector show_totals="yes"]
 *   [anexly_plan_selector button_text="Buy Now"]
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'anexly_plan_selector', 'anexly_plan_selector_render' );

/**
 * Suppress the WooCommerce "Please choose product options" session notice
 * that fires when wc-ajax=add_to_cart is called from outside a product page.
 *
 * How it works:
 *   WC_Form_Handler::add_to_cart_action() calls wc_add_notice( ..., 'error' )
 *   before it validates the variation. Our hook runs on woocommerce_add_to_cart
 *   (fires only on a SUCCESSFUL add) and clears any error notices that were
 *   queued during the same request — so only the success notice survives in
 *   the session.
 *
 *   We scope this to our shortcode's AJAX requests only by checking for the
 *   custom header / POST flag we send from script.js.
 */
add_action( 'woocommerce_add_to_cart', 'anexly_ps_clear_error_notices', 999 );
function anexly_ps_clear_error_notices() {
    if ( ! wp_doing_ajax() ) {
        return;
    }

    if ( empty( $_POST['anexly_ps_request'] ) ) {
        return;
    }

    // Clear all queued WC notices for this custom AJAX request.
    wc_clear_notices();
}

function anexly_plan_selector_render( $atts ) {

    $atts = shortcode_atts( [
        'product_id'  => 0,
        'show_totals' => 'yes',
        'button_text' => 'Add to cart',
    ], $atts, 'anexly_plan_selector' );

    /* ── Resolve product ── */
    $product_id = intval( $atts['product_id'] );
    if ( ! $product_id ) $product_id = get_the_ID();

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        return '<p class="anexly-ps-error">This shortcode requires a variable product.</p>';
    }

    /* ── Enqueue assets ── */
    $url = ANEXLY_SC_URL . 'shortcodes/plan-selector/';
    wp_enqueue_style(  'anexly-sc-plan-selector', $url . 'style.css',  [], ANEXLY_SC_VERSION );
    wp_enqueue_script( 'anexly-sc-plan-selector', $url . 'script.js', [ 'jquery' ], ANEXLY_SC_VERSION . '.cb1', true );



    /* ── Pass WC currency format to JS once per page ── */
    static $currency_printed = false;
    if ( ! $currency_printed ) {
        wp_add_inline_script( 'anexly-sc-plan-selector',
            'var wc_price_format = ' . wp_json_encode( [
                'currency_symbol'    => get_woocommerce_currency_symbol(),
                'currency_pos'       => get_option( 'woocommerce_currency_pos', 'left' ),
                'decimal_separator'  => wc_get_price_decimal_separator(),
                'thousand_separator' => wc_get_price_thousand_separator(),
                'decimals'           => wc_get_price_decimals(),
            ] ) . ';',
            'before'
        );
        $currency_printed = true;
    }

    /* ── Ensure wc_add_to_cart_params is available for our script ── */
    // FIX: Always inject ajax_url and cart_url into wc_add_to_cart_params.
    // The woocommerce_add_to_cart AJAX action (admin-ajax.php) needs ajax_url,
    // and the success message "View cart" link needs cart_url.
    // We merge our values so existing WC params are preserved if already set.
    static $ajax_data_printed = false;
    if ( ! $ajax_data_printed ) {
        wp_add_inline_script( 'anexly-sc-plan-selector',
            '(function(){' .
                'var defaults = ' . wp_json_encode( [
                    'ajax_url'                => admin_url( 'admin-ajax.php' ),
                    'wc_ajax_url'             => WC_AJAX::get_endpoint( '%%endpoint%%' ),
                    'i18n_view_cart'          => esc_attr__( 'View cart', 'woocommerce' ),
                    'cart_url'                => wc_get_cart_url(),
                    'is_cart'                 => is_cart() ? '1' : '0',
                    'cart_redirect_after_add' => get_option( 'woocommerce_cart_redirect_after_add' ),
                ] ) . ';' .
                // Merge: if wc_add_to_cart_params already exists (enqueued by WC on product pages),
                // keep it but ensure ajax_url and cart_url are always present.
                'if (typeof window.wc_add_to_cart_params === "undefined") {' .
                    'window.wc_add_to_cart_params = defaults;' .
                '} else {' .
                    'window.wc_add_to_cart_params.ajax_url = window.wc_add_to_cart_params.ajax_url || defaults.ajax_url;' .
                    'window.wc_add_to_cart_params.cart_url = window.wc_add_to_cart_params.cart_url || defaults.cart_url;' .
                '}' .
            '})();',
            'before'
        );
        $ajax_data_printed = true;
    }

    /* ── Raw WC data ── */
    $attributes = $product->get_variation_attributes();
    $variations = $product->get_available_variations();

    if ( empty( $attributes ) ) {
        return '<p class="anexly-ps-error">No attributes found on this product.</p>';
    }

    /*
     * Use get_available_variations() keys/values VERBATIM — these are the
     * exact strings WooCommerce AJAX expects when adding to cart:
     *   taxonomy attribute  → key: 'attribute_pa_type',   value: 'shared'  (slug)
     *   custom attribute    → key: 'attribute_months',    value: '1 Month' (original)
     *
     * We do NOT normalise, lowercase, or add/remove prefixes — any mutation
     * causes a server-side variation mismatch ("Please choose product options").
     *
     * $normalized_variations keeps the original name for compatibility with
     * the JS window.anexlyPsVariations data structure.
     */
    // Pass variations as-is — WC_Form_Handler matches custom attribute values
    // as raw strings (exact match), NOT slugified. Taxonomy attributes are slugs already.
    $normalized_variations = $variations;

    /*
     * Build variation lookup: attribute_key → value → price + variation_id.
     * Keys and values come directly from get_available_variations() so they
     * match both the JS findVariation() lookup and the WC AJAX handler.
     */
    $var_lookup = [];
    foreach ( $variations as $v ) {
        foreach ( $v['attributes'] as $attr_key => $attr_val ) {
            if ( $attr_val === '' ) continue; // "Any" wildcard — skip
            if ( ! isset( $var_lookup[ $attr_key ] ) ) {
                $var_lookup[ $attr_key ] = [];
            }
            if ( ! isset( $var_lookup[ $attr_key ][ $attr_val ] ) ) {
                $var_lookup[ $attr_key ][ $attr_val ] = [
                    'price'        => (float) $v['display_price'],
                    'regular'      => (float) $v['display_regular_price'],
                    'variation_id' => $v['variation_id'],
                ];
            }
        }
    }

    /*
     * Map each attribute name (from get_variation_attributes()) to the exact
     * key that appears in $var_lookup (from get_available_variations()).
     *
     * For taxonomy attributes, get_variation_attributes() uses the taxonomy
     * name (e.g. 'pa_type') while get_available_variations() uses
     * 'attribute_pa_type' — so we try both forms.
     *
     * For custom attributes, both use the same lowercased slug.
     */
    $attr_keys = [];
    foreach ( $attributes as $attr_name => $values ) {
        // Form 1: key as returned by get_available_variations() with attribute_ prefix
        $with_prefix    = 'attribute_' . $attr_name;
        // Form 2: sanitized slug version (covers custom attributes stored as slugs)
        $sanitized      = 'attribute_' . sanitize_title( $attr_name );

        if ( isset( $var_lookup[ $with_prefix ] ) ) {
            $attr_keys[ $attr_name ] = $with_prefix;
        } elseif ( isset( $var_lookup[ $sanitized ] ) ) {
            $attr_keys[ $attr_name ] = $sanitized;
        } else {
            // Fallback: find any key in var_lookup that matches when both are sanitized
            $sanitized_lower = strtolower( $sanitized );
            $found = false;
            foreach ( array_keys( $var_lookup ) as $vk ) {
                if ( strtolower( 'attribute_' . sanitize_title( $vk ) ) === $sanitized_lower
                  || strtolower( $vk ) === $sanitized_lower ) {
                    $attr_keys[ $attr_name ] = $vk;
                    $found = true;
                    break;
                }
            }
            if ( ! $found ) {
                $attr_keys[ $attr_name ] = $with_prefix; // best guess
            }
        }
    }

    /* ── "Most Popular" = median-priced option per group ── */
    $popular = [];
    foreach ( $attributes as $attr_name => $values ) {
        $key    = $attr_keys[ $attr_name ];
        $prices = [];
        foreach ( $values as $val ) {
            if ( isset( $var_lookup[ $key ][ $val ] ) ) {
                $prices[ $val ] = $var_lookup[ $key ][ $val ]['price'];
            }
        }
        if ( count( $prices ) >= 2 ) {
            asort( $prices );
            $ks = array_keys( $prices );
            $popular[ $attr_name ] = $ks[ (int) floor( count( $ks ) / 2 ) ];
        }
    }

    ob_start();
    ?>
    <div class="anexly-ps-wrap" data-product-id="<?php echo esc_attr( $product_id ); ?>">

        <h3 class="anexly-ps-title">Select Your Plan</h3>

        <?php foreach ( $attributes as $attr_name => $values ) :

            $taxonomy = get_taxonomy( $attr_name );
            $label    = $taxonomy
                ? $taxonomy->labels->singular_name
                : $attr_name;

            $attr_key     = $attr_keys[ $attr_name ];
            $popular_val  = $popular[ $attr_name ] ?? '';
            $idx          = 0;
        ?>

        <div class="anexly-ps-group" data-attr="<?php echo esc_attr( $attr_key ); ?>">
            <p class="anexly-ps-group-label"><?php echo esc_html( $label ); ?>:</p>

            <?php foreach ( $values as $val ) :
                $idx++;

                $term       = $taxonomy ? get_term_by( 'slug', $val, $attr_name ) : false;
                $label_text = $term ? $term->name : $val;

                $pd      = $var_lookup[ $attr_key ][ $val ] ?? null;
                $price   = $pd ? $pd['price']   : null;
                $regular = $pd ? $pd['regular'] : null;
                $on_sale = ( $price !== null && $regular !== null && $regular > $price );
            ?>

            <label class="anexly-ps-item <?php echo $idx === 1 ? 'is-checked' : ''; ?>"
                   data-attr="<?php echo esc_attr( $attr_key ); ?>"
                   data-value="<?php echo esc_attr( $val ); ?>"
                   data-price="<?php echo esc_attr( $price ?? 0 ); ?>"
                   data-regular="<?php echo esc_attr( $regular ?? 0 ); ?>">

                <span class="anexly-ps-checkbox">
                    <svg viewBox="0 0 12 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 5L4.5 8.5L11 1.5" stroke="white" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>

                <span class="anexly-ps-name">
                    <?php echo esc_html( $label_text ); ?>
                    <?php if ( $val === $popular_val ) : ?>
                        <span class="anexly-ps-badge">Most Popular</span>
                    <?php endif; ?>
                </span>

                <span class="anexly-ps-price">
                    <?php if ( $on_sale ) : ?>
                        <s class="anexly-ps-was"><?php echo wc_price( $regular ); ?></s>
                    <?php endif; ?>
                    <?php echo $price !== null ? wc_price( $price ) : '—'; ?>
                </span>

            </label>

            <?php endforeach; // values ?>
        </div><!-- .anexly-ps-group -->

        <?php endforeach; // attributes ?>

        <?php if ( $atts['show_totals'] === 'yes' ) : ?>
        <div class="anexly-ps-totals">
            <div class="anexly-ps-totals-row is-total">
                <span>Total</span>
                <span class="anexly-ps-total">—</span>
            </div>
        </div>
        <?php endif; ?>

        <button type="button" class="anexly-ps-btn"
                data-original-text="<?php echo esc_attr( $atts['button_text'] ); ?>">
            <?php echo esc_html( $atts['button_text'] ); ?>
        </button>

        <p class="anexly-ps-msg" aria-live="polite" style="display:none;"></p>

    </div><!-- .anexly-ps-wrap -->

    <script>
    /* ── Pass normalised variation data so JS attribute keys/values match AJAX expectations ── */
    window.anexlyPsVariations = window.anexlyPsVariations || {};
    window.anexlyPsVariations[<?php echo intval( $product_id ); ?>] = <?php echo wp_json_encode( $normalized_variations ); ?>;


    </script>
    <?php
    return ob_get_clean();
}