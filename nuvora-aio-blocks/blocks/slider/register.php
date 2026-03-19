<?php
/**
 * Nuvora Slider Block – Registration
 * Called directly from nugba_init() — no add_action() here.
 *
 * @package Nuvora_AIO_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'nugba_register_slider' ) ) :
function nugba_register_slider() {

	wp_register_script(
		'nugba-slider-editor',
		NUGBA_URL . 'blocks/slider/assets/js/editor.js',
		[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-api-fetch' ],
		NUGBA_VERSION, true
	);

	wp_register_style(
		'nugba-slider-style',
		NUGBA_URL . 'blocks/slider/assets/css/style.css',
		[], NUGBA_VERSION
	);

	wp_register_script(
		'nugba-slider-frontend',
		NUGBA_URL . 'blocks/slider/assets/js/frontend.js',
		[], NUGBA_VERSION, true
	);

	if ( WP_Block_Type_Registry::get_instance()->is_registered( 'nuvora/slider' ) ) {
		return;
	}

	register_block_type( 'nuvora/slider', [
		'editor_script'   => 'nugba-slider-editor',
		'style'           => 'nugba-slider-style',
		'script'          => 'nugba-slider-frontend',
		'render_callback' => 'nugba_slider_render',
		'attributes'      => [
			// Mode
			'mode'             => [ 'type' => 'string',  'default' => 'slider' ],      // slider | carousel | hero
			'contentType'      => [ 'type' => 'string',  'default' => 'custom' ],      // custom | posts
			// Slides (custom)
			'slides'           => [ 'type' => 'string',  'default' => '[{"imageUrl":"","imageAlt":"","title":"Welcome to Our Site","description":"A beautiful and powerful slider for your Gutenberg editor.","btnText":"Get Started","btnUrl":"#","btnTarget":false,"overlayColor":"rgba(0,0,0,0.45)","overlayEnable":true},{"imageUrl":"","imageAlt":"","title":"Discover More","description":"Explore our features and find everything you need in one place.","btnText":"Learn More","btnUrl":"#","btnTarget":false,"overlayColor":"rgba(30,30,80,0.5)","overlayEnable":true}]' ],
			// Posts settings
			'postCount'        => [ 'type' => 'number',  'default' => 5 ],
			'postCategory'     => [ 'type' => 'string',  'default' => '' ],
			// Layout
			'fullWidth'        => [ 'type' => 'boolean', 'default' => false ],
			'height'           => [ 'type' => 'number',  'default' => 500 ],
			'carouselCols'     => [ 'type' => 'number',  'default' => 3 ],
			'gap'              => [ 'type' => 'number',  'default' => 20 ],
			'borderRadius'     => [ 'type' => 'number',  'default' => 12 ],
			// Autoplay
			'autoplay'         => [ 'type' => 'boolean', 'default' => true ],
			'autoplaySpeed'    => [ 'type' => 'number',  'default' => 4000 ],
			'pauseOnHover'     => [ 'type' => 'boolean', 'default' => true ],
			// Animation
			'animation'        => [ 'type' => 'string',  'default' => 'slide' ],       // slide | fade | zoom | flip
			'animationSpeed'   => [ 'type' => 'number',  'default' => 600 ],
			// Navigation
			'showArrows'       => [ 'type' => 'boolean', 'default' => true ],
			'showDots'         => [ 'type' => 'boolean', 'default' => true ],
			'arrowStyle'       => [ 'type' => 'string',  'default' => 'circle' ],      // circle | square | minimal
			// Caption / overlay
			'showCaption'      => [ 'type' => 'boolean', 'default' => true ],
			'captionPosition'  => [ 'type' => 'string',  'default' => 'center' ],      // top-left | center | bottom-left | bottom-center
			'titleSize'        => [ 'type' => 'number',  'default' => 42 ],
			'titleColor'       => [ 'type' => 'string',  'default' => '#ffffff' ],
			'descSize'         => [ 'type' => 'number',  'default' => 18 ],
			'descColor'        => [ 'type' => 'string',  'default' => 'rgba(255,255,255,0.9)' ],
			'btnBg'            => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'btnColor'         => [ 'type' => 'string',  'default' => '#ffffff' ],
			'btnRadius'        => [ 'type' => 'number',  'default' => 50 ],
			'dotColor'         => [ 'type' => 'string',  'default' => '#ffffff' ],
		],
	] );
}
endif;

nugba_register_slider();

if ( ! function_exists( 'nugba_slider_render' ) ) :
function nugba_slider_render( $a ) {

	$block_id  = 'nugba-slider-' . wp_rand( 1000, 9999 );
	$mode      = $a['mode'];
	$is_hero   = $mode === 'hero';
	$is_car    = $mode === 'carousel';
	$h         = intval( $a['height'] );
	$r         = intval( $a['borderRadius'] );

	// ── Build slides array ───────────────────────────────────────────────────
	$slides = [];

	if ( $a['contentType'] === 'posts' ) {
		$query_args = [
			'post_type'      => 'post',
			'posts_per_page' => intval( $a['postCount'] ),
			'post_status'    => 'publish',
		];
		if ( ! empty( $a['postCategory'] ) ) {
			$query_args['cat'] = intval( $a['postCategory'] );
		}
		$query = new WP_Query( $query_args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
				$slides[] = [
					'imageUrl'      => $thumb_url ? $thumb_url : '',
					'imageAlt'      => get_the_title(),
					'title'         => get_the_title(),
					'description'   => wp_trim_words( get_the_excerpt(), 18 ),
					'btnText'       => __( 'Read More', 'nuvora-aio-blocks' ),
					'btnUrl'        => get_permalink(),
					'btnTarget'     => false,
					'overlayColor'  => 'rgba(0,0,0,0.5)',
					'overlayEnable' => true,
				];
			}
			wp_reset_postdata();
		}
	} else {
		$decoded = json_decode( $a['slides'], true );
		if ( ! empty( $decoded ) ) $slides = $decoded;
	}

	if ( empty( $slides ) ) return '<p>' . esc_html__( 'No slides found.', 'nuvora-aio-blocks' ) . '</p>';

	// ── Caption position map ─────────────────────────────────────────────────
	$cap_pos_map = [
		'top-left'      => 'top:10%;left:8%;transform:none;text-align:left;',
		'center'        => 'top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;',
		'bottom-left'   => 'bottom:12%;left:8%;transform:none;text-align:left;',
		'bottom-center' => 'bottom:12%;left:50%;transform:translateX(-50%);text-align:center;',
	];
	$cap_pos_style = $cap_pos_map[ $a['captionPosition'] ] ?? $cap_pos_map['center'];

	// ── Arrow SVGs ───────────────────────────────────────────────────────────
	$prev_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg>';
	$next_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>';

	$arrow_shape_class = 'nugba-arrow-' . esc_attr( $a['arrowStyle'] );

	// ── Build slides HTML ────────────────────────────────────────────────────
	$slides_html = '';
	foreach ( $slides as $idx => $slide ) {

		$bg_style = ! empty( $slide['imageUrl'] )
			? sprintf( 'background-image:url(%s);background-size:cover;background-position:center;', esc_url( $slide['imageUrl'] ) )
			: 'background:#1a1a2e;';

		$overlay_html = '';
		if ( ! empty( $slide['overlayEnable'] ) ) {
			$overlay_html = sprintf(
				'<div class="nugba-slide-overlay" style="background:%s;"></div>',
				esc_attr( $slide['overlayColor'] ?? 'rgba(0,0,0,0.4)' )
			);
		}

		$caption_html = '';
		if ( $a['showCaption'] ) {
			$title_html = ! empty( $slide['title'] )
				? sprintf( '<h2 class="nugba-slide-title" style="color:%s;font-size:%dpx;">%s</h2>',
					esc_attr( $a['titleColor'] ), intval( $a['titleSize'] ), esc_html( $slide['title'] ) )
				: '';

			$desc_html = ! empty( $slide['description'] )
				? sprintf( '<p class="nugba-slide-desc" style="color:%s;font-size:%dpx;">%s</p>',
					esc_attr( $a['descColor'] ), intval( $a['descSize'] ), esc_html( $slide['description'] ) )
				: '';

			$btn_html = ( ! empty( $slide['btnText'] ) && ! empty( $slide['btnUrl'] ) )
				? sprintf( '<a href="%s" class="nugba-slide-btn" style="background:%s;color:%s;border-radius:%dpx;" %s>%s</a>',
					esc_url( $slide['btnUrl'] ),
					esc_attr( $a['btnBg'] ),
					esc_attr( $a['btnColor'] ),
					intval( $a['btnRadius'] ),
					! empty( $slide['btnTarget'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
					esc_html( $slide['btnText'] ) )
				: '';

			if ( $title_html || $desc_html || $btn_html ) {
				$caption_html = sprintf(
					'<div class="nugba-slide-caption" style="%s">%s%s%s</div>',
					$cap_pos_style, $title_html, $desc_html, $btn_html
				);
			}
		}

		$slides_html .= sprintf(
			'<div class="nugba-slide%s" style="%s">%s%s</div>',
			$idx === 0 ? ' active' : '',
			$bg_style,
			$overlay_html,
			$caption_html
		);
	}

	// ── Dots ─────────────────────────────────────────────────────────────────
	$dots_html = '';
	if ( $a['showDots'] ) {
		$dot_btns = '';
		foreach ( $slides as $idx => $slide ) {
			$dot_btns .= sprintf(
				'<button class="nugba-dot%s" data-index="%d" aria-label="%s" style="--dot-color:%s;"></button>',
				$idx === 0 ? ' active' : '', $idx,
				/* translators: %d: slide number */
				sprintf( __( 'Go to slide %d', 'nuvora-aio-blocks' ), $idx + 1 ),
				esc_attr( $a['dotColor'] )
			);
		}
		$dots_html = '<div class="nugba-slider-dots">' . $dot_btns . '</div>';
	}

	// ── Arrows ───────────────────────────────────────────────────────────────
	$arrows_html = '';
	if ( $a['showArrows'] ) {
		$arrows_html = sprintf(
			'<button class="nugba-arrow nugba-prev %s" aria-label="%s">%s</button><button class="nugba-arrow nugba-next %s" aria-label="%s">%s</button>',
			$arrow_shape_class, esc_attr__( 'Previous slide', 'nuvora-aio-blocks' ), $prev_svg,
			$arrow_shape_class, esc_attr__( 'Next slide', 'nuvora-aio-blocks' ), $next_svg
		);
	}

	// ── Wrapper styles ────────────────────────────────────────────────────────
	$wrap_style = sprintf( 'height:%dpx;border-radius:%dpx;', $h, $is_hero ? 0 : $r );
	if ( $is_car ) {
		$wrap_style = sprintf( 'border-radius:%dpx;', $r );
	}

	$car_data = $is_car ? sprintf( 'data-cols="%d" data-gap="%d"', intval( $a['carouselCols'] ), intval( $a['gap'] ) ) : '';

	return sprintf(
		'<div id="%s" class="nugba-slider-block nugba-mode-%s%s" style="%s"
			data-animation="%s"
			data-animation-speed="%d"
			data-autoplay="%s"
			data-autoplay-speed="%d"
			data-pause-hover="%s"
			%s>
			<div class="nugba-slider-track" style="height:%s;position:relative;">%s</div>
			%s%s
		</div>',
		esc_attr( $block_id ),
		esc_attr( $mode ),
		$a['fullWidth'] ? ' nugba-full-width' : '',
		$wrap_style,
		esc_attr( $a['animation'] ),
		intval( $a['animationSpeed'] ),
		$a['autoplay'] ? 'true' : 'false',
		intval( $a['autoplaySpeed'] ),
		$a['pauseOnHover'] ? 'true' : 'false',
		$car_data,
		$is_car ? 'auto' : '100%',
		$slides_html,
		$arrows_html,
		$dots_html
	);
}
endif;
