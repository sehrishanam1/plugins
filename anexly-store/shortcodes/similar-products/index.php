<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Similar Products — Shortcode + Elementor Widget
 *
 * Shortcode : [similar_products]
 * Elementor : "Similar Products Slider (Woo)"  →  category "Anexly"
 *
 * Shortcode attributes & defaults:
 *   heading      = "Similar Products"
 *   source       = latest | featured | sale        (default: latest)
 *   category     = WC category slug                (default: all)
 *   count        = 1-20                            (default: 6)
 *   per_view     = 1-6 slides on desktop           (default: 3)
 *   orderby      = date | title | menu_order | rand (default: date)
 *   order        = ASC | DESC                      (default: DESC)
 *   button_text  = CTA label                       (default: "Purchase now")
 *   show_badge   = yes | no                        (default: yes)
 *   price_suffix = text after price                (default: "")
 */

define( 'ANEXLY_SP_PATH', __DIR__ . '/' );
define( 'ANEXLY_SP_URL',  plugins_url( '', __FILE__ ) . '/' );

/* ================================================================
   ELEMENTOR WIDGET REGISTRATION
   ================================================================ */
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    require_once ANEXLY_SP_PATH . 'widgets/class-similar-products-widget.php';
    $widgets_manager->register( new Anexly_Similar_Products_Widget() );
} );

// Register the "Anexly" Elementor category if it doesn't exist yet.
add_action( 'elementor/elements/categories_registered', function ( $elements_manager ) {
    $elements_manager->add_category( 'anexly', [
        'title' => __( 'Anexly', 'anexly-shortcodes' ),
        'icon'  => 'fa fa-plug',
    ] );
} );

/* ================================================================
   SHORTCODE
   ================================================================ */
add_shortcode( 'similar_products', 'anx_sp_shortcode' );

function anx_sp_shortcode( $atts ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return '<p style="color:red;border:1px solid red;padding:10px;">[similar_products] WooCommerce is not active.</p>';
    }

    $atts = shortcode_atts( [
        'heading'      => 'Similar Products',
        'source'       => 'latest',
        'category'     => '',
        'count'        => 6,
        'per_view'     => 3,
        'orderby'      => 'date',
        'order'        => 'DESC',
        'button_text'  => 'Purchase now',
        'show_badge'   => 'yes',
        'price_suffix' => '',
    ], $atts, 'similar_products' );

    $settings = anx_sp_normalise_settings( [
        'heading'                 => $atts['heading'],
        'products_source'         => $atts['source'],
        'product_category'        => $atts['category'],
        'posts_per_page'          => $atts['count'],
        'slides_per_view_desktop' => $atts['per_view'],
        'orderby'                 => $atts['orderby'],
        'order'                   => $atts['order'],
        'button_text'             => $atts['button_text'],
        'show_sale_badge'         => $atts['show_badge'],
        'price_suffix'            => $atts['price_suffix'],
    ] );

    $products = anx_sp_get_products( $settings );

    if ( empty( $products ) ) {
        return '<p style="color:#888;padding:12px;border:1px dashed #ccc;border-radius:8px;">'
             . '📦 No products found. Check your Similar Products shortcode settings.'
             . '</p>';
    }

    anx_sp_enqueue_assets();

    $widget_id = 'anx-sp-' . wp_rand( 1000, 9999 );
    $per_view  = $settings['slides_per_view_desktop'];

    ob_start();
    include ANEXLY_SP_PATH . 'templates/widget.php';
    return ob_get_clean();
}

/* ================================================================
   SHARED HELPERS  (used by both shortcode and Elementor widget)
   ================================================================ */

/**
 * Sanitise & normalise a raw settings array from either the shortcode
 * attributes or the Elementor widget's get_settings_for_display().
 */
