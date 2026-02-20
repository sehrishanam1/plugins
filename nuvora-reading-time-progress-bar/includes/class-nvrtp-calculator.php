<?php
/**
 * Reading Time Calculator.
 *
 * Handles all word-count and time-estimation logic,
 * including the AI-assisted adjustment for structured content.
 *
 * @package NuvoraReadingTime
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Nvrtp_Calculator
 */
class Nvrtp_Calculator {

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
	}

	/**
	 * Calculate reading time for a given post.
	 *
	 * Returns an array with 'minutes', 'words', and 'adjusted' keys.
	 *
	 * @param int|\WP_Post $post Post ID or WP_Post object.
	 * @return array{minutes: int, words: int, adjusted: bool, label: string}
	 */
	public function calculate( $post ): array {
		$post = get_post( $post );
		if ( ! $post ) {
			return $this->empty_result();
		}

		// Check for per-post override first.
		$override = get_post_meta( $post->ID, '_nvrtp_reading_time_override', true );
		if ( '' !== $override && is_numeric( $override ) ) {
			$minutes = max( 1, (int) $override );
			return array(
				'minutes'  => $minutes,
				'words'    => 0,
				'adjusted' => false,
				'override' => true,
				'label'    => $this->format_label( $minutes ),
			);
		}

		$content    = $this->prepare_content( $post );
		$word_count = $this->count_words( $content );
		$wpm        = $this->get_effective_wpm();

		if ( 0 === $word_count ) {
			return $this->empty_result();
		}

		// Base time in minutes (floating point for precision).
		$minutes_float = $word_count / $wpm;

		// AI-assisted adjustment: faster scanning for structured content.
		$adjusted = false;
		if ( ! empty( $this->settings['ai_adjustment'] ) ) {
			$adjustment_factor = $this->calculate_ai_adjustment( $post->post_content );
			if ( $adjustment_factor < 1.0 ) {
				$minutes_float *= $adjustment_factor;
				$adjusted       = true;
			}
		}

		// Round up to nearest minute; never show 0 minutes.
		$minutes = max( 1, (int) ceil( $minutes_float ) );

		return array(
			'minutes'  => $minutes,
			'words'    => $word_count,
			'adjusted' => $adjusted,
			'override' => false,
			'label'    => $this->format_label( $minutes ),
		);
	}

	/**
	 * Prepare post content for word counting:
	 * strip shortcodes/HTML and optionally remove code blocks.
	 *
	 * @param \WP_Post $post Post object.
	 * @return string
	 */
	private function prepare_content( \WP_Post $post ): string {
		$content = $post->post_content;

		// Optionally strip shortcodes before processing.
		if ( ! empty( $this->settings['exclude_shortcodes'] ) ) {
			$content = strip_shortcodes( $content );
		} else {
			$content = do_shortcode( $content );
		}

		// Apply wpautop so block content gets proper paragraph structure
		// without recursively firing the_content (which would cause an infinite loop).
		$content = wpautop( $content );

		// Optionally remove code blocks (pre, code) — these skew estimates.
		if ( ! empty( $this->settings['exclude_code_blocks'] ) ) {
			$content = preg_replace( '#<pre[^>]*>.*?</pre>#is', '', $content );
			$content = preg_replace( '#<code[^>]*>.*?</code>#is', '', $content );
		}

		// Strip all HTML tags, decode entities.
		$content = wp_strip_all_tags( $content );
		$content = html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		return $content;
	}

	/**
	 * Count words, with CJK (Chinese/Japanese/Korean) character support.
	 *
	 * CJK characters are treated as individual "words" at a different rate.
	 *
	 * @param string $content Prepared plain-text content.
	 * @return int
	 */
	private function count_words( string $content ): int {
		if ( '' === trim( $content ) ) {
			return 0;
		}

		// Detect CJK characters.
		$cjk_count = 0;
		if ( preg_match_all( '/[\x{4e00}-\x{9fff}\x{3040}-\x{30ff}\x{ac00}-\x{d7af}]/u', $content, $matches ) ) {
			$cjk_count = count( $matches[0] );
		}

		// Remove CJK characters before standard word count.
		$latin_content = preg_replace( '/[\x{4e00}-\x{9fff}\x{3040}-\x{30ff}\x{ac00}-\x{d7af}]/u', '', $content );
		$latin_count   = str_word_count( trim( $latin_content ) );

		if ( $cjk_count > 0 ) {
			$cjk_wpm = max( 1, (int) ( $this->settings['cpm'] ?? 1000 ) );
			$wpm     = max( 1, (int) $this->settings['wpm'] );
			// Combine: normalise CJK character count into equivalent "Latin words".
			$equivalent_latin = ( $cjk_count / $cjk_wpm ) * $wpm;
			return (int) round( $latin_count + $equivalent_latin );
		}

		return $latin_count;
	}

	/**
	 * Compute an AI-assisted adjustment factor (0.75–1.0) based on
	 * how structured the content is (headings, lists, short paragraphs).
	 *
	 * Readers scan structured content faster than dense prose.
	 *
	 * @param string $raw_html Raw post HTML (before filtering).
	 * @return float Multiplier applied to estimated minutes.
	 */
	private function calculate_ai_adjustment( string $raw_html ): float {
		if ( empty( $raw_html ) ) {
			return 1.0;
		}

		$score = 0.0;

		// Count structural elements that indicate scannability.
		$headings    = preg_match_all( '/<h[2-6][^>]*>/i', $raw_html );
		$list_items  = preg_match_all( '/<li[^>]*>/i', $raw_html );
		$blockquotes = preg_match_all( '/<blockquote[^>]*>/i', $raw_html );
		$paragraphs  = preg_match_all( '/<p[^>]*>/i', $raw_html );

		// Heading density: ≥1 heading per 300 words scores points.
		$word_estimate = max( 1, str_word_count( wp_strip_all_tags( $raw_html ) ) );
		$heading_ratio = $headings / max( 1, $word_estimate / 300 );
		if ( $heading_ratio >= 1 ) {
			$score += 0.08;
		}

		// List density: ≥20% of "blocks" are list items.
		$total_blocks = max( 1, $paragraphs + $list_items );
		if ( ( $list_items / $total_blocks ) >= 0.2 ) {
			$score += 0.08;
		}

		// Short paragraphs (≤50 words): split HTML paragraphs, measure average.
		if ( $paragraphs > 0 ) {
			preg_match_all( '/<p[^>]*>(.*?)<\/p>/is', $raw_html, $p_matches );
			if ( ! empty( $p_matches[1] ) ) {
				$lengths = array_map(
					fn( $p ) => str_word_count( wp_strip_all_tags( $p ) ),
					$p_matches[1]
				);
				$avg_len = array_sum( $lengths ) / count( $lengths );
				if ( $avg_len < 60 ) {
					$score += 0.07;
				}
			}
		}

		// Blockquotes reduce reading time slightly (reader can skim).
		if ( $blockquotes >= 1 ) {
			$score += 0.02;
		}

		// Cap adjustment at 25% faster (factor = 0.75).
		$factor = max( 0.75, 1.0 - $score );

		/**
		 * Filter the AI adjustment factor.
		 *
		 * @param float  $factor     Calculated adjustment factor (0.75–1.0).
		 * @param string $raw_html   Raw post HTML.
		 */
		return (float) apply_filters( 'nvrtp_ai_adjustment_factor', $factor, $raw_html );
	}

	/**
	 * Effective words-per-minute from settings (bounded for safety).
	 *
	 * @return int
	 */
	private function get_effective_wpm(): int {
		$wpm = (int) ( $this->settings['wpm'] ?? 238 );
		return max( 60, min( 600, $wpm ) );
	}

	/**
	 * Format the human-readable label.
	 *
	 * @param int $minutes Estimated minutes.
	 * @return string
	 */
	public function format_label( int $minutes ): string {
		if ( $minutes < 1 ) {
			return wp_kses_post(
				str_replace( '{time}', '1', $this->settings['badge_label_under_one'] ?? __( 'Less than 1 min read', 'nuvora-reading-time-progress-bar' ) )
			);
		}

		$label = $this->settings['badge_label'] ?? __( '{time} min read', 'nuvora-reading-time-progress-bar' );
		return wp_kses_post( str_replace( '{time}', (string) $minutes, $label ) );
	}

	/**
	 * Return a sensible empty/zero result.
	 *
	 * @return array
	 */
	private function empty_result(): array {
		return array(
			'minutes'  => 0,
			'words'    => 0,
			'adjusted' => false,
			'override' => false,
			'label'    => '',
		);
	}
}
