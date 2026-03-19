<?php
/**
 * Advanced Tabs Block – Registration
 * Called directly from nuvora_addons_init() — no add_action() here.
 *
 * @package Nuvora_AIO_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'nugba_register_advanced_tabs' ) ) :
function nugba_register_advanced_tabs() {

	wp_register_script(
		'nuvora-tabs-editor',
		NUGBA_URL . 'blocks/advanced-tabs/assets/js/editor.js',
		[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor' ],
		NUGBA_VERSION, true
	);

	wp_register_style(
		'nuvora-tabs-style',
		NUGBA_URL . 'blocks/advanced-tabs/assets/css/style.css',
		[], NUGBA_VERSION
	);

	wp_register_script(
		'nuvora-tabs-frontend',
		NUGBA_URL . 'blocks/advanced-tabs/assets/js/frontend.js',
		[], NUGBA_VERSION, true
	);

	if ( WP_Block_Type_Registry::get_instance()->is_registered( 'nuvora/advanced-tabs' ) ) {
		return;
	}

	register_block_type( 'nuvora/advanced-tabs', [
		'editor_script'   => 'nuvora-tabs-editor',
		'style'           => 'nuvora-tabs-style',
		'script'          => 'nuvora-tabs-frontend',
		'render_callback' => 'nugba_tabs_render',
		'attributes'      => [
			'tabs'            => [ 'type' => 'string', 'default' => '[{"title":"Features","description":"Explore our powerful features that help you build better products faster.","icon":"star","iconColor":"#6c63ff"},{"title":"Benefits","description":"Save time and money with our all-in-one solution designed for modern teams.","icon":"check","iconColor":"#f72585"},{"title":"Support","description":"Our dedicated support team is available 24/7 to help you succeed.","icon":"heart","iconColor":"#06d6a0"}]' ],
			'layout'          => [ 'type' => 'string',  'default' => 'style1' ],
			'tabPosition'     => [ 'type' => 'string',  'default' => 'top' ],
			'activeTab'       => [ 'type' => 'number',  'default' => 0 ],
			'activeBg'        => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'activeText'      => [ 'type' => 'string',  'default' => '#ffffff' ],
			'inactiveBg'      => [ 'type' => 'string',  'default' => '#f5f5f5' ],
			'inactiveText'    => [ 'type' => 'string',  'default' => '#555555' ],
			'contentBg'       => [ 'type' => 'string',  'default' => '#ffffff' ],
			'contentText'     => [ 'type' => 'string',  'default' => '#333333' ],
			'borderColor'     => [ 'type' => 'string',  'default' => '#e0e0e0' ],
			'tabPadding'      => [ 'type' => 'number',  'default' => 14 ],
			'contentPadding'  => [ 'type' => 'number',  'default' => 28 ],
			'borderRadius'    => [ 'type' => 'number',  'default' => 10 ],
			'titleSize'       => [ 'type' => 'number',  'default' => 15 ],
			'iconSize'        => [ 'type' => 'number',  'default' => 20 ],
			'descSize'        => [ 'type' => 'number',  'default' => 16 ],
			'showIcon'        => [ 'type' => 'boolean', 'default' => true ],
			'showTitle'       => [ 'type' => 'boolean', 'default' => true ],
			'showDesc'        => [ 'type' => 'boolean', 'default' => true ],
		],
	] );
}
endif;

nugba_register_advanced_tabs();

if ( ! function_exists( 'nugba_tabs_render' ) ) :
function nugba_tabs_render( $a ) {
	$tabs = json_decode( $a['tabs'], true );
	if ( empty( $tabs ) ) return '';

	$block_id = 'nuvora-tabs-' . wp_rand( 1000, 9999 );
	$r  = intval( $a['borderRadius'] );
	$tp = intval( $a['tabPadding'] );
	$cp = intval( $a['contentPadding'] );

	$icons = [
		'star'    => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
		'check'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>',
		'heart'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09A5.988 5.988 0 0 1 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
		'rocket'  => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.5 2 7.5 3.5 6.6 5.6L2 14h4v4l2-2 2 2v-4h8v4l2-2 2 2v-4h4L21.4 5.6C20.5 3.5 18.5 2 16 2h-4zm0 2h4c1.8 0 3.3 1 4 2.5l.9 2.5H3.1l.9-2.5C4.7 5 6.2 4 8 4h4zm0 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg>',
		'globe'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm-1 17.93V18c0-.55-.45-1-1-1H8v-2c0-.55-.45-1-1-1H5.07A8.003 8.003 0 0 1 4 12c0-.34.02-.67.07-1H6c.55 0 1-.45 1-1V9c0-.55.45-1 1-1h.5c.55 0 1-.45 1-1V5.08A8.006 8.006 0 0 1 12 4c.34 0 .67.02 1 .07V6c0 .55.45 1 1 1h2v1c0 .55.45 1 1 1h1.93c.05.33.07.66.07 1 0 4.07-3.06 7.44-7 7.93z"/></svg>',
		'users'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05C16.19 13.89 17 15.02 17 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
		'chart'   => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M3.5 18.5l6-6 4 4L22 6.92 20.59 5.5l-7.09 8-4-4L2 17l1.5 1.5z"/></svg>',
		'diamond' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5L2 9l10 12L22 9l-3-6zm-8.5 14.5L3.74 9.5h4.76l3 8zm2 0l3-8h4.76L13.5 17.5zM14.5 8h-5L12 4.5 14.5 8zm-6.76 0H3.5L5.5 4h4.24L7.74 8zm9.76 0L15.26 4H19.5l2 4h-4.26z"/></svg>',
	];

	$tab_buttons = '';
	foreach ( $tabs as $i => $tab ) {
		$is_active  = $i === 0;
		$active_bg  = $is_active ? $a['activeBg'] : $a['inactiveBg'];
		$active_txt = $is_active ? $a['activeText'] : $a['inactiveText'];
		$icon_svg   = isset( $icons[ $tab['icon'] ] ) ? $icons[ $tab['icon'] ] : $icons['star'];
		$icon_color = $is_active ? $a['activeText'] : ( $tab['iconColor'] ?? $a['activeBg'] );

		switch ( $a['layout'] ) {
			case 'style2':
				$tab_style = sprintf( 'padding:%dpx %dpx;background:transparent;color:%s;border-bottom:%s;font-size:%dpx;',
					$tp, $tp * 2, $is_active ? $a['activeBg'] : $a['inactiveText'],
					$is_active ? "2px solid {$a['activeBg']}" : '2px solid transparent',
					intval( $a['titleSize'] ) );
				break;
			case 'style3':
				$tab_style = sprintf( 'padding:%dpx %dpx;background:%s;color:%s;border-radius:50px;font-size:%dpx;',
					intval( $tp * 0.7 ), $tp * 2, $active_bg, $active_txt, intval( $a['titleSize'] ) );
				break;
			case 'style4':
				$tab_style = sprintf( 'padding:%dpx;background:%s;color:%s;border-radius:%dpx %dpx 0 0;font-size:%dpx;border:1px solid %s;border-bottom:%s;',
					$tp, $active_bg, $active_txt, $r, $r, intval( $a['titleSize'] ),
					$a['borderColor'],
					$is_active ? "1px solid {$active_bg}" : "1px solid {$a['borderColor']}" );
				break;
			default:
				$tab_style = sprintf( 'padding:%dpx %dpx;background:%s;color:%s;font-size:%dpx;border-radius:%dpx;',
					$tp, $tp * 2, $active_bg, $active_txt, intval( $a['titleSize'] ), $r );
		}

		$icon_html  = $a['showIcon'] ? sprintf( '<span class="nuvora-tab-icon" style="color:%s;font-size:%dpx;">%s</span>', esc_attr( $icon_color ), intval( $a['iconSize'] ), $icon_svg ) : '';
		$title_html = $a['showTitle'] ? sprintf( '<span class="nuvora-tab-label">%s</span>', esc_html( $tab['title'] ) ) : '';

		$tab_buttons .= sprintf(
			'<button class="nuvora-tab-btn%s" data-tab="%d" style="%s" aria-selected="%s">%s%s</button>',
			$is_active ? ' active' : '', $i, esc_attr( $tab_style ),
			$is_active ? 'true' : 'false', $icon_html, $title_html
		);
	}

	$content_panels = '';
	foreach ( $tabs as $i => $tab ) {
		$icon_svg = isset( $icons[ $tab['icon'] ] ) ? $icons[ $tab['icon'] ] : $icons['star'];
		$content_panels .= sprintf(
			'<div class="nuvora-tab-panel%s" data-panel="%d" style="background:%s;color:%s;padding:%dpx;" role="tabpanel">%s%s</div>',
			$i === 0 ? ' active' : '', $i,
			esc_attr( $a['contentBg'] ), esc_attr( $a['contentText'] ), $cp,
			$a['showIcon'] ? sprintf( '<div class="nuvora-panel-icon" style="color:%s;font-size:%dpx;">%s</div>', esc_attr( $tab['iconColor'] ?? $a['activeBg'] ), intval( $a['iconSize'] * 1.5 ), $icon_svg ) : '',
			$a['showDesc'] ? sprintf( '<p class="nuvora-panel-desc" style="font-size:%dpx;line-height:1.7;margin:12px 0 0;">%s</p>', intval( $a['descSize'] ), esc_html( $tab['description'] ) ) : ''
		);
	}

	$nav_wrap_style = '';
	switch ( $a['layout'] ) {
		case 'style2': $nav_wrap_style = sprintf( 'border-bottom:1px solid %s;', esc_attr( $a['borderColor'] ) ); break;
		case 'style3': $nav_wrap_style = sprintf( 'background:%s;padding:8px;border-radius:%dpx;', esc_attr( $a['inactiveBg'] ), $r ); break;
		case 'style4': $nav_wrap_style = 'align-items:flex-end;'; break;
	}

	$content_wrap_style = sprintf( 'border:%s;border-radius:%s;overflow:hidden;',
		$a['layout'] === 'style2' ? 'none' : "1px solid {$a['borderColor']}",
		$a['layout'] === 'style1' || $a['layout'] === 'style3' ? "{$r}px" : "0 {$r}px {$r}px {$r}px"
	);

	return sprintf(
		'<div id="%s" class="nuvora-tabs-block nuvora-tabs-layout-%s" data-active-bg="%s" data-active-text="%s" data-inactive-bg="%s" data-inactive-text="%s">
			<div class="nuvora-tabs-nav" style="%s" role="tablist">%s</div>
			<div class="nuvora-tabs-content" style="%s">%s</div>
		</div>',
		esc_attr( $block_id ), esc_attr( $a['layout'] ),
		esc_attr( $a['activeBg'] ), esc_attr( $a['activeText'] ),
		esc_attr( $a['inactiveBg'] ), esc_attr( $a['inactiveText'] ),
		$nav_wrap_style, $tab_buttons,
		$content_wrap_style, $content_panels
	);
}
endif;
