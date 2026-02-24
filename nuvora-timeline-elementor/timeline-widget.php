<?php
/**
 * Plugin Name: Nuvora Timeline Elementor
 * Description: A beautiful animated timeline widget for Elementor With two styles
 * Version: 1.2.1
 * Author: Your Name
 * Text Domain: nuvora-timeline-elementor
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Main Plugin Class
 */
final class Nuvora_Timeline_Widget {

    /**
     * Plugin Version
     */
    const VERSION = '1.2.1';

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    /**
     * Initialize the plugin
     */
    public function init() {

        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        // Register Widget - Use the correct action hook
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Also include on elementor init for compatibility
        add_action('elementor/init', [$this, 'elementor_init']);

        // Register Widget Styles
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'widget_styles']);
        
        // Register Editor Styles
        add_action('elementor/editor/after_enqueue_styles', [$this, 'editor_styles']);
    }
    
    /**
     * Elementor Init
     */
    public function elementor_init() {
        // This ensures Elementor classes are loaded
    }

    /**
     * Admin notice for missing Elementor
     */
    public function admin_notice_missing_main_plugin() {
        if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            unset( $_GET['activate'] );
        }
        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'nuvora-timeline-elementor'),
            '<strong>' . esc_html__('Nuvora Timeline Elementor', 'nuvora-timeline-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'nuvora-timeline-elementor') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * Admin notice for minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            unset( $_GET['activate'] );
        }
        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'nuvora-timeline-elementor'),
            '<strong>' . esc_html__('Nuvora Timeline Elementor', 'nuvora-timeline-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'nuvora-timeline-elementor') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * Admin notice for minimum PHP version
     */
    public function admin_notice_minimum_php_version() {
        if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            unset( $_GET['activate'] );
        }
        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'nuvora-timeline-elementor'),
            '<strong>' . esc_html__('Nuvora Timeline Elementor', 'nuvora-timeline-elementor') . '</strong>',
            '<strong>' . esc_html__('PHP', 'nuvora-timeline-elementor') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * Register Widgets
     */
    public function register_widgets($widgets_manager) {
        // Include widget file only when needed
        require_once(__DIR__ . '/widgets/timeline-widget.php');
        
        // Register widget
        $widgets_manager->register(new \Timeline_Elementor_Widget());
    }

    /**
     * Register Widget Styles (Frontend)
     */
    public function widget_styles() {
        wp_register_style('timeline-widget-style', plugins_url('assets/css/timeline-style.css', __FILE__), [], self::VERSION);
        wp_enqueue_style('timeline-widget-style');
        
        wp_register_style('timeline-widget-style2', plugins_url('assets/css/timeline-style2.css', __FILE__), [], self::VERSION);
        wp_enqueue_style('timeline-widget-style2');
        
        // Enqueue JavaScript for SVG fix
        wp_register_script('timeline-widget-script', plugins_url('assets/js/timeline-widget.js', __FILE__), ['jquery'], self::VERSION, true);
        wp_enqueue_script('timeline-widget-script');
    }
    
    /**
     * Register Editor Styles (Elementor Editor)
     */
    public function editor_styles() {
        wp_enqueue_style('timeline-widget-style', plugins_url('assets/css/timeline-style.css', __FILE__), [], self::VERSION);
        wp_enqueue_style('timeline-widget-style2', plugins_url('assets/css/timeline-style2.css', __FILE__), [], self::VERSION);
    }
}

Nuvora_Timeline_Widget::instance();