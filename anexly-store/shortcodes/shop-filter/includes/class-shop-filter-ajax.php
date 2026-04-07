<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Shop_Filter_Ajax
 * Handles AJAX requests for filtering products
 */
class Shop_Filter_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_shop_filter_products',        array( $this, 'filter_products' ) );
        add_action( 'wp_ajax_nopriv_shop_filter_products', array( $this, 'filter_products' ) );

        add_action( 'wp_ajax_shop_filter_options',        array( $this, 'get_filter_options' ) );
        add_action( 'wp_ajax_nopriv_shop_filter_options', array( $this, 'get_filter_options' ) );
    }

    /**
     * Return filtered products as JSON
     */
    public function filter_products() {
        check_ajax_referer( 'shop_filter_nonce', 'nonce' );

        $filters = array(
            'categories' => isset( $_POST['categories'] ) ? (array) $_POST['categories'] : array(),
            'brands'     => isset( $_POST['brands'] )     ? (array) $_POST['brands']     : array(),
            'duration'   => isset( $_POST['duration'] )   ? (array) $_POST['duration']   : array(),
            'price_min'  => isset( $_POST['price_min'] )  ? floatval( $_POST['price_min'] )  : 0,
            'price_max'  => isset( $_POST['price_max'] )  ? floatval( $_POST['price_max'] )  : 999999,
            'orderby'    => isset( $_POST['orderby'] )    ? sanitize_text_field( $_POST['orderby'] ) : 'popular',
            'paged'      => isset( $_POST['paged'] )      ? intval( $_POST['paged'] )      : 1,
            'per_page'   => isset( $_POST['per_page'] )   ? intval( $_POST['per_page'] )   : 6,
        );

        $result = Shop_Filter_Query::get_products( $filters );

        wp_send_json_success( array(
            'products'  => $result['products'],
            'total'     => $result['total'],
            'max_pages' => $result['max_pages'],
            'paged'     => $filters['paged'],
            'currency'  => html_entity_decode( get_woocommerce_currency_symbol() ),
        ));
    }

    /**
     * Return filter options (categories, brands, duration, price range)
     */
    public function get_filter_options() {
        check_ajax_referer( 'shop_filter_nonce', 'nonce' );
        $options = Shop_Filter_Query::get_filter_options();
        wp_send_json_success( $options );
    }
}
