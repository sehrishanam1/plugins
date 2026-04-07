<?php
/**
 * Leads Popup — Anexly Store
 *
 * Merged from the standalone "Anexly Leads" plugin.
 * Drop this folder into /shortcodes/ — the autoloader picks it up automatically.
 *
 * Shortcodes registered:
 *   [anexly_newsletter]  — inline email signup form
 *   [anexly_popup]       — force popup on any page
 *
 * Admin page: WP Admin → Anexly Leads (top-level menu)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Constants scoped to this shortcode folder ──────────────────────────────
if ( ! defined( 'ALEADS_VERSION' ) ) define( 'ALEADS_VERSION', '1.0.0' );
if ( ! defined( 'ALEADS_PATH'    ) ) define( 'ALEADS_PATH',    ANEXLY_SC_PATH . 'shortcodes/leads-popup/' );
if ( ! defined( 'ALEADS_URL'     ) ) define( 'ALEADS_URL',     ANEXLY_SC_URL  . 'shortcodes/leads-popup/' );

// ── Load classes ───────────────────────────────────────────────────────────
require_once ALEADS_PATH . 'includes/class-db.php';
require_once ALEADS_PATH . 'includes/class-ajax.php';
require_once ALEADS_PATH . 'includes/class-shortcodes.php';
require_once ALEADS_PATH . 'includes/class-admin.php';

// ── Create DB table on first load (replaces register_activation_hook) ──────
// Since this is a shortcode folder (not a plugin), activation hooks don't fire.
// We use a DB-version option instead — table is created once and never again.
add_action( 'plugins_loaded', function () {
    if ( get_option( 'aleads_db_version' ) !== ALEADS_VERSION ) {
        Anexly_Leads_DB::create_table();
    }
}, 20 );
