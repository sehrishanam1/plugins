<?php
/**
 * Shortcode Module: Price Comparison Calculator
 * ─────────────────────────────────────────────
 * Shortcode: [anexly_price_compare]
 *
 * Fully AUTO — reads the WooCommerce product price from the current page.
 * Regular price is auto-generated (50–80% markup, seeded by product ID so
 * it stays consistent across page loads). No manual meta fields needed.
 *
 * ── HOW TO USE ──────────────────────────────────────────────────
 * Option A — Drop inside your existing shortcodes plugin folder and
 *            require_once this file from your main plugin file.
 *
 * Option B — Standalone: rename index.php top comment to a WP plugin
 *            header and activate from Plugins screen.
 *
 * Optional shortcode attributes:
 *   id=""          — product ID (auto-detected on product pages)
 *   months="12"    — months in a year for annual calc (default 12)
 *   cta_text=""    — button label (default "Browse Deals")
 *   cta_url=""     — button URL  (default: product permalink)
 *   title=""       — heading text
 *   subtitle=""    — sub-heading text
 */

// Works both as a standalone plugin and as a required module
if ( ! defined( 'ABSPATH' ) ) {
    // If somehow called outside WordPress, bail
    exit;
}

// Use if-not-defined so the module can be required multiple times safely
if ( ! defined( 'ANEXLY_PC_VERSION' ) ) {
    define( 'ANEXLY_PC_VERSION', '2.0.0' );
}

// __DIR__ always gives the real absolute path regardless of how the file is loaded
if ( ! defined( 'ANEXLY_PC_PATH' ) ) {
    define( 'ANEXLY_PC_PATH', __DIR__ . '/' );
}

// Build URL from WP_CONTENT_URL + relative path from wp-content dir
if ( ! defined( 'ANEXLY_PC_URL' ) ) {
    // Works for both /plugins/... and /mu-plugins/... locations
    $anexly_pc_rel = str_replace( wp_normalize_path( WP_CONTENT_DIR ), '', wp_normalize_path( __DIR__ ) );
    define( 'ANEXLY_PC_URL', content_url( $anexly_pc_rel ) . '/' );
}

require_once ANEXLY_PC_PATH . 'includes/shortcode.php';
require_once ANEXLY_PC_PATH . 'includes/assets.php';
