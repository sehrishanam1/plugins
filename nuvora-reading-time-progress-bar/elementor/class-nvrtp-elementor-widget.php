<?php
/**
 * Elementor Widget: Nuvora Reading Time
 *
 * Provides two independent controls in one widget:
 *   1. Reading Time Badge  – shows estimated read time for the current post.
 *   2. Scroll Progress Bar – shows a live scroll-progress bar.
 *
 * Each section can be toggled on/off independently, so the widget can act as
 * a badge-only, progress-bar-only, or combined widget.
 *
 * @package NuvoraReadingTime
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only proceed when Elementor is active.
if ( ! did_action( 'elementor/loaded' ) ) {
	return;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Class Nvrtp_Elementor_Widget
 */
class Nvrtp_Elementor_Widget extends Widget_Base {

	// ── Widget identity ────────────────────────────────────────────────

	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string {
		return 'nvrtp_reading_time';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_title(): string {
		return esc_html__( 'Reading Time & Progress', 'nuvora-reading-time-progress-bar' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_icon(): string {
		return 'eicon-clock-o';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_categories(): array {
		return array( 'general' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_keywords(): array {
		return array( 'reading time', 'read time', 'progress bar', 'scroll', 'estimator' );
	}

	// ── Controls ───────────────────────────────────────────────────────

	/**
	 * Register all Elementor controls (settings panels).
	 */
	protected function register_controls(): void {

		// ════════════════════════════════════════════════════════════════
		// SECTION: Badge Content
		// ════════════════════════════════════════════════════════════════
		$this->start_controls_section(
			'section_badge',
			array(
				'label' => esc_html__( 'Reading Time Badge', 'nuvora-reading-time-progress-bar' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_badge',
			array(
				'label'        => esc_html__( 'Show Badge', 'nuvora-reading-time-progress-bar' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'nuvora-reading-time-progress-bar' ),
				'label_off'    => esc_html__( 'No', 'nuvora-reading-time-progress-bar' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_icon',
			array(
				'label'        => esc_html__( 'Show Clock Icon', 'nuvora-reading-time-progress-bar' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'nuvora-reading-time-progress-bar' ),
				'label_off'    => esc_html__( 'No', 'nuvora-reading-time-progress-bar' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_label',
			array(
				'label'       => esc_html__( 'Badge Label', 'nuvora-reading-time-progress-bar' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( '{time} min read', 'nuvora-reading-time-progress-bar' ),
				'description' => esc_html__( 'Use {time} as placeholder for the calculated minutes.', 'nuvora-reading-time-progress-bar' ),
				'condition'   => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_label_under_one',
			array(
				'label'     => esc_html__( 'Label (under 1 min)', 'nuvora-reading-time-progress-bar' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Less than 1 min read', 'nuvora-reading-time-progress-bar' ),
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'nuvora-reading-time-progress-bar' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array( 'title' => esc_html__( 'Left', 'nuvora-reading-time-progress-bar' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => esc_html__( 'Center', 'nuvora-reading-time-progress-bar' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => esc_html__( 'Right', 'nuvora-reading-time-progress-bar' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default'   => 'left',
				'toggle'    => true,
				'condition' => array( 'show_badge' => 'yes' ),
				'selectors' => array(
					'{{WRAPPER}} .nvrtp-widget-badge' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// ════════════════════════════════════════════════════════════════
		// SECTION: Progress Bar Content
		// ════════════════════════════════════════════════════════════════
		$this->start_controls_section(
			'section_progress',
			array(
				'label' => esc_html__( 'Scroll Progress Bar', 'nuvora-reading-time-progress-bar' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_progress',
			array(
				'label'        => esc_html__( 'Show Progress Bar', 'nuvora-reading-time-progress-bar' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'nuvora-reading-time-progress-bar' ),
				'label_off'    => esc_html__( 'No', 'nuvora-reading-time-progress-bar' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'progress_position',
			array(
				'label'     => esc_html__( 'Bar Position', 'nuvora-reading-time-progress-bar' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inline',
				'options'   => array(
					'inline' => esc_html__( 'Inline (where widget is placed)', 'nuvora-reading-time-progress-bar' ),
					'top'    => esc_html__( 'Top of Viewport (Sticky)', 'nuvora-reading-time-progress-bar' ),
					'bottom' => esc_html__( 'Bottom of Viewport (Sticky)', 'nuvora-reading-time-progress-bar' ),
				),
				'condition' => array( 'show_progress' => 'yes' ),
			)
		);

		$this->add_control(
			'progress_show_tooltip',
			array(
				'label'        => esc_html__( 'Show Percentage Tooltip', 'nuvora-reading-time-progress-bar' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'nuvora-reading-time-progress-bar' ),
				'label_off'    => esc_html__( 'No', 'nuvora-reading-time-progress-bar' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'show_progress' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// ════════════════════════════════════════════════════════════════
		// SECTION: Badge Style
		// ════════════════════════════════════════════════════════════════
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label'     => esc_html__( 'Badge Style', 'nuvora-reading-time-progress-bar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'nuvora-reading-time-progress-bar' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6366f1',
				'selectors' => array(
					'{{WRAPPER}} .nvrtp-widget-badge .nvrtp-badge__icon' => 'color: {{VALUE}};',
				),
				'condition' => array( 'show_badge' => 'yes', 'show_icon' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'nuvora-reading-time-progress-bar' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .nvrtp-widget-badge' => 'color: {{VALUE}};',
				),
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'nuvora-reading-time-progress-bar' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .nvrtp-widget-badge' => 'background-color: {{VALUE}};',
				),
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'      => 'badge_typography',
				'selector'  => '{{WRAPPER}} .nvrtp-widget-badge',
				'condition' => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'nuvora-reading-time-progress-bar' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .nvrtp-widget-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'show_badge' => 'yes' ),
			)
		);

		$this->add_control(
			'badge_padding',
			array(
				'label'      => esc_html__( 'Padding', 'nuvora-reading-time-progress-bar' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .nvrtp-widget-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'show_badge' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// ════════════════════════════════════════════════════════════════
		// SECTION: Progress Bar Style
		// ════════════════════════════════════════════════════════════════
		$this->start_controls_section(
			'section_style_progress',
			array(
				'label'     => esc_html__( 'Progress Bar Style', 'nuvora-reading-time-progress-bar' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_progress' => 'yes' ),
			)
		);

		$this->add_control(
			'progress_color',
			array(
				'label'     => esc_html__( 'Bar Color', 'nuvora-reading-time-progress-bar' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6366f1',
				'selectors' => array(
					'{{WRAPPER}} .nvrtp-progress-bar__fill' => 'background: {{VALUE}};',
				),
				'condition' => array( 'show_progress' => 'yes' ),
			)
		);

		$this->add_control(
			'progress_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'nuvora-reading-time-progress-bar' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(99,102,241,0.15)',
				'selectors' => array(
					'{{WRAPPER}} .nvrtp-progress-bar' => 'background: {{VALUE}};',
				),
				'condition' => array( 'show_progress' => 'yes' ),
			)
		);

		$this->add_control(
			'progress_height',
			array(
				'label'      => esc_html__( 'Bar Height (px)', 'nuvora-reading-time-progress-bar' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 1,
						'max'  => 20,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 3,
				),
				'selectors'  => array(
					'{{WRAPPER}} .nvrtp-progress-bar' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'show_progress' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	// ── Render ─────────────────────────────────────────────────────────

	/**
	 * Render the widget output on the frontend.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$plugin_settings = get_option( 'nvrtp_settings', array() );

		echo '<div class="nvrtp-widget-wrapper">';

		if ( 'yes' === $settings['show_badge'] ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$this->render_badge_placeholder( $settings );
			} else {
				$this->render_badge( $settings, $plugin_settings );
			}
		}

		if ( 'yes' === $settings['show_progress'] ) {
			$this->render_progress_bar( $settings );
		}

		echo '</div>';
	}

	/**
	 * Render the reading time badge (frontend only).
	 *
	 * @param array $settings        Widget settings.
	 * @param array $plugin_settings Plugin-level settings from DB.
	 */
	private function render_badge( array $settings, array $plugin_settings ): void {
		// Build a temporary settings array that merges plugin defaults with
		// widget-level overrides so Nvrtp_Calculator picks up the right config.
		$calc_settings = wp_parse_args(
			array(
				'badge_label'           => sanitize_text_field( $settings['badge_label'] ),
				'badge_label_under_one' => sanitize_text_field( $settings['badge_label_under_one'] ),
			),
			$plugin_settings
		);

		$calculator = new Nvrtp_Calculator( $calc_settings );

		// In the editor, Elementor may not have a real post context — fall
		// back gracefully so the widget is still visible.
		$post_id = get_the_ID();
		if ( ! $post_id && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$this->render_badge_placeholder( $settings );
			return;
		}

		$result = $calculator->calculate( $post_id );

		if ( empty( $result['minutes'] ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$this->render_badge_placeholder( $settings );
			}
			return;
		}

		$label     = esc_html( $result['label'] );
		$minutes   = absint( $result['minutes'] );
		$adjusted  = ! empty( $result['adjusted'] );
		$show_icon = 'yes' === $settings['show_icon'];

		$aria_label = sprintf(
			/* translators: %d: estimated minutes */
			esc_attr__( 'Estimated reading time: %d minute(s).', 'nuvora-reading-time-progress-bar' ),
			$minutes
		);

		$ai_attr = $adjusted ? ' data-nvrtp-ai-adjusted="true"' : '';

		$icon_html = '';
		if ( $show_icon ) {
			$icon_html = '<span class="nvrtp-badge__icon" aria-hidden="true">'
				. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">'
				. '<circle cx="12" cy="12" r="10"/>'
				. '<polyline points="12 6 12 12 16 14"/>'
				. '</svg></span>';
		}

		$ai_note = '';
		if ( $adjusted ) {
			$ai_note = sprintf(
				'<span class="nvrtp-badge__ai-note" aria-hidden="true">%s</span>',
				esc_html__( 'AI-adjusted', 'nuvora-reading-time-progress-bar' )
			);
		}

		// Use nvrtp-widget-badge (in addition to nvrtp-badge) so widget-specific
		// Elementor selectors work without breaking the plugin's own CSS.
		echo '<div class="nvrtp-badge nvrtp-widget-badge" role="note" aria-label="' . esc_attr( $aria_label ) . '"' . $ai_attr . ' data-nvrtp-minutes="' . $minutes . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $ai_attr contains only a static data attribute or empty string.
		echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup.
		echo '<span class="nvrtp-badge__label">' . $label . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $label is escaped with esc_html() on line 421.
		echo $ai_note; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is run through esc_html__ above.
		echo '</div>';
	}

	/**
	 * Render a static placeholder badge for the Elementor editor.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_badge_placeholder( array $settings ): void {
		$label     = str_replace( '{time}', '5', sanitize_text_field( $settings['badge_label'] ) );
		$show_icon = 'yes' === $settings['show_icon'];

		$icon_html = '';
		if ( $show_icon ) {
			$icon_html = '<span class="nvrtp-badge__icon" aria-hidden="true">'
				. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">'
				. '<circle cx="12" cy="12" r="10"/>'
				. '<polyline points="12 6 12 12 16 14"/>'
				. '</svg></span>';
		}

		echo '<div class="nvrtp-badge nvrtp-widget-badge" role="note" aria-label="' . esc_attr__( 'Reading time preview', 'nuvora-reading-time-progress-bar' ) . '" data-nvrtp-minutes="5">';
		echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup.
		echo '<span class="nvrtp-badge__label">' . esc_html( $label ) . '</span>';
		echo '</div>';
	}

	/**
	 * Render the scroll progress bar and its inline JS config.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_progress_bar( array $settings ): void {
		$position    = in_array( $settings['progress_position'], array( 'top', 'bottom', 'inline' ), true )
			? $settings['progress_position']
			: 'inline';
		$show_tooltip = 'yes' === $settings['progress_show_tooltip'];

		// Unique ID ensures multiple widgets on the same page don't clash.
		$uid = 'nvrtp-pb-' . $this->get_id();

		// Determine position styles
		$is_fixed = in_array( $position, array( 'top', 'bottom' ), true );
		$position_style = '';
		
		if ( $is_fixed ) {
			$position_style = sprintf(
				'position: fixed; left: 0; width: 100%%; z-index: 9999; %s: 0;',
				esc_attr( $position )
			);
		} else {
			$position_style = 'position: relative; width: 100%;';
		}

		// Tooltip positioning style depends on whether bar is fixed or inline
		$tooltip_position = $is_fixed ? 'fixed' : 'absolute';
		
		printf(
			'<div id="%1$s"
				class="nvrtp-progress-bar"
				role="progressbar"
				aria-valuemin="0"
				aria-valuemax="100"
				aria-valuenow="0"
				aria-label="%2$s"
				data-position="%3$s"
				data-is-fixed="%5$s"
				style="%4$s --nvrtp-progress:0%%;">
				<div class="nvrtp-progress-bar__fill"
					id="%1$s-fill"
					style="
						height:100%%;
						width:var(--nvrtp-progress,0%%);
						transition:width 0.2s linear;
						will-change:width;
					">
				</div>
				<span id="%1$s-tooltip"
					class="nvrtp-progress-tooltip"
					aria-hidden="true"
					style="
						position:%6$s;
						background:rgba(17,24,39,0.95);
						color:#fff;
						font-size:0.6875rem;
						padding:4px 8px;
						border-radius:4px;
						white-space:nowrap;
						pointer-events:none;
						opacity:0;
						transform:translate(-50%%,0);
						transition:opacity 0.15s ease;
						z-index:10001;
					">
				</span>
			</div>',
			esc_attr( $uid ),
			esc_attr__( 'Reading progress', 'nuvora-reading-time-progress-bar' ),
			esc_attr( $position ),
			$position_style, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped above
			$is_fixed ? 'true' : 'false',
			esc_attr( $tooltip_position )
		);

		// Inline JS — self-contained per widget instance so it works even
		// when the plugin's global progress bar is disabled.
		$complete_label = esc_js( __( 'Complete', 'nuvora-reading-time-progress-bar' ) );
		$inline_js = sprintf(
			'( function () {
				"use strict";
				var barId = %1$s;
				var showTip = %2$s;
				var position = %3$s;
				var isFixed = %4$s;
				var complete = %5$s;
				function initBar() {
					var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
					var bar = document.getElementById(barId);
					var fill = document.getElementById(barId + "-fill");
					var tooltip = document.getElementById(barId + "-tooltip");
					if (!bar || !fill) { return; }
					if (reduceMotion) { fill.style.transition = "none"; }
					function getScrollPercent() {
						var docEl = document.documentElement;
						var body = document.body;
						var article = document.querySelector("article .entry-content") || document.querySelector(".entry-content") || document.querySelector("article") || document.querySelector("main");
						var scrollTop = docEl.scrollTop || body.scrollTop;
						var windowH = docEl.clientHeight || window.innerHeight;
						var totalH, startOffset;
						if (article) { var rect = article.getBoundingClientRect(); startOffset = rect.top + scrollTop; totalH = article.offsetHeight; }
						else { startOffset = 0; totalH = Math.max(body.scrollHeight, body.offsetHeight, docEl.scrollHeight, docEl.offsetHeight) - windowH; }
						if (totalH <= 0) { return 0; }
						var scrolled = scrollTop - startOffset;
						var scrollEnd = totalH - (article ? windowH : 0);
						if (scrollEnd <= 0) { return 100; }
						return Math.min(100, Math.max(0, Math.round((scrolled / scrollEnd) * 100)));
					}
					function update() {
						var pct = getScrollPercent();
						fill.style.width = pct + "%%";
						bar.setAttribute("aria-valuenow", pct);
						if (tooltip) { tooltip.textContent = pct < 100 ? pct + "%%" : complete; }
					}
					window.addEventListener("scroll", update, { passive: true });
					window.addEventListener("resize", update, { passive: true });
					update();
				}
				if (document.readyState === "loading") {
					document.addEventListener("DOMContentLoaded", initBar);
				} else {
					initBar();
				}
			} )();',
			wp_json_encode( $uid ),
			$show_tooltip ? 'true' : 'false',
			wp_json_encode( $position ),
			$is_fixed ? 'true' : 'false',
			wp_json_encode( $complete_label )
		);

		// Ensure the script handle is registered even if main plugin didn't enqueue it
		// (e.g. when Elementor widget is used on a post type not in plugin settings)
		if ( ! wp_script_is( 'nvrtp-progress', 'registered' ) && ! wp_script_is( 'nvrtp-progress', 'enqueued' ) ) {
			wp_register_script(
				'nvrtp-progress',
				'', // No external file needed — logic is fully inline
				array(),
				NVRTP_VERSION,
				true
			);
			wp_enqueue_script( 'nvrtp-progress' );
		}

		wp_add_inline_script( 'nvrtp-progress', $inline_js );
	}
}
