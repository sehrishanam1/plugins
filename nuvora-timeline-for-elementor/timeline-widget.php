<?php
/**
 * Plugin Name:         Nuvora Timeline for Elementor
 * Plugin URI: 		    https://github.com/sehrishanam1/plugins
 * Description:         A beautiful animated timeline widget for Elementor With two styles
 * Version:             1.0.0
 * Requires at least:   5.9
 * Requires PHP:        8.0
 * Requires Plugins:    elementor
 * Author:              Sehrish Anam
 * Author URI:          https://www.linkedin.com/in/sehrish-anam/         
 * Text Domain:         nuvora-timeline-for-elementor
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package Nuvora_Timeline_for_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class NUVOTIFO_Plugin {

    const VERSION = '1.0.0';
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';
    const MINIMUM_PHP_VERSION = '8.0';

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {

        // Elementor is absolutely required — deactivate and show error if missing.
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
            add_action( 'admin_init', [ $this, 'deactivate_plugin' ] );
            return;
        }

        // Check for required Elementor version.
        if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return;
        }

        // Check for required PHP version.
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return;
        }

        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editor_styles' ] );
    }

    public function deactivate_plugin() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            unset( $_GET['activate'] );
        }
    }

    public function admin_notice_missing_elementor() {
        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated. This plugin has been deactivated.', 'nuvora-timeline-for-elementor' ),
            '<strong>' . esc_html__( 'Nuvora Timeline for Elementor', 'nuvora-timeline-for-elementor' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'nuvora-timeline-for-elementor' ) . '</strong>'
        );
        printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
    }

    public function admin_notice_minimum_elementor_version() {
        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'nuvora-timeline-for-elementor' ),
            '<strong>' . esc_html__( 'Nuvora Timeline for Elementor', 'nuvora-timeline-for-elementor' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'nuvora-timeline-for-elementor' ) . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );
        printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
    }

    public function admin_notice_minimum_php_version() {
        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'nuvora-timeline-for-elementor' ),
            '<strong>' . esc_html__( 'Nuvora Timeline for Elementor', 'nuvora-timeline-for-elementor' ) . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'nuvora-timeline-for-elementor' ) . '</strong>',
            self::MINIMUM_PHP_VERSION
        );
        printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
    }

    public function register_widgets( $widgets_manager ) {
        require_once __DIR__ . '/widgets/timeline-widget.php';
        $widgets_manager->register( new \NUVOTIFO_Timeline_Widget() );
    }

    public function widget_styles() {
        wp_register_style( 'nuvotifo-timeline-style', plugins_url( 'assets/css/timeline-style.css', __FILE__ ), [], self::VERSION );
        wp_enqueue_style( 'nuvotifo-timeline-style' );

        wp_register_style( 'nuvotifo-timeline-style2', plugins_url( 'assets/css/timeline-style2.css', __FILE__ ), [], self::VERSION );
        wp_enqueue_style( 'nuvotifo-timeline-style2' );

        wp_register_script( 'nuvotifo-timeline-script', plugins_url( 'assets/js/timeline-widget.js', __FILE__ ), [ 'jquery' ], self::VERSION, true );
        wp_enqueue_script( 'nuvotifo-timeline-script' );
    }

    public function editor_styles() {
        wp_enqueue_style( 'nuvotifo-timeline-style', plugins_url( 'assets/css/timeline-style.css', __FILE__ ), [], self::VERSION );
        wp_enqueue_style( 'nuvotifo-timeline-style2', plugins_url( 'assets/css/timeline-style2.css', __FILE__ ), [], self::VERSION );
    }
}

NUVOTIFO_Plugin::instance();
