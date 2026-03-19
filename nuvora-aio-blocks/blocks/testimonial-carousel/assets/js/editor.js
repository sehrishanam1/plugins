/**
 * Nuvora Testimonial Carousel Block – Editor
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';
	const el = element.createElement, F = element.Fragment, { __ } = i18n;
	const { registerBlockType } = blocks;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, PanelRow, RangeControl, SelectControl, ToggleControl, TextControl, TextareaControl, ColorPicker, Popover, Button } = components;

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

	function TestimonialEditor( { attributes, setAttributes } ) {
		const a = attributes;
		const testimonials = JSON.parse( a.testimonials || '[]' );

		function updateTestimonial( index, key, value ) {
			const updated = [ ...testimonials ];
			updated[ index ] = { ...updated[ index ], [ key ]: value };
			setAttributes( { testimonials: JSON.stringify( updated ) } );
		}

		function addTestimonial() {
			const updated = [ ...testimonials, { name: 'New Person', role: 'Role', quote: 'Enter testimonial quote here.', rating: 5, initials: 'NP', accentColor: '#6c63ff' } ];
			setAttributes( { testimonials: JSON.stringify( updated ) } );
		}

		function removeTestimonial( index ) {
			const updated = testimonials.filter( ( _, i ) => i !== index );
			setAttributes( { testimonials: JSON.stringify( updated ) } );
		}

		const starSvg = el( 'svg', { xmlns: 'http://www.w3.org/2000/svg', width: 20, height: 20, fill: 'currentColor', viewBox: '0 0 24 24' }, el( 'path', { d: 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z' } ) );

		// Preview of first slide
		const t = testimonials[0] || {};
		const isGrad = false;

		const quoteIcon = el( 'svg', { xmlns: 'http://www.w3.org/2000/svg', width: 36, height: 36, fill: 'currentColor', viewBox: '0 0 24 24', style: { opacity: 0.12, color: t.accentColor || '#6c63ff' } }, el( 'path', { d: 'M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z' } ) );

		let cardStyle = { boxSizing: 'border-box', padding: a.padding + 'px', borderRadius: a.borderRadius + 'px', textAlign: 'center', background: a.bgColor };
		if ( a.layout === 'style1' ) cardStyle.boxShadow = '0 4px 30px rgba(0,0,0,0.08)';
		if ( a.layout === 'style2' ) cardStyle = { ...cardStyle, border: '1px solid rgba(0,0,0,0.08)' };
		if ( a.layout === 'style3' ) cardStyle = { ...cardStyle, boxShadow: '0 8px 32px rgba(0,0,0,0.08)', border: '1px solid rgba(255,255,255,0.6)' };
		if ( a.layout === 'style4' ) cardStyle = { ...cardStyle, borderLeft: `4px solid ${t.accentColor || '#6c63ff'}`, textAlign: 'left' };

		const preview = el( 'div', { style: { padding: '8px' } },
			el( 'div', { style: cardStyle },
				quoteIcon,
				el( 'p', { style: { color: a.quoteColor, fontSize: a.quoteSize + 'px', fontStyle: 'italic', lineHeight: 1.7, margin: '0 0 16px' } }, t.quote || '' ),
				a.showRating && el( 'div', { style: { display: 'flex', justifyContent: a.layout === 'style4' ? 'flex-start' : 'center', gap: 2, marginBottom: 16 } },
					...[ 1,2,3,4,5 ].map( i => el( 'span', { style: { color: i <= ( t.rating || 5 ) ? a.starColor : '#ccc' } }, starSvg ) )
				),
				el( 'div', { style: { display: 'flex', alignItems: 'center', gap: 12, justifyContent: a.layout === 'style4' ? 'flex-start' : 'center', flexDirection: a.layout === 'style4' ? 'row' : 'column', marginTop: 16 } },
					el( 'div', { style: { width: a.avatarSize + 'px', height: a.avatarSize + 'px', borderRadius: '50%', background: t.accentColor || '#6c63ff', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 700, fontSize: Math.round( a.avatarSize * 0.35 ) + 'px', flexShrink: 0 } }, t.initials || '?' ),
					el( 'div', { style: { display: 'flex', flexDirection: 'column', gap: 2, textAlign: a.layout === 'style4' ? 'left' : 'center' } },
						el( 'strong', { style: { color: a.textColor, fontSize: a.nameSize + 'px', display: 'block' } }, t.name || '' ),
						el( 'span', { style: { color: a.subColor, fontSize: 13 } }, t.role || '' )
					)
				)
			),
			testimonials.length > 1 && el( 'p', { style: { textAlign: 'center', fontSize: 12, color: '#888', marginTop: 8 } }, `+${testimonials.length - 1} more testimonial(s) — configure in sidebar` )
		);

		return el( F, null,
			el( InspectorControls, null,
				el( PanelBody, { title: __( 'Layout', 'nuvora-aio-blocks' ), initialOpen: true },
					el( PanelRow, null, el( SelectControl, { label: 'Style', value: a.layout, options: [ { label: 'Style 1 – Card with Shadow', value: 'style1' }, { label: 'Style 2 – Bordered', value: 'style2' }, { label: 'Style 3 – Glass', value: 'style3' }, { label: 'Style 4 – Left Accent', value: 'style4' } ], onChange: v => setAttributes( { layout: v } ) } ) ),
					el( PanelRow, null, el( ToggleControl, { label: 'Autoplay', checked: a.autoplay, onChange: v => setAttributes( { autoplay: v } ) } ) ),
					a.autoplay && el( PanelRow, null, el( RangeControl, { label: 'Autoplay Speed (ms)', value: a.autoplaySpeed, min: 1000, max: 10000, step: 500, onChange: v => setAttributes( { autoplaySpeed: v } ) } ) ),
					el( PanelRow, null, el( ToggleControl, { label: 'Show Arrows', checked: a.showArrows, onChange: v => setAttributes( { showArrows: v } ) } ) ),
					el( PanelRow, null, el( ToggleControl, { label: 'Show Dots', checked: a.showDots, onChange: v => setAttributes( { showDots: v } ) } ) ),
					el( PanelRow, null, el( ToggleControl, { label: 'Show Star Rating', checked: a.showRating, onChange: v => setAttributes( { showRating: v } ) } ) ),
				),
				el( PanelBody, { title: __( 'Testimonials', 'nuvora-aio-blocks' ), initialOpen: false },
					...testimonials.map( ( t, i ) =>
						el( 'div', { key: i, style: { border: '1px solid #eee', borderRadius: 8, padding: 12, marginBottom: 12 } },
							el( 'strong', { style: { display: 'block', marginBottom: 8 } }, `Testimonial ${i + 1}` ),
							el( TextControl, { label: 'Name', value: t.name, onChange: v => updateTestimonial( i, 'name', v ) } ),
							el( TextControl, { label: 'Role / Company', value: t.role, onChange: v => updateTestimonial( i, 'role', v ) } ),
							el( TextControl, { label: 'Initials (for avatar)', value: t.initials, onChange: v => updateTestimonial( i, 'initials', v ) } ),
							el( 'div', { style: { marginBottom: 8 } }, el( TextareaControl, { label: 'Quote', value: t.quote, rows: 3, onChange: v => updateTestimonial( i, 'quote', v ) } ) ),
							el( RangeControl, { label: 'Rating (stars)', value: t.rating, min: 1, max: 5, onChange: v => updateTestimonial( i, 'rating', v ) } ),
							el( ColorControl, { label: 'Accent Color', value: t.accentColor, onChange: v => updateTestimonial( i, 'accentColor', v ) } ),
							el( Button, { isDestructive: true, isSmall: true, onClick: () => removeTestimonial( i ) }, 'Remove' )
						)
					),
					el( Button, { isPrimary: true, onClick: addTestimonial, style: { width: '100%' } }, '+ Add Testimonial' )
				),
				el( PanelBody, { title: __( 'Colors', 'nuvora-aio-blocks' ), initialOpen: false },
					el( ColorControl, { label: 'Card Background', value: a.bgColor, onChange: v => setAttributes( { bgColor: v } ) } ),
					el( ColorControl, { label: 'Quote Text Color', value: a.quoteColor, onChange: v => setAttributes( { quoteColor: v } ) } ),
					el( ColorControl, { label: 'Name Color', value: a.textColor, onChange: v => setAttributes( { textColor: v } ) } ),
					el( ColorControl, { label: 'Role Color', value: a.subColor, onChange: v => setAttributes( { subColor: v } ) } ),
					el( ColorControl, { label: 'Star Color', value: a.starColor, onChange: v => setAttributes( { starColor: v } ) } ),
					el( ColorControl, { label: 'Dot Color', value: a.dotColor, onChange: v => setAttributes( { dotColor: v } ) } ),
				),
				el( PanelBody, { title: __( 'Sizes', 'nuvora-aio-blocks' ), initialOpen: false },
					el( PanelRow, null, el( RangeControl, { label: 'Quote Size (px)', value: a.quoteSize, min: 12, max: 28, onChange: v => setAttributes( { quoteSize: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Name Size (px)', value: a.nameSize, min: 12, max: 28, onChange: v => setAttributes( { nameSize: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Avatar Size (px)', value: a.avatarSize, min: 32, max: 100, onChange: v => setAttributes( { avatarSize: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Padding (px)', value: a.padding, min: 16, max: 60, onChange: v => setAttributes( { padding: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Border Radius (px)', value: a.borderRadius, min: 0, max: 32, onChange: v => setAttributes( { borderRadius: v } ) } ) ),
				),
			),
			preview
		);
	}

	registerBlockType( 'nuvora/testimonial-carousel', {
		title: __( 'Nuvora Testimonial Carousel', 'nuvora-aio-blocks' ),
		description: __( 'Animated testimonial carousel with avatars, star ratings, autoplay, and 4 styles.', 'nuvora-aio-blocks' ),
		category: 'nuvora-blocks', icon: 'format-quote',
		keywords: [ 'testimonial', 'carousel', 'review', 'slider', 'nuvora' ],
		supports: { html: false },
		attributes: {
			testimonials: { type: 'string', default: '[{"name":"Sarah Johnson","role":"CEO, TechCorp","quote":"This product completely transformed how our team works. Absolutely outstanding quality and support.","rating":5,"initials":"SJ","accentColor":"#6c63ff"},{"name":"Mark Williams","role":"Designer","quote":"The best tool I have used in years. Clean, intuitive, and powerful. Highly recommend to everyone.","rating":5,"initials":"MW","accentColor":"#f72585"},{"name":"Emily Chen","role":"Developer","quote":"Incredible value for money. The features are exactly what we needed and setup was a breeze.","rating":4,"initials":"EC","accentColor":"#06d6a0"}]' },
			layout: { type: 'string', default: 'style1' }, autoplay: { type: 'boolean', default: true }, autoplaySpeed: { type: 'number', default: 4000 },
			showDots: { type: 'boolean', default: true }, showArrows: { type: 'boolean', default: true }, showRating: { type: 'boolean', default: true },
			bgColor: { type: 'string', default: '#ffffff' }, textColor: { type: 'string', default: '#333333' }, subColor: { type: 'string', default: '#777777' }, quoteColor: { type: 'string', default: '#444444' }, starColor: { type: 'string', default: '#f59e0b' }, dotColor: { type: 'string', default: '#6c63ff' },
			borderRadius: { type: 'number', default: 16 }, padding: { type: 'number', default: 32 }, quoteSize: { type: 'number', default: 16 }, nameSize: { type: 'number', default: 17 }, avatarSize: { type: 'number', default: 52 },
		},
		edit( props ) { return el( TestimonialEditor, props ); },
		save: () => null,
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
