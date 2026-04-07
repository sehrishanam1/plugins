<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Shop_Filter_Assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
    }

    public function enqueue() {
        wp_enqueue_style( 'shop-filter-fonts',
            'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
            array(), null
        );
        wp_enqueue_style( 'shop-filter-css',
            SHOP_FILTER_URL . 'assets/css/shop-filter.css',
            array(), SHOP_FILTER_VERSION
        );
        wp_enqueue_style( 'shop-filter-products-css',
            SHOP_FILTER_URL . 'assets/css/shop-products.css',
            array( 'shop-filter-css' ), SHOP_FILTER_VERSION
        );
        wp_enqueue_script( 'shop-filter-js',
            SHOP_FILTER_URL . 'assets/js/shop-filter.js',
            array( 'jquery' ), SHOP_FILTER_VERSION, true
        );

        // Pass AJAX data to JS
        wp_localize_script( 'shop-filter-js', 'ShopFilterData', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'shop_filter_nonce' ),
            'currency_symbol' => html_entity_decode( get_woocommerce_currency_symbol() ),
        ));
    }
}
