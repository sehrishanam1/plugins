<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Inline the CSS directly into <head> — avoids any URL path issues
 * when the module is loaded from a non-standard plugin location.
 */
add_action( 'wp_head', 'anexly_pc_inline_styles' );

function anexly_pc_inline_styles() {
    $css_file = ANEXLY_PC_PATH . 'assets/style.css';
    if ( ! file_exists( $css_file ) ) return;

    $css = file_get_contents( $css_file );
    if ( ! $css ) return;

    echo '<style id="anexly-price-compare-css">' . $css . '</style>' . "\n";
}

add_action( 'wp_footer', 'anexly_pc_inline_scripts' );

function anexly_pc_inline_scripts() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.anexly-pc-cta[data-scroll-top="1"]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    });
    </script>
    <?php
}
