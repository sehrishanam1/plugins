<?php
/**
 * Review order table — Anexly override (with product images, clean one-line meta)
 *
 * @package WooCommerce\Templates
 * @version 5.2.0 compatible
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a clean deduplicated one-line meta string from variation attributes ONLY.
 * Never reads item_data or any other source to avoid duplicates.
 */
function anx_cart_meta_line( $cart_item, $_product ) {
    if ( empty( $cart_item['variation'] ) ) return '';

    $parts = [];
    $seen  = [];

    foreach ( $cart_item['variation'] as $raw_key => $value ) {
        $value = trim( $value );
        if ( $value === '' ) continue;

        // Normalise key → human label
        $key   = preg_replace( '/^attribute_(pa_)?/i', '', $raw_key );
        $key   = str_replace( [ '-', '_' ], ' ', $key );
        $label = ucwords( trim( $key ) );

        // Resolve taxonomy slug → term name
        $tax = 'pa_' . sanitize_title( preg_replace( '/^attribute_(pa_)?/i', '', $raw_key ) );
        if ( taxonomy_exists( $tax ) ) {
            $term = get_term_by( 'slug', $value, $tax );
            if ( $term ) $value = $term->name;
        }

        $value = ucfirst( $value );

        // Deduplicate by label (only first occurrence wins)
        $uid = strtolower( $label );
        if ( isset( $seen[ $uid ] ) ) continue;
        $seen[ $uid ] = true;

        $parts[] = '<span class="anx-meta-key">' . esc_html( $label ) . ':</span> <span class="anx-meta-val">' . esc_html( $value ) . '</span>';
    }

    if ( empty( $parts ) ) return '';
    return implode( ' &middot; ', $parts );
}
?>
<table class="shop_table woocommerce-checkout-review-order-table">
    <thead>
        <tr>
            <th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
            <th class="product-total"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php do_action( 'woocommerce_review_order_before_cart_contents' ); ?>

        <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) : ?>
            <?php
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) continue;
            if ( ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) continue;

            // Thumbnail
            $product_id = $_product->get_id();
            $thumb_url  = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
            if ( ! $thumb_url && $_product->is_type( 'variation' ) ) {
                $thumb_url = get_the_post_thumbnail_url( $_product->get_parent_id(), 'thumbnail' );
            }
            if ( ! $thumb_url ) $thumb_url = wc_placeholder_img_src();

            // Clean product name (strip any HTML/links)
            $product_name = wp_strip_all_tags( $_product->get_name() );

            // One-line meta
            $meta_line = anx_cart_meta_line( $cart_item, $_product );
            ?>
            <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
                <td class="product-name">
                    <div class="anx-checkout-product-row">

                        <div class="anx-checkout-thumb-wrap">
                            <img src="<?php echo esc_url( $thumb_url ); ?>"
                                 alt="<?php echo esc_attr( $product_name ); ?>"
                                 class="anx-checkout-thumb" />
                        </div>

                        <div class="anx-checkout-product-info">
                            <div class="anx-checkout-name-row">
                                <span class="anx-checkout-product-name"><?php echo esc_html( $product_name ); ?></span>
                                <span class="anx-checkout-qty">&times;<?php echo esc_html( $cart_item['quantity'] ); ?></span>
                            </div>
                            <?php if ( $meta_line ) : ?>
                                <div class="anx-checkout-meta-row"><?php echo $meta_line; // phpcs:ignore ?></div>
                            <?php endif; ?>
                        </div>

                    </div>
                </td>
                <td class="product-total">
                    <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php do_action( 'woocommerce_review_order_after_cart_contents' ); ?>
    </tbody>
    <tfoot>
        <tr class="cart-subtotal">
            <th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
            <td><?php wc_cart_totals_subtotal_html(); ?></td>
        </tr>

        <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
            <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                <td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
            <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
            <?php wc_cart_totals_shipping_html(); ?>
            <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
        <?php endif; ?>

        <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
            <tr class="fee">
                <th><?php echo esc_html( $fee->name ); ?></th>
                <td><?php wc_cart_totals_fee_html( $fee ); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
            <tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                <th><?php echo esc_html( $tax->label ); ?></th>
                <td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

        <tr class="order-total">
            <th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
            <td><?php wc_cart_totals_order_total_html(); ?></td>
        </tr>

        <?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
    </tfoot>
</table>