function anx_sp_normalise_settings( $raw ) {
    return [
        'heading'                 => sanitize_text_field( $raw['heading'] ?? 'Similar Products' ),
        'products_source'         => in_array( $raw['products_source'] ?? '', [ 'latest', 'featured', 'sale', 'related' ], true )
                                        ? $raw['products_source'] : 'latest',
        'product_category'        => sanitize_text_field( $raw['product_category'] ?? '' ),
        'posts_per_page'          => min( 20, max( 1, absint( $raw['posts_per_page'] ?? 6 ) ) ),
        'slides_per_view_desktop' => min( 6,  max( 1, absint( $raw['slides_per_view_desktop'] ?? 3 ) ) ),
        'orderby'                 => in_array( $raw['orderby'] ?? '', [ 'date', 'title', 'menu_order', 'rand' ], true )
                                        ? $raw['orderby'] : 'date',
        'order'                   => in_array( strtoupper( $raw['order'] ?? '' ), [ 'ASC', 'DESC' ], true )
                                        ? strtoupper( $raw['order'] ) : 'DESC',
        'button_text'             => sanitize_text_field( $raw['button_text'] ?? 'Purchase now' ),
        'show_sale_badge'         => ( 'yes' === strtolower( $raw['show_sale_badge'] ?? 'yes' ) ) ? 'yes' : '',
        'price_suffix'            => sanitize_text_field( $raw['price_suffix'] ?? '' ),
    ];
}

/** Query WooCommerce products from normalised settings. */
function anx_sp_get_products( $settings ) {
    $args = [
        'status'  => 'publish',
        'limit'   => $settings['posts_per_page'],
        'orderby' => $settings['orderby'],
        'order'   => $settings['order'],
        'return'  => 'objects',
    ];

    // ── AUTO-DETECT current product's categories ──────────────────
    // If we're on a single product page and no category is manually set,
    // pull products from the same categories as the current product.
    if ( empty( $settings['product_category'] ) && is_singular( 'product' ) ) {
        $current_id = get_the_ID();

        // Exclude the current product from results
        $args['exclude'] = [ $current_id ];

        // Get the current product's category slugs
        $terms = get_the_terms( $current_id, 'product_cat' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $category_slugs    = wp_list_pluck( $terms, 'slug' );
            $args['category']  = $category_slugs;
        }

    } elseif ( ! empty( $settings['product_category'] ) ) {
        // Manual category override from shortcode attribute
        $args['category'] = [ $settings['product_category'] ];
    }

    // ── Source filters ─────────────────────────────────────────────
    if ( 'featured' === $settings['products_source'] ) {
        $args['featured'] = true;
    }

    if ( 'sale' === $settings['products_source'] ) {
        $sale_ids = wc_get_product_ids_on_sale();
        if ( empty( $sale_ids ) ) return [];
        $args['include'] = $sale_ids;
    }

    // ── Fallback: if still no results, return latest products ──────
    $products = wc_get_products( $args );

    if ( empty( $products ) && is_singular( 'product' ) ) {
        // Category had no other products — show general latest instead
        $fallback_args = [
            'status'  => 'publish',
            'limit'   => $settings['posts_per_page'],
            'orderby' => 'date',
            'order'   => 'DESC',
            'exclude' => [ get_the_ID() ],
            'return'  => 'objects',
        ];
        $products = wc_get_products( $fallback_args );
    }

    return $products;
}

/** Build sale badge text (e.g. "Save 30%") for a product. */
function anx_sp_get_sale_badge_text( $product ) {
    if ( ! $product || ! $product->is_on_sale() ) {
        return '';
    }

    // Variable product
    if ( $product->is_type( 'variable' ) ) {
        $variation_ids = $product->get_children();
        $max_discount  = 0;

        if ( ! empty( $variation_ids ) ) {
            foreach ( $variation_ids as $variation_id ) {
                $variation = wc_get_product( $variation_id );
                if ( ! $variation ) continue;

                $regular = (float) $variation->get_regular_price();
                $sale    = (float) $variation->get_sale_price();

                if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
                    $discount = round( ( ( $regular - $sale ) / $regular ) * 100 );
                    if ( $discount > $max_discount ) {
                        $max_discount = $discount;
                    }
                }
            }
        }

        if ( $max_discount > 0 ) {
            return 'Save ' . $max_discount . '%';
        }

        return 'Sale';
    }

    // Simple product
    $regular = (float) $product->get_regular_price();
    $sale    = (float) $product->get_sale_price();

    if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
        $discount = round( ( ( $regular - $sale ) / $regular ) * 100 );
        if ( $discount > 0 ) {
            return 'Save ' . $discount . '%';
        }
    }

    return 'Sale';
}

