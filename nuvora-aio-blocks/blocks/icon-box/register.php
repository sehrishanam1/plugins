<?php
/**
 * Icon Box Block – Registration
 * Called directly from nugba_init() — no add_action() here.
 *
 * @package Nuvora_AIO_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'nugba_register_icon_box' ) ) :
function nugba_register_icon_box() {

	wp_register_script(
		'nugba-icon-box-editor',
		NUGBA_URL . 'blocks/icon-box/assets/js/editor.js',
		[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor' ],
		NUGBA_VERSION, true
	);

	wp_register_style(
		'nugba-icon-box-style',
		NUGBA_URL . 'blocks/icon-box/assets/css/style.css',
		[], NUGBA_VERSION
	);

	if ( WP_Block_Type_Registry::get_instance()->is_registered( 'nuvora/icon-box' ) ) {
		return;
	}

	register_block_type( 'nuvora/icon-box', [
		'editor_script'   => 'nugba-icon-box-editor',
		'style'           => 'nugba-icon-box-style',
		'render_callback' => 'nugba_icon_box_render',
		'attributes'      => [
			// Layout & global
			'layout'          => [ 'type' => 'string',  'default' => 'style1' ],
			'columns'         => [ 'type' => 'number',  'default' => 3 ],
			'iconPosition'    => [ 'type' => 'string',  'default' => 'top' ],
			'alignment'       => [ 'type' => 'string',  'default' => 'center' ],
			// Icon styling
			'iconSize'        => [ 'type' => 'number',  'default' => 48 ],
			'iconShape'       => [ 'type' => 'string',  'default' => 'circle' ],
			'iconColor'       => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'iconBg'          => [ 'type' => 'string',  'default' => '#ede9ff' ],
			// Text styling
			'headingSize'     => [ 'type' => 'number',  'default' => 18 ],
			'headingColor'    => [ 'type' => 'string',  'default' => '#1a1a2e' ],
			'descSize'        => [ 'type' => 'number',  'default' => 14 ],
			'descColor'       => [ 'type' => 'string',  'default' => '#666677' ],
			// Box styling
			'boxBg'           => [ 'type' => 'string',  'default' => '#ffffff' ],
			'boxBorderColor'  => [ 'type' => 'string',  'default' => '#e8e8f0' ],
			'boxBorderRadius' => [ 'type' => 'number',  'default' => 16 ],
			'boxPadding'      => [ 'type' => 'number',  'default' => 32 ],
			'accentColor'     => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'accentColor2'    => [ 'type' => 'string',  'default' => '#f72585' ],
			// Items repeater
			'items'           => [ 'type' => 'string',  'default' => '[{"icon":"star","heading":"Our Vision","description":"We strive to deliver world-class solutions that empower teams and drive innovation forward."},{"icon":"rocket","heading":"Our Mission","description":"Building products that are fast, reliable and beautifully designed for every kind of user."},{"icon":"heart","heading":"Our Values","description":"Integrity, collaboration and excellence guide everything we do at every step of the journey."}]' ],
		],
	] );
}
endif;

nugba_register_icon_box();

if ( ! function_exists( 'nugba_icon_box_render' ) ) :
function nugba_icon_box_render( $a ) {

	$items = json_decode( $a['items'], true );
	if ( empty( $items ) ) return '';

	$icons = nugba_icon_box_icons();

	$cols   = intval( $a['columns'] );
	$p      = intval( $a['boxPadding'] );
	$r      = intval( $a['boxBorderRadius'] );
	$h_size = intval( $a['headingSize'] );
	$d_size = intval( $a['descSize'] );
	$i_size = intval( $a['iconSize'] );

	// Icon shape styles
	$shape_extra = '';
	switch ( $a['iconShape'] ) {
		case 'circle':
			$shape_extra = "border-radius:50%;";
			break;
		case 'rounded':
			$shape_extra = "border-radius:12px;";
			break;
		case 'square':
			$shape_extra = "border-radius:0;";
			break;
		case 'none':
			$shape_extra = "background:transparent!important;";
			break;
	}

	$icon_wrap_size = $i_size + 24;
	$icon_wrap_style = sprintf(
		'width:%dpx;height:%dpx;display:inline-flex;align-items:center;justify-content:center;background:%s;%s;flex-shrink:0;',
		$icon_wrap_size, $icon_wrap_size, esc_attr( $a['iconBg'] ), $shape_extra
	);

	// Box layout styles
	switch ( $a['layout'] ) {
		case 'style2': // Card with top color bar
			$box_style = sprintf(
				'background:%s;border-radius:%dpx;padding:%dpx;border-top:4px solid %s;box-shadow:0 4px 24px rgba(0,0,0,0.07);',
				esc_attr( $a['boxBg'] ), $r, $p, esc_attr( $a['accentColor'] )
			);
			break;
		case 'style3': // Gradient background
			$box_style = sprintf(
				'background:linear-gradient(135deg,%s,%s);border-radius:%dpx;padding:%dpx;',
				esc_attr( $a['accentColor'] ), esc_attr( $a['accentColor2'] ), $r, $p
			);
			$a['headingColor'] = '#ffffff';
			$a['descColor']    = 'rgba(255,255,255,0.85)';
			$icon_wrap_style   = sprintf(
				'width:%dpx;height:%dpx;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.2);%s;flex-shrink:0;',
				$icon_wrap_size, $icon_wrap_size, $shape_extra
			);
			$a['iconColor'] = '#ffffff';
			break;
		case 'style4': // Minimal left border
			$box_style = sprintf(
				'background:%s;border-radius:%dpx;padding:%dpx;border-left:4px solid %s;',
				esc_attr( $a['boxBg'] ), $r, $p, esc_attr( $a['accentColor'] )
			);
			break;
		default: // style1 - clean card with border
			$box_style = sprintf(
				'background:%s;border-radius:%dpx;padding:%dpx;border:1px solid %s;',
				esc_attr( $a['boxBg'] ), $r, $p, esc_attr( $a['boxBorderColor'] )
			);
	}

	// Icon position layout
	$is_left_right = in_array( $a['iconPosition'], [ 'left', 'right' ], true );
	$flex_dir = 'column';
	if ( $a['iconPosition'] === 'left' )  $flex_dir = 'row';
	if ( $a['iconPosition'] === 'right' ) $flex_dir = 'row-reverse';

	$inner_style = sprintf(
		'display:flex;flex-direction:%s;align-items:%s;gap:%dpx;text-align:%s;',
		$flex_dir,
		$is_left_right ? 'flex-start' : ( $a['alignment'] === 'center' ? 'center' : 'flex-start' ),
		$is_left_right ? 20 : 16,
		$is_left_right ? 'left' : esc_attr( $a['alignment'] )
	);

	$cards_html = '';
	foreach ( $items as $item ) {
		$svg = isset( $icons[ $item['icon'] ] ) ? $icons[ $item['icon'] ] : $icons['star'];

		$icon_html = sprintf(
			'<span class="nugba-ib-icon-wrap" style="%s"><span class="nugba-ib-icon" style="color:%s;font-size:%dpx;display:flex;">%s</span></span>',
			$icon_wrap_style, esc_attr( $a['iconColor'] ), $i_size, $svg
		);

		$text_html = sprintf(
			'<div class="nugba-ib-text"><h3 class="nugba-ib-heading" style="color:%s;font-size:%dpx;margin:0 0 8px;">%s</h3><p class="nugba-ib-desc" style="color:%s;font-size:%dpx;margin:0;line-height:1.7;">%s</p></div>',
			esc_attr( $a['headingColor'] ), $h_size, esc_html( $item['heading'] ),
			esc_attr( $a['descColor'] ), $d_size, esc_html( $item['description'] )
		);

		$cards_html .= sprintf(
			'<div class="nugba-ib-item" style="%s"><div class="nugba-ib-inner" style="%s">%s%s</div></div>',
			$box_style, $inner_style, $icon_html, $text_html
		);
	}

	$grid_style = sprintf(
		'display:grid;grid-template-columns:repeat(%d,1fr);gap:24px;',
		$cols
	);

	return sprintf(
		'<div class="nugba-icon-box-block nugba-ib-layout-%s nugba-ib-cols-%d"><div class="nugba-ib-grid" style="%s">%s</div></div>',
		esc_attr( $a['layout'] ), $cols, $grid_style, $cards_html
	);
}
endif;

if ( ! function_exists( 'nugba_icon_box_icons' ) ) :
function nugba_icon_box_icons() {
	return [
		'star'     => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
		'rocket'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.5 2 7.5 3.5 6.6 5.6L2 14h4v4l2-2 2 2v-4h8v4l2-2 2 2v-4h4L21.4 5.6C20.5 3.5 18.5 2 16 2h-4zm0 2h4c1.8 0 3.3 1 4 2.5l.9 2.5H3.1l.9-2.5C4.7 5 6.2 4 8 4h4zm0 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg>',
		'heart'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09A5.988 5.988 0 0 1 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
		'check'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>',
		'shield'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>',
		'globe'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm-1 17.93V18c0-.55-.45-1-1-1H8v-2c0-.55-.45-1-1-1H5.07A8.003 8.003 0 0 1 4 12c0-.34.02-.67.07-1H6c.55 0 1-.45 1-1V9c0-.55.45-1 1-1h.5c.55 0 1-.45 1-1V5.08A8.006 8.006 0 0 1 12 4c.34 0 .67.02 1 .07V6c0 .55.45 1 1 1h2v1c0 .55.45 1 1 1h1.93c.05.33.07.66.07 1 0 4.07-3.06 7.44-7 7.93z"/></svg>',
		'users'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05C16.19 13.89 17 15.02 17 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
		'chart'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M3.5 18.5l6-6 4 4L22 6.92 20.59 5.5l-7.09 8-4-4L2 17l1.5 1.5z"/></svg>',
		'diamond'  => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5L2 9l10 12L22 9l-3-6zm-8.5 14.5L3.74 9.5h4.76l3 8zm2 0l3-8h4.76L13.5 17.5zM14.5 8h-5L12 4.5 14.5 8zm-6.76 0H3.5L5.5 4h4.24L7.74 8zm9.76 0L15.26 4H19.5l2 4h-4.26z"/></svg>',
		'lightning'=> '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg>',
		'trophy'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5C3.9 5 3 5.9 3 7v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V18H8v2h8v-2h-3v-2.1a5.01 5.01 0 0 0 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.86 10.4 5 9.3 5 8zm14 0c0 1.3-.86 2.4-2 2.82V7h2v1z"/></svg>',
		'smile'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm-3-9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm6 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm1.5 3.5c-.8 1.2-2 2-3.5 2s-2.7-.8-3.5-2h7z"/></svg>',
		'fire'     => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM12 20c-3.31 0-6-2.69-6-6 0-1.53.57-3.05 1.6-4.2C8.45 11.4 10.2 12 12 12c1.8 0 3.28-.7 4.43-1.87C17.38 11.37 18 12.87 18 14c0 3.31-2.69 6-6 6z"/></svg>',
		'clock'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm.01 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>',
	];
}
endif;
