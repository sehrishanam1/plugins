<?php
/**
 * Badge Output Class.
 *
 * Injects the reading-time badge into post/page content
 * via the_content filter, fully accessible with ARIA attributes.
 *
 * @package ReadingTimeEstimator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RTE_Badge
 */
class RTE_Badge {

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private array $settings;

	/**
	 * Calculator instance.
	 *
	 * @var RTE_Calculator
	 */
	private RTE_Calculator $calculator;

	/**
	 * Constructor.
	 *
	 * @param array $settings Plugin settings.
	 */
	public function __construct( array $settings ) {
		$this->settings   = $settings;
		$this->calculator = new RTE_Calculator( $settings );
		$this->init();
	}

	/**
	 * Hook into the_content.
	 */
	private function init(): void {
		add_filter( 'the_content', array( $this, 'inject_badge' ), 12 );
	}

	/**
	 * Prepend / append the badge to post content.
	 *
	 * @param string $content Post content HTML.
	 * @return string Modified content.
	 */
	public function inject_badge( string $content ): string {
		if ( ! $this->should_display() ) {
			return $content;
		}

		$result = $this->calculator->calculate( get_the_ID() );
		if ( empty( $result['minutes'] ) ) {
			return $content;
		}

		$badge    = $this->render( $result );
		$position = $this->settings['badge_position'] ?? 'before';

		switch ( $position ) {
			case 'after':
				return $content . $badge;
			case 'both':
				return $badge . $content . $badge;
			default:
				return $badge . $content;
		}
	}

	/**
	 * Render the badge HTML.
	 *
	 * @param array $result Calculator result.
	 * @return string
	 */
	private function render( array $result ): string {
		$label     = esc_html( $result['label'] );
		$minutes   = absint( $result['minutes'] );
		$words     = absint( $result['words'] );
		$adjusted  = ! empty( $result['adjusted'] );
		$show_icon = ! empty( $this->settings['badge_icon'] );

		$ai_attr = '';
		$ai_note = '';
		if ( $adjusted ) {
			$ai_attr = ' data-rte-ai-adjusted="true"';
			$ai_note = sprintf(
				'<span class="rte-badge__ai-note" aria-hidden="true">%s</span>',
				esc_html__( 'AI-adjusted', 'reading-time-estimator' )
			);
		}

		$word_note = ( $words > 0 )
			? sprintf(
				/* translators: %s: word count */
				_n( '%s word', '%s words', $words, 'reading-time-estimator' ),
				number_format_i18n( $words )
			)
			: '';

		$aria_label = sprintf(
			/* translators: %1$d: minutes, %2$s: word count note */
			__( 'Estimated reading time: %1$d minute(s). %2$s.', 'reading-time-estimator' ),
			$minutes,
			$word_note
		);

		$icon_html = '';
		if ( $show_icon ) {
			// Inline SVG clock icon — no external dependency.
			$icon_html = '<span class="rte-badge__icon" aria-hidden="true">'
				. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">'
				. '<circle cx="12" cy="12" r="10"/>'
				. '<polyline points="12 6 12 12 16 14"/>'
				. '</svg></span>';
		}

		$badge = sprintf(
			'<div class="rte-badge" role="note" aria-label="%s"%s data-rte-minutes="%d">',
			esc_attr( $aria_label ),
			$ai_attr,
			$minutes
		);
		$badge .= $icon_html;
		$badge .= sprintf( '<span class="rte-badge__label">%s</span>', $label );
		$badge .= $ai_note;
		$badge .= '</div>';

		/**
		 * Filter the rendered badge HTML.
		 *
		 * @param string $badge  HTML output.
		 * @param array  $result Calculator result array.
		 */
		return apply_filters( 'rte_badge_html', $badge, $result );
	}

	/**
	 * Check if badge should display on the current page.
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

		$allowed_types = (array) ( $this->settings['badge_post_types'] ?? array( 'post' ) );
		if ( ! is_singular( $allowed_types ) ) {
			return false;
		}

		// Allow per-post opt-out.
		$disabled = get_post_meta( get_the_ID(), '_rte_disable_badge', true );
		if ( '1' === $disabled ) {
			return false;
		}

		/**
		 * Filter to conditionally disable the badge.
		 *
		 * @param bool $show Whether to show the badge.
		 */
		return (bool) apply_filters( 'rte_show_badge', true );
	}

	/**
	 * Public helper — get reading time data for a given post.
	 * Useful for theme developers via template tags.
	 *
	 * @param int|\WP_Post $post Post ID or object.
	 * @return array
	 */
	public function get_data( $post ): array {
		return $this->calculator->calculate( $post );
	}
}

/**
 * Template tag: get reading time array for a post.
 *
 * @param int|\WP_Post|null $post Post ID, object, or null for current post.
 * @return array
 */
function rte_get_reading_time( $post = null ): array {
	$post     = get_post( $post );
	$settings = get_option( 'rte_settings', array() );
	$calc     = new RTE_Calculator( $settings );
	return $calc->calculate( $post );
}

/**
 * Template tag: echo the reading time label.
 *
 * @param int|\WP_Post|null $post Post ID, object, or null for current post.
 */
function rte_the_reading_time( $post = null ): void {
	$data = rte_get_reading_time( $post );
	echo esc_html( $data['label'] ?? '' );
}
