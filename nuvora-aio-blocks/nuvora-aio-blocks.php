<?php
/**
 * Plugin Name:         Nuvora AIO Blocks – Page Builder Toolkit for the Block Editor
 * Plugin URI:          https://github.com/sehrishanam1/plugins
 * Description:         Advanced, fully customizable WordPress blocks — Counter, Pricing Table, Testimonial Carousel, Tabs, Icon Box, and Slider.
 * Version:             1.0.0
 * Requires at least:   6.0
 * Requires PHP:        8.0
 * Author:              Sehrish Anam
 * Author URI:          https://www.linkedin.com/in/sehrish-anam/
 * Text Domain:         nuvora-aio-blocks
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Nuvora_AIO_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NUGBA_VERSION', '1.0.0' );
define( 'NUGBA_PATH', plugin_dir_path( __FILE__ ) );
define( 'NUGBA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register a custom "Nuvora Blocks" category in the block inserter.
 */
function nugba_block_category( $categories ) {
	// Avoid duplicate if standalone plugin also adds it
	foreach ( $categories as $cat ) {
		if ( isset( $cat['slug'] ) && $cat['slug'] === 'nuvora-blocks' ) {
			return $categories;
		}
	}
	return array_merge(
		[
			[
				'slug'  => 'nuvora-blocks',
				'title' => __( 'Nuvora Blocks', 'nuvora-aio-blocks' ),
				'icon'  => null,
			],
		],
		$categories
	);
}
add_filter( 'block_categories_all', 'nugba_block_category', 10, 2 );

/**
 * Load and register all blocks.
 * Each block's register.php must NOT add its own add_action('init').
 */
function nugba_init() {
	$blocks = [
		'counter-block',
		'pricing-table',
		'testimonial-carousel',
		'advanced-tabs',
		'icon-box',
		'slider',
	];

	foreach ( $blocks as $block ) {
		$file = NUGBA_PATH . 'blocks/' . $block . '/register.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
add_action( 'init', 'nugba_init' );
