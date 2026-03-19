<?php
/**
 * Counter Block – Registration
 * Called directly from nuvora_addons_init() — no add_action() here.
 *
 * @package Nuvora_AIO_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Guard: skip if already registered (e.g. standalone plugin active)
if ( ! function_exists( 'nugba_register_counter_block' ) ) :

function nugba_register_counter_block() {

	wp_register_script(
		'nuvora-counter-editor',
		NUGBA_URL . 'blocks/counter-block/assets/js/editor.js',
		[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor' ],
		NUGBA_VERSION, true
	);

	wp_register_style(
		'nuvora-counter-style',
		NUGBA_URL . 'blocks/counter-block/assets/css/style.css',
		[], NUGBA_VERSION
	);

	wp_register_style(
		'nuvora-counter-editor-style',
		NUGBA_URL . 'blocks/counter-block/assets/css/editor.css',
		[ 'nuvora-counter-style' ], NUGBA_VERSION
	);

	wp_register_script(
		'nuvora-counter-frontend',
		NUGBA_URL . 'blocks/counter-block/assets/js/frontend.js',
		[], NUGBA_VERSION, true
	);

	// Skip if the block is already registered by the standalone plugin
	if ( WP_Block_Type_Registry::get_instance()->is_registered( 'nuvora/counter-block' ) ) {
		return;
	}

	register_block_type( 'nuvora/counter-block', [
		'editor_script'   => 'nuvora-counter-editor',
		'editor_style'    => 'nuvora-counter-editor-style',
		'style'           => 'nuvora-counter-style',
		'script'          => 'nuvora-counter-frontend',
		'render_callback' => 'nugba_counter_render',
		'attributes'      => [
			'endNumber'       => [ 'type' => 'number',  'default' => 250 ],
			'startNumber'     => [ 'type' => 'number',  'default' => 0 ],
			'duration'        => [ 'type' => 'number',  'default' => 2000 ],
			'prefix'          => [ 'type' => 'string',  'default' => '' ],
			'suffix'          => [ 'type' => 'string',  'default' => '+' ],
			'separator'       => [ 'type' => 'string',  'default' => '' ],
			'decimals'        => [ 'type' => 'number',  'default' => 0 ],
			'title'           => [ 'type' => 'string',  'default' => 'Happy Clients' ],
			'description'     => [ 'type' => 'string',  'default' => '' ],
			'showDescription' => [ 'type' => 'boolean', 'default' => false ],
			'showIcon'        => [ 'type' => 'boolean', 'default' => true ],
			'icon'            => [ 'type' => 'string',  'default' => 'smile' ],
			'iconPosition'    => [ 'type' => 'string',  'default' => 'top' ],
			'layout'          => [ 'type' => 'string',  'default' => 'style1' ],
			'alignment'       => [ 'type' => 'string',  'default' => 'center' ],
			'numberColor'     => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'titleColor'      => [ 'type' => 'string',  'default' => '#333333' ],
			'descColor'       => [ 'type' => 'string',  'default' => '#777777' ],
			'iconColor'       => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'bgColor'         => [ 'type' => 'string',  'default' => '#ffffff' ],
			'borderColor'     => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'gradientFrom'    => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'gradientTo'      => [ 'type' => 'string',  'default' => '#f72585' ],
			'numberSize'      => [ 'type' => 'number',  'default' => 48 ],
			'titleSize'       => [ 'type' => 'number',  'default' => 16 ],
			'descSize'        => [ 'type' => 'number',  'default' => 14 ],
			'iconSize'        => [ 'type' => 'number',  'default' => 40 ],
			'numberWeight'    => [ 'type' => 'string',  'default' => '700' ],
			'titleWeight'     => [ 'type' => 'string',  'default' => '500' ],
			'padding'         => [ 'type' => 'number',  'default' => 30 ],
			'borderRadius'    => [ 'type' => 'number',  'default' => 12 ],
			'borderWidth'     => [ 'type' => 'number',  'default' => 2 ],
			'animationType'   => [ 'type' => 'string',  'default' => 'ease-out' ],
			'enableAnimation' => [ 'type' => 'boolean', 'default' => true ],
		],
	] );
}

endif;

nugba_register_counter_block();

if ( ! function_exists( 'nugba_counter_render' ) ) :
function nugba_counter_render( $a ) {
	$icons = nugba_icons();
	$svg   = isset( $icons[ $a['icon'] ] ) ? $icons[ $a['icon'] ] : $icons['smile'];

	$num_style = "color:{$a['numberColor']};font-size:{$a['numberSize']}px;font-weight:{$a['numberWeight']};";
	$tit_style = "color:{$a['titleColor']};font-size:{$a['titleSize']}px;font-weight:{$a['titleWeight']};";
	$dsc_style = "color:{$a['descColor']};font-size:{$a['descSize']}px;";
	$icn_style = "color:{$a['iconColor']};font-size:{$a['iconSize']}px;";

	$p = intval( $a['padding'] ); $r = intval( $a['borderRadius'] ); $bw = intval( $a['borderWidth'] );

	switch ( $a['layout'] ) {
		case 'style2':
			$wrap = "background:{$a['bgColor']};border:{$bw}px solid {$a['borderColor']};border-radius:{$r}px;padding:{$p}px;text-align:{$a['alignment']};";
			break;
		case 'style3':
			$wrap = "background:linear-gradient(135deg,{$a['gradientFrom']},{$a['gradientTo']});border-radius:{$r}px;padding:{$p}px;text-align:{$a['alignment']};";
			$num_style = "color:#fff;font-size:{$a['numberSize']}px;font-weight:{$a['numberWeight']};";
			$tit_style = "color:rgba(255,255,255,0.9);font-size:{$a['titleSize']}px;font-weight:{$a['titleWeight']};";
			$icn_style = "color:rgba(255,255,255,0.9);font-size:{$a['iconSize']}px;";
			break;
		case 'style4':
			$wrap = "background:{$a['bgColor']};border-radius:{$r}px;padding:{$p}px;border-left:{$bw}px solid {$a['numberColor']};text-align:left;box-shadow:0 2px 12px rgba(0,0,0,0.06);";
			break;
		case 'style5':
			$wrap = "padding:{$p}px;text-align:{$a['alignment']};border-bottom:2px solid {$a['numberColor']};";
			break;
		default:
			$wrap = "background:{$a['bgColor']};border-radius:{$r}px;padding:{$p}px;text-align:{$a['alignment']};box-shadow:0 4px 24px rgba(0,0,0,0.08);";
	}

	$display = $a['enableAnimation'] ? $a['startNumber'] : $a['endNumber'];
	$fmt     = $a['decimals'] > 0 ? number_format( $display, $a['decimals'] ) : number_format( $display );
	if ( ! $a['separator'] ) $fmt = str_replace( ',', '', $fmt );

	$num_html = sprintf(
		'<span class="nuvora-counter-number" style="%s" data-end="%d" data-start="%d" data-duration="%d" data-separator="%s" data-decimals="%d" data-easing="%s">%s%s%s</span>',
		esc_attr( $num_style ), intval( $a['endNumber'] ), intval( $a['startNumber'] ),
		intval( $a['duration'] ), esc_attr( $a['separator'] ), intval( $a['decimals'] ),
		esc_attr( $a['animationType'] ), esc_html( $a['prefix'] ), $fmt, esc_html( $a['suffix'] )
	);

	$icon_html = $a['showIcon'] ? sprintf( '<span class="nuvora-counter-icon" style="%s">%s</span>', esc_attr( $icn_style ), $svg ) : '';
	$tit_html  = sprintf( '<span class="nuvora-counter-title" style="%s">%s</span>', esc_attr( $tit_style ), esc_html( $a['title'] ) );
	$dsc_html  = ( $a['showDescription'] && $a['description'] ) ? sprintf( '<p class="nuvora-counter-desc" style="%s">%s</p>', esc_attr( $dsc_style ), esc_html( $a['description'] ) ) : '';

	if ( $a['layout'] === 'style4' ) {
		$inner = '<div class="nuvora-counter-inner nuvora-counter-inline">' . $icon_html . '<div class="nuvora-counter-content">' . $num_html . $tit_html . $dsc_html . '</div></div>';
	} else {
		$top    = $a['iconPosition'] === 'top' ? $icon_html : '';
		$bottom = $a['iconPosition'] === 'bottom' ? $icon_html : '';
		$inner  = '<div class="nuvora-counter-inner">' . $top . $num_html . $tit_html . $dsc_html . $bottom . '</div>';
	}

	return sprintf(
		'<div class="nuvora-counter-block nuvora-layout-%s%s" style="%s">%s</div>',
		esc_attr( $a['layout'] ),
		$a['enableAnimation'] ? ' nuvora-animate' : '',
		$wrap, $inner
	);
}
endif;

if ( ! function_exists( 'nugba_icons' ) ) :
function nugba_icons() {
	return [
		'smile'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm-3-9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm6 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm1.5 3.5c-.8 1.2-2 2-3.5 2s-2.7-.8-3.5-2h7z"/></svg>',
		'users'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05C16.19 13.89 17 15.02 17 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
		'star'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
		'trophy'  => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5C3.9 5 3 5.9 3 7v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V18H8v2h8v-2h-3v-2.1a5.01 5.01 0 0 0 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.86 10.4 5 9.3 5 8zm14 0c0 1.3-.86 2.4-2 2.82V7h2v1z"/></svg>',
		'rocket'  => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.5 2 7.5 3.5 6.6 5.6L2 14h4v4l2-2 2 2v-4h8v4l2-2 2 2v-4h4L21.4 5.6C20.5 3.5 18.5 2 16 2h-4zm0 2h4c1.8 0 3.3 1 4 2.5l.9 2.5H3.1l.9-2.5C4.7 5 6.2 4 8 4h4zm0 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg>',
		'heart'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09A5.988 5.988 0 0 1 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
		'globe'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm-1 17.93V18c0-.55-.45-1-1-1H8v-2c0-.55-.45-1-1-1H5.07A8.003 8.003 0 0 1 4 12c0-.34.02-.67.07-1H6c.55 0 1-.45 1-1V9c0-.55.45-1 1-1h.5c.55 0 1-.45 1-1V5.08A8.006 8.006 0 0 1 12 4c.34 0 .67.02 1 .07V6c0 .55.45 1 1 1h2v1c0 .55.45 1 1 1h1.93c.05.33.07.66.07 1 0 4.07-3.06 7.44-7 7.93z"/></svg>',
		'chart'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M3.5 18.5l6-6 4 4L22 6.92 20.59 5.5l-7.09 8-4-4L2 17l1.5 1.5z"/></svg>',
		'check'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>',
		'diamond' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5L2 9l10 12L22 9l-3-6zm-8.5 14.5L3.74 9.5h4.76l3 8zm2 0l3-8h4.76L13.5 17.5zM14.5 8h-5L12 4.5 14.5 8zm-6.76 0H3.5L5.5 4h4.24L7.74 8zm9.76 0L15.26 4H19.5l2 4h-4.26z"/></svg>',
		'fire'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM12 20c-3.31 0-6-2.69-6-6 0-1.53.57-3.05 1.6-4.2C8.45 11.4 10.2 12 12 12c1.8 0 3.28-.7 4.43-1.87C17.38 11.37 18 12.87 18 14c0 3.31-2.69 6-6 6z"/></svg>',
		'clock'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm.01 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>',
	];
}
endif;
