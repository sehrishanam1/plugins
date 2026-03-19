<?php
/**
 * Pricing Table Block – Registration
 * Called directly from nuvora_addons_init() — no add_action() here.
 *
 * @package Nuvora_AIO_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'nugba_register_pricing_table' ) ) :
function nugba_register_pricing_table() {

	wp_register_script(
		'nuvora-pricing-editor',
		NUGBA_URL . 'blocks/pricing-table/assets/js/editor.js',
		[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor' ],
		NUGBA_VERSION, true
	);

	wp_register_style(
		'nuvora-pricing-style',
		NUGBA_URL . 'blocks/pricing-table/assets/css/style.css',
		[], NUGBA_VERSION
	);

	if ( WP_Block_Type_Registry::get_instance()->is_registered( 'nuvora/pricing-table' ) ) {
		return;
	}

	register_block_type( 'nuvora/pricing-table', [
		'editor_script'   => 'nuvora-pricing-editor',
		'style'           => 'nuvora-pricing-style',
		'render_callback' => 'nugba_pricing_render',
		'attributes'      => [
			'layout'         => [ 'type' => 'string',  'default' => 'style1' ],
			'featured'       => [ 'type' => 'boolean', 'default' => false ],
			'featuredLabel'  => [ 'type' => 'string',  'default' => 'Most Popular' ],
			'alignment'      => [ 'type' => 'string',  'default' => 'center' ],
			'planName'       => [ 'type' => 'string',  'default' => 'Pro Plan' ],
			'planDesc'       => [ 'type' => 'string',  'default' => 'Perfect for growing businesses' ],
			'showPlanDesc'   => [ 'type' => 'boolean', 'default' => true ],
			'currency'       => [ 'type' => 'string',  'default' => '$' ],
			'price'          => [ 'type' => 'string',  'default' => '29' ],
			'pricePeriod'    => [ 'type' => 'string',  'default' => '/month' ],
			'showPeriod'     => [ 'type' => 'boolean', 'default' => true ],
			'originalPrice'  => [ 'type' => 'string',  'default' => '' ],
			'features'       => [ 'type' => 'string',  'default' => "10 Projects\n50GB Storage\nPriority Support\nCustom Domain\nAnalytics" ],
			'featureIcon'    => [ 'type' => 'string',  'default' => 'check' ],
			'btnText'        => [ 'type' => 'string',  'default' => 'Get Started' ],
			'btnUrl'         => [ 'type' => 'string',  'default' => '#' ],
			'btnTarget'      => [ 'type' => 'boolean', 'default' => false ],
			'accentColor'    => [ 'type' => 'string',  'default' => '#6c63ff' ],
			'accentColor2'   => [ 'type' => 'string',  'default' => '#f72585' ],
			'bgColor'        => [ 'type' => 'string',  'default' => '#ffffff' ],
			'textColor'      => [ 'type' => 'string',  'default' => '#333333' ],
			'featureColor'   => [ 'type' => 'string',  'default' => '#555555' ],
			'btnTextColor'   => [ 'type' => 'string',  'default' => '#ffffff' ],
			'borderRadius'   => [ 'type' => 'number',  'default' => 16 ],
			'padding'        => [ 'type' => 'number',  'default' => 36 ],
			'priceSize'      => [ 'type' => 'number',  'default' => 56 ],
			'nameSize'       => [ 'type' => 'number',  'default' => 22 ],
			'featureSize'    => [ 'type' => 'number',  'default' => 15 ],
		],
	] );
}
endif;

nugba_register_pricing_table();

if ( ! function_exists( 'nugba_pricing_render' ) ) :
function nugba_pricing_render( $a ) {
	$features = array_filter( array_map( 'trim', explode( "\n", $a['features'] ) ) );
	$p = intval( $a['padding'] ); $r = intval( $a['borderRadius'] );

	$check_svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>';
	$arrow_svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>';
	$dot_svg    = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/></svg>';
	$star_svg   = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
	$icons_map  = [ 'check' => $check_svg, 'arrow' => $arrow_svg, 'dot' => $dot_svg, 'star' => $star_svg ];
	$feat_icon  = $icons_map[ $a['featureIcon'] ] ?? $check_svg;

	switch ( $a['layout'] ) {
		case 'style2':
			$wrap_style = "background:{$a['bgColor']};border-radius:{$r}px;padding:0;overflow:hidden;text-align:{$a['alignment']};box-shadow:0 8px 40px rgba(0,0,0,0.10);";
			break;
		case 'style3':
			$wrap_style = "background:linear-gradient(135deg,{$a['accentColor']},{$a['accentColor2']});border-radius:{$r}px;padding:{$p}px;text-align:{$a['alignment']};box-shadow:0 8px 40px rgba(0,0,0,0.18);";
			break;
		case 'style4':
			$wrap_style = "background:{$a['bgColor']};border:2px solid {$a['accentColor']};border-radius:{$r}px;padding:{$p}px;text-align:{$a['alignment']};";
			break;
		default:
			$wrap_style = "background:{$a['bgColor']};border-radius:{$r}px;padding:{$p}px;text-align:{$a['alignment']};box-shadow:0 4px 30px rgba(0,0,0,0.08);";
	}

	$is_grad  = $a['layout'] === 'style3';
	$txt      = $is_grad ? '#ffffff' : $a['textColor'];
	$feat_txt = $is_grad ? 'rgba(255,255,255,0.9)' : $a['featureColor'];
	$icon_clr = $is_grad ? '#ffffff' : $a['accentColor'];

	$badge = '';
	if ( $a['featured'] ) {
		$badge_bg = $is_grad ? 'rgba(255,255,255,0.25)' : $a['accentColor'];
		$badge = sprintf(
			'<div class="nuvora-pt-badge" style="background:%s;color:#fff;">%s</div>',
			$badge_bg, esc_html( $a['featuredLabel'] )
		);
	}

	$name_html = sprintf(
		'<h3 class="nuvora-pt-name" style="color:%s;font-size:%dpx;">%s</h3>',
		$txt, intval( $a['nameSize'] ), esc_html( $a['planName'] )
	);

	$desc_html = '';
	if ( $a['showPlanDesc'] ) {
		$desc_html = sprintf( '<p class="nuvora-pt-desc" style="color:%s;">%s</p>', $feat_txt, esc_html( $a['planDesc'] ) );
	}

	$orig = '';
	if ( $a['originalPrice'] ) {
		$orig = sprintf( '<span class="nuvora-pt-original" style="color:%s;">%s%s</span>', $feat_txt, esc_html( $a['currency'] ), esc_html( $a['originalPrice'] ) );
	}
	$period = $a['showPeriod'] ? sprintf( '<span class="nuvora-pt-period" style="color:%s;">%s</span>', $feat_txt, esc_html( $a['pricePeriod'] ) ) : '';
	$price_html = sprintf(
		'<div class="nuvora-pt-price-wrap">%s<div class="nuvora-pt-price"><span class="nuvora-pt-currency" style="color:%s;">%s</span><span class="nuvora-pt-amount" style="color:%s;font-size:%dpx;">%s</span>%s</div></div>',
		$orig, $txt, esc_html( $a['currency'] ), $txt, intval( $a['priceSize'] ), esc_html( $a['price'] ), $period
	);

	$feat_items = '';
	foreach ( $features as $feat ) {
		$feat_items .= sprintf(
			'<li class="nuvora-pt-feature" style="color:%s;font-size:%dpx;"><span class="nuvora-pt-feat-icon" style="color:%s;">%s</span>%s</li>',
			$feat_txt, intval( $a['featureSize'] ), $icon_clr, $feat_icon, esc_html( $feat )
		);
	}
	$features_html = '<ul class="nuvora-pt-features">' . $feat_items . '</ul>';

	$btn_bg     = $is_grad ? 'rgba(255,255,255,0.2)' : $a['accentColor'];
	$btn_border = $is_grad ? '2px solid rgba(255,255,255,0.5)' : 'none';
	$btn_html   = sprintf(
		'<a href="%s" class="nuvora-pt-btn" style="background:%s;color:%s;border:%s;border-radius:%dpx;" %s>%s</a>',
		esc_url( $a['btnUrl'] ),
		$btn_bg, $is_grad ? '#fff' : esc_attr( $a['btnTextColor'] ),
		$btn_border,
		max( 6, intval( $a['borderRadius'] ) - 6 ),
		$a['btnTarget'] ? 'target="_blank" rel="noopener noreferrer"' : '',
		esc_html( $a['btnText'] )
	);

	if ( $a['layout'] === 'style2' ) {
		$header = sprintf(
			'<div class="nuvora-pt-header-grad" style="background:linear-gradient(135deg,%s,%s);padding:%dpx %dpx;">%s%s%s</div><div class="nuvora-pt-body" style="padding:%dpx;">%s%s</div>',
			esc_attr( $a['accentColor'] ), esc_attr( $a['accentColor2'] ),
			intval( $p * 0.7 ), $p,
			$badge, $name_html, $price_html,
			$p, $features_html, $btn_html
		);
		return sprintf( '<div class="nuvora-pricing-block nuvora-pt-layout-%s%s" style="%s">%s</div>',
			esc_attr( $a['layout'] ), $a['featured'] ? ' nuvora-pt-featured' : '', $wrap_style, $header );
	}

	$inner = $badge . $name_html . $desc_html . $price_html . $features_html . $btn_html;
	return sprintf(
		'<div class="nuvora-pricing-block nuvora-pt-layout-%s%s" style="%s">%s</div>',
		esc_attr( $a['layout'] ), $a['featured'] ? ' nuvora-pt-featured' : '', $wrap_style, $inner
	);
}
endif;
