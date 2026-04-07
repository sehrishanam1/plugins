<?php
/**
 * Plugin Name: SG Blocks
 * Description: Custom Gutenberg blocks. Drop any widget folder inside /widgets/ to activate it.
 * Version: 1.0.1
 * Author: Your Name
 * Text Domain: sg-blocks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SG_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SG_BLOCKS_URL',  plugin_dir_url( __FILE__ ) );

add_action( 'wp_enqueue_scripts', 'sg_blocks_enqueue_assets' );
add_action( 'enqueue_block_editor_assets', 'sg_blocks_enqueue_assets' );

function sg_blocks_enqueue_assets() {
    $css_file = SG_BLOCKS_PATH . 'assets/style.css';

    if ( file_exists( $css_file ) ) {
        wp_enqueue_style(
            'sg-blocks-style',
            SG_BLOCKS_URL . 'assets/style.css',
            [],
            filemtime( $css_file )
        );
    }
}

add_action( 'plugins_loaded', 'sg_blocks_load_widgets' );

function sg_blocks_load_widgets() {
    $widgets_dir = SG_BLOCKS_PATH . 'widgets';
    if ( ! is_dir( $widgets_dir ) ) {
        return;
    }
    $entries = scandir( $widgets_dir );
    if ( ! $entries ) {
        return;
    }
    foreach ( $entries as $entry ) {
        if ( $entry === '.' || $entry === '..' ) {
            continue;
        }
        $widget_file = $widgets_dir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'index.php';
        if ( file_exists( $widget_file ) ) {
            require_once $widget_file;
        }
    }
}

function mytheme_enqueue_google_fonts() {
    wp_enqueue_style(
        'google-fonts-fustat',
        'https://fonts.googleapis.com/css2?family=Fustat:wght@200;300;400;500;600;700;800&display=swap',
        [],
        null
    );
}
add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_google_fonts' );