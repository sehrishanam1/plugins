<?php
/**
 * Testimonial Carousel Block – Registration
 * Called directly from nuvora_addons_init() — no add_action() here.
 *
 * @package Nuvora_AIO_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'nugba_register_testimonial_carousel' ) ) :
function nugba_register_testimonial_carousel() {

	wp_register_script(
		'nuvora-testimonial-editor',
		NUGBA_URL . 'blocks/testimonial-carousel/assets/js/editor.js',
		[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor' ],
		NUGBA_VERSION, true
	);

	wp_register_style(
		'nuvora-testimonial-style',
		NUGBA_URL . 'blocks/testimonial-carousel/assets/css/style.css',
		[], NUGBA_VERSION
	);

	wp_register_script(
		'nuvora-testimonial-frontend',
		NUGBA_URL . 'blocks/testimonial-carousel/assets/js/frontend.js',
		[], NUGBA_VERSION, true
	);

	if ( WP_Block_Type_Registry::get_instance()->is_registered( 'nuvora/testimonial-carousel' ) ) {
		return;
	}

	register_block_type( 'nuvora/testimonial-carousel', [
		'editor_script'   => 'nuvora-testimonial-editor',
		'style'           => 'nuvora-testimonial-style',
		'script'          => 'nuvora-testimonial-frontend',
		'render_callback' => 'nugba_testimonial_render',
		'attributes'      => [
			'testimonials'   => [ 'type' => 'string', 'default' => '[{"name":"Sarah Johnson","role":"CEO, TechCorp","quote":"This product completely transformed how our team works. Absolutely outstanding quality and support.","rating":5,"initials":"SJ","accentColor":"#6c63ff"},{"name":"Mark Williams","role":"Designer","quote":"The best tool I have used in years. Clean, intuitive, and powerful. Highly recommend to everyone.","rating":5,"initials":"MW","accentColor":"#f72585"},{"name":"Emily Chen","role":"Developer","quote":"Incredible value for money. The features are exactly what we needed and setup was a breeze.","rating":4,"initials":"EC","accentColor":"#06d6a0"}]' ],
			'layout'         => [ 'type' => 'string',  'default' => 'style1' ],
			'autoplay'       => [ 'type' => 'boolean', 'default' => true ],
			'autoplaySpeed'  => [ 'type' => 'number',  'default' => 4000 ],
			'showDots'       => [ 'type' => 'boolean', 'default' => true ],
			'showArrows'     => [ 'type' => 'boolean', 'default' => true ],
			'showRating'     => [ 'type' => 'boolean', 'default' => true ],
			'bgColor'        => [ 'type' => 'string',  'default' => '#ffffff' ],
			'textColor'      => [ 'type' => 'string',  'default' => '#333333' ],
			'subColor'       => [ 'type' => 'string',  'default' => '#777777' ],
			'quoteColor'     => [ 'type' => 'string',  'default' => '#444444' ],
			'starColor'      => [ 'type' => 'string',  'default' => '#f59e0b' ],
			'dotColor'       => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'borderRadius'   => [ 'type' => 'number',  'default' => 16 ],
			'padding'        => [ 'type' => 'number',  'default' => 32 ],
			'quoteSize'      => [ 'type' => 'number',  'default' => 16 ],
			'nameSize'       => [ 'type' => 'number',  'default' => 17 ],
			'avatarSize'     => [ 'type' => 'number',  'default' => 52 ],
		],
	] );
}
endif;

nugba_register_testimonial_carousel();

if ( ! function_exists( 'nugba_testimonial_render' ) ) :
function nugba_testimonial_render( $a ) {
	$testimonials = json_decode( $a['testimonials'], true );
	if ( empty( $testimonials ) ) return '';

	$block_id = 'nuvora-tc-' . wp_rand( 1000, 9999 );
	$p = intval( $a['padding'] ); $r = intval( $a['borderRadius'] );

	$card_base = "box-sizing:border-box;padding:{$p}px;border-radius:{$r}px;text-align:center;";
	switch ( $a['layout'] ) {
		case 'style2': $card_extra = "background:{$a['bgColor']};border:1px solid rgba(0,0,0,0.08);"; break;
		case 'style3': $card_extra = "background:{$a['bgColor']};backdrop-filter:blur(10px);box-shadow:0 8px 32px rgba(0,0,0,0.08);border:1px solid rgba(255,255,255,0.6);"; break;
		case 'style4': $card_extra = "background:{$a['bgColor']};text-align:left;border-left:4px solid currentAccent;"; break;
		default:       $card_extra = "background:{$a['bgColor']};box-shadow:0 4px 30px rgba(0,0,0,0.08);";
	}

	$star_svg   = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
	$star_empty = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24" opacity="0.3"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
	$quote_mark = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 24 24" style="opacity:0.12;"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>';

	$slides = '';
	foreach ( $testimonials as $t ) {
		$rating = intval( $t['rating'] ?? 5 );
		$stars  = '';
		if ( $a['showRating'] ) {
			for ( $i = 1; $i <= 5; $i++ ) {
				$stars .= sprintf( '<span style="color:%s;">%s</span>', esc_attr( $a['starColor'] ), $i <= $rating ? $star_svg : $star_empty );
			}
			$stars = '<div class="nuvora-tc-stars">' . $stars . '</div>';
		}

		$avatar_style = sprintf(
			'width:%dpx;height:%dpx;border-radius:50%%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:%dpx;color:#fff;background:%s;margin:0 auto 14px;flex-shrink:0;',
			intval( $a['avatarSize'] ), intval( $a['avatarSize'] ),
			intval( $a['avatarSize'] * 0.35 ),
			esc_attr( $t['accentColor'] ?? '#6c63ff' )
		);

		$card_style = $card_base . $card_extra;
		if ( $a['layout'] === 'style4' ) {
			$card_style = str_replace( 'currentAccent', esc_attr( $t['accentColor'] ?? '#6c63ff' ), $card_style );
		}

		$slides .= sprintf(
			'<div class="nuvora-tc-slide">
				<div class="nuvora-tc-card" style="%s">
					<div class="nuvora-tc-quote-icon" style="color:%s;">%s</div>
					<p class="nuvora-tc-quote" style="color:%s;font-size:%dpx;">%s</p>
					%s
					<div class="nuvora-tc-author">
						<div class="nuvora-tc-avatar" style="%s">%s</div>
						<div class="nuvora-tc-author-info">
							<strong class="nuvora-tc-name" style="color:%s;font-size:%dpx;">%s</strong>
							<span class="nuvora-tc-role" style="color:%s;">%s</span>
						</div>
					</div>
				</div>
			</div>',
			esc_attr( $card_style ),
			esc_attr( $t['accentColor'] ?? '#6c63ff' ), $quote_mark,
			esc_attr( $a['quoteColor'] ), intval( $a['quoteSize'] ), esc_html( $t['quote'] ?? '' ),
			$stars,
			$avatar_style, esc_html( $t['initials'] ?? '?' ),
			esc_attr( $a['textColor'] ), intval( $a['nameSize'] ), esc_html( $t['name'] ?? '' ),
			esc_attr( $a['subColor'] ), esc_html( $t['role'] ?? '' )
		);
	}

	$prev_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg>';
	$next_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>';

	$arrows = '';
	if ( $a['showArrows'] ) {
		$arrows = sprintf(
			'<button class="nuvora-tc-prev" aria-label="Previous">%s</button><button class="nuvora-tc-next" aria-label="Next">%s</button>',
			$prev_svg, $next_svg
		);
	}

	$dots = '';
	if ( $a['showDots'] ) {
		$dot_items = '';
		foreach ( $testimonials as $i => $t ) {
			$dot_items .= sprintf( '<button class="nuvora-tc-dot%s" data-index="%d" aria-label="Go to slide %d" style="--dot-color:%s;"></button>',
				$i === 0 ? ' active' : '', $i, $i + 1, esc_attr( $a['dotColor'] ) );
		}
		$dots = '<div class="nuvora-tc-dots">' . $dot_items . '</div>';
	}

	return sprintf(
		'<div id="%s" class="nuvora-testimonial-block nuvora-tc-layout-%s" data-autoplay="%s" data-speed="%d">
			<div class="nuvora-tc-track-wrap">
				<div class="nuvora-tc-track">%s</div>
				%s
			</div>
			%s
		</div>',
		esc_attr( $block_id ),
		esc_attr( $a['layout'] ),
		$a['autoplay'] ? 'true' : 'false',
		intval( $a['autoplaySpeed'] ),
		$slides, $arrows, $dots
	);
}
endif;
