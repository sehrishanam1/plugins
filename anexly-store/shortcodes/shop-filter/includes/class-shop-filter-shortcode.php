<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Shop_Filter_Shortcode
 * Usage: [shop_filter] or [shop_filter per_page="6" columns="3" cart_btn_text="View Plans"]
 */
class Shop_Filter_Shortcode {

    public function __construct() {
        add_shortcode( 'shop_filter', array( $this, 'render' ) );
        add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'custom_cart_btn_text' ), 10, 2 );
    }

    /**
     * Override WooCommerce "Select options" button text
     */
    public function custom_cart_btn_text( $text, $product ) {
        if ( $product->is_type( 'variable' ) ) {
            return 'Add to cart';
        }
        return $text;
    }

    public function render( $atts ) {
        $total_products = wp_count_posts( 'product' );
        $published_products = isset( $total_products->publish ) ? $total_products->publish : 0;

        $atts = shortcode_atts( array(
            'per_page' => 6,
            'columns'  => 3,
            'title'    => 'All products',
            'subtitle' => 'Library of ' . $published_products . ' subscriptions',
            'orderby'  => 'popular',
        ), $atts, 'shop_filter' );

        // Pass config to JS
        wp_add_inline_script( 'shop-filter-js',
            'window.ShopFilterConfig = ' . wp_json_encode( array(
                'per_page' => intval( $atts['per_page'] ),
                'columns'  => intval( $atts['columns'] ),
                'orderby'  => sanitize_text_field( $atts['orderby'] ),
            )) . ';',
            'before'
        );

        ob_start();
        include SHOP_FILTER_PATH . 'templates' . DIRECTORY_SEPARATOR . 'shop-filter-template.php';
        return ob_get_clean();
    }
}