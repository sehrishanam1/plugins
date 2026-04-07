<?php
/**
 * Plugin Name: Anexly Account
 * Plugin URI:  https://anexly.us
 * Description: All-in-one WooCommerce plugin — Beautiful Login/Register/Forgot Password Popup with Google & Facebook OAuth + Custom My Account Dashboard (Orders, Account Settings). Use [wc_auth_popup] and [anexly_my_account] shortcodes.
 * Version:     1.0.2
 * Author:      Anexly
 * Text Domain: anexly-account
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Constants ───────────────────────────────────────────────────────────────
define( 'ANXA_VERSION', '1.0.5' );
define( 'ANXA_DIR',     plugin_dir_path( __FILE__ ) );
define( 'ANXA_URL',     plugin_dir_url( __FILE__ ) );

// ─── WooCommerce check notice ────────────────────────────────────────────────
add_action( 'admin_notices', function () {
    if ( ! class_exists( 'WooCommerce' ) ) {
        echo '<div class="notice notice-error"><p>'
            . '<strong>Anexly Account:</strong> WooCommerce is required. Please install and activate WooCommerce.'
            . '</p></div>';
    }
} );

// ─── Include modules ─────────────────────────────────────────────────────────
require_once ANXA_DIR . 'includes/class-cart.php';
require_once ANXA_DIR . 'includes/class-auth-popup.php';     // Auth popup + OAuth (modal.php loaded on demand)
require_once ANXA_DIR . 'includes/class-dashboard.php';
require_once ANXA_DIR . 'includes/class-orders.php';
require_once ANXA_DIR . 'includes/class-account-settings.php';
require_once ANXA_DIR . 'includes/class-my-account-shortcode.php';

// ─── Bootstrap the auth-popup singleton ──────────────────────────────────────
Anexly_Suite_Auth_Popup::instance();
