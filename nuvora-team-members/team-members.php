<?php
/**
 * Plugin Name:       Nuvora Team Members
 * Plugin URI:        https://github.com/sehrishanam1
 * Description:       Manage and display team members dynamically with a Custom Post Type, custom fields, Elementor widget, and shortcode support.
 * Version:           1.0.0
 * Author:            Sehrish Anam
 * Author URI:        https://github.com/sehrishanam1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nuvora-team-members
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Constants ────────────────────────────────────────────────────────────────
define( 'TM_VERSION',     '1.0.0' );
define( 'TM_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'TM_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'TM_PLUGIN_FILE', __FILE__ );

// ── Bootstrap (non-Elementor — always loads) ──────────────────────────────────
require_once TM_PLUGIN_DIR . 'includes/class-cpt.php';
require_once TM_PLUGIN_DIR . 'includes/class-meta-boxes.php';
require_once TM_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once TM_PLUGIN_DIR . 'includes/class-assets.php';

// ── Elementor Widget ──────────────────────────────────────────────────────────
// elementor/widgets/register fires after Elementor has FULLY booted, meaning
// Widget_Base and all Controls classes are guaranteed to exist at this point.
// This is the ONLY correct hook to register custom Elementor widgets.
add_action( 'elementor/widgets/register', 'tm_register_elementor_widget' );

function tm_register_elementor_widget( $widgets_manager ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return; // Elementor not available, skip silently.
	}
	require_once TM_PLUGIN_DIR . 'includes/class-elementor-widget.php';
	$widgets_manager->register( new TM_Elementor_Widget() );
}

// ── Activation / Deactivation ────────────────────────────────────────────────
register_activation_hook( TM_PLUGIN_FILE, 'tm_activate' );
register_deactivation_hook( TM_PLUGIN_FILE, 'tm_deactivate' );

function tm_activate() {
	TM_CPT::register();
	flush_rewrite_rules();
}

function tm_deactivate() {
	flush_rewrite_rules();
}
