<?php
/**
 * Shortcode Module: Shop Filter
 * ─────────────────────────────────────────────
 * Shortcode: [shop_filter]
 *            [shop_filter per_page="6" columns="3" title="All products" subtitle="Library of subscriptions" orderby="popular"]
 *
 * Place this entire folder inside:
 *   anexly-shortcodes/shortcodes/shop-filter/
 *
 * Requires WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>Shop Filter module:</strong> WooCommerce is required.</p></div>';
    } );
    return;
}

define( 'SHOP_FILTER_VERSION', '2.0.0' );
// define( 'SHOP_FILTER_PATH',    __DIR__ . '/' );
define( 'SHOP_FILTER_PATH',    __DIR__ . DIRECTORY_SEPARATOR );

define( 'SHOP_FILTER_URL',     plugins_url( '', __FILE__ ) . '/' );

// require_once SHOP_FILTER_PATH . 'includes/class-shop-filter-assets.php';
// require_once SHOP_FILTER_PATH . 'includes/class-shop-filter-query.php';
// require_once SHOP_FILTER_PATH . 'includes/class-shop-filter-ajax.php';
// require_once SHOP_FILTER_PATH . 'includes/class-shop-filter-shortcode.php';

require_once SHOP_FILTER_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class-shop-filter-assets.php';
require_once SHOP_FILTER_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class-shop-filter-query.php';
require_once SHOP_FILTER_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class-shop-filter-ajax.php';
require_once SHOP_FILTER_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class-shop-filter-shortcode.php';

new Shop_Filter_Assets();
new Shop_Filter_Ajax();
new Shop_Filter_Shortcode();
