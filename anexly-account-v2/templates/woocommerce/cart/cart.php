<?php
defined( 'ABSPATH' ) || exit;
?>

<div class="anxa-cart-wrapper">
    <h1 class="anxa-cart-title"><?php esc_html_e( 'Cart', 'woocommerce' ); ?></h1>

    <?php do_action( 'woocommerce_before_cart' ); ?>

    <form class="woocommerce-cart-form anxa-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

        <div class="anxa-cart-layout">

            <!-- LEFT: Cart Items -->
            <div class="anxa-cart-items">

                <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
                    if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) continue;
                    if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) continue;
                    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                ?>

                <div class="anxa-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>"
                     data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">

                    <div class="anxa-item-remove">
                        <?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                            '<a href="%s" class="remove anxa-remove-btn" aria-label="%s" data-product_id="%s" data-product_sku="%s">&#x2715;</a>',
                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                            esc_attr__( 'Remove this item', 'woocommerce' ),
                            esc_attr( $product_id ),
                            esc_attr( $_product->get_sku() )
                        ), $cart_item_key ); ?>
                    </div>

                    <div class="anxa-item-image">
                        <?php $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                        if ( $product_permalink ) {
                            echo '<a href="' . esc_url( $product_permalink ) . '">' . $thumbnail . '</a>';
                        } else {
                            echo $thumbnail;
                        } ?>
                    </div>

                    <div class="anxa-item-info">
                        <div class="anxa-item-name">
                            <?php if ( $product_permalink ) {
                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                            } else {
                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) );
                            }
                            ?>
                        </div>
                        <?php
                            // Only show duration/period meta, hide everything else
                            $item_data = $cart_item['data']->get_meta_data();
                            $allowed   = ['duration', 'period', 'months', '_duration']; // add your actual meta keys here if different
                            $formatted = wc_get_formatted_cart_item_data( $cart_item );
                            
                            // Show nothing — meta is hidden via CSS, price shown below name instead
                            ?>
                            <div class="anxa-item-duration">
                                <?php
                                // Try to get the variation attribute for duration
                                if ( ! empty( $cart_item['variation'] ) ) {
                                    foreach ( $cart_item['variation'] as $attr => $value ) {
                                        if ( $value ) {
                                            echo '<span>' . esc_html( $value ) . '</span>';
                                            break; // only show first one (e.g. "1 month")
                                        }
                                    }
                                }
                            ?>
                        </div>
                        <div class="anxa-item-price"
                             data-unit-price="<?php echo esc_attr( $_product->get_price() ); ?>"
                             data-currency-symbol="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>">
                            <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                        </div>
                    </div>

                    <div class="anxa-item-qty">
                        <?php woocommerce_quantity_input( [
                            'input_name'  => "cart[{$cart_item_key}][qty]",
                            'input_value' => $cart_item['quantity'],
                            'max_value'   => $_product->get_max_purchase_quantity(),
                            'min_value'   => $_product->is_sold_individually() ? 1 : 0,
                            'step'        => 1,
                        ], $_product, true ); ?>
                    </div>

                </div>

                <?php endforeach; ?>

                <?php do_action( 'woocommerce_cart_contents' ); ?>
                <?php do_action( 'woocommerce_after_cart_contents' ); ?>

                <!-- Promo code — Mobile -->
                <div class="anxa-coupon-row anxa-mobile-only">
                    <div class="anxa-coupon">
                        <input type="text" name="coupon_code" class="input-text anxa-coupon-input" value="" placeholder="<?php esc_attr_e( 'Promo Code', 'woocommerce' ); ?>" />
                        <button type="submit" class="button anxa-coupon-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
                    </div>
                </div>

            </div><!-- /.anxa-cart-items -->

            <!-- RIGHT: Summary -->
            <div class="anxa-cart-summary">

                <!-- Promo code — Desktop -->
                <div class="anxa-coupon-row anxa-desktop-only">
                    <div class="anxa-coupon">
                        <input type="text" name="coupon_code" class="input-text anxa-coupon-input" value="" placeholder="<?php esc_attr_e( 'Promo Code', 'woocommerce' ); ?>" />
                        <button type="submit" class="button anxa-coupon-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
                    </div>
                </div>

                <div class="anxa-cart-totals">

                    <div class="anxa-totals-row">
                        <span><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
                        <span><?php wc_cart_totals_subtotal_html(); ?></span>
                    </div>

                    <?php
                    // --- Tier-based discount (same logic as cart popup) ---
                    $tiers = apply_filters( 'acp_discount_tiers', [
                        [ 'threshold' => 30, 'pct' => 5,  'label' => '5%'  ],
                        [ 'threshold' => 70, 'pct' => 10, 'label' => '10%' ],
                        [ 'threshold' => 90, 'pct' => 15, 'label' => '15%' ],
                    ] );

                    $subtotal     = (float) WC()->cart->get_subtotal();
                    $current_tier = null;
                    foreach ( $tiers as $t ) {
                        if ( $subtotal >= $t['threshold'] ) {
                            $current_tier = $t;
                        }
                    }
                    $discount_amount = $current_tier ? round( $subtotal * ( $current_tier['pct'] / 100 ), 2 ) : 0;

                    if ( $discount_amount > 0 ) : ?>
                    <div class="anxa-totals-row anxa-discount-row">
                        <span><?php esc_html_e( 'Discount', 'woocommerce' ); ?> <small class="anxa-coupon-code">(<?php echo esc_html( $current_tier['label'] ); ?>)</small></span>
                        <span class="anxa-discount-amount">-<?php echo wc_price( $discount_amount ); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php
                    // --- Coupon discounts on top ---
                    foreach ( WC()->cart->get_coupons() as $code => $coupon ) :
                        $coupon_amount = WC()->cart->get_coupon_discount_amount( $code, WC()->cart->display_cart_ex_tax );
                    ?>
                    <div class="anxa-totals-row anxa-discount-row">
                        <span><?php esc_html_e( 'Coupon', 'woocommerce' ); ?> <small class="anxa-coupon-code">(<?php echo esc_html( strtoupper( $code ) ); ?>)</small></span>
                        <span class="anxa-discount-amount">-<?php echo wc_price( $coupon_amount ); ?></span>
                    </div>
                    <?php endforeach; ?>

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
                        <span class="anxa-total-amount">
                            <?php
                            $wc_total      = (float) WC()->cart->get_total( 'edit' );
                            $coupon_total  = (float) WC()->cart->get_discount_total();
                            $final_total   = max( 0, $wc_total - $discount_amount );
                            echo wc_price( $final_total );
                            ?>
                        </span>
                    </div>

                </div>

                <div class="anxa-checkout-btn-wrap">
                    <?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
                </div>

            </div><!-- /.anxa-cart-summary -->

        </div><!-- /.anxa-cart-layout -->

        <?php do_action( 'woocommerce_cart_actions' ); ?>
        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
        <button type="submit" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" style="display:none!important" aria-hidden="true">
            <?php esc_html_e( 'Update cart', 'woocommerce' ); ?>
        </button>

    </form>

    <?php do_action( 'woocommerce_after_cart' ); ?>

</div>

