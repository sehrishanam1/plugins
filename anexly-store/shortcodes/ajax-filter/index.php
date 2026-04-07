<?php
/**
 * Shortcode Module: AJAX Filter
 * ─────────────────────────────────────────────
 * Shortcodes:
 *   [anexly_filter_bar]     — category tabs + search bar
 *   [anexly_products_grid]  — product slider with loader
 *
 * Place this entire folder inside:
 *   anexly-shortcodes/shortcodes/ajax-filter/
 *
 * The parent plugin auto-loads this index.php.
 * Assets are enqueued only when a shortcode is present on the page.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Module paths ──────────────────────────────────────────────────────────────
// define( 'ANEXLY_AF_PATH', __DIR__ . '/' );
define( 'ANEXLY_AF_PATH', __DIR__ . DIRECTORY_SEPARATOR );

define( 'ANEXLY_AF_URL',  plugins_url( '', __FILE__ ) . '/' );

// ─── Enqueue assets only on pages that use either shortcode ───────────────────
add_action( 'wp_enqueue_scripts', 'anexly_af_enqueue' );
function anexly_af_enqueue() {
    wp_register_style(
        'anexly-af-style',
        ANEXLY_AF_URL . 'assets/css/filter.css',
        [],
        '1.0.0'
    );
    wp_register_style(
        'anexly-af-responsive',
        ANEXLY_AF_URL . 'assets/css/responsive.css',
        [ 'anexly-af-style' ],
        '1.0.0'
    );
    wp_register_script(
        'anexly-af-script',
        ANEXLY_AF_URL . 'assets/js/filter.js',
        [ 'jquery' ],
        '1.0.0',
        true
    );
    wp_localize_script( 'anexly-af-script', 'AnexlyFilter', [
        'ajax_url'   => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'anexly_filter_nonce' ),
        'no_results' => __( 'No products found.', 'anexly-shortcodes' ),
        'loading'    => __( 'Loading...', 'anexly-shortcodes' ),
    ] );
}

// ─── Helper: actually enqueue (called inside shortcode render) ─────────────────
function anexly_af_load_assets() {
    wp_enqueue_style( 'anexly-af-style' );
    wp_enqueue_style( 'anexly-af-responsive' );
    wp_enqueue_script( 'anexly-af-script' );
}

// ─── Shortcode: Filter Bar ────────────────────────────────────────────────────
add_shortcode( 'anexly_filter_bar', 'anexly_af_render_filter_bar' );
function anexly_af_render_filter_bar( $atts ) {
    $atts = shortcode_atts( [
        'categories' => '', // comma-separated slugs e.g. "streaming,music,games"
    ], $atts, 'anexly_filter_bar' );

    $query_args = [
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => 0,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
    ];

    // If specific categories passed, filter to only those slugs
    if ( ! empty( $atts['categories'] ) ) {
        $slugs = array_map( 'trim', explode( ',', $atts['categories'] ) );
        $query_args['slug'] = $slugs;
        unset( $query_args['parent'] ); // slug query ignores parent
    }

    $categories = get_terms( $query_args );

    ob_start(); ?>
    <div class="anexly-filter-wrapper">

        <!-- Category Tabs -->
        <div class="anexly-category-tabs">
            <?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) :
                $first = true;
                foreach ( $categories as $cat ) :
                    // $first = false;
                    $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                    $icon_url     = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : '';
                    $fallback_svg = anexly_af_get_fallback_icon( $cat->slug );
            ?>
                <button class="anexly-cat-btn <?php echo $first ? 'active' : ''; ?>" data-cat="<?php echo esc_attr( $cat->slug ); ?>">
                    <span class="cat-icon">
                        <?php if ( $icon_url ) : ?>
                            <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>">
                        <?php else : ?>
                            <?php echo $fallback_svg; ?>
                        <?php endif; ?>
                    </span>
                    <span class="cat-label"><?php echo esc_html( $cat->name ); ?></span>
                </button>
           <?php $first = false; endforeach; endif; ?>
        </div>

        <!-- Search Bar -->
        <div class="anexly-search-bar">
            <input type="text" id="anexly-search-input"
                   placeholder="Search your favourite subscription"
                   autocomplete="off" />
            <button class="anexly-search-btn" id="anexly-search-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </button>
        </div>

    </div>
    <?php return ob_get_clean();
}

// ─── Shortcode: Products Grid ─────────────────────────────────────────────────
add_shortcode( 'anexly_products_grid', 'anexly_af_render_products_grid' );
function anexly_af_render_products_grid() {
    anexly_af_load_assets();

    ob_start(); ?>
    <div class="anexly-products-section">

        <!-- Loader Overlay -->
        <div class="anexly-loader-overlay" id="anexly-loader">
            <div class="anexly-loader-inner">
                <div class="anexly-spinner">
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                    <div class="spinner-core">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                </div>
                <p class="loader-text">Finding subscriptions<span class="dot-anim">...</span></p>
            </div>
        </div>

        <!-- Products Slider -->
        <div class="anexly-slider-wrapper">
            <div class="anexly-slider-viewport" id="anexly-slider-viewport">
                <div class="anexly-products-grid" id="anexly-products-grid">
                    <?php echo anexly_af_build_products_html(); ?>
                </div>
            </div>
        </div>

        <!-- Slider Dots -->
        <div class="anexly-slider-dots" id="anexly-slider-dots"></div>

        <!-- No Results -->
        <div class="anexly-no-results" id="anexly-no-results" style="display:none;">
            <div class="no-results-inner">
                <svg viewBox="0 0 64 64" fill="none">
                    <circle cx="28" cy="28" r="20" stroke="currentColor" stroke-width="3"/>
                    <path d="M42 42l14 14" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    <path d="M21 28h14M28 21v14" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <h3>No subscriptions found</h3>
                <p>Try a different category or search term</p>
                <button class="reset-btn" id="anexly-reset-btn">Show All</button>
            </div>
        </div>

    </div>
    <?php return ob_get_clean();
}

// ─── Helper: Build initial products HTML ──────────────────────────────────────
function anexly_af_build_products_html( $cat_slug = 'all', $search = '', $page = 1, $per_page = 10 ) {
    $result = json_decode( anexly_af_get_products_html( $cat_slug, $search, $page, $per_page ), true );
    return isset( $result['html'] ) ? $result['html'] : '';
}

// ─── Core query: returns JSON with html + meta ─────────────────────────────────
function anexly_af_get_products_html( $cat_slug = 'all', $search = '', $page = 1, $per_page = 10 ) {
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
    ];

    if ( $cat_slug && $cat_slug !== 'all' ) {
        $args['tax_query'] = [ [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $cat_slug ),
        ] ];
    }

    if ( ! empty( $search ) ) {
        $args['s'] = sanitize_text_field( $search );
    }

    $products = new WP_Query( $args );
    $html     = '';
    $has_more = $products->max_num_pages > $page;

    if ( $products->have_posts() ) :
        while ( $products->have_posts() ) : $products->the_post();
            global $product;
            if ( ! $product ) continue;

            $product_id   = $product->get_id();
            $name         = $product->get_name();

            if ( $product->is_type( 'variable' ) ) {
                $min_price = $product->get_variation_price( 'min', true );
                $price     = wc_price( $min_price );
            } else {
                $price     = $product->get_price_html();
            }

            $permalink    = get_permalink( $product_id );
            $image        = get_the_post_thumbnail_url( $product_id, 'woocommerce_thumbnail' );
            $sale_percent = '';

            if ( $product->is_on_sale() ) {
                $reg  = (float) $product->get_regular_price();
                $sale = (float) $product->get_sale_price();
                if ( $reg > 0 ) $sale_percent = round( ( ( $reg - $sale ) / $reg ) * 100 );
            }

            $html .= '<div class="anexly-product-card" data-product-id="' . esc_attr( $product_id ) . '">';

            if ( $sale_percent ) {
                $html .= '<span class="card-badge save-badge">Save ' . esc_html( $sale_percent ) . '%</span>';
            }
            $html .= '<div class="card-body-container">';
            $html .= '<div class="card-image-wrap">';
            if ( $image ) {
                $html .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $name ) . '" class="card-image" loading="lazy">';
            } else {
                $html .= '<div class="card-image-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>';
            }
            $html .= '</div>';

            $html .= '<div class="card-body">';
            $html .= '<h3 class="card-title">' . esc_html( $name ) . anexly_get_product_badges_html( $product_id ) . '</h3>';
            $html .= '<div class="card-price">' . $price . '<span class="price-period">/month</span></div>';
            $html .= '</div>';
            
            $html .= '</div>';
            $html .= '<a href="' . esc_url( $permalink ) . '" class="card-cta">Purchase now <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></a>';
            $html .= '</div>';

        endwhile;
        wp_reset_postdata();
    endif;

    return json_encode( [
        'html'     => $html,
        'has_more' => $has_more,
        'found'    => $products->found_posts,
        'page'     => $page,
    ] );
}

// ─── AJAX Handler ─────────────────────────────────────────────────────────────
add_action( 'wp_ajax_anexly_filter_products',        'anexly_af_ajax_handler' );
add_action( 'wp_ajax_nopriv_anexly_filter_products', 'anexly_af_ajax_handler' );
function anexly_af_ajax_handler() {
    check_ajax_referer( 'anexly_filter_nonce', 'nonce' );

    $cat_slug = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : 'all';
    $search   = isset( $_POST['search'] )   ? sanitize_text_field( $_POST['search'] )   : '';
    $page     = isset( $_POST['page'] )     ? absint( $_POST['page'] )                  : 1;

    $result = json_decode( anexly_af_get_products_html( $cat_slug, $search, $page, 10 ), true );
    wp_send_json_success( $result );
}

// ─── Fallback SVG icons for categories ────────────────────────────────────────
function anexly_af_get_fallback_icon( $slug ) {
    $icons = [
        'streaming'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>',
        'music'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>',
        'ai-private' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>',
        'anexly-tv'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 8.5c0-2.209-4.03-4-9-4s-9 1.791-9 4 4.03 4 9 4 9-1.791 9-4z"/><path d="M4 8.5v7c0 2.209 4.03 4 9 4s9-1.791 9-4v-7"/><path d="M4 12c0 2.209 4.03 4 9 4s9-1.791 9-4"/></svg>',
        'games'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 12h4m-2-2v4"/><circle cx="17" cy="12" r="1" fill="currentColor"/><circle cx="19" cy="10" r="1" fill="currentColor"/><rect x="2" y="6" width="20" height="12" rx="6"/></svg>',
        'upgrade'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
    ];
    return $icons[ $slug ] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>';
}