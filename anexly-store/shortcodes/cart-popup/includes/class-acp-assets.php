<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ACP_Assets {

	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'dequeue_theme_cart_scripts' ], 99 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
	}

	public static function enqueue() {
		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_front_page() && ! is_shop() ) {
			// Load on ALL frontend pages so cart icon always works
		}

		wp_enqueue_style(
			'acp-popup',
			ACP_PLUGIN_URL . 'assets/css/acp-popup.css',
			[],
			ACP_VERSION
		);

		wp_enqueue_script(
			'acp-popup',
			ACP_PLUGIN_URL . 'assets/js/acp-popup.js',
			[ 'jquery' ],
			ACP_VERSION,
			true
		);

		wp_localize_script( 'acp-popup', 'acpData', [
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'acp_nonce' ),
			'cartUrl'     => wc_get_cart_url(),
			'checkoutUrl' => wc_get_checkout_url(),
			'currency'    => get_woocommerce_currency_symbol(),
			'i18n'        => [
				'yourCart'     => __( 'Your Cart', 'anexly-cart-popup' ),
				'emptyCart'    => __( 'Your cart is empty', 'anexly-cart-popup' ),
				'remove'       => __( 'Remove', 'anexly-cart-popup' ),
				'subtotal'     => __( 'Subtotal', 'anexly-cart-popup' ),
				'discount'     => __( 'Discount', 'anexly-cart-popup' ),
				'finalTotal'   => __( 'Final Total', 'anexly-cart-popup' ),
				'checkout'     => __( 'Checkout', 'anexly-cart-popup' ),
				'recommended'  => __( 'Recommended for You', 'anexly-cart-popup' ),
				'discProgress' => __( 'Discount Progress', 'anexly-cart-popup' ),
				'viewCart'     => __( 'View Full Cart', 'anexly-cart-popup' ),
				'adding'       => __( 'Adding…', 'anexly-cart-popup' ),
			],
		] );
	}

	public static function dequeue_theme_cart_scripts() {
		$handles = [
			'flatsome-woocommerce', 'flatsome-cart',
			'blocksy-woocommerce-cart', 'ct-scripts',
			'astra-woocommerce',
			'oceanwp-woo-cart',
		];
		foreach ( $handles as $handle ) {
			wp_dequeue_script( $handle );
		}
	}
}
