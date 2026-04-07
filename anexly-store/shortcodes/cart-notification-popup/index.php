<?php
/**
 * Cart Notification Popup — Anexly Store
 *
 * Rules:
 *  - Shows when cart has items
 *  - Hidden on cart, checkout, my-account pages
 *  - Triggered by scroll at a random % (handled in JS)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'ALP_PATH' ) ) define( 'ALP_PATH', ANEXLY_SC_PATH . 'shortcodes/cart-notification-popup/' );
if ( ! defined( 'ALP_URL'  ) ) define( 'ALP_URL',  ANEXLY_SC_URL  . 'shortcodes/cart-notification-popup/' );

/* -------------------------------------------------------
   1. Visibility rules
------------------------------------------------------- */
if ( ! function_exists( 'alp_should_show_popup' ) ) {
    function alp_should_show_popup() {
        if ( ! class_exists( 'WooCommerce' ) ) return false;
        if ( ! WC()->cart )                    return false;
        if ( WC()->cart->is_empty() )          return false; // must have items
        if ( is_cart() )                       return false; // not on cart page
        if ( is_checkout() )                   return false; // not on checkout
        if ( is_account_page() )               return false; // not on my account
        if ( is_wc_endpoint_url( 'order-received' ) ) return false;
        return true;
    }
}

/* -------------------------------------------------------
   2. Enqueue assets + pass data to JS
------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    if ( ! alp_should_show_popup() ) return;

    wp_enqueue_style(
        'alp-style',
        ALP_URL . 'assets/popup.css',
        [],
        ANEXLY_SC_VERSION
    );

    wp_enqueue_script(
        'alp-script',
        ALP_URL . 'assets/popup.js',
        [ 'jquery' ],
        ANEXLY_SC_VERSION,
        true
    );

    // Build cart items for JS
    $cart_items = [];
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product      = $cart_item['data'];
        $image        = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
        
        $main_name     = $product->get_title();
        $variation_str = '';
        $short_desc    = '';

        if ( $product->is_type( 'variation' ) ) {
            $parent = wc_get_product( $product->get_parent_id() );
            if ( $parent ) {
                $short_desc = $parent->get_short_description();
            }
            
            // Get variation values without labels
            $formatted_attributes = [];
            if ( isset( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
                foreach ( $cart_item['variation'] as $name => $value ) {
                    if ( '' === $value ) continue;
                    $taxonomy = str_replace( 'attribute_', '', $name );
                    if ( taxonomy_exists( $taxonomy ) ) {
                        $term = get_term_by( 'slug', $value, $taxonomy );
                        if ( ! is_wp_error( $term ) && $term ) {
                            $formatted_attributes[] = $term->name;
                        } else {
                            $formatted_attributes[] = ucfirst( $value );
                        }
                    } else {
                        $formatted_attributes[] = ucfirst( $value );
                    }
                }
            }
            if ( ! empty( $formatted_attributes ) ) {
                $variation_str = '(' . implode( ' | ', $formatted_attributes ) . ')';
            }
        } else {
            $short_desc = $product->get_short_description();
        }

        $short_desc = wp_trim_words( strip_tags( $short_desc ), 10, '...' );

        $cart_items[] = [
            'name'      => $main_name,
            'variation' => $variation_str,
            'desc'      => $short_desc,
            'price'     => strip_tags( wc_price( (float) $product->get_price() ) ),
            'qty'       => (int) $cart_item['quantity'],
            'image'     => $image ?: '',
        ];
    }

    wp_localize_script( 'alp-script', 'ALP_DATA', [
        'cartItems'   => $cart_items,
        'checkoutUrl' => wc_get_checkout_url(),
        'cartUrl'     => wc_get_cart_url(),
        'maxShows'    => (int)  get_option( 'alp_max_shows', 3  ),
        'cooldownMin' => (int)  get_option( 'alp_cooldown',  30 ),
        'iconImg'     => get_option( 'alp_icon_img', '' ),
    ] );
} );

/* -------------------------------------------------------
   3. Admin settings — Settings → Leads Popup
------------------------------------------------------- */
add_action( 'admin_menu', function () {
    $hook = add_submenu_page(
        'anexly-account',
        'Cart Notification Popup — Anexly Store',
        'Cart Notification Popup',
        'manage_options',
        'anexly-leads-popup',
        'alp_settings_page'
    );
    add_action( "admin_print_scripts-{$hook}", function() {
        wp_enqueue_media();
    } );
} );

