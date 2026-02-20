<?php
/**
 * Plugin Name:       Nuvora Reading Time & Progress Bar
 * Plugin URI: 		  https://github.com/sehrishanam1/plugins
 * Description:       A minimalist, accessible reading time estimator with a smooth scroll progress bar. Features AI-assisted reading time adjustment, per-post overrides, and full ARIA support. No tracking. No upsells.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Sehrish Anam
 * Author URI:        https://github.com/sehrishanam1/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nuvora-reading-time-progress-bar
 * Domain Path:       /languages
 *
 * @package NuvoraReadingTime
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'NVRTP_VERSION', '1.0.0' );
define( 'NVRTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NVRTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NVRTP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
final class Nvrtp_Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Nvrtp_Plugin|null
	 */
	private static ?Nvrtp_Plugin $instance = null;

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private array $settings = array();

	/**
	 * Get plugin instance (singleton).
	 *
	 * @return Nvrtp_Plugin
	 */
	public static function get_instance(): Nvrtp_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — private to enforce singleton.
	 */
	private function __construct() {
		$this->load_settings();
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load plugin settings from the database with defaults.
	 */
	private function load_settings(): void {
		$defaults = array(
			// Reading speed.
			'wpm'                   => 238,
			'ai_adjustment'         => true,
			'cpm'                   => 1000, // characters per minute for CJK content.

			// Display — badge.
			'show_badge'            => true,
			'badge_position'        => 'before', // before|after|both.
			'badge_post_types'      => array( 'post' ),
			'badge_label'           => '{time} min read',
			'badge_label_under_one' => 'Less than 1 min read',
			'badge_icon'            => true,

			// Display — progress bar.
			'show_progress_bar'     => true,
			'progress_position'     => 'top', // top|bottom.
			'progress_post_types'   => array( 'post' ),
			'progress_color'        => '#6366f1',
			'progress_bg_color'     => 'rgba(99,102,241,0.15)',
			'progress_height'       => 3,
			'progress_show_tooltip' => true,

			// Accessibility.
			'reduce_motion_respect' => true,

			// Advanced.
			'exclude_code_blocks'   => true,
			'exclude_shortcodes'    => false,
		);

		$saved          = get_option( 'nvrtp_settings', array() );
		$this->settings = wp_parse_args( $saved, $defaults );
	}

	/**
	 * Load required class files.
	 */
	private function load_dependencies(): void {
		require_once NVRTP_PLUGIN_DIR . 'includes/class-nvrtp-calculator.php';
		require_once NVRTP_PLUGIN_DIR . 'includes/class-nvrtp-badge.php';
		require_once NVRTP_PLUGIN_DIR . 'includes/class-nvrtp-progress-bar.php';
		require_once NVRTP_PLUGIN_DIR . 'includes/class-nvrtp-meta-box.php';
		require_once NVRTP_PLUGIN_DIR . 'admin/class-nvrtp-admin.php';
	}

	/**
	 * Register all hooks.
	 */
	private function init_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );

		// Front-end output.
		if ( $this->settings['show_badge'] ) {
			new Nvrtp_Badge( $this->settings );
		}
		if ( $this->settings['show_progress_bar'] ) {
			new Nvrtp_Progress_Bar( $this->settings );
		}

		// Admin.
		if ( is_admin() ) {
			new Nvrtp_Admin( $this->settings );
			new Nvrtp_Meta_Box( $this->settings );
		}

		// Activation / deactivation.
		register_activation_hook( __FILE__, array( $this, 'on_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'on_deactivate' ) );

		// Plugin action links.
		add_filter(
			'plugin_action_links_' . NVRTP_PLUGIN_BASENAME,
			array( $this, 'add_action_links' )
		);
	}

	/**
	 * Enqueue front-end CSS and JS.
	 */
	public function enqueue_frontend_assets(): void {
		if ( ! $this->should_load_on_current_page() ) {
			return;
		}

		wp_enqueue_style(
			'nvrtp-styles',
			NVRTP_PLUGIN_URL . 'public/css/nvrtp-public.css',
			array(),
			NVRTP_VERSION
		);

		wp_enqueue_script(
			'nvrtp-progress',
			NVRTP_PLUGIN_URL . 'public/js/nvrtp-progress.js',
			array(),
			NVRTP_VERSION,
			true
		);

		// Pass settings to JS.
		wp_localize_script(
			'nvrtp-progress',
			'nvrtpConfig',
			array(
				'showProgress'        => (bool) $this->settings['show_progress_bar'],
				'progressPosition'    => sanitize_key( $this->settings['progress_position'] ),
				'progressColor'       => sanitize_hex_color( $this->settings['progress_color'] ) ?: '#6366f1',
				'progressBgColor'     => $this->settings['progress_bg_color'],
				'progressHeight'      => absint( $this->settings['progress_height'] ),
				'showTooltip'         => (bool) $this->settings['progress_show_tooltip'],
				'respectReduceMotion' => (bool) $this->settings['reduce_motion_respect'],
				'i18n'                => array(
					'progressLabel' => esc_html__( 'Reading progress', 'nuvora-reading-time-progress-bar' ),
					'complete'      => esc_html__( 'Complete', 'nuvora-reading-time-progress-bar' ),
				),
			)
		);
	}

	/**
	 * Check whether our assets should load on the current page.
	 */
	private function should_load_on_current_page(): bool {
		$badge_types    = (array) $this->settings['badge_post_types'];
		$progress_types = (array) $this->settings['progress_post_types'];
		$all_types      = array_unique( array_merge( $badge_types, $progress_types ) );

		foreach ( $all_types as $type ) {
			if ( is_singular( $type ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Activation: set default settings if not already set.
	 */
	public function on_activate(): void {
		if ( false === get_option( 'nvrtp_settings' ) ) {
			add_option( 'nvrtp_settings', array() );
		}
		// Flush rewrite rules just in case.
		flush_rewrite_rules();
	}

	/**
	 * Deactivation: flush rewrite rules.
	 */
	public function on_deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Add Settings link to plugin list.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=nuvora-reading-time-progress-bar' ),
			esc_html__( 'Settings', 'nuvora-reading-time-progress-bar' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Public getter for settings — used by includes.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_setting( string $key, $default = null ) {
		return $this->settings[ $key ] ?? $default;
	}

	public function register_elementor_widget( $widgets_manager ): void {
		require_once NVRTP_PLUGIN_DIR . 'elementor/class-nvrtp-elementor-widget.php';
		$widgets_manager->register( new Nvrtp_Elementor_Widget() );
	}
}

// Bootstrap the plugin.
Nvrtp_Plugin::get_instance();