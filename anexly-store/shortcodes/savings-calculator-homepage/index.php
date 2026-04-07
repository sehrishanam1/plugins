<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode: [savings_calculator_homepage]
 *
 * Homepage savings calculator — dropdown of WooCommerce products with icons.
 * Features a custom dropdown UI with product thumbnails/icons per item.
 *
 * Optional attributes:
 *   title      — heading text
 *   subtitle   — sub-heading text
 *   months     — months for annual calc (default 12)
 *   limit      — products to show in dropdown (default 10)
 *   cta_text   — button label (default "Browse Deals")
 *   cta_url    — button URL (default /shop)
 *   orderby    — date | popularity | rating | price (default date)
 *   products   — comma-separated product IDs to show (e.g. products="12,45,78")
 *                overrides limit & orderby when set
 *
 * Example with manual products:
 *   [savings_calculator_homepage products="12,45,78,102"]
 */

add_shortcode( 'savings_calculator_homepage', 'anexly_sch_render' );

function anexly_sch_render( $atts ) {

    $atts = shortcode_atts( [
        'title'    => 'See How Much You Save Instantly',
        'subtitle' => '',
        'months'   => 12,
        'limit'    => 10,
        'cta_text' => 'Browse Deals',
        'cta_url'  => '/shop',
        'orderby'  => 'date',
        'products' => '', // NEW: comma-separated product IDs
    ], $atts, 'savings_calculator_homepage' );

    if ( ! function_exists( 'wc_get_product' ) ) {
        return '<p style="color:red;border:1px solid red;padding:10px;">[savings_calculator_homepage] WooCommerce is not active.</p>';
    }

    $site_name  = get_bloginfo( 'name' );
    $brand_name = strtoupper( $site_name );
    $subtitle   = ! empty( $atts['subtitle'] )
        ? $atts['subtitle']
        : 'Compare prices and unlock instant savings with ' . $site_name . '.';
    $months     = max( 1, intval( $atts['months'] ) );
    $limit      = max( 1, intval( $atts['limit'] ) );
    $cta_text   = sanitize_text_field( $atts['cta_text'] );
    $cta_url    = esc_url( ! empty( $atts['cta_url'] ) ? $atts['cta_url'] : '/shop' );

    $currency = function_exists( 'get_woocommerce_currency_symbol' )
        ? html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
        : '$';

    // ── Fetch products ─────────────────────────────────────────────
    if ( ! empty( $atts['products'] ) ) {
        // Manual product IDs provided via shortcode attribute
        $raw_ids  = array_map( 'absint', explode( ',', $atts['products'] ) );
        $post_ids = array_filter( $raw_ids );
        $query_args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => count( $post_ids ),
            'post__in'       => $post_ids,
            'orderby'        => 'post__in', // preserve supplied order
        ];
    } else {
        $allowed_orderby = [ 'date', 'popularity', 'rating', 'price', 'title' ];
        $orderby         = in_array( $atts['orderby'], $allowed_orderby, true ) ? $atts['orderby'] : 'date';

        $query_args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => $orderby,
            'order'          => 'DESC',
            'meta_query'     => [ [ 'key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ] ],
        ];
        if ( $orderby === 'popularity' ) {
            $query_args['meta_key'] = 'total_sales';
            $query_args['orderby']  = 'meta_value_num';
        } elseif ( $orderby === 'rating' ) {
            $query_args['meta_key'] = '_wc_average_rating';
            $query_args['orderby']  = 'meta_value_num';
        } elseif ( $orderby === 'price' ) {
            $query_args['meta_key'] = '_price';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'ASC';
        }
    }

    $q = new WP_Query( $query_args );
    if ( ! $q->have_posts() ) {
        return '<p>[savings_calculator_homepage] No published products with a price found.</p>';
    }

    // ── Build data array ───────────────────────────────────────────
    $products_data = [];
    foreach ( $q->posts as $post ) {
        $product = wc_get_product( $post->ID );
        if ( ! $product ) continue;

        $store_monthly = floatval( $product->get_price() );
        if ( $store_monthly <= 0 ) $store_monthly = floatval( $product->get_regular_price() );
        if ( $store_monthly <= 0 ) continue;

        // Deterministic markup — same seed as shortcode.php
        mt_srand( absint( $post->ID ) * 7 + 13 );
        $markup_pct = mt_rand( 50, 80 );
        mt_srand();

        $market_monthly  = round( $store_monthly * ( 1 + $markup_pct / 100 ), 2 );
        $store_annual    = round( $store_monthly  * $months, 2 );
        $market_annual   = round( $market_monthly * $months, 2 );
        $savings_monthly = round( $market_monthly - $store_monthly, 2 );
        $savings_annual  = round( $market_annual  - $store_annual,  2 );
        $save_pct        = $market_monthly > 0
            ? round( ( $savings_monthly / $market_monthly ) * 100 )
            : 0;

        $products_data[] = [
            'id'             => $post->ID,
            'name'           => $product->get_name(),
            'thumb'          => get_the_post_thumbnail_url( $post->ID, 'thumbnail' ) ?: wc_placeholder_img_src( 'thumbnail' ),
            'store_monthly'  => $store_monthly,
            'store_annual'   => $store_annual,
            'market_monthly' => $market_monthly,
            'market_annual'  => $market_annual,
            'savings_annual' => $savings_annual,
            'save_pct'       => $save_pct,
        ];
    }
    wp_reset_postdata();

    if ( empty( $products_data ) ) {
        return '<p>[savings_calculator_homepage] No valid products found.</p>';
    }

    // Unique instance ID (supports multiple widgets on one page)
    static $inst = 0;
    $inst++;
    $uid = 'anexly-sch-' . $inst;

    ob_start();
    ?>
    <div class="anexly-sch-wrap" id="<?php echo esc_attr( $uid ); ?>">

        <!-- ── Header ──────────────────────────────────────── -->
        <div class="anexly-sch-header">
            <h2 class="anexly-pc-title"><?php echo esc_html( $atts['title'] ); ?></h2>
            <p class="anexly-pc-subtitle"><?php echo esc_html( $subtitle ); ?></p>
        </div>

        <!-- ── Dropdown row ────────────────────────────────── -->
        <div class="main-calculator-container">
            <div class="anexly-sch-selector-row">
                <div class="anexly-sch-dropdown-wrap" role="combobox" aria-haspopup="listbox" aria-expanded="false" tabindex="0">

                    <!-- Selected state display -->
                    <span class="anexly-sch-icon-slot">
                        <img class="anexly-sch-thumb" src="<?php echo esc_url( $products_data[0]['thumb'] ); ?>" alt="" width="28" height="28">
                    </span>
                    <div class="anexly-sch-divider-v"></div>
                    <span class="anexly-sch-selected-label">Select your subscription…</span>
                    <span class="anexly-sch-chevron">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path d="M5 7.5l5 5 5-5" stroke="#555" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>

                    <!-- Custom dropdown list -->
                    <ul class="anexly-sch-listbox" role="listbox" aria-label="Select product">
                        <li class="anexly-sch-list-placeholder" data-value="" role="option">Select your subscription</li>
                        <?php foreach ( $products_data as $i => $p ) : ?>
                            <li class="anexly-sch-list-item<?php echo $i === 0 ? ' is-selected' : ''; ?>"
                                data-value="<?php echo esc_attr( $p['id'] ); ?>"
                                data-thumb="<?php echo esc_url( $p['thumb'] ); ?>"
                                role="option"
                                aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                                <img class="anexly-sch-list-icon" src="<?php echo esc_url( $p['thumb'] ); ?>" alt="" width="28" height="28">
                                <span class="anexly-sch-list-name"><?php echo esc_html( $p['name'] ); ?></span>
                                <?php if ( $i === 0 ) : ?>
                                    <svg class="anexly-sch-check-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 8l3.5 3.5L13 4.5" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Hidden real select for form compatibility -->
                    <select class="anexly-sch-select" aria-hidden="true" tabindex="-1">
                        <option value="">Select your subscription…</option>
                        <?php foreach ( $products_data as $i => $p ) : ?>
                            <option value="<?php echo esc_attr( $p['id'] ); ?>" <?php echo $i === 0 ? 'selected' : ''; ?>>
                                <?php echo esc_html( $p['name'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                </div>
            </div>

            <!-- ── "Best value" badge ─────────────────────── -->
            <div class="anexly-sch-badge-row" style="display:flex;">
                <span class="anexly-pc-best-value">Best value</span>
            </div>

            <!-- ── Three cards ───────────────────────────── -->
            <div class="anexly-pc-cards" style="display:grid;">

                <!-- Card 1: Regular / Market price -->
                <div class="anexly-pc-card anexly-pc-card--market">
                    <p class="anexly-pc-card-label">Regular Price</p>
                    <hr class="anexly-pc-divider">
                    <div class="anexly-pc-row">
                        <span>Monthly:</span>
                        <strong class="sch-market-monthly"><?php echo esc_html( $currency . number_format( $products_data[0]['market_monthly'], 2 ) ); ?></strong>
                    </div>
                    <div class="anexly-pc-row">
                        <span>Annual:</span>
                        <strong class="anexly-pc-red sch-market-annual"><?php echo esc_html( $currency . number_format( $products_data[0]['market_annual'], 2 ) ); ?></strong>
                    </div>
                </div>

                <!-- Card 2: Brand / Store price -->
                <div class="anexly-pc-card anexly-pc-card--store">
                    <p class="anexly-pc-brand"><?php echo esc_html( $brand_name ); ?></p>
                    <hr class="anexly-pc-divider">
                    <div class="anexly-pc-row">
                        <span>Monthly:</span>
                        <strong class="sch-store-monthly"><?php echo esc_html( $currency . number_format( $products_data[0]['store_monthly'], 2 ) ); ?></strong>
                    </div>
                    <div class="anexly-pc-row">
                        <span>Annual:</span>
                        <strong class="anexly-pc-green sch-store-annual"><?php echo esc_html( $currency . number_format( $products_data[0]['store_annual'], 2 ) ); ?></strong>
                    </div>
                </div>

                <!-- Card 3: Savings -->
                <div class="anexly-pc-card anexly-pc-card--savings">
                    <div class="anexly-pc-savings-header">
                        <span class="anexly-pc-green-label">Savings</span>
                        <span class="anexly-pc-savings-amount sch-savings-amount"><?php echo esc_html( $currency . number_format( $products_data[0]['savings_annual'], 2 ) ); ?></span>
                    </div>
                    <hr class="anexly-pc-divider">
                    <div class="anexly-pc-row">
                        <span>Save</span>
                        <span class="anexly-pc-badge sch-save-pct"><?php echo esc_html( $products_data[0]['save_pct'] ); ?> %</span>
                    </div>
                    <div class="anexly-pc-row">
                        <span>Total Savings</span>
                        <span class="anexly-pc-badge sch-total-savings"><?php echo esc_html( $currency . number_format( $products_data[0]['savings_annual'], 2 ) ); ?></span>
                    </div>
                </div>

            </div><!-- /.anexly-pc-cards -->

            <!-- ── CTA button ────────────────────────────── -->
            <div class="anexly-pc-cta-wrap" style="display:block;">
                <a href="<?php echo esc_url( $cta_url ); ?>" class="anexly-pc-cta">
                    <?php echo esc_html( $cta_text ); ?>
                </a>
            </div>
        </div>
    </div><!-- /.anexly-sch-wrap -->

    <script>
    (function(){
        var PRODUCTS = <?php echo wp_json_encode( $products_data ); ?>;
        var CURRENCY = <?php echo wp_json_encode( $currency ); ?>;
        var UID      = <?php echo wp_json_encode( $uid ); ?>;

        function fmt(n){
            var parts = parseFloat(n).toFixed(2).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g,',');
            return CURRENCY + parts.join('.');
        }

        document.addEventListener('DOMContentLoaded', function(){
            var wrap     = document.getElementById(UID);
            if(!wrap) return;

            var trigger     = wrap.querySelector('.anexly-sch-dropdown-wrap');
            var listbox     = wrap.querySelector('.anexly-sch-listbox');
            var thumbEl     = wrap.querySelector('.anexly-sch-thumb');
            var labelEl     = wrap.querySelector('.anexly-sch-selected-label');
            var hiddenSel   = wrap.querySelector('.anexly-sch-select');
            var badgeRow    = wrap.querySelector('.anexly-sch-badge-row');
            var cards       = wrap.querySelector('.anexly-pc-cards');
            var ctaWrap     = wrap.querySelector('.anexly-pc-cta-wrap');
            var isOpen      = false;

            // ── Open / close ────────────────────────────────────────────
            function openDropdown(){
                isOpen = true;
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded','true');
                listbox.style.display = 'block';
            }
            function closeDropdown(){
                isOpen = false;
                trigger.classList.remove('is-open');
                trigger.setAttribute('aria-expanded','false');
                listbox.style.display = 'none';
            }

            trigger.addEventListener('click', function(e){
                // Don't close when clicking list items
                if(e.target.closest('.anexly-sch-listbox')) return;
                isOpen ? closeDropdown() : openDropdown();
            });

            // Close on outside click
            document.addEventListener('click', function(e){
                if(isOpen && !trigger.contains(e.target)) closeDropdown();
            });

            // ── Select item ─────────────────────────────────────────────
            function selectProduct(id, thumbSrc, name){
                // Update trigger display
                if(thumbEl) thumbEl.src = thumbSrc;
                if(labelEl) labelEl.textContent = name;

                // Sync hidden select
                if(hiddenSel) hiddenSel.value = id;

                // Update checkmarks
                var items = listbox.querySelectorAll('.anexly-sch-list-item');
                items.forEach(function(item){
                    var chk = item.querySelector('.anexly-sch-check-icon');
                    if(item.dataset.value == id){
                        item.classList.add('is-selected');
                        item.setAttribute('aria-selected','true');
                        if(!chk){
                            var svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
                            svg.setAttribute('class','anexly-sch-check-icon');
                            svg.setAttribute('width','16'); svg.setAttribute('height','16');
                            svg.setAttribute('viewBox','0 0 16 16'); svg.setAttribute('fill','none');
                            var path = document.createElementNS('http://www.w3.org/2000/svg','path');
                            path.setAttribute('d','M3 8l3.5 3.5L13 4.5');
                            path.setAttribute('stroke','#2563EB'); path.setAttribute('stroke-width','2');
                            path.setAttribute('stroke-linecap','round'); path.setAttribute('stroke-linejoin','round');
                            svg.appendChild(path);
                            item.appendChild(svg);
                        }
                    } else {
                        item.classList.remove('is-selected');
                        item.setAttribute('aria-selected','false');
                        if(chk) chk.remove();
                    }
                });

                closeDropdown();

                // ── Update calculator ────────────────────────────────────
                var p = null;
                for(var i=0;i<PRODUCTS.length;i++){
                    if(PRODUCTS[i].id == id){ p=PRODUCTS[i]; break; }
                }
                if(!p) return;

                wrap.querySelector('.sch-market-monthly').textContent = fmt(p.market_monthly);
                wrap.querySelector('.sch-market-annual').textContent  = fmt(p.market_annual);
                wrap.querySelector('.sch-store-monthly').textContent  = fmt(p.store_monthly);
                wrap.querySelector('.sch-store-annual').textContent   = fmt(p.store_annual);
                wrap.querySelector('.sch-savings-amount').textContent = fmt(p.savings_annual);
                wrap.querySelector('.sch-save-pct').textContent       = p.save_pct + ' %';
                wrap.querySelector('.sch-total-savings').textContent  = fmt(p.savings_annual);

                badgeRow.style.display = 'flex';
                cards.style.display    = 'grid';
                ctaWrap.style.display  = 'block';
            }

            // Listen for list item clicks
            listbox.addEventListener('click', function(e){
                var item = e.target.closest('.anexly-sch-list-item');
                if(!item) return;
                selectProduct(item.dataset.value, item.dataset.thumb, item.querySelector('.anexly-sch-list-name').textContent);
            });

            // Keyboard navigation
            trigger.addEventListener('keydown', function(e){
                if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); isOpen ? closeDropdown() : openDropdown(); }
                if(e.key === 'Escape') closeDropdown();
            });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ── Styles ────────────────────────────────────────────────────────────────────
add_action( 'wp_head', 'anexly_sch_styles' );
function anexly_sch_styles(){
    static $done = false;
    if($done) return;
    $done = true;
    ?>
    <style id="anexly-sch-css">
    /* ── Wrapper ──────────────────────────────────────── */
    .anexly-sch-wrap{
        max-width:976px;
        margin:0 auto;
        padding:60px 24px 70px;
        text-align:center;
        box-sizing:border-box;
        background:#F7F7F8;
        border-radius:24px;
    }
    .anexly-sch-header{ margin-bottom:28px; }

    /* ── Selector row ─────────────────────────────────── */
    .anexly-sch-selector-row{
        display:flex;
        justify-content:center;
        align-items:center;
        gap:14px;
        margin-bottom:24px;
        flex-wrap:wrap;
    }

    /* ── Dropdown trigger — original styles preserved exactly ── */
    .anexly-sch-dropdown-wrap{
        position:relative;
        display:flex;
        align-items:center;
        background:#fff;
        border:1.5px solid #E0E0E0;
        border-radius:50px;
        padding:10px 18px 10px 14px;
        min-width:300px;
        max-width:480px;
        width:100%;
        box-shadow:0 2px 12px rgba(0,0,0,.06);
        cursor:pointer;
        user-select:none;
    }
    .anexly-sch-dropdown-wrap.is-open{
        border-radius:18px 18px 0 0;
    }

    .anexly-sch-icon-slot{
        display:flex;
        align-items:center;
        flex-shrink:0;
        margin-right:10px;
    }
    .anexly-sch-thumb{
        width:28px;
        height:28px;
        border-radius:6px;
        object-fit:cover;
        display:block;
    }
    .anexly-sch-divider-v{
        width:1px;
        height:22px;
        background:#D5D5D5;
        margin-right:14px;
        flex-shrink:0;
    }
    /* Label that replaces the native select text */
    .anexly-sch-selected-label{
        flex:1;
        font-size:16px;
        color:#666666;
        text-align:left;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        background-image: url(https://test.anexly.us/wp-content/uploads/2026/03/Vector-1-2-2.svg);
        background-size: 16px;
        background-repeat: no-repeat;
        background-position: center right;
    }
    /* Chevron — original: pointer-events:none only, no rotation */
    .anexly-sch-chevron{
        display:flex;
        align-items:center;
        flex-shrink:0;
        margin-left:8px;
        pointer-events:none;
    }

    /* ── Custom listbox ───────────────────────────────── */
    .anexly-sch-listbox{
        display:none;
        position:absolute;
        top:100%;
        left:-1px;
        background:#fff;
        border:1px solid #BCBCBC;
        border-top:none;
        border-radius:0 0 18px 18px;
        box-shadow:0 8px 24px rgba(0,0,0,.08);
        z-index:9999;
        list-style:none;
        margin:0;
        padding:6px 0 10px;
        width: calc(100% + 2px);
        max-height:300px;
        overflow-y:auto;
    }
    /* Scrollbar styling */
    .anexly-sch-listbox::-webkit-scrollbar{ width:4px; }
    .anexly-sch-listbox::-webkit-scrollbar-track{ background:transparent; }
    .anexly-sch-listbox::-webkit-scrollbar-thumb{ background:#DDD6D5; border-radius:4px; }

    .anexly-sch-list-placeholder {
        padding: 0px 18px;
        font-weight: 600;
        font-size: 16px;
        color: #737175;
        cursor: default;
    }

    .anexly-sch-list-item{
        display:flex;
        align-items:center;
        gap:10px;
        padding:9px 18px;
        cursor:pointer;
        transition:background .12s;
        position:relative;
    }
    .anexly-sch-list-item:hover,.anexly-sch-list-item.is-selected{
        background: #F8F8F8;
    }
    
    .anexly-sch-list-icon{
        width:32px;
        height:32px;
        border-radius:6px;
        object-fit:cover;
        flex-shrink:0;
        border:1px solid #E5E7EB;
    }
    .anexly-sch-list-name{
        flex:1;
        font-size:14px;
        color:#111013;
        text-align:left;
    }

    .anexly-sch-check-icon{
        flex-shrink:0;
        margin-left:auto;
    }
    
    .anexly-sch-check-icon path {
        stroke: #111013;
    }

    /* ── Hidden native select (for potential form fallback) ── */
    .anexly-sch-select{
        display:none;
    }

    /* ── Badge row ────────────────────────────────────── */
    .anexly-sch-badge-row{
        justify-content:center;
        margin-bottom:-14px;
    }

    /* ── Cards ────────────────────────────────────────── */
    .anexly-sch-wrap .anexly-pc-cards{
        margin-top:24px;
    }
    .anexly-sch-wrap .anexly-pc-cta-wrap{
        margin-top:0;
    }
    .anexly-sch-wrap .anexly-pc-cta{
        display:inline-flex;
    }

    /* ── Responsive ───────────────────────────────────── */
    @media(max-width:600px){
        .anexly-sch-dropdown-wrap{ min-width:0; }
    }
    </style>
    <?php
}


// ══════════════════════════════════════════════════════════════════════════════
// ELEMENTOR WIDGET
// ══════════════════════════════════════════════════════════════════════════════

add_action( 'elementor/widgets/register', 'anexly_sch_register_elementor_widget' );

function anexly_sch_register_elementor_widget( $widgets_manager ) {
    if ( ! did_action( 'elementor/loaded' ) ) return;
    require_once __DIR__ . '/elementor-widget.php';
    $widgets_manager->register( new \Anexly_SCH_Elementor_Widget() );
}