<?php
/**
 * Enqueue plugin assets.
 *
 * @package TeamMembers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TM_Assets {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend' ) );
	}

	public static function frontend() {
		wp_register_style(
			'team-members',
			TM_PLUGIN_URL . 'assets/css/team-members.css',
			array(),
			TM_VERSION
		);

		wp_register_script(
			'team-members',
			TM_PLUGIN_URL . 'assets/js/team-members.js',
			array(),
			TM_VERSION,
			true
		);

		// Only load if shortcode or Elementor widget is present on this page.
		// For simplicity we enqueue globally; for production you'd detect usage.
		wp_enqueue_style( 'team-members' );
		wp_enqueue_script( 'team-members' );
	}
}

TM_Assets::init();
