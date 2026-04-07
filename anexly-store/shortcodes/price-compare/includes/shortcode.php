<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * [anexly_price_compare]
 *
 * Auto-detects WooCommerce product price from the current page.
 * Generates a deterministic "Regular Price" (50–80% above store price).
 * All annual prices = monthly × months (default 12).
 */
add_shortcode( 'anexly_price_compare', 'anexly_pc_render_shortcode' );

function anexly_pc_render_shortcode( $atts ) {

    $atts = shortcode_atts( [
        'id'       => 0,
        'months'   => 12,
        'cta_text' => '',
        'cta_url'  => '',
        'title'    => '',
        'subtitle' => '',
        'debug'    => 0,
    ], $atts, 'anexly_price_compare' );

    $debug = intval( $atts['debug'] );

    // ── 1. Resolve Product ID ──────────────────────────────────────
    $product_id = intval( $atts['id'] );

    if ( ! $product_id ) {
        $product_id = (int) get_the_ID();
    }

    if ( ! $product_id ) {
        global $post;
        if ( ! empty( $post->ID ) ) {
            $product_id = (int) $post->ID;
        }
    }

    if ( ! $product_id ) {
        if ( $debug ) {
            return '<p style="color:red;border:1px solid red;padding:10px;">[anexly_price_compare] ERROR: Could not detect a product ID. Try: <code>[anexly_price_compare id="123"]</code></p>';
        }
        return '';
    }

    // ── 2. WooCommerce check ───────────────────────────────────────
    if ( ! function_exists( 'wc_get_product' ) ) {
        if ( $debug ) {
            return '<p style="color:red;border:1px solid red;padding:10px;">[anexly_price_compare] ERROR: WooCommerce is not active.</p>';
        }
        return '';
    }

    // ── 3. Get the product ─────────────────────────────────────────
    $product = wc_get_product( $product_id );

    if ( ! $product ) {
        if ( $debug ) {
            return '<p style="color:red;border:1px solid red;padding:10px;">[anexly_price_compare] ERROR: No WooCommerce product found for ID ' . $product_id . '. Post type: ' . get_post_type( $product_id ) . '</p>';
        }
        return '';
    }

    // ── 4. Get the price ───────────────────────────────────────────
    $store_monthly = floatval( $product->get_price() );

    if ( $store_monthly <= 0 ) {
        $store_monthly = floatval( $product->get_regular_price() );
    }

    if ( $store_monthly <= 0 ) {
        if ( $debug ) {
            return '<p style="color:red;border:1px solid red;padding:10px;">[anexly_price_compare] ERROR: Product ID ' . $product_id . ' has no price. Raw get_price(): "' . esc_html( $product->get_price() ) . '"</p>';
        }
        return '';
    }

    // ── 5. Generate deterministic market price ─────────────────────
    mt_srand( absint( $product_id ) * 7 + 13 );
    $markup_pct     = mt_rand( 50, 80 );
    mt_srand();

    $market_monthly = round( $store_monthly * ( 1 + $markup_pct / 100 ), 2 );

    // ── 6. Annual prices ───────────────────────────────────────────
    $months        = max( 1, intval( $atts['months'] ) );
    $store_annual  = round( $store_monthly  * $months, 2 );
    $market_annual = round( $market_monthly * $months, 2 );

    // ── 7. Savings ─────────────────────────────────────────────────
    $savings_monthly = round( $market_monthly - $store_monthly, 2 );
    $savings_annual  = round( $market_annual  - $store_annual,  2 );
    $save_pct        = $market_monthly > 0
        ? round( ( $savings_monthly / $market_monthly ) * 100 )
        : 0;

    // ── 8. Labels ──────────────────────────────────────────────────
    $site_name       = get_bloginfo( 'name' );
    $brand_label     = strtoupper( $site_name );
    $cta_text        = ! empty( $atts['cta_text'] ) ? $atts['cta_text'] : 'Browse Deals';
    $cta_url         = ! empty( $atts['cta_url'] )  ? $atts['cta_url']  : get_permalink( $product_id );
    $widget_title    = ! empty( $atts['title'] )
        ? $atts['title']
        : 'See How Much You Save Instantly';
    $widget_subtitle = ! empty( $atts['subtitle'] )
        ? $atts['subtitle']
        : 'Compare prices and unlock instant savings with Anexly';

    // ── 9. Currency symbol ─────────────────────────────────────────
    $currency = function_exists( 'get_woocommerce_currency_symbol' )
        ? get_woocommerce_currency_symbol()
        : '$';

    $f = function( $n ) use ( $currency ) {
        return $currency . number_format( (float) $n, 2 );
    };

    // ── 10. Render ─────────────────────────────────────────────────
    ob_start();
    ?>
    <div class="anexly-pc-wrap">

        <div class="anexly-pc-header">
            <h2 class="anexly-pc-title"><?php echo esc_html( $widget_title ); ?></h2>
            <p class="anexly-pc-subtitle"><?php echo esc_html( $widget_subtitle ); ?></p>
            <span class="anexly-pc-best-value">Best value</span>
        </div>

        <div class="anexly-pc-cards">

            <!-- Card 1: Regular Price (auto-inflated) -->
            <div class="anexly-pc-card anexly-pc-card--market">
                <p class="anexly-pc-card-label">Regular Price</p>
                <hr class="anexly-pc-divider">
                <div class="anexly-pc-row">
                    <span>Monthly:</span>
                    <strong><?php echo esc_html( $f( $market_monthly ) ); ?></strong>
                </div>
                <div class="anexly-pc-row">
                    <span>Annual:</span>
                    <strong class="anexly-pc-red"><?php echo esc_html( $f( $market_annual ) ); ?></strong>
                </div>
            </div>

            <!-- Card 2: Store Price (live WooCommerce price) -->
            <div class="anexly-pc-card anexly-pc-card--store">
                <p class="anexly-pc-brand"><?php echo esc_html( $brand_label ); ?></p>
                <hr class="anexly-pc-divider">
                <div class="anexly-pc-row">
                    <span>Monthly:</span>
                    <strong><?php echo esc_html( $f( $store_monthly ) ); ?></strong>
                </div>
                <div class="anexly-pc-row">
                    <span>Annual:</span>
                    <strong class="anexly-pc-green"><?php echo esc_html( $f( $store_annual ) ); ?></strong>
                </div>
            </div>

            <!-- Card 3: Savings -->
            <div class="anexly-pc-card anexly-pc-card--savings">
                <div class="anexly-pc-savings-header">
                    <span class="anexly-pc-green-label">Savings</span>
                    <span class="anexly-pc-savings-amount"><?php echo esc_html( $f( $savings_annual ) ); ?></span>
                </div>
                <hr class="anexly-pc-divider">
                <div class="anexly-pc-row">
                    <span>Save</span>
                    <span class="anexly-pc-badge"><?php echo esc_html( $save_pct ); ?> %</span>
                </div>
                <div class="anexly-pc-row">
                    <span>Total Savings</span>
                    <span class="anexly-pc-badge"><?php echo esc_html( $f( $savings_annual ) ); ?></span>
                </div>
            </div>

        </div>

        <div class="anexly-pc-cta-wrap">
            <a href="<?php echo esc_url( $cta_url ); ?>" class="anexly-pc-cta" <?php echo empty( $atts['cta_url'] ) ? 'data-scroll-top="1"' : ''; ?>>
                <?php echo esc_html( $cta_text ); ?>
            </a>
        </div>

    </div>
    <?php
    return ob_get_clean();
}