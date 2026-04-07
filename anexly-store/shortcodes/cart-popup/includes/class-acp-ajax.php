<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ACP_Ajax {

	public static function init() {
		add_action( 'wp_ajax_acp_get_cart',        [ __CLASS__, 'get_cart' ] );
		add_action( 'wp_ajax_nopriv_acp_get_cart', [ __CLASS__, 'get_cart' ] );

		add_action( 'wp_ajax_acp_update_qty',        [ __CLASS__, 'update_qty' ] );
		add_action( 'wp_ajax_nopriv_acp_update_qty', [ __CLASS__, 'update_qty' ] );

		add_action( 'wp_ajax_acp_remove_item',        [ __CLASS__, 'remove_item' ] );
		add_action( 'wp_ajax_nopriv_acp_remove_item', [ __CLASS__, 'remove_item' ] );

		add_action( 'wp_ajax_acp_add_product',        [ __CLASS__, 'add_product' ] );
		add_action( 'wp_ajax_nopriv_acp_add_product', [ __CLASS__, 'add_product' ] );
	}

	/* ---------------------------------------------------------------
	 * Helper: build full cart payload
	 * ------------------------------------------------------------- */
	public static function build_cart_data() {
		$cart      = WC()->cart;
		$cart->calculate_totals();
		$items     = [];

		foreach ( $cart->get_cart() as $key => $item ) {
			$product  = $item['data'];
			$image_id = $product->get_image_id();
			$image    = $image_id
				? wp_get_attachment_image_url( $image_id, 'thumbnail' )
				: wc_placeholder_img_src( 'thumbnail' );

			$is_variation = $product->is_type( 'variation' );
			$main_name    = $is_variation ? wc_get_product( $product->get_parent_id() )->get_name() : $product->get_name();

			$variation_name = '';
			if ( $is_variation ) {
				$attributes = $product->get_variation_attributes();
				$var_values = [];
				foreach ( $attributes as $attr_name => $attr_value ) {
					$taxonomy = str_replace( 'attribute_', '', $attr_name );
					if ( taxonomy_exists( $taxonomy ) ) {
						$term = get_term_by( 'slug', $attr_value, $taxonomy );
						if ( $term ) {
							$var_values[] = $term->name;
						} else {
							$var_values[] = array_reduce( explode( '-', $attr_value ), function( $carry, $word ) { return $carry . ( $carry ? ' ' : '' ) . ucfirst( $word ); } );
						}
					} else {
						$var_values[] = array_reduce( explode( '-', $attr_value ), function( $carry, $word ) { return $carry . ( $carry ? ' ' : '' ) . ucfirst( $word ); } );
					}
				}
				$variation_name = implode( ' - ', array_filter( $var_values ) );
			}

			$items[] = [
				'cart_key'       => $key,
				'product_id'     => $item['product_id'],
				'name'           => $product->get_name(),
				'main_name'      => $main_name,
				'variation_name' => $variation_name,
				'price'          => (float) $product->get_price(),
				'qty'            => (int) $item['quantity'],
				'line_total'     => (float) $item['line_total'],
				'image'          => $image,
				'permalink'      => get_permalink( $item['product_id'] ),
			];
		}

		// Discount tiers (filterable)
		$tiers = apply_filters( 'acp_discount_tiers', [
			[ 'threshold' => 30, 'pct' => 5,  'label' => '5%'  ],
			[ 'threshold' => 70, 'pct' => 10, 'label' => '10%' ],
			[ 'threshold' => 90, 'pct' => 15, 'label' => '15%' ],
		] );

		$subtotal      = (float) $cart->get_subtotal();
		$current_tier  = null;
		$next_tier     = null;

		foreach ( $tiers as $t ) {
			if ( $subtotal >= $t['threshold'] ) {
				$current_tier = $t;
			}
		}
		foreach ( $tiers as $t ) {
			if ( $subtotal < $t['threshold'] ) {
				$next_tier = $t;
				break;
			}
		}

		$discount_amount = $current_tier ? round( $subtotal * ( $current_tier['pct'] / 100 ), 2 ) : 0;

		// Recommended products (on-sale or featured, exclude cart items)
		$cart_ids    = wp_list_pluck( $items, 'product_id' );
		$rec_query   = new WP_Query( [
			'post_type'      => 'product',
			'posts_per_page' => 2,
			'post__not_in'   => $cart_ids,
			'orderby'        => 'rand',
			'tax_query'      => [ [
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			] ],
			'fields'         => 'ids',
		] );

		// Fallback: random products if no featured
		if ( empty( $rec_query->posts ) ) {
			$rec_query = new WP_Query( [
				'post_type'      => 'product',
				'posts_per_page' => 2,
				'post__not_in'   => $cart_ids,
				'orderby'        => 'rand',
				'fields'         => 'ids',
			] );
		}

		$recommended = [];
		foreach ( $rec_query->posts as $pid ) {
			$p = wc_get_product( $pid );
			if ( ! $p || ! $p->is_in_stock() || $p->is_type( 'external' ) ) continue;

			$img_id       = $p->get_image_id();
			$variation_id = 0;
			$variation    = [];
			$price        = (float) $p->get_price();

			// For variable products, resolve the first available in-stock variation
			if ( $p->is_type( 'variable' ) ) {
				$available = $p->get_available_variations();
				foreach ( $available as $v ) {
					if ( $v['is_in_stock'] && $v['variation_id'] ) {
						$variation_id = $v['variation_id'];
						foreach ( $v['attributes'] as $attr_key => $attr_val ) {
							$clean_key           = str_replace( 'attribute_', '', $attr_key );
							$variation[ $clean_key ] = $attr_val;
						}
						// Use the variation image and price if available
						$var_obj = wc_get_product( $variation_id );
						if ( $var_obj ) {
							$var_img_id = $var_obj->get_image_id();
							if ( $var_img_id ) $img_id = $var_img_id;
							$price = (float) $var_obj->get_price();
						}
						break;
					}
				}
				// No purchasable variation found — skip
				if ( ! $variation_id ) continue;
			}

			$recommended[] = [
				'product_id'   => $pid,
				'variation_id' => $variation_id,
				'variation'    => $variation,
				'name'         => $p->get_name(),
				'price'        => $price,
				'image'        => $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : wc_placeholder_img_src( 'thumbnail' ),
				'permalink'    => get_permalink( $pid ),
			];
		}

		return [
			'items'           => $items,
			'item_count'      => $cart->get_cart_contents_count(),
			'subtotal'        => $subtotal,
			'subtotal_html'   => wc_price( $subtotal ),
			'discount_amount' => $discount_amount,
			'discount_html'   => $discount_amount > 0 ? '-' . wc_price( $discount_amount ) : wc_price( 0 ),
			'total'           => round( $subtotal - $discount_amount, 2 ),
			'total_html'      => wc_price( round( $subtotal - $discount_amount, 2 ) ),
			'tiers'           => $tiers,
			'current_tier'    => $current_tier,
			'next_tier'       => $next_tier,
			'recommended'     => $recommended,
			'cart_url'        => wc_get_cart_url(),
			'checkout_url'    => wc_get_checkout_url(),
		];
	}

	public static function get_cart() {
		check_ajax_referer( 'acp_nonce', 'nonce' );
		wp_send_json_success( self::build_cart_data() );
	}

	public static function update_qty() {
		check_ajax_referer( 'acp_nonce', 'nonce' );
		$key = sanitize_text_field( $_POST['cart_key'] ?? '' );
		$qty = absint( $_POST['qty'] ?? 1 );

		if ( ! $key ) {
			wp_send_json_error( 'Invalid cart key' );
		}

		WC()->cart->set_quantity( $key, $qty, true );
		wp_send_json_success( self::build_cart_data() );
	}

	public static function remove_item() {
		check_ajax_referer( 'acp_nonce', 'nonce' );
		$key = sanitize_text_field( $_POST['cart_key'] ?? '' );

		if ( ! $key ) {
			wp_send_json_error( 'Invalid cart key' );
		}

		WC()->cart->remove_cart_item( $key );
		wp_send_json_success( self::build_cart_data() );
	}

	public static function add_product() {
		check_ajax_referer( 'acp_nonce', 'nonce' );
		$product_id   = absint( $_POST['product_id']   ?? 0 );
		$variation_id = absint( $_POST['variation_id'] ?? 0 );
		$variation    = isset( $_POST['variation'] ) && is_array( $_POST['variation'] )
			? array_map( 'sanitize_text_field', $_POST['variation'] )
			: [];

		if ( ! $product_id ) {
			wp_send_json_error( [ 'message' => 'Invalid product' ] );
		}

		$result = WC()->cart->add_to_cart( $product_id, 1, $variation_id, $variation );

		if ( $result ) {
			wp_send_json_success( self::build_cart_data() );
		} else {
			$notices = wc_get_notices( 'error' );
			$msg     = ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : 'Could not add product';
			wc_clear_notices();
			wp_send_json_error( [ 'message' => $msg ] );
		}
	}
}