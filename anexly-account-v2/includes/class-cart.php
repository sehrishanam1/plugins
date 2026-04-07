<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_Cart {

    public function __construct() {
        add_filter( 'woocommerce_locate_template',      [ $this, 'locate_template' ], 10, 3 );
        add_filter( 'woocommerce_locate_core_template', [ $this, 'locate_template' ], 10, 3 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_anxa_update_cart_qty',        [ $this, 'ajax_update_qty' ] );
        add_action( 'wp_ajax_nopriv_anxa_update_cart_qty', [ $this, 'ajax_update_qty' ] );
    }

    public function locate_template( $template, $template_name, $template_path ) {
        $plugin_template = ANXA_DIR . 'templates/woocommerce/' . $template_name;
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
        return $template;
    }

    public function enqueue_assets() {
        if ( ! is_cart() ) return;

        wp_enqueue_style( 'anxa-cart', ANXA_URL . 'css/custom-cart.css', [], ANXA_VERSION );

        // Enqueue the account script (already loaded sitewide) or fall back to jquery
        $deps = wp_script_is( 'ansx-account-script', 'enqueued' ) ? [ 'ansx-account-script' ] : [ 'jquery' ];

        wp_register_script( 'anxa-cart-qty', ANXA_URL . 'js/anxa-cart-qty.js', $deps, ANXA_VERSION, true );
        wp_enqueue_script( 'anxa-cart-qty' );

        wp_localize_script( 'anxa-cart-qty', 'anxaCart', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'anxa_cart_nonce' ),
        ] );
    }

    public function ajax_update_qty() {
        if ( ! check_ajax_referer( 'anxa_cart_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
        }

        $cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ?? '' ) );
        $quantity      = max( 0, intval( $_POST['quantity'] ?? 0 ) );

        WC()->cart->set_quantity( $cart_item_key, $quantity, true );
        WC()->cart->calculate_totals();

        // Item line total after update
        $item_html = '';
        $removed   = true;
        foreach ( WC()->cart->get_cart() as $key => $item ) {
            if ( $key === $cart_item_key ) {
                $item_html = WC()->cart->get_product_subtotal( $item['data'], $item['quantity'] );
                $removed   = false;
                break;
            }
        }

        // Re-render totals rows (mirrors cart.php totals block)
        ob_start(); ?>
        <div class="anxa-totals-row">
            <span><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
            <span><?php wc_cart_totals_subtotal_html(); ?></span>
        </div>
        <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
        <div class="anxa-totals-row anxa-discount-row">
            <span><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
            <span class="anxa-discount-amount">-<?php wc_cart_totals_coupon_html( $coupon ); ?></span>
        </div>
        <?php endforeach; ?>
        <?php $discount = WC()->cart->get_discount_total();
        if ( $discount > 0 && empty( WC()->cart->get_coupons() ) ) : ?>
        <div class="anxa-totals-row anxa-discount-row">
            <span><?php esc_html_e( 'Discount', 'woocommerce' ); ?></span>
            <span class="anxa-discount-amount">-<?php echo wc_price( $discount ); ?></span>
        </div>
        <?php endif; ?>
        <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
        <div class="anxa-totals-row">
            <span><?php echo esc_html( $fee->name ); ?></span>
            <span><?php wc_cart_totals_fee_html( $fee ); ?></span>
        </div>
        <?php endforeach; ?>
        <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) :
            if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) :
                foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
        <div class="anxa-totals-row">
            <span><?php echo esc_html( $tax->label ); ?></span>
            <span><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
        </div>
        <?php endforeach; else : ?>
        <div class="anxa-totals-row">
            <span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
            <span><?php wc_cart_totals_taxes_total_html(); ?></span>
        </div>
        <?php endif; endif; ?>
        <div class="anxa-totals-row anxa-total-row">
            <span><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
            <span class="anxa-total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
        </div>
        <?php $totals_html = ob_get_clean();

        wp_send_json_success( [
            'item_html'   => $item_html,
            'removed'     => $removed,
            'totals_html' => $totals_html,
        ] );
    }

    public function qty_minus_button() {
        echo '<button type="button" class="anxa-qty-btn anxa-qty-minus" aria-label="Decrease quantity">&#8722;</button>';
    }

    public function qty_plus_button() {
        echo '<button type="button" class="anxa-qty-btn anxa-qty-plus" aria-label="Increase quantity">&#43;</button>';
    }
}

new Anexly_Cart();