/** Return WC price HTML with optional suffix span. */
function anx_sp_get_price_html( $product, $suffix = '' ) {
    if ( ! $product ) return '';

    // Show only the minimum (starting) price instead of a range
    if ( $product->is_type( 'variable' ) ) {
        $min_price = $product->get_variation_price( 'min', true );
        $html = '<span class="woocommerce-Price-amount amount">'
              . wc_price( $min_price )
              . ' </span> / month';
    } else {
        $html = '<span class="woocommerce-Price-amount amount">'
              . wc_price( $product->get_price() )
              . '/ month </span>';
    }

    if ( $suffix ) {
        $html .= '<span class="anx-sp-price-suffix-custom">' . esc_html( $suffix ) . '</span>';
    }
    return $html;
}

/* ================================================================
   ASSETS  (enqueued once per page regardless of how many instances)
   ================================================================ */
function anx_sp_enqueue_assets() {
    if ( wp_style_is( 'anx-similar-products', 'enqueued' ) ) return;

    // Swiper — reuse whatever is already registered (Elementor / WC ship it).
    if ( ! wp_script_is( 'swiper', 'registered' ) ) {
        wp_register_script( 'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            [], '11', true );
        wp_register_style( 'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            [], '11' );
    }
    wp_enqueue_script( 'swiper' );
    wp_enqueue_style( 'swiper' );

    // Inline widget CSS.
    wp_register_style( 'anx-similar-products', false, [ 'swiper' ], ANEXLY_SC_VERSION );
    wp_enqueue_style( 'anx-similar-products' );
    wp_add_inline_style( 'anx-similar-products', anx_sp_get_css() );
}

/** Widget CSS — single source of truth for both shortcode and Elementor. */
function anx_sp_get_css() {
    return '
.anx-similar-products-widget{position:relative;padding:28px 0 8px;}
.anx-similar-products-widget .anx-sp-header{text-align:center;margin-bottom:28px;}
.anx-similar-products-widget .anx-sp-header h2{margin:0;color:#202124;font-size:41px;line-height:1.12;font-weight:800;letter-spacing:-0.03em;}
.anx-similar-products-widget .anx-sp-slider{position:relative;padding:0 58px 42px;overflow:hidden;}
.anx-similar-products-widget .swiper-slide{height:auto;}
.anx-similar-products-widget .anx-sp-card{background:#fff;border:1px solid #e8e8e8;border-radius:24px;overflow:hidden;height:100%;display:flex;flex-direction:column;box-shadow:0 0 0 rgba(0,0,0,0);}
.anx-similar-products-widget .anx-sp-card-top{position:relative;min-height:257px;padding:32px 26px 20px;text-align:center;}
.anx-similar-products-widget .anx-sp-badge{position:absolute;top:10px;right:14px;display:inline-flex;align-items:center;justify-content:center;min-height:28px;padding:5px 12px;background:#fff;color:#ff5b60;border:1px solid #ff7f82;border-radius:30px;font-size:13px;line-height:1;font-weight:600;}
.anx-similar-products-widget .anx-sp-logo-wrap{width:74px;height:74px;border-radius:16px;overflow:hidden;margin:0 auto 18px;background:#f7f7f7;}
.anx-similar-products-widget .anx-sp-logo{width:100%;height:100%;object-fit:cover;display:block;}
.anx-similar-products-widget .anx-sp-title{margin:0;color:#202124;font-size:21px;line-height:1.2;font-weight:700;}
.anx-similar-products-widget .anx-sp-price-wrap{margin-top:16px;display:flex;align-items:flex-start;justify-content:center;gap:4px;flex-wrap:wrap;}
.anx-similar-products-widget .anx-sp-price-wrap .price{display:flex;align-items:flex-end;gap:6px;flex-wrap:wrap;justify-content:center;margin:0;}
.anx-similar-products-widget .anx-sp-price-wrap ins{text-decoration:none;}
.anx-similar-products-widget .anx-sp-price-wrap .amount,.anx-similar-products-widget .anx-sp-price-wrap bdi{color:#ff5d63;font-size:18px;line-height:1;font-weight:800;}
.anx-similar-products-widget .anx-sp-price-wrap del .amount,.anx-similar-products-widget .anx-sp-price-wrap del bdi{color:#999;font-size:14px;font-weight:500;}
.anx-similar-products-widget .anx-sp-price-suffix-custom{color:#777;font-size:14px;line-height:1.2;font-weight:500;margin-left:2px;}
.anx-similar-products-widget .anx-sp-card-bottom{margin-top:auto;padding:20px 24px 18px;border-top:1px solid #f0f0f0;}
.anx-similar-products-widget .anx-sp-btn{height:48px;width:100%;border-radius:999px;display:flex;align-items:center;justify-content:center;gap:9px;text-decoration:none;color:#fff;font-size:16px;line-height:1;font-weight:700;background:linear-gradient(90deg,#f76576 0%,#f26b3d 100%);transition:transform .2s ease,box-shadow .2s ease,opacity .2s ease;}
.anx-similar-products-widget .anx-sp-btn:hover{transform:translateY(-1px);box-shadow:0 12px 24px rgba(242,107,61,.18);color:#fff;}
.anx-similar-products-widget .anx-sp-btn svg{flex:0 0 auto;}
.anx-similar-products-widget .anx-sp-nav{position:absolute;top:50%;transform:translateY(-50%);width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:3;color:#fff;background:rgba(91,86,90,.75);box-shadow:0 10px 22px rgba(0,0,0,.18);transition:all .2s ease;}
.anx-similar-products-widget .anx-sp-nav:hover{background:#5b565a;}
.anx-similar-products-widget .anx-sp-prev{left:12px;}
.anx-similar-products-widget .anx-sp-next{right:12px;}
.anx-similar-products-widget .anx-sp-pagination{margin-top:18px;text-align:center;}
.anx-similar-products-widget .anx-sp-pagination .swiper-pagination-bullet{width:8px;height:8px;margin:0 4px !important;background:#d5d1d1;opacity:1;}
.anx-similar-products-widget .anx-sp-pagination .swiper-pagination-bullet-active{width:22px;border-radius:20px;background:#f36576;}
@media(max-width:1024px){.anx-similar-products-widget .anx-sp-header h2{font-size:34px;}.anx-similar-products-widget .anx-sp-slider{padding:0 48px 40px;}}
@media(max-width:767px){.anx-similar-products-widget{padding:20px 0 0;}.anx-similar-products-widget .anx-sp-header{margin-bottom:20px;}.anx-similar-products-widget .anx-sp-header h2{font-size:28px;}.anx-similar-products-widget .anx-sp-slider{padding:0 18px 34px;overflow:visible;}.anx-similar-products-widget .swiper-wrapper{display:grid;grid-template-columns:1fr 1fr;gap:16px;transform:none !important;width:auto !important;}.anx-similar-products-widget .swiper-slide{width:auto !important;margin:0 !important;}.anx-similar-products-widget .anx-sp-nav,.anx-similar-products-widget .anx-sp-pagination{display:none;}.anx-similar-products-widget .anx-sp-card-top{min-height:235px;padding:28px 18px 18px;}.anx-similar-products-widget .anx-sp-card-bottom{padding:16px 18px 18px;}}
';
}