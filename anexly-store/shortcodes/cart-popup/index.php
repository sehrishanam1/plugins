<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Guard against redefinition (safety if standalone plugin is also active)
if ( ! defined( 'ACP_VERSION' ) )     define( 'ACP_VERSION',     '1.0.0' );
if ( ! defined( 'ACP_PLUGIN_DIR' ) )  define( 'ACP_PLUGIN_DIR',  __DIR__ . '/' );
if ( ! defined( 'ACP_PLUGIN_URL' ) )  define( 'ACP_PLUGIN_URL',  ANEXLY_SC_URL . 'shortcodes/cart-popup/' );
if ( ! defined( 'ACP_PLUGIN_FILE' ) ) define( 'ACP_PLUGIN_FILE', __FILE__ );

/**
 * Boot the cart popup feature.
 *
 * NOTE: We use 'init' instead of 'plugins_loaded' because this file is
 * already required FROM inside the 'plugins_loaded' callback in the main
 * plugin's autoloader — meaning 'plugins_loaded' has already fired by the
 * time this code runs, so any add_action( 'plugins_loaded', ... ) here
 * would never execute.
 */
add_action( 'init', 'acp_check_woocommerce' );

function acp_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Anexly Cart Popup:</strong> WooCommerce install aur activate karein.</p></div>';
		} );
		return;
	}

	require_once ACP_PLUGIN_DIR . 'includes/class-acp-ajax.php';
	require_once ACP_PLUGIN_DIR . 'includes/class-acp-assets.php';
	require_once ACP_PLUGIN_DIR . 'includes/class-acp-markup.php';

	ACP_Ajax::init();
	ACP_Assets::init();
	ACP_Markup::init();
}