/**
 * Nuvora Advanced Tabs Block – Editor
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';
	const el = element.createElement, F = element.Fragment, { __ } = i18n;
	const { registerBlockType } = blocks;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, PanelRow, RangeControl, SelectControl, ToggleControl, TextControl, TextareaControl, ColorPicker, Popover, Button } = components;

	const ICON_OPTIONS = [
		{ label: '⭐ Star', value: 'star' }, { label: '✔ Check', value: 'check' }, { label: '❤ Heart', value: 'heart' },
		{ label: '🚀 Rocket', value: 'rocket' }, { label: '🌐 Globe', value: 'globe' }, { label: '👥 Users', value: 'users' },
		{ label: '📈 Chart', value: 'chart' }, { label: '💎 Diamond', value: 'diamond' },
	];

	const ICONS = {
		star: 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
		check: 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z',
		heart: 'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09A5.988 5.988 0 0 1 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z',
		rocket: 'M12 2C9.5 2 7.5 3.5 6.6 5.6L2 14h4v4l2-2 2 2v-4h8v4l2-2 2 2v-4h4L21.4 5.6C20.5 3.5 18.5 2 16 2h-4zm0 2h4c1.8 0 3.3 1 4 2.5l.9 2.5H3.1l.9-2.5C4.7 5 6.2 4 8 4h4zm0 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4z',
		globe: 'M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm-1 17.93V18c0-.55-.45-1-1-1H8v-2c0-.55-.45-1-1-1H5.07A8.003 8.003 0 0 1 4 12c0-.34.02-.67.07-1H6c.55 0 1-.45 1-1V9c0-.55.45-1 1-1h.5c.55 0 1-.45 1-1V5.08A8.006 8.006 0 0 1 12 4c.34 0 .67.02 1 .07V6c0 .55.45 1 1 1h2v1c0 .55.45 1 1 1h1.93c.05.33.07.66.07 1 0 4.07-3.06 7.44-7 7.93z',
		users: 'M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05C16.19 13.89 17 15.02 17 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z',
		chart: 'M3.5 18.5l6-6 4 4L22 6.92 20.59 5.5l-7.09 8-4-4L2 17l1.5 1.5z',
		diamond: 'M19 3H5L2 9l10 12L22 9l-3-6zm-8.5 14.5L3.74 9.5h4.76l3 8zm2 0l3-8h4.76L13.5 17.5zM14.5 8h-5L12 4.5 14.5 8zm-6.76 0H3.5L5.5 4h4.24L7.74 8zm9.76 0L15.26 4H19.5l2 4h-4.26z',
	};

	function SvgIcon( { name, size = 18, color = 'currentColor' } ) {
		const d = ICONS[ name ] || ICONS.star;
		return el( 'svg', { xmlns: 'http://www.w3.org/2000/svg', width: size, height: size, fill: color, viewBox: '0 0 24 24' }, el( 'path', { d } ) );
	}

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

	function TabsEditor( { attributes, setAttributes } ) {
		const a    = attributes;
		const tabs = JSON.parse( a.tabs || '[]' );
		const [ activePreview, setActivePreview ] = element.useState( 0 );

		function updateTab( index, key, value ) {
			const updated = [ ...tabs ]; updated[ index ] = { ...updated[ index ], [ key ]: value };
			setAttributes( { tabs: JSON.stringify( updated ) } );
		}
		function addTab() {
			const updated = [ ...tabs, { title: 'New Tab', description: 'Tab content goes here.', icon: 'star', iconColor: '#6c63ff' } ];
			setAttributes( { tabs: JSON.stringify( updated ) } );
		}
		function removeTab( i ) {
			const updated = tabs.filter( ( _, idx ) => idx !== i );
			setAttributes( { tabs: JSON.stringify( updated ) } );
			if ( activePreview >= updated.length ) setActivePreview( Math.max( 0, updated.length - 1 ) );
		}

		// Build preview
		const navTabs = tabs.map( ( t, i ) => {
			const isActive = i === activePreview;
			let btnStyle = { display: 'inline-flex', alignItems: 'center', gap: 8, cursor: 'pointer', border: 'none', fontWeight: 600, padding: `${a.tabPadding}px ${a.tabPadding * 1.5}px`, fontSize: a.titleSize + 'px', transition: 'all 0.2s' };
			switch ( a.layout ) {
				case 'style2':
					btnStyle = { ...btnStyle, background: 'transparent', color: isActive ? a.activeBg : a.inactiveText, borderBottom: isActive ? `2px solid ${a.activeBg}` : '2px solid transparent', borderRadius: 0 };
					break;
				case 'style3':
					btnStyle = { ...btnStyle, background: isActive ? a.activeBg : a.inactiveBg, color: isActive ? a.activeText : a.inactiveText, borderRadius: 50 };
					break;
				case 'style4':
					btnStyle = { ...btnStyle, background: isActive ? a.activeBg : a.inactiveBg, color: isActive ? a.activeText : a.inactiveText, borderRadius: `${a.borderRadius}px ${a.borderRadius}px 0 0`, border: `1px solid ${a.borderColor}`, borderBottom: isActive ? `1px solid ${a.activeBg}` : `1px solid ${a.borderColor}`, marginBottom: -1 };
					break;
				default:
					btnStyle = { ...btnStyle, background: isActive ? a.activeBg : a.inactiveBg, color: isActive ? a.activeText : a.inactiveText, borderRadius: a.borderRadius + 'px' };
			}
			return el( 'button', { key: i, style: btnStyle, onClick: () => setActivePreview( i ) },
				a.showIcon && el( SvgIcon, { name: t.icon, size: a.iconSize, color: isActive ? a.activeText : ( t.iconColor || a.activeBg ) } ),
				a.showTitle && t.title
			);
		} );

		const activeTab = tabs[ activePreview ] || {};
		const contentStyle = { padding: a.contentPadding + 'px', background: a.contentBg, color: a.contentText, borderRadius: a.layout === 'style1' || a.layout === 'style3' ? a.borderRadius + 'px' : `0 ${a.borderRadius}px ${a.borderRadius}px ${a.borderRadius}px`, border: a.layout === 'style2' ? 'none' : `1px solid ${a.borderColor}` };

		const navStyle = {};
		if ( a.layout === 'style2' ) navStyle.borderBottom = `1px solid ${a.borderColor}`;
		if ( a.layout === 'style3' ) { navStyle.background = a.inactiveBg; navStyle.padding = '8px'; navStyle.borderRadius = a.borderRadius + 'px'; }

		const preview = el( 'div', null,
			el( 'div', { style: { display: 'flex', gap: 4, flexWrap: 'wrap', marginBottom: 0, ...navStyle } }, ...navTabs ),
			el( 'div', { style: contentStyle },
				a.showIcon && el( 'div', { style: { color: activeTab.iconColor || a.activeBg, fontSize: a.iconSize * 1.5 + 'px', marginBottom: 8 } }, el( SvgIcon, { name: activeTab.icon, size: a.iconSize * 1.5, color: activeTab.iconColor || a.activeBg } ) ),
				a.showDesc && el( 'p', { style: { margin: '8px 0 0', fontSize: a.descSize + 'px', lineHeight: 1.7 } }, activeTab.description )
			)
		);

		return el( F, null,
			el( InspectorControls, null,
				el( PanelBody, { title: __( 'Layout', 'nuvora-aio-blocks' ), initialOpen: true },
					el( PanelRow, null, el( SelectControl, { label: 'Tab Style', value: a.layout, options: [ { label: 'Style 1 – Solid Background', value: 'style1' }, { label: 'Style 2 – Underline', value: 'style2' }, { label: 'Style 3 – Pills', value: 'style3' }, { label: 'Style 4 – Card Tabs', value: 'style4' } ], onChange: v => setAttributes( { layout: v } ) } ) ),
					el( PanelRow, null, el( ToggleControl, { label: 'Show Icon', checked: a.showIcon, onChange: v => setAttributes( { showIcon: v } ) } ) ),
					el( PanelRow, null, el( ToggleControl, { label: 'Show Title', checked: a.showTitle, onChange: v => setAttributes( { showTitle: v } ) } ) ),
					el( PanelRow, null, el( ToggleControl, { label: 'Show Description', checked: a.showDesc, onChange: v => setAttributes( { showDesc: v } ) } ) ),
				),
				el( PanelBody, { title: __( 'Tabs', 'nuvora-aio-blocks' ), initialOpen: false },
					...tabs.map( ( t, i ) => el( 'div', { key: i, style: { border: '1px solid #eee', borderRadius: 8, padding: 12, marginBottom: 12 } },
						el( 'strong', { style: { display: 'block', marginBottom: 8 } }, `Tab ${i + 1}` ),
						el( TextControl, { label: 'Title', value: t.title, onChange: v => updateTab( i, 'title', v ) } ),
						el( 'div', { style: { marginBottom: 8 } }, el( TextareaControl, { label: 'Description', value: t.description, rows: 3, onChange: v => updateTab( i, 'description', v ) } ) ),
						el( SelectControl, { label: 'Icon', value: t.icon, options: ICON_OPTIONS, onChange: v => updateTab( i, 'icon', v ) } ),
						el( ColorControl, { label: 'Icon Color', value: t.iconColor, onChange: v => updateTab( i, 'iconColor', v ) } ),
						el( Button, { isDestructive: true, isSmall: true, onClick: () => removeTab( i ) }, 'Remove' )
					) ),
					el( Button, { isPrimary: true, onClick: addTab, style: { width: '100%' } }, '+ Add Tab' )
				),
				el( PanelBody, { title: __( 'Colors', 'nuvora-aio-blocks' ), initialOpen: false },
					el( ColorControl, { label: 'Active Tab Background', value: a.activeBg, onChange: v => setAttributes( { activeBg: v } ) } ),
					el( ColorControl, { label: 'Active Tab Text', value: a.activeText, onChange: v => setAttributes( { activeText: v } ) } ),
					el( ColorControl, { label: 'Inactive Tab Background', value: a.inactiveBg, onChange: v => setAttributes( { inactiveBg: v } ) } ),
					el( ColorControl, { label: 'Inactive Tab Text', value: a.inactiveText, onChange: v => setAttributes( { inactiveText: v } ) } ),
					el( ColorControl, { label: 'Content Background', value: a.contentBg, onChange: v => setAttributes( { contentBg: v } ) } ),
					el( ColorControl, { label: 'Content Text', value: a.contentText, onChange: v => setAttributes( { contentText: v } ) } ),
					el( ColorControl, { label: 'Border Color', value: a.borderColor, onChange: v => setAttributes( { borderColor: v } ) } ),
				),
				el( PanelBody, { title: __( 'Sizes', 'nuvora-aio-blocks' ), initialOpen: false },
					el( PanelRow, null, el( RangeControl, { label: 'Tab Padding (px)', value: a.tabPadding, min: 6, max: 30, onChange: v => setAttributes( { tabPadding: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Content Padding (px)', value: a.contentPadding, min: 12, max: 60, onChange: v => setAttributes( { contentPadding: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Border Radius (px)', value: a.borderRadius, min: 0, max: 30, onChange: v => setAttributes( { borderRadius: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Tab Title Size (px)', value: a.titleSize, min: 10, max: 24, onChange: v => setAttributes( { titleSize: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Icon Size (px)', value: a.iconSize, min: 12, max: 40, onChange: v => setAttributes( { iconSize: v } ) } ) ),
					el( PanelRow, null, el( RangeControl, { label: 'Description Size (px)', value: a.descSize, min: 12, max: 28, onChange: v => setAttributes( { descSize: v } ) } ) ),
				),
			),
			preview
		);
	}

	registerBlockType( 'nuvora/advanced-tabs', {
		title: __( 'Nuvora Advanced Tabs', 'nuvora-aio-blocks' ),
		description: __( 'Advanced tabs block with title, description, icon per tab, 4 styles and full design controls.', 'nuvora-aio-blocks' ),
		category: 'nuvora-blocks', icon: 'menu',
		keywords: [ 'tabs', 'accordion', 'toggle', 'nuvora' ],
		supports: { html: false },
		attributes: {
			tabs: { type: 'string', default: '[{"title":"Features","description":"Explore our powerful features that help you build better products faster.","icon":"star","iconColor":"#6c63ff"},{"title":"Benefits","description":"Save time and money with our all-in-one solution designed for modern teams.","icon":"check","iconColor":"#f72585"},{"title":"Support","description":"Our dedicated support team is available 24/7 to help you succeed.","icon":"heart","iconColor":"#06d6a0"}]' },
			layout: { type: 'string', default: 'style1' }, activeTab: { type: 'number', default: 0 },
			activeBg: { type: 'string', default: '#6c63ff' }, activeText: { type: 'string', default: '#ffffff' }, inactiveBg: { type: 'string', default: '#f5f5f5' }, inactiveText: { type: 'string', default: '#555555' }, contentBg: { type: 'string', default: '#ffffff' }, contentText: { type: 'string', default: '#333333' }, borderColor: { type: 'string', default: '#e0e0e0' },
			tabPadding: { type: 'number', default: 14 }, contentPadding: { type: 'number', default: 28 }, borderRadius: { type: 'number', default: 10 }, titleSize: { type: 'number', default: 15 }, iconSize: { type: 'number', default: 20 }, descSize: { type: 'number', default: 16 },
			showIcon: { type: 'boolean', default: true }, showTitle: { type: 'boolean', default: true }, showDesc: { type: 'boolean', default: true },
		},
		edit( props ) { return el( TabsEditor, props ); },
		save: () => null,
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
