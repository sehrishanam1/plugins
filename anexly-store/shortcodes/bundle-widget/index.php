<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode: [bundle_widget]
 *
 * Displays a bundle product selector widget.
 * Configure products at Settings → Bundle Widget in WP Admin.
 *
 * Usage: [bundle_widget]
 */

define( 'ANEXLY_BW_PATH', __DIR__ . '/' );
define( 'ANEXLY_BW_URL',  plugins_url( '', __FILE__ ) . '/' );

/* ================================================================
   ACTIVATION DEFAULTS
   ================================================================ */
register_activation_hook( ANEXLY_SC_PATH . 'anexly-shortcodes.php', function () {
    $defaults = [
        'abw_products'     => [],
        'abw_discount_pct' => 25,
        'abw_timer_end'    => '',
        'abw_social_name'  => 'John from Brazil',
        'abw_social_item'  => 'Spotify Premium Subscription',
        'abw_bundle_name'  => 'Subscription Bundle',
    ];
    foreach ( $defaults as $key => $val ) {
        if ( false === get_option( $key ) ) update_option( $key, $val );
    }
} );

/* ================================================================
   ADMIN MENU + SETTINGS
   ================================================================ */
add_action( 'admin_menu',  'abw_register_admin_menu' );
add_action( 'admin_init',  'abw_register_settings' );

function abw_register_admin_menu() {
    add_submenu_page(
        'anexly-account',
        'Bundle Widget',
        'Bundle Widget',
        'manage_woocommerce',
        'abw-settings',
        'abw_settings_page'
    );
}

function abw_register_settings() {
    foreach ( [ 'abw_discount_pct', 'abw_timer_end', 'abw_social_name', 'abw_social_item', 'abw_bundle_name' ] as $opt ) {
        register_setting( 'abw_group', $opt );
    }
    register_setting( 'abw_group', 'abw_products', [ 'sanitize_callback' => 'abw_sanitize_products' ] );
}

function abw_sanitize_products( $raw ) {
    if ( ! is_array( $raw ) ) return [];
    return array_values( array_filter( array_map( function ( $item ) {
        $wc_id = absint( $item['wc_product_id'] ?? 0 );
        if ( ! $wc_id ) return null;
        return [
            'wc_product_id'  => $wc_id,
            'price_override' => round( floatval( $item['price_override'] ?? 0 ), 2 ),
            'default_on'     => ! empty( $item['default_on'] ) ? 1 : 0,
        ];
    }, $raw ) ) );
}

/* ================================================================
   SETTINGS PAGE
   ================================================================ */
