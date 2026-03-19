/**
 * Nuvora Icon Box Block – Gutenberg Editor
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	const el              = element.createElement;
	const { __ }          = i18n;
	const { registerBlockType } = blocks;
	const { InspectorControls, useBlockProps } = blockEditor;
	const {
		PanelBody, PanelRow,
		TextControl, TextareaControl,
		RangeControl, SelectControl,
		Button, ColorPicker, Popover,
	} = components;

	// ── SVG Icons ────────────────────────────────────────────────────────────
	const ICONS = {
		star:      '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
		rocket:    '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C9.5 2 7.5 3.5 6.6 5.6L2 14h4v4l2-2 2 2v-4h8v4l2-2 2 2v-4h4L21.4 5.6C20.5 3.5 18.5 2 16 2h-4zm0 2h4c1.8 0 3.3 1 4 2.5l.9 2.5H3.1l.9-2.5C4.7 5 6.2 4 8 4h4zm0 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/></svg>',
		heart:     '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09A5.988 5.988 0 0 1 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
		check:     '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>',
		shield:    '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>',
		globe:     '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm-1 17.93V18c0-.55-.45-1-1-1H8v-2c0-.55-.45-1-1-1H5.07A8.003 8.003 0 0 1 4 12c0-.34.02-.67.07-1H6c.55 0 1-.45 1-1V9c0-.55.45-1 1-1h.5c.55 0 1-.45 1-1V5.08A8.006 8.006 0 0 1 12 4c.34 0 .67.02 1 .07V6c0 .55.45 1 1 1h2v1c0 .55.45 1 1 1h1.93c.05.33.07.66.07 1 0 4.07-3.06 7.44-7 7.93z"/></svg>',
		users:     '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05C16.19 13.89 17 15.02 17 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
		chart:     '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M3.5 18.5l6-6 4 4L22 6.92 20.59 5.5l-7.09 8-4-4L2 17l1.5 1.5z"/></svg>',
		diamond:   '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5L2 9l10 12L22 9l-3-6zm-8.5 14.5L3.74 9.5h4.76l3 8zm2 0l3-8h4.76L13.5 17.5zM14.5 8h-5L12 4.5 14.5 8zm-6.76 0H3.5L5.5 4h4.24L7.74 8zm9.76 0L15.26 4H19.5l2 4h-4.26z"/></svg>',
		lightning: '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg>',
		trophy:    '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5C3.9 5 3 5.9 3 7v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V18H8v2h8v-2h-3v-2.1a5.01 5.01 0 0 0 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.86 10.4 5 9.3 5 8zm14 0c0 1.3-.86 2.4-2 2.82V7h2v1z"/></svg>',
		smile:     '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm-3-9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm6 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm1.5 3.5c-.8 1.2-2 2-3.5 2s-2.7-.8-3.5-2h7z"/></svg>',
		fire:      '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM12 20c-3.31 0-6-2.69-6-6 0-1.53.57-3.05 1.6-4.2C8.45 11.4 10.2 12 12 12c1.8 0 3.28-.7 4.43-1.87C17.38 11.37 18 12.87 18 14c0 3.31-2.69 6-6 6z"/></svg>',
		clock:     '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm.01 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>',
	};

	const ICON_KEYS = Object.keys( ICONS );

	// ── Color Picker Helper ──────────────────────────────────────────────────
	function ColorControl( { label, value, onChange } ) {
		const [ open, setOpen ] = element.useState( false );
		return el( 'div', { style: { marginBottom: 12 } },
			el( 'p', { style: { marginBottom: 4, fontWeight: 600, fontSize: 12 } }, label ),
			el( 'div', { style: { display: 'flex', alignItems: 'center', gap: 8 } },
				el( 'button', {
					onClick: () => setOpen( ! open ),
					style: { width: 32, height: 32, borderRadius: 4, border: '2px solid #ddd', background: value, cursor: 'pointer' },
				} ),
				el( 'span', { style: { fontSize: 12, color: '#555' } }, value ),
				open && el( Popover, { onClose: () => setOpen( false ) },
					el( ColorPicker, { color: value, onChange: ( c ) => onChange( c ) } )
				)
			)
		);
	}

	// ── Icon Picker ──────────────────────────────────────────────────────────
	function IconPicker( { value, onChange } ) {
		return el( 'div', { style: { marginBottom: 12 } },
			el( 'p', { style: { marginBottom: 6, fontWeight: 600, fontSize: 12 } }, __( 'Icon', 'nuvora-aio-blocks' ) ),
			el( 'div', { style: { display: 'flex', flexWrap: 'wrap', gap: 6 } },
				ICON_KEYS.map( key =>
					el( 'button', {
						key,
						title: key,
						onClick: () => onChange( key ),
						style: {
							width: 36, height: 36,
							display: 'flex', alignItems: 'center', justifyContent: 'center',
							border: value === key ? '2px solid #6c63ff' : '1px solid #ddd',
							borderRadius: 6,
							background: value === key ? '#ede9ff' : '#fafafa',
							cursor: 'pointer',
							fontSize: 18,
							color: value === key ? '#6c63ff' : '#444',
						},
						dangerouslySetInnerHTML: { __html: ICONS[ key ] },
					} )
				)
			)
		);
	}

	// ── Live Preview ─────────────────────────────────────────────────────────
	function IconBoxPreview( { attributes } ) {
		const {
			layout, columns, iconPosition, alignment,
			iconSize, iconShape, iconColor, iconBg,
			headingSize, headingColor, descSize, descColor,
			boxBg, boxBorderColor, boxBorderRadius, boxPadding,
			accentColor, accentColor2, items,
		} = attributes;

		const parsedItems = ( () => { try { return JSON.parse( items ); } catch(e) { return []; } } )();

		const shapeRadius = { circle: '50%', rounded: '12px', square: '0', none: '0' }[ iconShape ] || '50%';
		const iconWrapSize = iconSize + 24;

		const isLeftRight = iconPosition === 'left' || iconPosition === 'right';
		const flexDir = iconPosition === 'left' ? 'row' : iconPosition === 'right' ? 'row-reverse' : 'column';
		const alignItems = isLeftRight ? 'flex-start' : alignment === 'center' ? 'center' : 'flex-start';

		function getBoxStyle() {
			const base = { borderRadius: boxBorderRadius, padding: boxPadding, boxSizing: 'border-box', height: '100%' };
			switch ( layout ) {
				case 'style2': return { ...base, background: boxBg, borderTop: `4px solid ${accentColor}`, boxShadow: '0 4px 24px rgba(0,0,0,0.07)' };
				case 'style3': return { ...base, background: `linear-gradient(135deg,${accentColor},${accentColor2})` };
				case 'style4': return { ...base, background: boxBg, borderLeft: `4px solid ${accentColor}` };
				default:       return { ...base, background: boxBg, border: `1px solid ${boxBorderColor}` };
			}
		}

		const isGrad = layout === 'style3';
		const hColor = isGrad ? '#ffffff' : headingColor;
		const dColor = isGrad ? 'rgba(255,255,255,0.85)' : descColor;
		const iColor = isGrad ? '#ffffff' : iconColor;
		const iBg    = isGrad ? 'rgba(255,255,255,0.2)' : iconBg;

		const colsNum = Math.max( 1, Math.min( 4, columns ) );
		const gridStyle = { display: 'grid', gridTemplateColumns: `repeat(${colsNum},1fr)`, gap: 20 };

		return el( 'div', { className: `nugba-icon-box-block nugba-ib-layout-${layout}` },
			el( 'div', { style: gridStyle },
				parsedItems.map( ( item, idx ) =>
					el( 'div', { key: idx, style: getBoxStyle() },
						el( 'div', { style: { display: 'flex', flexDirection: flexDir, alignItems, gap: isLeftRight ? 18 : 14, textAlign: isLeftRight ? 'left' : alignment } },
							el( 'span', {
								style: {
									width: iconWrapSize, height: iconWrapSize, flexShrink: 0,
									display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
									background: iconShape === 'none' ? 'transparent' : iBg,
									borderRadius: shapeRadius,
								},
								dangerouslySetInnerHTML: {
									__html: `<span style="color:${iColor};font-size:${iconSize}px;display:flex;">${ICONS[ item.icon ] || ICONS.star}</span>`,
								},
							} ),
							el( 'div', { style: { flex: 1 } },
								el( 'h3', { style: { color: hColor, fontSize: headingSize, fontWeight: 700, margin: '0 0 8px', lineHeight: 1.3 } }, item.heading ),
								el( 'p',  { style: { color: dColor, fontSize: descSize, margin: 0, lineHeight: 1.7 } }, item.description )
							)
						)
					)
				)
			)
		);
	}

	// ── Main Edit Component ──────────────────────────────────────────────────
	function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const [ openItemIdx, setOpenItemIdx ] = element.useState( null );

		const parsedItems = ( () => { try { return JSON.parse( attributes.items ); } catch(e) { return []; } } )();

		function updateItems( newItems ) {
			setAttributes( { items: JSON.stringify( newItems ) } );
		}

		function addItem() {
			const newItems = [ ...parsedItems, { icon: 'star', heading: 'New Feature', description: 'Describe this feature here.' } ];
			updateItems( newItems );
			setOpenItemIdx( newItems.length - 1 );
		}

		function removeItem( idx ) {
			const newItems = parsedItems.filter( ( _, i ) => i !== idx );
			updateItems( newItems );
			if ( openItemIdx === idx ) setOpenItemIdx( null );
		}

		function updateItem( idx, key, val ) {
			const newItems = parsedItems.map( ( item, i ) => i === idx ? { ...item, [key]: val } : item );
			updateItems( newItems );
		}

		function moveItem( idx, dir ) {
			const newItems = [ ...parsedItems ];
			const target = idx + dir;
			if ( target < 0 || target >= newItems.length ) return;
			[ newItems[ idx ], newItems[ target ] ] = [ newItems[ target ], newItems[ idx ] ];
			updateItems( newItems );
		}

		return el( 'div', blockProps,
			// ── Sidebar Controls ──────────────────────────────────────────
			el( InspectorControls, null,

				// Layout Panel
				el( PanelBody, { title: __( 'Layout', 'nuvora-aio-blocks' ), initialOpen: true },
					el( SelectControl, {
						label: __( 'Card Style', 'nuvora-aio-blocks' ),
						value: attributes.layout,
						options: [
							{ value: 'style1', label: __( 'Style 1 – Clean Card', 'nuvora-aio-blocks' ) },
							{ value: 'style2', label: __( 'Style 2 – Top Color Bar', 'nuvora-aio-blocks' ) },
							{ value: 'style3', label: __( 'Style 3 – Gradient', 'nuvora-aio-blocks' ) },
							{ value: 'style4', label: __( 'Style 4 – Left Border', 'nuvora-aio-blocks' ) },
						],
						onChange: ( v ) => setAttributes( { layout: v } ),
					} ),
					el( SelectControl, {
						label: __( 'Columns', 'nuvora-aio-blocks' ),
						value: String( attributes.columns ),
						options: [
							{ value: '1', label: '1 Column' },
							{ value: '2', label: '2 Columns' },
							{ value: '3', label: '3 Columns' },
							{ value: '4', label: '4 Columns' },
						],
						onChange: ( v ) => setAttributes( { columns: parseInt( v ) } ),
					} ),
					el( SelectControl, {
						label: __( 'Icon Position', 'nuvora-aio-blocks' ),
						value: attributes.iconPosition,
						options: [
							{ value: 'top',   label: __( 'Top', 'nuvora-aio-blocks' ) },
							{ value: 'left',  label: __( 'Left', 'nuvora-aio-blocks' ) },
							{ value: 'right', label: __( 'Right', 'nuvora-aio-blocks' ) },
						],
						onChange: ( v ) => setAttributes( { iconPosition: v } ),
					} ),
					el( SelectControl, {
						label: __( 'Text Alignment', 'nuvora-aio-blocks' ),
						value: attributes.alignment,
						options: [
							{ value: 'left',   label: __( 'Left', 'nuvora-aio-blocks' ) },
							{ value: 'center', label: __( 'Center', 'nuvora-aio-blocks' ) },
							{ value: 'right',  label: __( 'Right', 'nuvora-aio-blocks' ) },
						],
						onChange: ( v ) => setAttributes( { alignment: v } ),
					} ),
				),

				// Icon Styling Panel
				el( PanelBody, { title: __( 'Icon Style', 'nuvora-aio-blocks' ), initialOpen: false },
					el( RangeControl, {
						label: __( 'Icon Size (px)', 'nuvora-aio-blocks' ),
						value: attributes.iconSize,
						min: 16, max: 96,
						onChange: ( v ) => setAttributes( { iconSize: v } ),
					} ),
					el( SelectControl, {
						label: __( 'Icon Shape', 'nuvora-aio-blocks' ),
						value: attributes.iconShape,
						options: [
							{ value: 'circle',  label: __( 'Circle', 'nuvora-aio-blocks' ) },
							{ value: 'rounded', label: __( 'Rounded Square', 'nuvora-aio-blocks' ) },
							{ value: 'square',  label: __( 'Square', 'nuvora-aio-blocks' ) },
							{ value: 'none',    label: __( 'None (no background)', 'nuvora-aio-blocks' ) },
						],
						onChange: ( v ) => setAttributes( { iconShape: v } ),
					} ),
					el( ColorControl, { label: __( 'Icon Color', 'nuvora-aio-blocks' ), value: attributes.iconColor, onChange: ( v ) => setAttributes( { iconColor: v } ) } ),
					el( ColorControl, { label: __( 'Icon Background', 'nuvora-aio-blocks' ), value: attributes.iconBg, onChange: ( v ) => setAttributes( { iconBg: v } ) } ),
				),

				// Text Styling Panel
				el( PanelBody, { title: __( 'Text Style', 'nuvora-aio-blocks' ), initialOpen: false },
					el( RangeControl, {
						label: __( 'Heading Size (px)', 'nuvora-aio-blocks' ),
						value: attributes.headingSize,
						min: 12, max: 48,
						onChange: ( v ) => setAttributes( { headingSize: v } ),
					} ),
					el( ColorControl, { label: __( 'Heading Color', 'nuvora-aio-blocks' ), value: attributes.headingColor, onChange: ( v ) => setAttributes( { headingColor: v } ) } ),
					el( RangeControl, {
						label: __( 'Description Size (px)', 'nuvora-aio-blocks' ),
						value: attributes.descSize,
						min: 10, max: 24,
						onChange: ( v ) => setAttributes( { descSize: v } ),
					} ),
					el( ColorControl, { label: __( 'Description Color', 'nuvora-aio-blocks' ), value: attributes.descColor, onChange: ( v ) => setAttributes( { descColor: v } ) } ),
				),

				// Box Styling Panel
				el( PanelBody, { title: __( 'Box Style', 'nuvora-aio-blocks' ), initialOpen: false },
					el( ColorControl, { label: __( 'Box Background', 'nuvora-aio-blocks' ), value: attributes.boxBg, onChange: ( v ) => setAttributes( { boxBg: v } ) } ),
					el( ColorControl, { label: __( 'Border Color', 'nuvora-aio-blocks' ), value: attributes.boxBorderColor, onChange: ( v ) => setAttributes( { boxBorderColor: v } ) } ),
					el( ColorControl, { label: __( 'Accent Color', 'nuvora-aio-blocks' ), value: attributes.accentColor, onChange: ( v ) => setAttributes( { accentColor: v } ) } ),
					el( ColorControl, { label: __( 'Accent Color 2 (Gradient)', 'nuvora-aio-blocks' ), value: attributes.accentColor2, onChange: ( v ) => setAttributes( { accentColor2: v } ) } ),
					el( RangeControl, {
						label: __( 'Border Radius (px)', 'nuvora-aio-blocks' ),
						value: attributes.boxBorderRadius,
						min: 0, max: 48,
						onChange: ( v ) => setAttributes( { boxBorderRadius: v } ),
					} ),
					el( RangeControl, {
						label: __( 'Box Padding (px)', 'nuvora-aio-blocks' ),
						value: attributes.boxPadding,
						min: 8, max: 80,
						onChange: ( v ) => setAttributes( { boxPadding: v } ),
					} ),
				),

				// Items Repeater Panel
				el( PanelBody, { title: __( 'Icon Box Items', 'nuvora-aio-blocks' ), initialOpen: true },
					parsedItems.map( ( item, idx ) =>
						el( 'div', {
							key: idx,
							style: { border: '1px solid #ddd', borderRadius: 8, marginBottom: 10, overflow: 'hidden' },
						},
							// Item header
							el( 'div', {
								style: { display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '8px 10px', background: '#f5f5f7', cursor: 'pointer' },
								onClick: () => setOpenItemIdx( openItemIdx === idx ? null : idx ),
							},
								el( 'div', { style: { display: 'flex', alignItems: 'center', gap: 8 } },
									el( 'span', { dangerouslySetInnerHTML: { __html: ICONS[ item.icon ] || ICONS.star }, style: { fontSize: 16, color: '#6c63ff' } } ),
									el( 'strong', { style: { fontSize: 12 } }, item.heading || __( 'Item', 'nuvora-aio-blocks' ) )
								),
								el( 'div', { style: { display: 'flex', gap: 4 } },
									el( Button, { isSmall: true, onClick: ( e ) => { e.stopPropagation(); moveItem( idx, -1 ); }, disabled: idx === 0, style: { minWidth: 'auto', padding: '0 6px' } }, '↑' ),
									el( Button, { isSmall: true, onClick: ( e ) => { e.stopPropagation(); moveItem( idx, 1 ); }, disabled: idx === parsedItems.length - 1, style: { minWidth: 'auto', padding: '0 6px' } }, '↓' ),
									el( Button, { isSmall: true, isDestructive: true, onClick: ( e ) => { e.stopPropagation(); removeItem( idx ); }, style: { minWidth: 'auto', padding: '0 6px' } }, '✕' )
								)
							),
							// Item body
							openItemIdx === idx && el( 'div', { style: { padding: 12 } },
								el( IconPicker, { value: item.icon, onChange: ( v ) => updateItem( idx, 'icon', v ) } ),
								el( TextControl, {
									label: __( 'Heading', 'nuvora-aio-blocks' ),
									value: item.heading,
									onChange: ( v ) => updateItem( idx, 'heading', v ),
								} ),
								el( TextareaControl, {
									label: __( 'Description', 'nuvora-aio-blocks' ),
									value: item.description,
									rows: 3,
									onChange: ( v ) => updateItem( idx, 'description', v ),
								} ),
							)
						)
					),
					el( Button, {
						isPrimary: true,
						onClick: addItem,
						style: { width: '100%', justifyContent: 'center', marginTop: 8 },
					}, __( '+ Add Icon Box', 'nuvora-aio-blocks' ) )
				),
			),

			// ── Canvas Preview ────────────────────────────────────────────
			el( IconBoxPreview, { attributes } )
		);
	}

	// ── Register Block ───────────────────────────────────────────────────────
	registerBlockType( 'nuvora/icon-box', {
		title:       __( 'Nuvora Icon Box', 'nuvora-aio-blocks' ),
		description: __( 'Display icon boxes with heading, description and multiple layout options.', 'nuvora-aio-blocks' ),
		category:    'nuvora-blocks',
		icon:        'star-filled',
		supports:    { html: false },
		attributes: {
			layout:          { type: 'string',  default: 'style1' },
			columns:         { type: 'number',  default: 3 },
			iconPosition:    { type: 'string',  default: 'top' },
			alignment:       { type: 'string',  default: 'center' },
			iconSize:        { type: 'number',  default: 48 },
			iconShape:       { type: 'string',  default: 'circle' },
			iconColor:       { type: 'string',  default: '#6c63ff' },
			iconBg:          { type: 'string',  default: '#ede9ff' },
			headingSize:     { type: 'number',  default: 18 },
			headingColor:    { type: 'string',  default: '#1a1a2e' },
			descSize:        { type: 'number',  default: 14 },
			descColor:       { type: 'string',  default: '#666677' },
			boxBg:           { type: 'string',  default: '#ffffff' },
			boxBorderColor:  { type: 'string',  default: '#e8e8f0' },
			boxBorderRadius: { type: 'number',  default: 16 },
			boxPadding:      { type: 'number',  default: 32 },
			accentColor:     { type: 'string',  default: '#6c63ff' },
			accentColor2:    { type: 'string',  default: '#f72585' },
			items:           { type: 'string',  default: '[{"icon":"star","heading":"Our Vision","description":"We strive to deliver world-class solutions that empower teams and drive innovation forward."},{"icon":"rocket","heading":"Our Mission","description":"Building products that are fast, reliable and beautifully designed for every kind of user."},{"icon":"heart","heading":"Our Values","description":"Integrity, collaboration and excellence guide everything we do at every step of the journey."}]' },
		},
		edit: Edit,
		save: () => null,
	} );

} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);
