<?php
/**
 * Shortcode Module: Product Rating
 * ─────────────────────────────────────────────
 * Shortcode: [anexly_rating]
 *            [anexly_rating id="123"]
 *
 * Reads from meta keys:
 *   _anexly_rating       — star rating (0-5, set in Anexly Shop Settings)
 *   _anexly_review_count — review count number
 *
 * Place this folder inside:
 *   anexly-store/shortcodes/product-rating/
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'anexly_rating', 'anexly_rating_render' );
function anexly_rating_render( $atts ) {
    $atts = shortcode_atts( [
        'id'        => 0,
        'size'      => '18',   // star size in px
        'show_count' => 'yes', // yes or no
    ], $atts, 'anexly_rating' );

    $id     = $atts['id'] ? intval( $atts['id'] ) : get_the_ID();
    $rating = floatval( get_post_meta( $id, '_anexly_rating', true ) );
    $count  = intval( get_post_meta( $id, '_anexly_review_count', true ) );

    $full  = floor( $rating );
    $half  = ( $rating - $full ) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $size  = intval( $atts['size'] );

    $stars = '';
    for ( $i = 0; $i < $full;  $i++ ) {
        $stars .= '<span class="anx-star anx-star-full">★</span>';
    }
    if ( $half ) {
        $stars .= '<span class="anx-star anx-star-half">★</span>';
    }
    for ( $i = 0; $i < $empty; $i++ ) {
        $stars .= '<span class="anx-star anx-star-empty">★</span>';
    }

    ob_start(); ?>
    <div class="anx-rating-wrap">
        <div class="anx-stars" style="font-size:<?php echo $size; ?>px">
            <?php echo $stars; ?>
        </div>
        <span class="anx-rating-num"><?php echo number_format( $rating, 1 ); ?></span>
        <?php if ( $atts['show_count'] === 'yes' ) : ?>
        <span class="anx-review-count">
            (<?php echo number_format( $count ); ?> reviews)
        </span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// ── Enqueue CSS ───────────────────────────────
add_action( 'wp_enqueue_scripts', 'anexly_rating_enqueue' );
function anexly_rating_enqueue() {
    wp_enqueue_style(
        'anexly-rating',
        plugins_url( 'style.css', __FILE__ ),
        [],
        '1.0.0'
    );
}