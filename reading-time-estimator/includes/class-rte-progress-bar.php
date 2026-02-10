<?php
/**
 * Progress Bar Output Class.
 *
 * Injects the scroll-progress-bar container into the page.
 * All animation logic lives in the accompanying JS file.
 *
 * @package ReadingTimeEstimator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RTE_Progress_Bar
 */
class RTE_Progress_Bar {

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private array $settings;

	/**
	 * Constructor.
	 *
	 * @param array $settings Plugin settings.
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
		$this->init();
	}

	/**
	 * Register hooks.
	 */
	private function init(): void {
		add_action( 'wp_body_open', array( $this, 'render' ), 5 );
		// Fallback for themes that don't call wp_body_open.
		add_action( 'wp_footer', array( $this, 'render_fallback' ), 1 );
	}

	/**
	 * Render progress bar HTML via wp_body_open (preferred).
	 */
	public function render(): void {
		if ( ! $this->should_display() ) {
			return;
		}
		$this->output_html();
		// Flag that we've already rendered.
		add_action( 'wp_footer', array( $this, 'mark_rendered' ) );
	}

	/**
	 * Fallback: render in footer if wp_body_open wasn't fired.
	 */
	public function render_fallback(): void {
		if ( did_action( 'rte_progress_rendered' ) ) {
			return;
		}
		if ( ! $this->should_display() ) {
			return;
		}
		$this->output_html();
	}

	/**
	 * Mark the bar as rendered (prevents double output).
	 */
	public function mark_rendered(): void {
		do_action( 'rte_progress_rendered' );
	}

	/**
	 * Output the progress bar HTML markup.
	 *
	 * Completely managed via CSS custom properties so JS only needs
	 * to update --rte-progress: <value>.
	 */
	private function output_html(): void {
		$position = sanitize_key( $this->settings['progress_position'] ?? 'top' );
		$disabled = get_post_meta( get_the_ID(), '_rte_disable_progress', true );
		if ( '1' === $disabled ) {
			return;
		}

		// Data attributes pass config to JS without extra localization calls.
		printf(
			'<div id="rte-progress-bar"
				role="progressbar"
				aria-valuemin="0"
				aria-valuemax="100"
				aria-valuenow="0"
				aria-label="%s"
				data-position="%s"
				style="--rte-progress:0%%;">
				<div id="rte-progress-bar__fill"></div>
				<span id="rte-progress-bar__tooltip" aria-hidden="true"></span>
			</div>',
			esc_attr__( 'Reading progress', 'reading-time-estimator' ),
			esc_attr( $position )
		);
	}

	/**
	 * Determine whether the progress bar should appear on the current page.
	 *
	 * @return bool
	 */
	private function should_display(): bool {
		if ( ! is_singular() ) {
			return false;
		}
		if ( is_admin() ) {
			return false;
		}

		$allowed_types = (array) ( $this->settings['progress_post_types'] ?? array( 'post' ) );
		return is_singular( $allowed_types );
	}
}
