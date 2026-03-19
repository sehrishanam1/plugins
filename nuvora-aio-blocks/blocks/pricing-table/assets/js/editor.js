/**
 * Nuvora Pricing Table Block – Editor
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';
	const el = element.createElement, F = element.Fragment, { __ } = i18n;
	const { registerBlockType } = blocks;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, PanelRow, TextControl, RangeControl, SelectControl, ToggleControl, ColorPicker, Popover, TextareaControl } = components;

	function ColorControl( { label, value, onChange } ) {
		const [ open, setOpen ] = element.useState( false );
		return el( 'div', { style: { marginBottom: 12 } },
			el( 'p', { style: { marginBottom: 4, fontWeight: 600, fontSize: 12 } }, label ),
			el( 'div', { style: { display: 'flex', alignItems: 'center', gap: 8 } },
				el( 'button', { onClick: () => setOpen( !open ), style: { width: 32, height: 32, borderRadius: 4, border: '2px solid #ddd', background: value, cursor: 'pointer' } } ),
				el( 'span', { style: { fontSize: 12, color: '#555' } }, value ),
				open && el( Popover, { onClose: () => setOpen( false ) }, el( ColorPicker, { color: value, onChange } ) )
			)
		);
	}

	function PricingPreview( { a } ) {
		const features = a.features.split( '\n' ).map( s => s.trim() ).filter( Boolean );
		const isGrad = a.layout === 'style3';
		const txt = isGrad ? '#fff' : a.textColor;
		const featTxt = isGrad ? 'rgba(255,255,255,0.85)' : a.featureColor;
		const iconClr = isGrad ? '#fff' : a.accentColor;

		const checkSvg = el( 'svg', { xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, fill: 'currentColor', viewBox: '0 0 24 24' }, el( 'path', { d: 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z' } ) );

		let wrapStyle = { boxSizing: 'border-box', borderRadius: a.borderRadius + 'px', textAlign: a.alignment, position: 'relative', overflow: 'hidden' };
		switch ( a.layout ) {
			case 'style2': wrapStyle = { ...wrapStyle, background: a.bgColor, boxShadow: '0 8px 40px rgba(0,0,0,0.10)', padding: 0 }; break;
			case 'style3': wrapStyle = { ...wrapStyle, background: `linear-gradient(135deg,${a.accentColor},${a.accentColor2})`, padding: a.padding + 'px', boxShadow: '0 8px 40px rgba(0,0,0,0.18)' }; break;
			case 'style4': wrapStyle = { ...wrapStyle, background: a.bgColor, border: `2px solid ${a.accentColor}`, padding: a.padding + 'px' }; break;
			default: wrapStyle = { ...wrapStyle, background: a.bgColor, boxShadow: '0 4px 30px rgba(0,0,0,0.08)', padding: a.padding + 'px' };
		}

		const badge = a.featured ? el( 'div', { style: { display: 'inline-block', padding: '4px 14px', borderRadius: 20, fontSize: 12, fontWeight: 700, letterSpacing: 1, textTransform: 'uppercase', marginBottom: 14, background: isGrad ? 'rgba(255,255,255,0.25)' : a.accentColor, color: '#fff' } }, a.featuredLabel ) : null;
		const name  = el( 'h3', { style: { margin: '0 0 6px', fontWeight: 700, color: txt, fontSize: a.nameSize + 'px' } }, a.planName );
		const desc  = a.showPlanDesc ? el( 'p', { style: { margin: '0 0 18px', fontSize: 14, color: featTxt } }, a.planDesc ) : null;
		const orig  = a.originalPrice ? el( 'div', { style: { fontSize: 15, textDecoration: 'line-through', opacity: 0.7, color: featTxt, marginBottom: 4 } }, a.currency + a.originalPrice ) : null;
		const priceRow = el( 'div', { style: { display: 'flex', alignItems: 'flex-start', justifyContent: a.alignment === 'center' ? 'center' : a.alignment === 'right' ? 'flex-end' : 'flex-start', lineHeight: 1, marginBottom: 24 } },
			el( 'span', { style: { fontSize: '1.3em', fontWeight: 700, color: txt, paddingTop: 8 } }, a.currency ),
			el( 'span', { style: { fontSize: a.priceSize + 'px', fontWeight: 800, color: txt, letterSpacing: '-2px' } }, a.price ),
			a.showPeriod ? el( 'span', { style: { fontSize: 14, color: featTxt, alignSelf: 'flex-end', paddingBottom: 6, marginLeft: 2 } }, a.pricePeriod ) : null
		);
		const featList = el( 'ul', { style: { listStyle: 'none', padding: 0, margin: '0 0 28px', textAlign: 'left' } },
			...features.map( f => el( 'li', { style: { display: 'flex', alignItems: 'center', gap: 10, padding: '7px 0', borderBottom: `1px solid ${isGrad ? 'rgba(255,255,255,0.15)' : 'rgba(0,0,0,0.06)'}`, fontSize: a.featureSize + 'px', color: featTxt } },
				el( 'span', { style: { color: iconClr, display: 'inline-flex' } }, checkSvg ), f
			) )
		);
		const btn = el( 'a', { href: '#', style: { display: 'block', padding: '14px 24px', textAlign: 'center', fontWeight: 700, fontSize: 15, textDecoration: 'none', borderRadius: Math.max( 6, a.borderRadius - 6 ) + 'px', background: isGrad ? 'rgba(255,255,255,0.2)' : a.accentColor, color: isGrad ? '#fff' : a.btnTextColor, border: isGrad ? '2px solid rgba(255,255,255,0.5)' : 'none' } }, a.btnText );

		if ( a.layout === 'style2' ) {
			return el( 'div', { style: wrapStyle },
				el( 'div', { style: { background: `linear-gradient(135deg,${a.accentColor},${a.accentColor2})`, padding: `${Math.round( a.padding * 0.7 )}px ${a.padding}px`, textAlign: a.alignment } }, badge, name, el( 'div', null, orig, priceRow ) ),
				el( 'div', { style: { padding: a.padding + 'px' } }, featList, btn )
			);
		}
		return el( 'div', { style: wrapStyle }, badge, name, desc, el( 'div', null, orig, priceRow ), featList, btn );
	}

	registerBlockType( 'nuvora/pricing-table', {
		title: __( 'Nuvora Pricing Table', 'nuvora-aio-blocks' ),
		description: __( 'Fully customizable pricing table with 4 styles, badge, features list, and CTA button.', 'nuvora-aio-blocks' ),
		category: 'nuvora-blocks', icon: 'tag',
		keywords: [ 'pricing', 'table', 'plan', 'nuvora' ],
		supports: { html: false },

		attributes: {
			layout: { type: 'string', default: 'style1' }, featured: { type: 'boolean', default: false }, featuredLabel: { type: 'string', default: 'Most Popular' }, alignment: { type: 'string', default: 'center' },
			planName: { type: 'string', default: 'Pro Plan' }, planDesc: { type: 'string', default: 'Perfect for growing businesses' }, showPlanDesc: { type: 'boolean', default: true },
			currency: { type: 'string', default: '$' }, price: { type: 'string', default: '29' }, pricePeriod: { type: 'string', default: '/month' }, showPeriod: { type: 'boolean', default: true }, originalPrice: { type: 'string', default: '' },
			features: { type: 'string', default: "10 Projects\n50GB Storage\nPriority Support\nCustom Domain\nAnalytics" }, featureIcon: { type: 'string', default: 'check' },
			btnText: { type: 'string', default: 'Get Started' }, btnUrl: { type: 'string', default: '#' }, btnTarget: { type: 'boolean', default: false },
			accentColor: { type: 'string', default: '#6c63ff' }, accentColor2: { type: 'string', default: '#f72585' }, bgColor: { type: 'string', default: '#ffffff' }, textColor: { type: 'string', default: '#333333' }, featureColor: { type: 'string', default: '#555555' }, btnTextColor: { type: 'string', default: '#ffffff' },
			borderRadius: { type: 'number', default: 16 }, padding: { type: 'number', default: 36 }, priceSize: { type: 'number', default: 56 }, nameSize: { type: 'number', default: 22 }, featureSize: { type: 'number', default: 15 },
		},

		edit( { attributes: a, setAttributes: set } ) {
			const blockProps = useBlockProps();
			const s = k => v => set( { [k]: v } );
			return el( F, null,
				el( InspectorControls, null,
					el( PanelBody, { title: __( 'Plan', 'nuvora-aio-blocks' ), initialOpen: true },
						el( PanelRow, null, el( SelectControl, { label: 'Style', value: a.layout, options: [ { label: 'Style 1 – Clean Card', value: 'style1' }, { label: 'Style 2 – Gradient Header', value: 'style2' }, { label: 'Style 3 – Full Gradient', value: 'style3' }, { label: 'Style 4 – Outline', value: 'style4' } ], onChange: s( 'layout' ) } ) ),
						el( PanelRow, null, el( SelectControl, { label: 'Alignment', value: a.alignment, options: [ { label: 'Left', value: 'left' }, { label: 'Center', value: 'center' }, { label: 'Right', value: 'right' } ], onChange: s( 'alignment' ) } ) ),
						el( PanelRow, null, el( ToggleControl, { label: 'Featured Plan', checked: a.featured, onChange: s( 'featured' ) } ) ),
						a.featured && el( PanelRow, null, el( TextControl, { label: 'Badge Label', value: a.featuredLabel, onChange: s( 'featuredLabel' ) } ) ),
						el( PanelRow, null, el( TextControl, { label: 'Plan Name', value: a.planName, onChange: s( 'planName' ) } ) ),
						el( PanelRow, null, el( ToggleControl, { label: 'Show Description', checked: a.showPlanDesc, onChange: s( 'showPlanDesc' ) } ) ),
						a.showPlanDesc && el( PanelRow, null, el( TextControl, { label: 'Description', value: a.planDesc, onChange: s( 'planDesc' ) } ) ),
					),
					el( PanelBody, { title: __( 'Pricing', 'nuvora-aio-blocks' ), initialOpen: false },
						el( PanelRow, null, el( TextControl, { label: 'Currency Symbol', value: a.currency, onChange: s( 'currency' ) } ) ),
						el( PanelRow, null, el( TextControl, { label: 'Price', value: a.price, onChange: s( 'price' ) } ) ),
						el( PanelRow, null, el( ToggleControl, { label: 'Show Period', checked: a.showPeriod, onChange: s( 'showPeriod' ) } ) ),
						a.showPeriod && el( PanelRow, null, el( TextControl, { label: 'Period (e.g. /month)', value: a.pricePeriod, onChange: s( 'pricePeriod' ) } ) ),
						el( PanelRow, null, el( TextControl, { label: 'Original Price (optional, for strikethrough)', value: a.originalPrice, onChange: s( 'originalPrice' ) } ) ),
					),
					el( PanelBody, { title: __( 'Features', 'nuvora-aio-blocks' ), initialOpen: false },
						el( PanelRow, null, el( 'div', { style: { width: '100%' } }, el( TextareaControl, { label: 'Features (one per line)', value: a.features, rows: 8, onChange: s( 'features' ) } ) ) ),
						el( PanelRow, null, el( SelectControl, { label: 'Feature Icon', value: a.featureIcon, options: [ { label: '✔ Check', value: 'check' }, { label: '› Arrow', value: 'arrow' }, { label: '• Dot', value: 'dot' }, { label: '★ Star', value: 'star' } ], onChange: s( 'featureIcon' ) } ) ),
					),
					el( PanelBody, { title: __( 'Button', 'nuvora-aio-blocks' ), initialOpen: false },
						el( PanelRow, null, el( TextControl, { label: 'Button Text', value: a.btnText, onChange: s( 'btnText' ) } ) ),
						el( PanelRow, null, el( TextControl, { label: 'Button URL', value: a.btnUrl, onChange: s( 'btnUrl' ) } ) ),
						el( PanelRow, null, el( ToggleControl, { label: 'Open in New Tab', checked: a.btnTarget, onChange: s( 'btnTarget' ) } ) ),
					),
					el( PanelBody, { title: __( 'Colors', 'nuvora-aio-blocks' ), initialOpen: false },
						el( ColorControl, { label: 'Accent Color', value: a.accentColor, onChange: s( 'accentColor' ) } ),
						el( ColorControl, { label: 'Accent Color 2 (Gradient)', value: a.accentColor2, onChange: s( 'accentColor2' ) } ),
						el( ColorControl, { label: 'Background', value: a.bgColor, onChange: s( 'bgColor' ) } ),
						el( ColorControl, { label: 'Text Color', value: a.textColor, onChange: s( 'textColor' ) } ),
						el( ColorControl, { label: 'Feature Text Color', value: a.featureColor, onChange: s( 'featureColor' ) } ),
						el( ColorControl, { label: 'Button Text Color', value: a.btnTextColor, onChange: s( 'btnTextColor' ) } ),
					),
					el( PanelBody, { title: __( 'Sizes', 'nuvora-aio-blocks' ), initialOpen: false },
						el( PanelRow, null, el( RangeControl, { label: 'Price Size (px)', value: a.priceSize, min: 30, max: 100, onChange: s( 'priceSize' ) } ) ),
						el( PanelRow, null, el( RangeControl, { label: 'Plan Name Size (px)', value: a.nameSize, min: 14, max: 40, onChange: s( 'nameSize' ) } ) ),
						el( PanelRow, null, el( RangeControl, { label: 'Feature Size (px)', value: a.featureSize, min: 12, max: 22, onChange: s( 'featureSize' ) } ) ),
						el( PanelRow, null, el( RangeControl, { label: 'Padding (px)', value: a.padding, min: 16, max: 80, onChange: s( 'padding' ) } ) ),
						el( PanelRow, null, el( RangeControl, { label: 'Border Radius (px)', value: a.borderRadius, min: 0, max: 40, onChange: s( 'borderRadius' ) } ) ),
					),
				),
				el( 'div', { ...blockProps }, el( PricingPreview, { a } ) )
			);
		},
		save: () => null,
	} );

} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