function abw_settings_page() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        echo '<div class="wrap"><p style="color:red">WooCommerce is required.</p></div>';
        return;
    }

    $products     = get_option( 'abw_products', [] );
    $discount_pct = get_option( 'abw_discount_pct', 25 );
    $timer_end    = get_option( 'abw_timer_end', '' );
    $social_name  = get_option( 'abw_social_name', 'John from Brazil' );
    $social_item  = get_option( 'abw_social_item', 'Spotify Premium Subscription' );
    $bundle_name  = get_option( 'abw_bundle_name', 'Subscription Bundle' );

    $wc_products = wc_get_products( [ 'limit' => 500, 'status' => 'publish' ] );
    ?>
    <div class="wrap">
      <h1>🎁 Bundle Widget Settings</h1>
      <p>Use shortcode <code>[bundle_widget]</code> to display on any page.</p>

      <form method="post" action="options.php">
        <?php settings_fields( 'abw_group' ); ?>

        <h2>📦 Products</h2>
        <p style="color:#555">Name and image are fetched automatically from WooCommerce. Set price to 0 to use WC product price.</p>

        <table class="widefat" id="abw-table" style="max-width:700px">
          <thead style="background:#f9f9f9">
            <tr>
              <th style="width:30px;padding:10px">↕</th>
              <th style="padding:10px">WooCommerce Product <span style="color:#c00">*</span></th>
              <th style="width:150px;padding:10px">Price Override ($)<br><small style="font-weight:400;color:#777">0 = use WC price</small></th>
              <th style="width:100px;padding:10px;text-align:center">Default ON?</th>
              <th style="width:60px;padding:10px"></th>
            </tr>
          </thead>
          <tbody id="abw-sortable">
            <?php foreach ( $products as $i => $p ) : ?>
            <tr style="border-bottom:1px solid #eee">
              <td style="padding:10px;text-align:center;color:#aaa;cursor:move">↕</td>
              <td style="padding:10px">
                <select name="abw_products[<?= $i ?>][wc_product_id]" style="width:100%">
                  <option value="0">— Select Product —</option>
                  <?php foreach ( $wc_products as $wp ) : ?>
                    <option value="<?= $wp->get_id() ?>" <?php selected( $p['wc_product_id'], $wp->get_id() ) ?>>
                      <?= esc_html( $wp->get_name() ) ?> (#<?= $wp->get_id() ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td style="padding:10px">
                <input type="number" step="0.01" min="0"
                  name="abw_products[<?= $i ?>][price_override]"
                  value="<?= esc_attr( $p['price_override'] ) ?>"
                  style="width:110px">
              </td>
              <td style="padding:10px;text-align:center">
                <input type="checkbox" name="abw_products[<?= $i ?>][default_on]" value="1" <?php checked( $p['default_on'], 1 ) ?>>
              </td>
              <td style="padding:10px">
                <button type="button" class="button abw-remove-row" style="color:#c00">✕</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p><button type="button" class="button button-secondary" id="abw-add-row">+ Add Product</button></p>

        <h2>⚙️ General Settings</h2>
        <table class="form-table" style="max-width:700px">
          <tr>
            <th>Bundle Name</th>
            <td>
              <input type="text" name="abw_bundle_name" value="<?= esc_attr( $bundle_name ) ?>" class="regular-text">
              <p class="description">Shown in WooCommerce orders.</p>
            </td>
          </tr>
          <tr>
            <th>Discount % (on 2+ products)</th>
            <td><input type="number" name="abw_discount_pct" value="<?= esc_attr( $discount_pct ) ?>" min="0" max="100" style="width:70px"> %</td>
          </tr>
          <tr>
            <th>Countdown Timer End</th>
            <td>
              <input type="datetime-local" name="abw_timer_end" value="<?= esc_attr( $timer_end ) ?>">
              <p class="description">Leave empty to hide the timer.</p>
            </td>
          </tr>
          <tr>
            <th>Social Proof — Name</th>
            <td><input type="text" name="abw_social_name" value="<?= esc_attr( $social_name ) ?>" class="regular-text"></td>
          </tr>
          <tr>
            <th>Social Proof — Item</th>
            <td><input type="text" name="abw_social_item" value="<?= esc_attr( $social_item ) ?>" class="regular-text"></td>
          </tr>
        </table>

        <?php submit_button( '💾 Save Settings' ); ?>
      </form>
    </div>

    <script>
    var abwRowIdx  = <?= count( $products ) ?>;
    var abwWCProds = <?= wp_json_encode( array_map( fn($p) => [ 'id' => $p->get_id(), 'name' => $p->get_name() ], $wc_products ) ) ?>;

    function abwBuildRow( i ) {
        var opts = '<option value="0">— Select Product —</option>' +
            abwWCProds.map(function(p){
                return '<option value="'+p.id+'">'+p.name+' (#'+p.id+')</option>';
            }).join('');
        return '<tr style="border-bottom:1px solid #eee">' +
            '<td style="padding:10px;text-align:center;color:#aaa;cursor:move">↕</td>' +
            '<td style="padding:10px"><select name="abw_products['+i+'][wc_product_id]" style="width:100%">'+opts+'</select></td>' +
            '<td style="padding:10px"><input type="number" step="0.01" min="0" name="abw_products['+i+'][price_override]" value="0" style="width:110px"></td>' +
            '<td style="padding:10px;text-align:center"><input type="checkbox" name="abw_products['+i+'][default_on]" value="1" checked></td>' +
            '<td style="padding:10px"><button type="button" class="button abw-remove-row" style="color:#c00">✕</button></td>' +
            '</tr>';
    }

    document.getElementById('abw-add-row').addEventListener('click', function() {
        document.querySelector('#abw-sortable').insertAdjacentHTML('beforeend', abwBuildRow( abwRowIdx++ ));
    });
    document.addEventListener('click', function(e) {
        if ( e.target.classList.contains('abw-remove-row') ) e.target.closest('tr').remove();
    });
    </script>
    <?php
}

/* ================================================================
   SHORTCODE
   ================================================================ */
add_shortcode( 'bundle_widget', 'abw_shortcode' );

