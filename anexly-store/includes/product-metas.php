<?php
/**
 * Anexly Product Metas
 * ─────────────────────────────────────────────
 * Meta fields:
 * - _anexly_badge  — badge text (side meta box)
 * - _anexly_desc   — product description (normal meta box)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ══════════════════════════════════════════════
   1. BADGE — Meta Box
   ══════════════════════════════════════════════ */

add_action( 'add_meta_boxes', 'anexly_add_badges_meta_box' );
function anexly_add_badges_meta_box() {
    add_meta_box(
        'anexly_product_badge',
        'Anexly Product Badge',
        'anexly_render_badges_meta_box',
        'product',
        'side',
        'default'
    );
}

function anexly_render_badges_meta_box( $post ) {
    wp_nonce_field( 'anexly_badges_save', 'anexly_badges_nonce' );
    $badge = get_post_meta( $post->ID, '_anexly_badge', true );
    ?>
    <style>
        .anexly-badge-field { margin-bottom: 10px; }
        .anexly-badge-field label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 5px; color: #23282d; }
        .anexly-badge-field input[type="text"] { width: 100%; padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; box-sizing: border-box; }
        .anexly-badge-field input:focus { border-color: #F04E3E; outline: none; box-shadow: 0 0 0 2px rgba(240,78,62,.15); }
        .anexly-badge-preview { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #FDE8E6; color: #F04E3E; margin-left: 4px; }
        .anexly-badge-hint { font-size: 11px; color: #999; margin-top: 4px; }
    </style>
    <div class="anexly-badge-field">
        <label for="anexly_badge">
            🏷️ Badge
            <span class="anexly-badge-preview">Premium / Limited</span>
        </label>
        <input type="text" id="anexly_badge" name="anexly_badge"
               value="<?php echo esc_attr( $badge ); ?>" placeholder="e.g. Premium" />
        <p class="anexly-badge-hint">Whatever you write will be displayed. Leave it empty to hide it.</p>
    </div>
    <?php
}

/* ══════════════════════════════════════════════
   2. BADGE — Save
   ══════════════════════════════════════════════ */

add_action( 'save_post_product', 'anexly_save_badges_meta' );
function anexly_save_badges_meta( $post_id ) {
    if ( ! isset( $_POST['anexly_badges_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['anexly_badges_nonce'], 'anexly_badges_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['anexly_badge'] ) ) {
        $badge = sanitize_text_field( $_POST['anexly_badge'] );
        if ( ! empty( $badge ) ) {
            update_post_meta( $post_id, '_anexly_badge', $badge );
        } else {
            delete_post_meta( $post_id, '_anexly_badge' );
        }
    }
}

/* ══════════════════════════════════════════════
   3. BADGE — Helper + Frontend display
   ══════════════════════════════════════════════ */

function anexly_get_product_badges_html( $product_id ) {
    $badge = get_post_meta( $product_id, '_anexly_badge', true );
    if ( empty( $badge ) ) return '';
    return '<span class="anexly-inline-badge badge-type-1">' . esc_html( $badge ) . '</span>';
}

add_action( 'woocommerce_single_product_summary', 'anexly_show_badge_on_single_page', 6 );
function anexly_show_badge_on_single_page() {
    global $product;
    if ( ! $product ) return;
    $badge = get_post_meta( $product->get_id(), '_anexly_badge', true );
    if ( empty( $badge ) ) return;
    echo '<div class="anexly-single-badge-wrap">';
    echo '<span class="anexly-inline-badge badge-type-1">' . esc_html( $badge ) . '</span>';
    echo '</div>';
}

/* ══════════════════════════════════════════════
   4. DESCRIPTION — Meta Box
   ══════════════════════════════════════════════ 

add_action( 'add_meta_boxes', 'anexly_add_desc_meta_box' );
function anexly_add_desc_meta_box() {
    add_meta_box(
        'anexly_product_desc',
        'Anexly Product Description',
        'anexly_render_desc_meta_box',
        'product',
        'normal',
        'default'
    );
}

function anexly_render_desc_meta_box( $post ) {
    wp_nonce_field( 'anexly_desc_save', 'anexly_desc_nonce' );
    $desc = get_post_meta( $post->ID, '_anexly_desc', true );
    ?>
    <style>
        .anexly-desc-field { margin-bottom: 10px; }
        .anexly-desc-field label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 5px; color: #23282d; }
        .anexly-desc-field textarea { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; box-sizing: border-box; min-height: 100px; resize: vertical; }
        .anexly-desc-field textarea:focus { border-color: #F04E3E; outline: none; box-shadow: 0 0 0 2px rgba(240,78,62,.15); }
        .anexly-desc-hint { font-size: 11px; color: #999; margin-top: 4px; }
    </style>
    <div class="anexly-desc-field">
        <label for="anexly_desc">📝 Product Description</label>
        <textarea id="anexly_desc" name="anexly_desc"
                  placeholder="Enter a short product description..."
        ><?php echo esc_textarea( $desc ); ?></textarea>
        <p class="anexly-desc-hint">This will appear under the product description on the frontend.</p>
    </div>
    <?php
}

/* ══════════════════════════════════════════════
   5. DESCRIPTION — Save
   ══════════════════════════════════════════════ 

add_action( 'save_post_product', 'anexly_save_desc_meta' );
function anexly_save_desc_meta( $post_id ) {
    if ( ! isset( $_POST['anexly_desc_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['anexly_desc_nonce'], 'anexly_desc_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['anexly_desc'] ) ) {
        $desc = sanitize_textarea_field( $_POST['anexly_desc'] );
        if ( ! empty( $desc ) ) {
            update_post_meta( $post_id, '_anexly_desc', $desc );
        } else {
            delete_post_meta( $post_id, '_anexly_desc' );
        }
    }
}

/* ══════════════════════════════════════════════
   6. DESCRIPTION — Frontend display
   ══════════════════════════════════════════════ 

add_action( 'woocommerce_single_product_summary', 'anexly_show_desc_on_single_page', 21 );
function anexly_show_desc_on_single_page() {
    global $product;
    if ( ! $product ) return;
    $desc = get_post_meta( $product->get_id(), '_anexly_desc', true );
    if ( empty( $desc ) ) return;
    echo '<div class="anexly-product-desc-wrap">';
    echo '<p class="anexly-product-desc">' . esc_html( $desc ) . '</p>';
    echo '</div>';
}
*/