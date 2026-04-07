<?php
/**
 * Plugin Name:  Anexly Store
 * Description:  Modular shortcode library. Each shortcode lives in its own folder inside /shortcodes/. Drop a new folder in to add a shortcode — no other changes needed. Prefix a folder name with _ to disable it.
 * Version:      1.0.0
 * Author:       Anexly
 * Text Domain:  anexly-shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ANEXLY_SC_VERSION', '1.0.0' );
define( 'ANEXLY_SC_PATH',    plugin_dir_path( __FILE__ ) );
define( 'ANEXLY_SC_URL',     plugin_dir_url( __FILE__ ) );

/**
 * Auto-loader
 * Scans /shortcodes/ for subfolders and loads each index.php.
 * Folders prefixed with _ are skipped (easy way to disable a shortcode).
 */
function anexly_sc_autoload() {
    $shortcodes_dir = ANEXLY_SC_PATH . 'shortcodes/';

    if ( ! is_dir( $shortcodes_dir ) ) return;

    foreach ( glob( $shortcodes_dir . '*', GLOB_ONLYDIR ) as $folder ) {
        $name = basename( $folder );

        // Skip folders prefixed with underscore
        if ( strpos( $name, '_' ) === 0 ) continue;

        $index = $folder . '/index.php';
        if ( file_exists( $index ) ) {
            require_once $index;
        }
    }
}
add_action( 'plugins_loaded', 'anexly_sc_autoload' );

/**
 * Helper: enqueue a shortcode's CSS/JS only when its shortcode is on the page.
 *
 * Call this inside your shortcode's index.php:
 *   anexly_sc_assets( 'my-shortcode' );
 *
 * It will look for:
 *   shortcodes/my-shortcode/style.css
 *   shortcodes/my-shortcode/script.js
 * and enqueue whichever ones exist.
 */
function anexly_sc_assets( $shortcode_name, $js_deps = [ 'jquery' ] ) {
    $folder  = ANEXLY_SC_PATH . 'shortcodes/' . $shortcode_name . '/';
    $url     = ANEXLY_SC_URL  . 'shortcodes/' . $shortcode_name . '/';
    $handle  = 'anexly-sc-' . $shortcode_name;

    if ( file_exists( $folder . 'style.css' ) ) {
        wp_enqueue_style( $handle, $url . 'style.css', [], ANEXLY_SC_VERSION );
    }
    if ( file_exists( $folder . 'script.js' ) ) {
        wp_enqueue_script( $handle, $url . 'script.js', $js_deps, ANEXLY_SC_VERSION, true );
    }
}

// ─── Load sub-files ────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', function() {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'product-metas.php';
} );