function abw_shortcode() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return '<p style="color:red;border:1px solid red;padding:10px;">[bundle_widget] WooCommerce is not active.</p>';
    }

    $raw_products = get_option( 'abw_products', [] );
    $discount_pct = (int) get_option( 'abw_discount_pct', 25 );
    $timer_end    = get_option( 'abw_timer_end', '' );
    $social_name  = get_option( 'abw_social_name', 'John from Brazil' );
    $social_item  = get_option( 'abw_social_item', 'Spotify Premium Subscription' );

    $currency = function_exists( 'get_woocommerce_currency_symbol' )
        ? html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
        : '$';

    $js_products = [];
    foreach ( $raw_products as $p ) {
        $wc_id = (int) $p['wc_product_id'];
        if ( ! $wc_id ) continue;
        $wc = wc_get_product( $wc_id );
        if ( ! $wc ) continue;

        // For variable products, auto-select the first available (preferably in-stock) variation.
        $variation_id   = 0;
        $variation_data = [];
        if ( $wc->is_type( 'variable' ) ) {
            $available = $wc->get_available_variations();
            if ( ! empty( $available ) ) {
                $chosen = null;
                foreach ( $available as $v ) {
                    if ( $v['is_in_stock'] ) { $chosen = $v; break; }
                }
                if ( ! $chosen ) $chosen = $available[0];
                $variation_id   = (int) $chosen['variation_id'];
                $variation_data = $chosen['attributes']; // e.g. [ 'attribute_pa_color' => 'red' ]

                // Swap to the variation object so we get its price and image.
                if ( $p['price_override'] <= 0 ) {
                    $var_obj = wc_get_product( $variation_id );
                    if ( $var_obj ) $wc = $var_obj;
                }
            }
        }

        $price = $p['price_override'] > 0
            ? $p['price_override']
            : floatval( $wc->get_price() );

        $img_id  = $wc->get_image_id();
        $img_url = $img_id
            ? wp_get_attachment_image_url( $img_id, 'thumbnail' )
            : wc_placeholder_img_src( 'thumbnail' );

        $js_products[] = [
            'wc_id'        => $wc_id,
            'label'        => wc_get_product( $wc_id )->get_name(),
            'price'        => round( $price, 2 ),
            'img'          => $img_url,
            'checked'      => (bool) $p['default_on'],
            'variation_id' => $variation_id,
            'variation'    => $variation_data,
        ];
    }

    if ( empty( $js_products ) ) {
        return '<p style="color:#888;padding:12px;border:1px dashed #ccc;border-radius:8px;">'
             . '📦 No products configured. Go to <a href="' . admin_url('options-general.php?page=abw-settings') . '">Settings → Bundle Widget</a> to add products.'
             . '</p>';
    }

    $nonce = wp_create_nonce( 'abw_add_to_cart' );

    // Enqueue CSS
    wp_enqueue_style(
        'abw-style',
        ANEXLY_BW_URL . 'assets/widget.css',
        [],
        ANEXLY_SC_VERSION
    );

    ob_start();
    $widget_id       = 'abw-' . wp_rand( 1000, 9999 );
    $timer_timestamp = $timer_end ? strtotime( $timer_end ) : 0;
    $show_timer      = $timer_timestamp > 0;
    $timer_iso       = $show_timer ? date( 'c', $timer_timestamp ) : '';
    include ANEXLY_BW_PATH . 'templates/widget.php';
    return ob_get_clean();
}

/* ================================================================
   AJAX — ADD TO CART
   ================================================================ */
add_action( 'wp_ajax_abw_add_to_cart',        'abw_handle_add_to_cart' );
add_action( 'wp_ajax_nopriv_abw_add_to_cart', 'abw_handle_add_to_cart' );

function abw_handle_add_to_cart() {
    check_ajax_referer( 'abw_add_to_cart', 'nonce' );

    $selected = isset( $_POST['selected'] ) ? (array) $_POST['selected'] : [];
    if ( empty( $selected ) ) {
        wp_send_json_error( [ 'message' => 'No products selected.' ] );
    }

    $raw_products   = get_option( 'abw_products', [] );
    $valid_products = [];
    foreach ( $raw_products as $p ) {
        $valid_products[ $p['wc_product_id'] ] = floatval( $p['price_override'] ?? 0 );
    }

    $discount_pct  = (int) get_option( 'abw_discount_pct', 25 );
    $items_to_add  = [];

    foreach ( $selected as $s ) {
        $wc_id = absint( $s['wc_id'] ?? 0 );
        if ( ! $wc_id || ! isset( $valid_products[ $wc_id ] ) ) continue;

        $wc_obj          = wc_get_product( $wc_id );
        if ( ! $wc_obj ) continue;

        // Resolve variation: prefer what the front-end sent, fall back to server-side auto-detect.
        $variation_id   = absint( $s['variation_id'] ?? 0 );
        $variation_data = is_array( $s['variation'] ?? null ) ? array_map( 'sanitize_text_field', $s['variation'] ) : [];

        if ( $wc_obj->is_type( 'variable' ) && ! $variation_id ) {
            // Server-side fallback: pick the first in-stock variation.
            $available = $wc_obj->get_available_variations();
            if ( ! empty( $available ) ) {
                $chosen = null;
                foreach ( $available as $v ) {
                    if ( $v['is_in_stock'] ) { $chosen = $v; break; }
                }
                if ( ! $chosen ) $chosen = $available[0];
                $variation_id   = (int) $chosen['variation_id'];
                $variation_data = $chosen['attributes'];
            }
        }

        $override = $valid_products[ $wc_id ];
        $price    = round( $override > 0 ? $override : floatval( $wc_obj->get_price() ), 2 );
        if ( $price <= 0 ) continue;

        $items_to_add[] = [ 'wc_id' => $wc_id, 'price' => $price, 'label' => $wc_obj->get_name(), 'variation_id' => $variation_id, 'variation' => $variation_data ];
    }

    if ( empty( $items_to_add ) ) {
        wp_send_json_error( [ 'message' => 'No valid products found.' ] );
    }

    // Require at least 3 products to form a bundle.
    if ( count( $items_to_add ) < 3 ) {
        wp_send_json_error( [ 'message' => 'Please select at least 3 products to create a bundle.' ] );
    }

    WC()->cart->empty_cart();

    // Store each item at its FULL override price (no discount baked in).
    // The bundle discount is applied as a negative cart fee so WC displays
    // it as its own line, keeping individual product prices correct.
    foreach ( $items_to_add as $item ) {
        WC()->cart->add_to_cart( $item['wc_id'], 1, $item['variation_id'], $item['variation'], [
            'abw_bundled_item' => true,
            'abw_price'        => $item['price'],
        ] );
    }

    // Save discount info to session for the cart fee hook.
    $subtotal = array_sum( array_column( $items_to_add, 'price' ) );
    WC()->session->set( 'abw_discount_pct',    $discount_pct );
    WC()->session->set( 'abw_bundle_subtotal', round( $subtotal, 2 ) );
    WC()->session->set( 'abw_has_bundle',      true );

    wp_send_json_success( [
        'message'  => '✅ Items added to cart!',
        'cart_url' => wc_get_cart_url(),
    ] );
}

