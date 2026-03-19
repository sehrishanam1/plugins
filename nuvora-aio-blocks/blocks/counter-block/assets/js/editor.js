/**
 * Nuvora Counter Block - Gutenberg Editor
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	const el            = element.createElement;
	const Fragment      = element.Fragment;
	const { __  }       = i18n;
	const {
		registerBlockType,
	} = blocks;
	const {
		InspectorControls,
		useBlockProps,
	} = blockEditor;
	const {
		PanelBody,
		PanelRow,
		TextControl,
		RangeControl,
		SelectControl,
		ToggleControl,
		ColorPicker,
		Popover,
		Button,
		TabPanel,
		__experimentalNumberControl: NumberControl,
	} = components;

	// ── SVG Icons ──────────────────────────────────────────────────────────────
	const ICONS = {
		smile:   '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm-3-9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm6 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm1.5 3.5c-.8 1.2-2 2-3.5 2s-2.7-.8-3.5-2h7z"/></svg>',
		users:   '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05C16.19 13.89 17 15.02 17 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
		star:    '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
		trophy:  '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5C3.9 5 3 5.9 3 7v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V18H8v2h8v-2h-3v-2.1a5.01 5.01 0 0 0 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.86 10.4 5 9.3 5 8zm14 0c0 1.3-.86 2.4-2 2.82V7h2v1z"/></svg>',
		rocket:  '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.5 2 7.5 3.5 6.6 5.6L2 14h4v4l2-2 2 2v-4h8v4l2-2 2 2v-4h4L21.4 5.6C20.5 3.5 18.5 2 16 2h-4zm0 2h4c1.8 0 3.3 1 4 2.5l.9 2.5H3.1l.9-2.5C4.7 5 6.2 4 8 4h4zm0 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg>',
		heart:   '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09A5.988 5.988 0 0 1 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
		globe:   '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm-1 17.93V18c0-.55-.45-1-1-1H8v-2c0-.55-.45-1-1-1H5.07A8.003 8.003 0 0 1 4 12c0-.34.02-.67.07-1H6c.55 0 1-.45 1-1V9c0-.55.45-1 1-1h.5c.55 0 1-.45 1-1V5.08A8.006 8.006 0 0 1 12 4c.34 0 .67.02 1 .07V6c0 .55.45 1 1 1h2v1c0 .55.45 1 1 1h1.93c.05.33.07.66.07 1 0 4.07-3.06 7.44-7 7.93z"/></svg>',
		chart:   '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M3.5 18.5l6-6 4 4L22 6.92 20.59 5.5l-7.09 8-4-4L2 17l1.5 1.5z"/></svg>',
		check:   '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>',
		diamond: '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5L2 9l10 12L22 9l-3-6zm-8.5 14.5L3.74 9.5h4.76l3 8zm2 0l3-8h4.76L13.5 17.5zM14.5 8h-5L12 4.5 14.5 8zm-6.76 0H3.5L5.5 4h4.24L7.74 8zm9.76 0L15.26 4H19.5l2 4h-4.26z"/></svg>',
		fire:    '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 0.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5 0.67zM12 20c-3.31 0-6-2.69-6-6 0-1.53.57-3.05 1.6-4.2C8.45 11.4 10.2 12 12 12c1.8 0 3.28-.7 4.43-1.87C17.38 11.37 18 12.87 18 14c0 3.31-2.69 6-6 6z"/></svg>',
		clock:   '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm.01 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>',
	};

	// ── Color Picker Helper ────────────────────────────────────────────────────
	function ColorControl( { label, value, onChange } ) {
		const [ open, setOpen ] = element.useState( false );
		return el(
			'div',
			{ style: { marginBottom: 12 } },
			el( 'p', { style: { marginBottom: 4, fontWeight: 600, fontSize: 12 } }, label ),
			el(
				'div',
				{ style: { display: 'flex', alignItems: 'center', gap: 8 } },
				el( 'button', {
					onClick: () => setOpen( ! open ),
					style: {
						width: 32, height: 32,
						borderRadius: 4,
						border: '2px solid #ddd',
						background: value,
						cursor: 'pointer',
					},
				} ),
				el( 'span', { style: { fontSize: 12, color: '#555' } }, value ),
				open && el(
					Popover,
					{ onClose: () => setOpen( false ) },
					el( ColorPicker, {
						color: value,
						onChange: ( color ) => onChange( color ),
					} )
				)
			)
		);
	}

	// ── Live Preview Component ─────────────────────────────────────────────────
	function CounterPreview( { attributes } ) {
		const {
			endNumber, prefix, suffix, title, description, showDescription,
			showIcon, icon, iconPosition, layout, alignment,
			numberColor, titleColor, descColor, iconColor, bgColor,
			borderColor, gradientFrom, gradientTo,
			numberSize, titleSize, descSize, iconSize,
			numberWeight, titleWeight,
			padding, borderRadius, borderWidth,
		} = attributes;

		const svgIcon = ICONS[ icon ] || ICONS.smile;

		// Wrapper style per layout
		let wrapperStyle = {
			display: 'block',
			boxSizing: 'border-box',
			padding: padding + 'px',
			textAlign: alignment,
			borderRadius: borderRadius + 'px',
		};

		let numColor  = numberColor;
		let titColor  = titleColor;
		let dscColor  = descColor;
		let icnColor  = iconColor;

		switch ( layout ) {
			case 'style2':
				wrapperStyle.background   = bgColor;
				wrapperStyle.border       = `${ borderWidth }px solid ${ borderColor }`;
				break;
			case 'style3':
				wrapperStyle.background = `linear-gradient(135deg, ${ gradientFrom }, ${ gradientTo })`;
				numColor = '#fff';
				titColor = 'rgba(255,255,255,0.9)';
				dscColor = 'rgba(255,255,255,0.75)';
				icnColor = 'rgba(255,255,255,0.9)';
				break;
			case 'style4':
				wrapperStyle.background  = bgColor;
				wrapperStyle.borderLeft  = `${ borderWidth }px solid ${ numberColor }`;
				wrapperStyle.textAlign   = 'left';
				wrapperStyle.boxShadow   = '0 2px 12px rgba(0,0,0,0.06)';
				break;
			case 'style5':
				wrapperStyle.borderBottom = `2px solid ${ numberColor }`;
				break;
			default: // style1
				wrapperStyle.background = bgColor;
				wrapperStyle.boxShadow  = '0 4px 24px rgba(0,0,0,0.08)';
				break;
		}

		const iconEl = showIcon
			? el( 'span', {
				style: { color: icnColor, fontSize: iconSize + 'px', display: 'inline-flex', lineHeight: 1 },
				dangerouslySetInnerHTML: { __html: svgIcon },
			} )
			: null;

		const numberEl = el( 'span', {
			style: {
				display: 'block',
				color: numColor,
				fontSize: numberSize + 'px',
				fontWeight: numberWeight,
				lineHeight: 1.1,
				letterSpacing: '-1px',
			},
		}, prefix + endNumber + suffix );

		const titleEl = el( 'span', {
			style: {
				display: 'block',
				color: titColor,
				fontSize: titleSize + 'px',
				fontWeight: titleWeight,
				textTransform: 'uppercase',
				letterSpacing: '1px',
				lineHeight: 1.4,
			},
		}, title );

		const descEl = showDescription && description
			? el( 'p', { style: { color: dscColor, fontSize: descSize + 'px', margin: '4px 0 0', lineHeight: 1.6 } }, description )
			: null;

		let inner;
		if ( layout === 'style4' ) {
			inner = el(
				'div',
				{ style: { display: 'flex', alignItems: 'center', gap: 16 } },
				iconEl,
				el( 'div', { style: { display: 'flex', flexDirection: 'column', gap: 4 } }, numberEl, titleEl, descEl )
			);
		} else {
			const top    = iconPosition === 'top'    ? iconEl : null;
			const bottom = iconPosition === 'bottom' ? iconEl : null;
			inner = el(
				'div',
				{ style: { display: 'flex', flexDirection: 'column', alignItems: alignment === 'center' ? 'center' : alignment === 'right' ? 'flex-end' : 'flex-start', gap: 8 } },
				top, numberEl, titleEl, descEl, bottom
			);
		}

		return el( 'div', { style: wrapperStyle }, inner );
	}

	// ── Register Block ─────────────────────────────────────────────────────────
	registerBlockType( 'nuvora/counter-block', {
		title:       __( 'Nuvora Counter', 'nuvora-counter-block' ),
		description: __( 'Animated counter block with multiple styles and full design controls.', 'nuvora-counter-block' ),
		category:    'nuvora-blocks',
		icon:        'chart-bar',
		keywords:    [ 'counter', 'number', 'animated', 'stats', 'nuvora' ],
		supports:    { html: false, align: [ 'wide', 'full' ] },

		attributes: {
			endNumber:       { type: 'number',  default: 250 },
			startNumber:     { type: 'number',  default: 0 },
			duration:        { type: 'number',  default: 2000 },
			prefix:          { type: 'string',  default: '' },
			suffix:          { type: 'string',  default: '+' },
			separator:       { type: 'string',  default: '' },
			decimals:        { type: 'number',  default: 0 },
			title:           { type: 'string',  default: 'Happy Clients' },
			description:     { type: 'string',  default: '' },
			showDescription: { type: 'boolean', default: false },
			showIcon:        { type: 'boolean', default: true },
			icon:            { type: 'string',  default: 'smile' },
			iconPosition:    { type: 'string',  default: 'top' },
			layout:          { type: 'string',  default: 'style1' },
			alignment:       { type: 'string',  default: 'center' },
			numberColor:     { type: 'string',  default: '#6c63ff' },
			titleColor:      { type: 'string',  default: '#333333' },
			descColor:       { type: 'string',  default: '#777777' },
			iconColor:       { type: 'string',  default: '#6c63ff' },
			bgColor:         { type: 'string',  default: '#ffffff' },
			borderColor:     { type: 'string',  default: '#6c63ff' },
			gradientFrom:    { type: 'string',  default: '#6c63ff' },
			gradientTo:      { type: 'string',  default: '#f72585' },
			numberSize:      { type: 'number',  default: 48 },
			titleSize:       { type: 'number',  default: 16 },
			descSize:        { type: 'number',  default: 14 },
			iconSize:        { type: 'number',  default: 40 },
			numberWeight:    { type: 'string',  default: '700' },
			titleWeight:     { type: 'string',  default: '500' },
			padding:         { type: 'number',  default: 30 },
			borderRadius:    { type: 'number',  default: 12 },
			borderWidth:     { type: 'number',  default: 2 },
			animationType:   { type: 'string',  default: 'ease-out' },
			enableAnimation: { type: 'boolean', default: true },
		},

		edit: function ( { attributes, setAttributes } ) {
			const blockProps = useBlockProps();
			const attr = attributes;
			const set  = ( key ) => ( val ) => setAttributes( { [ key ]: val } );

			return el(
				Fragment,
				null,

				// ── Inspector Controls ───────────────────────────────────────
				el(
					InspectorControls,
					null,

					// ── CONTENT ──────────────────────────────────────────────
					el( PanelBody, { title: __( 'Counter', 'nuvora-counter-block' ), initialOpen: true },
						el( PanelRow, null,
							el( TextControl, { label: __( 'End Number', 'nuvora-counter-block' ), type: 'number', value: attr.endNumber, onChange: ( v ) => setAttributes( { endNumber: parseInt( v ) || 0 } ) } )
						),
						el( PanelRow, null,
							el( TextControl, { label: __( 'Start Number', 'nuvora-counter-block' ), type: 'number', value: attr.startNumber, onChange: ( v ) => setAttributes( { startNumber: parseInt( v ) || 0 } ) } )
						),
						el( PanelRow, null,
							el( TextControl, { label: __( 'Prefix (e.g. $)', 'nuvora-counter-block' ), value: attr.prefix, onChange: set( 'prefix' ) } )
						),
						el( PanelRow, null,
							el( TextControl, { label: __( 'Suffix (e.g. +)', 'nuvora-counter-block' ), value: attr.suffix, onChange: set( 'suffix' ) } )
						),
						el( PanelRow, null,
							el( SelectControl, {
								label: __( 'Thousands Separator', 'nuvora-counter-block' ),
								value: attr.separator,
								options: [
									{ label: 'None', value: '' },
									{ label: 'Comma (1,000)', value: ',' },
									{ label: 'Dot (1.000)', value: '.' },
									{ label: 'Space (1 000)', value: ' ' },
								],
								onChange: set( 'separator' ),
							} )
						),
						el( PanelRow, null,
							el( RangeControl, { label: __( 'Decimal Places', 'nuvora-counter-block' ), value: attr.decimals, min: 0, max: 4, onChange: set( 'decimals' ) } )
						),
					),

					// ── TITLE / DESCRIPTION ───────────────────────────────────
					el( PanelBody, { title: __( 'Title & Description', 'nuvora-counter-block' ), initialOpen: false },
						el( PanelRow, null,
							el( TextControl, { label: __( 'Title', 'nuvora-counter-block' ), value: attr.title, onChange: set( 'title' ) } )
						),
						el( PanelRow, null,
							el( ToggleControl, { label: __( 'Show Description', 'nuvora-counter-block' ), checked: attr.showDescription, onChange: set( 'showDescription' ) } )
						),
						attr.showDescription && el( PanelRow, null,
							el( TextControl, { label: __( 'Description', 'nuvora-counter-block' ), value: attr.description, onChange: set( 'description' ) } )
						),
					),

					// ── ICON ──────────────────────────────────────────────────
					el( PanelBody, { title: __( 'Icon', 'nuvora-counter-block' ), initialOpen: false },
						el( PanelRow, null,
							el( ToggleControl, { label: __( 'Show Icon', 'nuvora-counter-block' ), checked: attr.showIcon, onChange: set( 'showIcon' ) } )
						),
						attr.showIcon && el(
							Fragment,
							null,
							el( PanelRow, null,
								el( SelectControl, {
									label: __( 'Icon', 'nuvora-counter-block' ),
									value: attr.icon,
									options: [
										{ label: '😊 Smile',   value: 'smile' },
										{ label: '👥 Users',   value: 'users' },
										{ label: '⭐ Star',    value: 'star' },
										{ label: '🏆 Trophy',  value: 'trophy' },
										{ label: '🚀 Rocket',  value: 'rocket' },
										{ label: '❤️ Heart',   value: 'heart' },
										{ label: '🌐 Globe',   value: 'globe' },
										{ label: '📈 Chart',   value: 'chart' },
										{ label: '✔️ Check',   value: 'check' },
										{ label: '💎 Diamond', value: 'diamond' },
										{ label: '🔥 Fire',    value: 'fire' },
										{ label: '🕐 Clock',   value: 'clock' },
									],
									onChange: set( 'icon' ),
								} )
							),
							el( PanelRow, null,
								el( SelectControl, {
									label: __( 'Icon Position', 'nuvora-counter-block' ),
									value: attr.iconPosition,
									options: [
										{ label: 'Top', value: 'top' },
										{ label: 'Bottom', value: 'bottom' },
									],
									onChange: set( 'iconPosition' ),
								} )
							),
							el( PanelRow, null,
								el( RangeControl, { label: __( 'Icon Size (px)', 'nuvora-counter-block' ), value: attr.iconSize, min: 16, max: 100, onChange: set( 'iconSize' ) } )
							),
						),
					),

					// ── LAYOUT ────────────────────────────────────────────────
					el( PanelBody, { title: __( 'Layout & Style', 'nuvora-counter-block' ), initialOpen: false },
						el( PanelRow, null,
							el( SelectControl, {
								label: __( 'Style', 'nuvora-counter-block' ),
								value: attr.layout,
								options: [
									{ label: 'Style 1 – Clean Card',      value: 'style1' },
									{ label: 'Style 2 – Bordered Card',   value: 'style2' },
									{ label: 'Style 3 – Gradient',        value: 'style3' },
									{ label: 'Style 4 – Icon Left',       value: 'style4' },
									{ label: 'Style 5 – Minimal',         value: 'style5' },
								],
								onChange: set( 'layout' ),
							} )
						),
						el( PanelRow, null,
							el( SelectControl, {
								label: __( 'Alignment', 'nuvora-counter-block' ),
								value: attr.alignment,
								options: [
									{ label: 'Left',   value: 'left' },
									{ label: 'Center', value: 'center' },
									{ label: 'Right',  value: 'right' },
								],
								onChange: set( 'alignment' ),
							} )
						),
						el( PanelRow, null,
							el( RangeControl, { label: __( 'Padding (px)', 'nuvora-counter-block' ), value: attr.padding, min: 0, max: 80, onChange: set( 'padding' ) } )
						),
						el( PanelRow, null,
							el( RangeControl, { label: __( 'Border Radius (px)', 'nuvora-counter-block' ), value: attr.borderRadius, min: 0, max: 60, onChange: set( 'borderRadius' ) } )
						),
						el( PanelRow, null,
							el( RangeControl, { label: __( 'Border Width (px)', 'nuvora-counter-block' ), value: attr.borderWidth, min: 1, max: 8, onChange: set( 'borderWidth' ) } )
						),
					),

					// ── COLORS ────────────────────────────────────────────────
					el( PanelBody, { title: __( 'Colors', 'nuvora-counter-block' ), initialOpen: false },
						el( ColorControl, { label: __( 'Number Color', 'nuvora-counter-block' ),   value: attr.numberColor,  onChange: set( 'numberColor' ) } ),
						el( ColorControl, { label: __( 'Title Color', 'nuvora-counter-block' ),    value: attr.titleColor,   onChange: set( 'titleColor' ) } ),
						el( ColorControl, { label: __( 'Icon Color', 'nuvora-counter-block' ),     value: attr.iconColor,    onChange: set( 'iconColor' ) } ),
						el( ColorControl, { label: __( 'Background', 'nuvora-counter-block' ),     value: attr.bgColor,      onChange: set( 'bgColor' ) } ),
						el( ColorControl, { label: __( 'Border Color', 'nuvora-counter-block' ),   value: attr.borderColor,  onChange: set( 'borderColor' ) } ),
						attr.showDescription && el( ColorControl, { label: __( 'Description Color', 'nuvora-counter-block' ), value: attr.descColor, onChange: set( 'descColor' ) } ),
						attr.layout === 'style3' && el(
							Fragment,
							null,
							el( ColorControl, { label: __( 'Gradient From', 'nuvora-counter-block' ), value: attr.gradientFrom, onChange: set( 'gradientFrom' ) } ),
							el( ColorControl, { label: __( 'Gradient To', 'nuvora-counter-block' ),   value: attr.gradientTo,   onChange: set( 'gradientTo' ) } ),
						),
					),

					// ── TYPOGRAPHY ────────────────────────────────────────────
					el( PanelBody, { title: __( 'Typography', 'nuvora-counter-block' ), initialOpen: false },
						el( PanelRow, null,
							el( RangeControl, { label: __( 'Number Size (px)', 'nuvora-counter-block' ), value: attr.numberSize, min: 20, max: 120, onChange: set( 'numberSize' ) } )
						),
						el( PanelRow, null,
							el( SelectControl, {
								label: __( 'Number Weight', 'nuvora-counter-block' ),
								value: attr.numberWeight,
								options: [
									{ label: 'Normal (400)', value: '400' },
									{ label: 'Medium (500)', value: '500' },
									{ label: 'SemiBold (600)', value: '600' },
									{ label: 'Bold (700)', value: '700' },
									{ label: 'ExtraBold (800)', value: '800' },
									{ label: 'Black (900)', value: '900' },
								],
								onChange: set( 'numberWeight' ),
							} )
						),
						el( PanelRow, null,
							el( RangeControl, { label: __( 'Title Size (px)', 'nuvora-counter-block' ), value: attr.titleSize, min: 10, max: 40, onChange: set( 'titleSize' ) } )
						),
						el( PanelRow, null,
							el( SelectControl, {
								label: __( 'Title Weight', 'nuvora-counter-block' ),
								value: attr.titleWeight,
								options: [
									{ label: 'Normal (400)', value: '400' },
									{ label: 'Medium (500)', value: '500' },
									{ label: 'SemiBold (600)', value: '600' },
									{ label: 'Bold (700)', value: '700' },
								],
								onChange: set( 'titleWeight' ),
							} )
						),
						attr.showDescription && el( PanelRow, null,
							el( RangeControl, { label: __( 'Description Size (px)', 'nuvora-counter-block' ), value: attr.descSize, min: 10, max: 24, onChange: set( 'descSize' ) } )
						),
					),

					// ── ANIMATION ─────────────────────────────────────────────
					el( PanelBody, { title: __( 'Animation', 'nuvora-counter-block' ), initialOpen: false },
						el( PanelRow, null,
							el( ToggleControl, { label: __( 'Enable Animation', 'nuvora-counter-block' ), checked: attr.enableAnimation, onChange: set( 'enableAnimation' ) } )
						),
						attr.enableAnimation && el(
							Fragment,
							null,
							el( PanelRow, null,
								el( RangeControl, { label: __( 'Duration (ms)', 'nuvora-counter-block' ), value: attr.duration, min: 500, max: 5000, step: 100, onChange: set( 'duration' ) } )
							),
							el( PanelRow, null,
								el( SelectControl, {
									label: __( 'Easing', 'nuvora-counter-block' ),
									value: attr.animationType,
									options: [
										{ label: 'Ease Out (Default)', value: 'ease-out' },
										{ label: 'Linear',             value: 'linear' },
										{ label: 'Ease In',            value: 'ease-in' },
										{ label: 'Ease In Out',        value: 'ease-in-out' },
										{ label: 'Bounce',             value: 'bounce' },
										{ label: 'Elastic',            value: 'elastic' },
									],
									onChange: set( 'animationType' ),
								} )
							),
						),
					),
				),

				// ── Block Preview ────────────────────────────────────────────
				el(
					'div',
					{ ...blockProps },
					el( CounterPreview, { attributes } )
				)
			);
		},

		save: function () {
			// Server-side rendered
			return null;
		},
	} );

} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);