if ( ! function_exists( 'alp_settings_page' ) ) {
    function alp_settings_page() {
        if ( isset( $_POST['alp_save'] ) && check_admin_referer( 'alp_settings' ) ) {
            update_option( 'alp_max_shows', absint( $_POST['alp_max_shows'] ) );
            update_option( 'alp_cooldown',  absint( $_POST['alp_cooldown'] ) );
            if ( isset( $_POST['alp_icon_img'] ) ) {
                update_option( 'alp_icon_img', esc_url_raw( $_POST['alp_icon_img'] ) );
            }
            echo '<div class="updated"><p>✅ Settings saved!</p></div>';
        }

        $d = [
            'max_shows' => get_option( 'alp_max_shows', 3  ),
            'cooldown'  => get_option( 'alp_cooldown',  30 ),
            'icon_img'  => get_option( 'alp_icon_img',  '' ),
        ];
        ?>
        <div class="wrap">
            <h1>Cart Notification Popup Settings</h1>
            <p style="background:#e8f4fd;padding:12px 16px;border-left:4px solid #2196f3;border-radius:4px;color:#0d47a1;">
                <strong>When it shows:</strong> Cart has items + user is not on cart / checkout / my account.<br>
                <strong>Trigger:</strong> Scroll — at a random position between 20% and 60% of the page (changes every page load).
            </p>
            <hr>
            <form method="post">
                <?php wp_nonce_field( 'alp_settings' ); ?>
                <table class="form-table">
                    <tr>
                        <th>Max Shows Per User</th>
                        <td>
                            <input type="number" name="alp_max_shows" value="<?php echo esc_attr( $d['max_shows'] ); ?>" min="1" max="20" style="width:80px">
                            <p class="description">Maximum times a browser sees the popup total. After this it never shows again.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Cooldown Between Shows</th>
                        <td>
                            <input type="number" name="alp_cooldown" value="<?php echo esc_attr( $d['cooldown'] ); ?>" min="1" max="44640" style="width:90px"> minutes
                            <p class="description">How long to wait before showing again. Default: 30 min. Set to 1 for testing.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Popup Icon</th>
                        <td>
                            <input type="text" id="alp_icon_img" name="alp_icon_img" value="<?php echo esc_attr( $d['icon_img'] ); ?>" style="width:300px">
                            <button type="button" class="button button-secondary" id="alp_upload_icon_btn">Select Image</button>
                            <p class="description">Upload an image (like a logo) to replace the default cart icon at the top of the popup.</p>
                            <script>
                            jQuery(document).ready(function($){
                                var mediaUploader;
                                $('#alp_upload_icon_btn').click(function(e) {
                                    e.preventDefault();
                                    if (mediaUploader) {
                                        mediaUploader.open();
                                        return;
                                    }
                                    mediaUploader = wp.media.frames.file_frame = wp.media({
                                        title: 'Choose Icon Image',
                                        button: { text: 'Use this image' },
                                        multiple: false
                                    });
                                    mediaUploader.on('select', function() {
                                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                                        $('#alp_icon_img').val(attachment.url);
                                    });
                                    mediaUploader.open();
                                });
                            });
                            </script>
                        </td>
                    </tr>
                </table>
                <p><input type="submit" name="alp_save" class="button-primary" value="Save Settings"></p>
            </form>
        </div>
        <?php
    }
}