/* ================================================================
   FORCE OVERRIDE PRICE IN CART
   ================================================================ */

// Restore custom meta from session so prices survive page reloads.
add_filter( 'woocommerce_get_cart_item_from_session', 'abw_restore_cart_item_from_session', 10, 2 );

function abw_restore_cart_item_from_session( $cart_item, $session_values ) {
    if ( ! empty( $session_values['abw_bundled_item'] ) && isset( $session_values['abw_price'] ) ) {
        $cart_item['abw_bundled_item'] = true;
        $cart_item['abw_price']        = floatval( $session_values['abw_price'] );
    }
    return $cart_item;
}

// Apply the override price (full, undiscounted) to each bundle item.
add_action( 'woocommerce_before_calculate_totals', 'abw_force_prices', 20, 1 );

function abw_force_prices( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    $has_bundle = false;
    foreach ( $cart->get_cart() as $item ) {
        if ( ! empty( $item['abw_bundled_item'] ) && isset( $item['abw_price'] ) ) {
            $item['data']->set_price( floatval( $item['abw_price'] ) );
            $has_bundle = true;
        }
    }

    // If no bundle items remain (e.g. user manually removed them), clear session flags.
    if ( ! $has_bundle ) {
        WC()->session->set( 'abw_has_bundle',      false );
        WC()->session->set( 'abw_bundle_subtotal', 0 );
    }
}

/* ================================================================
   BUNDLE DISCOUNT AS A CART FEE
   Keeps individual product prices at their override values and
   shows the discount as its own line in the cart totals.
   ================================================================ */
add_action( 'woocommerce_cart_calculate_fees', 'abw_apply_bundle_discount_fee', 10, 1 );

function abw_apply_bundle_discount_fee( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
    if ( ! WC()->session->get( 'abw_has_bundle' ) ) return;

    // Count bundle items still in the cart.
    $bundle_items = [];
    foreach ( $cart->get_cart() as $item ) {
        if ( ! empty( $item['abw_bundled_item'] ) && isset( $item['abw_price'] ) ) {
            $bundle_items[] = floatval( $item['abw_price'] );
        }
    }

    if ( count( $bundle_items ) < 3 ) return; // discount only when 3+ products remain

    $discount_pct = (int) WC()->session->get( 'abw_discount_pct', get_option( 'abw_discount_pct', 25 ) );
    if ( $discount_pct <= 0 ) return;

    $subtotal        = array_sum( $bundle_items );
    $discount_amount = round( $subtotal * $discount_pct / 100, 2 ) * -1; // negative = deduction

    $cart->add_fee(
        sprintf( 'Bundle Discount (%d%%)', $discount_pct ),
        $discount_amount,
        false  // not taxable; set true if your store needs tax on discounts
    );
}

/* ================================================================
   AJAX URL for front-end
   ================================================================ */
add_action( 'wp_footer', 'abw_localize_ajax' );
function abw_localize_ajax() {
    if ( ! function_exists( 'wc_get_cart_url' ) ) return;
    ?>
    <script>
    if ( typeof ABW === 'undefined' ) {
        var ABW = {
            ajax_url: <?= wp_json_encode( admin_url( 'admin-ajax.php' ) ) ?>,
            cart_url: <?= wp_json_encode( wc_get_cart_url() ) ?>
        };
    }
    </script>
    <?php
}