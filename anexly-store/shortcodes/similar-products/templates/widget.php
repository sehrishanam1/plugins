<?php
/**
 * Similar Products — front-end template
 *
 * Shared by the [similar_products] shortcode AND the Elementor widget.
 *
 * Variables in scope:
 *   $widget_id  string        Unique DOM id for this instance
 *   $products   WC_Product[]  Products to display
 *   $settings   array         Normalised settings (from anx_sp_normalise_settings())
 *   $per_view   int           Slides per view on desktop
 */
defined( 'ABSPATH' ) || exit;
?>

<div id="<?php echo esc_attr( $widget_id ); ?>" class="anx-similar-products-widget">

    <!-- ── HEADER ──────────────────────────────────────────────── -->
    <div class="anx-sp-header">
        <h2><?php echo esc_html( $settings['heading'] ); ?></h2>
    </div>

    <!-- ── SLIDER ──────────────────────────────────────────────── -->
    <div class="anx-sp-slider swiper">
        <div class="swiper-wrapper">
            <?php foreach ( $products as $product ) :
                if ( ! $product ) continue;

                $image_id   = $product->get_image_id();
                $image_url  = $image_id
                    ? wp_get_attachment_image_url( $image_id, 'medium' )
                    : wc_placeholder_img_src();
                $title      = $product->get_name();
                $link       = get_permalink( $product->get_id() );
                $badge_text = ! empty( $settings['show_sale_badge'] )
                    ? anx_sp_get_sale_badge_text( $product )
                    : '';
                $price_html = anx_sp_get_price_html( $product, $settings['price_suffix'] );
            ?>
                <div class="swiper-slide">
                    <div class="anx-sp-card">

                        <div class="anx-sp-card-top">
                            <?php if ( ! empty( $badge_text ) ) : ?>
                                <span class="anx-sp-badge"><?php echo esc_html( $badge_text ); ?></span>
                            <?php endif; ?>

                            <div class="anx-sp-logo-wrap">
                                <img class="anx-sp-logo"
                                     src="<?php echo esc_url( $image_url ); ?>"
                                     alt="<?php echo esc_attr( $title ); ?>">
                            </div>

                            <h3 class="anx-sp-title"><?php echo esc_html( $title ); ?></h3>

                            <div class="anx-sp-price-wrap woo-price">
                                <?php echo wp_kses_post( $price_html ); ?>
                            </div>
                        </div>

                        <div class="anx-sp-card-bottom">
                            <a class="anx-sp-btn" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $settings['button_text'] ); ?><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <path d="M0 6.53983V5.10929H8.85576L4.80255 1.02202L5.82436 0.000197411L11.6487 5.82456L5.82436 11.6489L4.76849 10.6271L8.85576 6.53983H0Z" fill="white"/>
                                </svg>
                            </a>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="anx-sp-nav anx-sp-prev" aria-label="Previous">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M0 7.84761V6.13095H10.6269L5.76305 1.22622L6.98924 4.19617e-05L13.9785 6.98928L6.98924 13.9785L5.72218 12.7523L10.6269 7.84761H0Z" fill="white"/>
            </svg>
        </div>
        <div class="anx-sp-nav anx-sp-next" aria-label="Next">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M0 7.84761V6.13095H10.6269L5.76305 1.22622L6.98924 4.19617e-05L13.9785 6.98928L6.98924 13.9785L5.72218 12.7523L10.6269 7.84761H0Z" fill="white"/>
            </svg>
        </div>

        <div class="anx-sp-pagination"></div>
    </div>

</div>

<script>
(function () {
    var WIDGET_ID = <?php echo wp_json_encode( $widget_id ); ?>;
    var PER_VIEW  = <?php echo (int) $per_view; ?>;
    var LOOP      = <?php echo count( $products ) > $per_view ? 'true' : 'false'; ?>;

    function initSlider() {
        var wrap = document.getElementById( WIDGET_ID );
        if ( ! wrap ) return;

        var slider = wrap.querySelector( '.anx-sp-slider' );
        if ( ! slider ) return;

        // On mobile, destroy any existing swiper and let CSS grid take over.
        if ( window.innerWidth < 768 ) {
            if ( slider.swiper ) slider.swiper.destroy( true, true );
            return;
        }

        if ( slider.swiper ) slider.swiper.destroy( true, true );

        new Swiper( slider, {
            slidesPerView  : 1.15,
            spaceBetween   : 22,
            speed          : 700,
            loop           : LOOP,
            centeredSlides : false,
            navigation: {
                nextEl : wrap.querySelector( '.anx-sp-next' ),
                prevEl : wrap.querySelector( '.anx-sp-prev' ),
            },
            pagination: {
                el        : wrap.querySelector( '.anx-sp-pagination' ),
                clickable : true,
            },
            breakpoints: {
                768  : { slidesPerView: 2,        spaceBetween: 24 },
                1024 : { slidesPerView: PER_VIEW,  spaceBetween: 24 },
            },
        } );
    }

    // Re-run on resize / orientation change so switching between
    // mobile and desktop works correctly.
    window.addEventListener( 'resize', initSlider );

    // ── Elementor editor / frontend ──────────────────────────────
    // Elementor fires a JS hook once the widget scope is ready; hook into it
    // so the slider re-initialises when the editor re-renders the widget.
    if ( typeof elementorFrontend !== 'undefined' ) {
        jQuery( window ).on( 'elementor/frontend/init', function () {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/anexly_similar_products.default',
                function ( $scope ) {
                    initSlider();
                }
            );
        } );
        return; // Elementor will call initSlider via the hook above.
    }

    // ── Shortcode (plain page, no Elementor) ─────────────────────
    if ( typeof Swiper !== 'undefined' ) {
        // Swiper already loaded (footer script ran before DOMContentLoaded).
        initSlider();
    } else if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initSlider );
    } else {
        window.addEventListener( 'load', initSlider );
    }
})();
</script>