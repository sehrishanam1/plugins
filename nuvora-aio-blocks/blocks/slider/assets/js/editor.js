/**
 * Nuvora Slider Block – Gutenberg Editor
 */
( function ( blocks, element, blockEditor, components, i18n, apiFetch ) {
	'use strict';

	const el = element.createElement;
	const { __ } = i18n;
	const { registerBlockType } = blocks;
	const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck } = blockEditor;
	const {
		PanelBody, RangeControl, SelectControl,
		ToggleControl, TextControl, TextareaControl,
		Button, ColorPicker, Popover,
	} = components;

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

	// ── Live Preview ─────────────────────────────────────────────────────────
	function SliderPreview( { attributes, parsedSlides } ) {
		const [ current, setCurrent ] = element.useState( 0 );
		const {
			mode, height, fullWidth, borderRadius,
			showCaption, showArrows, showDots,
			captionPosition, titleColor, titleSize, descColor, descSize,
			btnBg, btnColor, btnRadius, dotColor,
			carouselCols, gap, animation,
		} = attributes;

		const isCarousel = mode === 'carousel';
		const isHero     = mode === 'hero';

		const capPosStyle = {
			'top-left':      { top: '10%', left: '8%', textAlign: 'left' },
			'center':        { top: '50%', left: '50%', transform: 'translate(-50%,-50%)', textAlign: 'center' },
			'bottom-left':   { bottom: '12%', left: '8%', textAlign: 'left' },
			'bottom-center': { bottom: '12%', left: '50%', transform: 'translateX(-50%)', textAlign: 'center' },
		}[ captionPosition ] || { top: '50%', left: '50%', transform: 'translate(-50%,-50%)', textAlign: 'center' };

		const wrapStyle = {
			position: 'relative',
			overflow: 'hidden',
			borderRadius: isHero ? 0 : borderRadius,
			height: isCarousel ? 'auto' : height,
			width: '100%',
			boxSizing: 'border-box',
		};

		function renderSlide( slide, idx, style ) {
			const bg = slide.imageUrl
				? { backgroundImage: `url(${slide.imageUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' }
				: { background: '#1a1a2e' };
			return el( 'div', { key: idx, style: { ...bg, ...style, position: 'relative', overflow: 'hidden' } },
				slide.overlayEnable && el( 'div', { style: { position: 'absolute', inset: 0, background: slide.overlayColor || 'rgba(0,0,0,0.45)', zIndex: 1 } } ),
				showCaption && el( 'div', { style: { position: 'absolute', zIndex: 2, width: '80%', maxWidth: 760, padding: '0 20px', ...capPosStyle } },
					slide.title && el( 'h2', { style: { color: titleColor, fontSize: titleSize, fontWeight: 800, margin: '0 0 14px', lineHeight: 1.15, textShadow: '0 2px 12px rgba(0,0,0,0.3)' } }, slide.title ),
					slide.description && el( 'p', { style: { color: descColor, fontSize: descSize, margin: '0 0 20px', lineHeight: 1.65, textShadow: '0 1px 6px rgba(0,0,0,0.3)' } }, slide.description ),
					slide.btnText && slide.btnUrl && el( 'a', { style: { display: 'inline-block', padding: '12px 28px', background: btnBg, color: btnColor, borderRadius: btnRadius, fontWeight: 700, textDecoration: 'none', fontSize: 14 } }, slide.btnText )
				)
			);
		}

		const prevSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg>';
		const nextSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>';

		const arrowBase = { position: 'absolute', top: '50%', transform: 'translateY(-50%)', zIndex: 10, width: 44, height: 44, borderRadius: '50%', background: 'rgba(0,0,0,0.45)', border: 'none', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer' };

		if ( isCarousel ) {
			const cols = Math.max( 1, carouselCols );
			return el( 'div', { style: wrapStyle },
				el( 'div', { style: { display: 'flex', gap, overflow: 'hidden' } },
					parsedSlides.map( ( slide, idx ) =>
						renderSlide( slide, idx, { flex: `0 0 calc(${100/cols}% - ${gap*(cols-1)/cols}px)`, height: height, borderRadius } )
					)
				)
			);
		}

		const slide = parsedSlides[ current ] || {};

		return el( 'div', { style: wrapStyle },
			renderSlide( slide, current, { position: 'absolute', inset: 0, width: '100%', height: '100%' } ),
			el( 'div', { style: { position: 'relative', height: '100%' } } ),
			showArrows && parsedSlides.length > 1 && el( 'button', {
				style: { ...arrowBase, left: 12 },
				onClick: () => setCurrent( ( current - 1 + parsedSlides.length ) % parsedSlides.length ),
				dangerouslySetInnerHTML: { __html: prevSvg },
			} ),
			showArrows && parsedSlides.length > 1 && el( 'button', {
				style: { ...arrowBase, right: 12 },
				onClick: () => setCurrent( ( current + 1 ) % parsedSlides.length ),
				dangerouslySetInnerHTML: { __html: nextSvg },
			} ),
			showDots && el( 'div', { style: { position: 'absolute', bottom: 14, left: '50%', transform: 'translateX(-50%)', display: 'flex', gap: 8, zIndex: 10 } },
				parsedSlides.map( ( _, idx ) =>
					el( 'button', {
						key: idx,
						onClick: () => setCurrent( idx ),
						style: { width: 10, height: 10, borderRadius: '50%', border: 'none', cursor: 'pointer', padding: 0, background: idx === current ? dotColor : 'rgba(255,255,255,0.4)', transform: idx === current ? 'scale(1.35)' : 'scale(1)', transition: 'all 0.2s' },
					} )
				)
			),
			el( 'div', { style: { position: 'absolute', bottom: 44, left: '50%', transform: 'translateX(-50%)', background: 'rgba(0,0,0,0.5)', color: '#fff', fontSize: 11, padding: '3px 10px', borderRadius: 20, zIndex: 10 } },
				`${current + 1} / ${parsedSlides.length} · ${animation}`
			)
		);
	}

	// ── Slide Item Editor ────────────────────────────────────────────────────
	function SlideEditor( { slide, idx, total, onChange, onRemove, onMove } ) {
		const [ open, setOpen ] = element.useState( idx === 0 );

		return el( 'div', { style: { border: '1px solid #ddd', borderRadius: 8, marginBottom: 10, overflow: 'hidden' } },
			// Header
			el( 'div', {
				style: { display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '8px 10px', background: '#f5f5f7', cursor: 'pointer' },
				onClick: () => setOpen( ! open ),
			},
				el( 'div', { style: { display: 'flex', alignItems: 'center', gap: 8 } },
					slide.imageUrl && el( 'img', { src: slide.imageUrl, style: { width: 32, height: 32, objectFit: 'cover', borderRadius: 4 } } ),
					el( 'strong', { style: { fontSize: 12 } }, slide.title || `Slide ${idx + 1}` )
				),
				el( 'div', { style: { display: 'flex', gap: 4 } },
					el( Button, { isSmall: true, onClick: e => { e.stopPropagation(); onMove( -1 ); }, disabled: idx === 0, style: { minWidth: 'auto', padding: '0 6px' } }, '↑' ),
					el( Button, { isSmall: true, onClick: e => { e.stopPropagation(); onMove( 1 ); }, disabled: idx === total - 1, style: { minWidth: 'auto', padding: '0 6px' } }, '↓' ),
					el( Button, { isSmall: true, isDestructive: true, onClick: e => { e.stopPropagation(); onRemove(); }, style: { minWidth: 'auto', padding: '0 6px' } }, '✕' )
				)
			),
			// Body
			open && el( 'div', { style: { padding: 12 } },
				// Image picker
				el( 'p', { style: { fontWeight: 600, fontSize: 12, marginBottom: 6 } }, __( 'Slide Image', 'nuvora-aio-blocks' ) ),
				el( MediaUploadCheck, null,
					el( MediaUpload, {
						onSelect: ( media ) => onChange( 'imageUrl', media.url ),
						allowedTypes: [ 'image' ],
						render: ( { open: openMedia } ) =>
							el( 'div', { style: { display: 'flex', gap: 8, alignItems: 'center', marginBottom: 12 } },
								slide.imageUrl && el( 'img', { src: slide.imageUrl, style: { width: 72, height: 48, objectFit: 'cover', borderRadius: 4, border: '1px solid #ddd' } } ),
								el( Button, { isSecondary: true, onClick: openMedia, style: { fontSize: 12 } }, slide.imageUrl ? __( 'Change Image', 'nuvora-aio-blocks' ) : __( 'Select Image', 'nuvora-aio-blocks' ) ),
								slide.imageUrl && el( Button, { isDestructive: true, isSmall: true, onClick: () => onChange( 'imageUrl', '' ) }, __( 'Remove', 'nuvora-aio-blocks' ) )
							)
					} )
				),
				el( TextControl, { label: __( 'Title', 'nuvora-aio-blocks' ), value: slide.title || '', onChange: v => onChange( 'title', v ) } ),
				el( TextareaControl, { label: __( 'Description', 'nuvora-aio-blocks' ), value: slide.description || '', rows: 2, onChange: v => onChange( 'description', v ) } ),
				el( TextControl, { label: __( 'Button Text', 'nuvora-aio-blocks' ), value: slide.btnText || '', onChange: v => onChange( 'btnText', v ) } ),
				el( TextControl, { label: __( 'Button URL', 'nuvora-aio-blocks' ), value: slide.btnUrl || '', onChange: v => onChange( 'btnUrl', v ) } ),
				el( ToggleControl, { label: __( 'Open in new tab', 'nuvora-aio-blocks' ), checked: !! slide.btnTarget, onChange: v => onChange( 'btnTarget', v ) } ),
				el( ToggleControl, { label: __( 'Enable Overlay', 'nuvora-aio-blocks' ), checked: !! slide.overlayEnable, onChange: v => onChange( 'overlayEnable', v ) } ),
				slide.overlayEnable && el( ColorControl, { label: __( 'Overlay Color', 'nuvora-aio-blocks' ), value: slide.overlayColor || 'rgba(0,0,0,0.45)', onChange: v => onChange( 'overlayColor', v ) } ),
			)
		);
	}

	// ── Main Edit ────────────────────────────────────────────────────────────
	function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const parsedSlides = ( () => { try { return JSON.parse( attributes.slides ); } catch(e) { return []; } } )();

		function updateSlides( arr ) { setAttributes( { slides: JSON.stringify( arr ) } ); }

		function addSlide() {
			updateSlides( [ ...parsedSlides, { imageUrl: '', imageAlt: '', title: 'New Slide', description: 'Add your description here.', btnText: 'Learn More', btnUrl: '#', btnTarget: false, overlayColor: 'rgba(0,0,0,0.45)', overlayEnable: true } ] );
		}

		function updateSlide( idx, key, val ) {
			updateSlides( parsedSlides.map( ( s, i ) => i === idx ? { ...s, [key]: val } : s ) );
		}

		function removeSlide( idx ) { updateSlides( parsedSlides.filter( ( _, i ) => i !== idx ) ); }

		function moveSlide( idx, dir ) {
			const arr = [ ...parsedSlides ];
			const t = idx + dir;
			if ( t < 0 || t >= arr.length ) return;
			[ arr[idx], arr[t] ] = [ arr[t], arr[idx] ];
			updateSlides( arr );
		}

		return el( 'div', blockProps,
			el( InspectorControls, null,

				// Mode & Layout
				el( PanelBody, { title: __( 'Mode & Layout', 'nuvora-aio-blocks' ), initialOpen: true },
					el( SelectControl, {
						label: __( 'Display Mode', 'nuvora-aio-blocks' ),
						value: attributes.mode,
						options: [
							{ value: 'slider',   label: __( 'Slider (one slide at a time)', 'nuvora-aio-blocks' ) },
							{ value: 'carousel', label: __( 'Carousel (multiple visible)', 'nuvora-aio-blocks' ) },
							{ value: 'hero',     label: __( 'Hero (full width, no radius)', 'nuvora-aio-blocks' ) },
						],
						onChange: v => setAttributes( { mode: v } ),
					} ),
					el( SelectControl, {
						label: __( 'Content Source', 'nuvora-aio-blocks' ),
						value: attributes.contentType,
						options: [
							{ value: 'custom', label: __( 'Custom Slides', 'nuvora-aio-blocks' ) },
							{ value: 'posts',  label: __( 'Latest Posts', 'nuvora-aio-blocks' ) },
						],
						onChange: v => setAttributes( { contentType: v } ),
					} ),
					attributes.contentType === 'posts' && el( RangeControl, {
						label: __( 'Number of Posts', 'nuvora-aio-blocks' ),
						value: attributes.postCount, min: 2, max: 12,
						onChange: v => setAttributes( { postCount: v } ),
					} ),
					el( ToggleControl, {
						label: __( 'Full Width', 'nuvora-aio-blocks' ),
						checked: attributes.fullWidth,
						onChange: v => setAttributes( { fullWidth: v } ),
					} ),
					el( RangeControl, {
						label: __( 'Height (px)', 'nuvora-aio-blocks' ),
						value: attributes.height, min: 200, max: 900,
						onChange: v => setAttributes( { height: v } ),
					} ),
					attributes.mode === 'carousel' && el( RangeControl, {
						label: __( 'Visible Columns', 'nuvora-aio-blocks' ),
						value: attributes.carouselCols, min: 1, max: 5,
						onChange: v => setAttributes( { carouselCols: v } ),
					} ),
					attributes.mode === 'carousel' && el( RangeControl, {
						label: __( 'Gap (px)', 'nuvora-aio-blocks' ),
						value: attributes.gap, min: 0, max: 60,
						onChange: v => setAttributes( { gap: v } ),
					} ),
					attributes.mode !== 'hero' && el( RangeControl, {
						label: __( 'Border Radius (px)', 'nuvora-aio-blocks' ),
						value: attributes.borderRadius, min: 0, max: 40,
						onChange: v => setAttributes( { borderRadius: v } ),
					} ),
				),

				// Animation & Autoplay
				el( PanelBody, { title: __( 'Animation & Autoplay', 'nuvora-aio-blocks' ), initialOpen: false },
					el( SelectControl, {
						label: __( 'Transition Animation', 'nuvora-aio-blocks' ),
						value: attributes.animation,
						options: [
							{ value: 'slide', label: __( 'Slide', 'nuvora-aio-blocks' ) },
							{ value: 'fade',  label: __( 'Fade', 'nuvora-aio-blocks' ) },
							{ value: 'zoom',  label: __( 'Zoom', 'nuvora-aio-blocks' ) },
							{ value: 'flip',  label: __( 'Flip', 'nuvora-aio-blocks' ) },
						],
						onChange: v => setAttributes( { animation: v } ),
					} ),
					el( RangeControl, {
						label: __( 'Animation Speed (ms)', 'nuvora-aio-blocks' ),
						value: attributes.animationSpeed, min: 200, max: 1500,
						onChange: v => setAttributes( { animationSpeed: v } ),
					} ),
					el( ToggleControl, {
						label: __( 'Autoplay', 'nuvora-aio-blocks' ),
						checked: attributes.autoplay,
						onChange: v => setAttributes( { autoplay: v } ),
					} ),
					attributes.autoplay && el( RangeControl, {
						label: __( 'Autoplay Speed (ms)', 'nuvora-aio-blocks' ),
						value: attributes.autoplaySpeed, min: 1000, max: 10000, step: 500,
						onChange: v => setAttributes( { autoplaySpeed: v } ),
					} ),
					attributes.autoplay && el( ToggleControl, {
						label: __( 'Pause on Hover', 'nuvora-aio-blocks' ),
						checked: attributes.pauseOnHover,
						onChange: v => setAttributes( { pauseOnHover: v } ),
					} ),
				),

				// Navigation
				el( PanelBody, { title: __( 'Navigation', 'nuvora-aio-blocks' ), initialOpen: false },
					el( ToggleControl, { label: __( 'Show Arrows', 'nuvora-aio-blocks' ), checked: attributes.showArrows, onChange: v => setAttributes( { showArrows: v } ) } ),
					attributes.showArrows && el( SelectControl, {
						label: __( 'Arrow Style', 'nuvora-aio-blocks' ),
						value: attributes.arrowStyle,
						options: [
							{ value: 'circle',  label: __( 'Circle', 'nuvora-aio-blocks' ) },
							{ value: 'square',  label: __( 'Square', 'nuvora-aio-blocks' ) },
							{ value: 'minimal', label: __( 'Minimal (outline)', 'nuvora-aio-blocks' ) },
						],
						onChange: v => setAttributes( { arrowStyle: v } ),
					} ),
					el( ToggleControl, { label: __( 'Show Dots', 'nuvora-aio-blocks' ), checked: attributes.showDots, onChange: v => setAttributes( { showDots: v } ) } ),
					attributes.showDots && el( ColorControl, { label: __( 'Active Dot Color', 'nuvora-aio-blocks' ), value: attributes.dotColor, onChange: v => setAttributes( { dotColor: v } ) } ),
				),

				// Caption Style
				el( PanelBody, { title: __( 'Caption Style', 'nuvora-aio-blocks' ), initialOpen: false },
					el( ToggleControl, { label: __( 'Show Captions', 'nuvora-aio-blocks' ), checked: attributes.showCaption, onChange: v => setAttributes( { showCaption: v } ) } ),
					attributes.showCaption && el( SelectControl, {
						label: __( 'Caption Position', 'nuvora-aio-blocks' ),
						value: attributes.captionPosition,
						options: [
							{ value: 'center',        label: __( 'Center', 'nuvora-aio-blocks' ) },
							{ value: 'top-left',      label: __( 'Top Left', 'nuvora-aio-blocks' ) },
							{ value: 'bottom-left',   label: __( 'Bottom Left', 'nuvora-aio-blocks' ) },
							{ value: 'bottom-center', label: __( 'Bottom Center', 'nuvora-aio-blocks' ) },
						],
						onChange: v => setAttributes( { captionPosition: v } ),
					} ),
					attributes.showCaption && el( RangeControl, { label: __( 'Title Size (px)', 'nuvora-aio-blocks' ), value: attributes.titleSize, min: 16, max: 80, onChange: v => setAttributes( { titleSize: v } ) } ),
					attributes.showCaption && el( ColorControl, { label: __( 'Title Color', 'nuvora-aio-blocks' ), value: attributes.titleColor, onChange: v => setAttributes( { titleColor: v } ) } ),
					attributes.showCaption && el( RangeControl, { label: __( 'Description Size (px)', 'nuvora-aio-blocks' ), value: attributes.descSize, min: 12, max: 32, onChange: v => setAttributes( { descSize: v } ) } ),
					attributes.showCaption && el( ColorControl, { label: __( 'Description Color', 'nuvora-aio-blocks' ), value: attributes.descColor, onChange: v => setAttributes( { descColor: v } ) } ),
					attributes.showCaption && el( ColorControl, { label: __( 'Button Background', 'nuvora-aio-blocks' ), value: attributes.btnBg, onChange: v => setAttributes( { btnBg: v } ) } ),
					attributes.showCaption && el( ColorControl, { label: __( 'Button Text Color', 'nuvora-aio-blocks' ), value: attributes.btnColor, onChange: v => setAttributes( { btnColor: v } ) } ),
					attributes.showCaption && el( RangeControl, { label: __( 'Button Radius (px)', 'nuvora-aio-blocks' ), value: attributes.btnRadius, min: 0, max: 60, onChange: v => setAttributes( { btnRadius: v } ) } ),
				),

				// Slides repeater (only for custom)
				attributes.contentType === 'custom' && el( PanelBody, { title: __( 'Slides', 'nuvora-aio-blocks' ), initialOpen: true },
					parsedSlides.map( ( slide, idx ) =>
						el( SlideEditor, {
							key: idx,
							slide,
							idx,
							total: parsedSlides.length,
							onChange: ( key, val ) => updateSlide( idx, key, val ),
							onRemove: () => removeSlide( idx ),
							onMove: dir => moveSlide( idx, dir ),
						} )
					),
					el( Button, {
						isPrimary: true,
						onClick: addSlide,
						style: { width: '100%', justifyContent: 'center', marginTop: 8 },
					}, __( '+ Add Slide', 'nuvora-aio-blocks' ) )
				),
			),

			// ── Canvas Preview ───────────────────────────────────────────────
			el( SliderPreview, { attributes, parsedSlides } )
		);
	}

	// ── Register ─────────────────────────────────────────────────────────────
	registerBlockType( 'nuvora/slider', {
		title:       __( 'Nuvora Slider', 'nuvora-aio-blocks' ),
		description: __( 'A powerful slider and carousel with multiple animation styles, captions, and latest posts support.', 'nuvora-aio-blocks' ),
		category:    'nuvora-blocks',
		icon:        'images-alt2',
		supports:    { html: false },
		attributes: {
			mode:           { type: 'string',  default: 'slider' },
			contentType:    { type: 'string',  default: 'custom' },
			slides:         { type: 'string',  default: '[{"imageUrl":"","imageAlt":"","title":"Welcome to Our Site","description":"A beautiful and powerful slider for your Gutenberg editor.","btnText":"Get Started","btnUrl":"#","btnTarget":false,"overlayColor":"rgba(0,0,0,0.45)","overlayEnable":true},{"imageUrl":"","imageAlt":"","title":"Discover More","description":"Explore our features and find everything you need in one place.","btnText":"Learn More","btnUrl":"#","btnTarget":false,"overlayColor":"rgba(30,30,80,0.5)","overlayEnable":true}]' },
			postCount:      { type: 'number',  default: 5 },
			postCategory:   { type: 'string',  default: '' },
			fullWidth:      { type: 'boolean', default: false },
			height:         { type: 'number',  default: 500 },
			carouselCols:   { type: 'number',  default: 3 },
			gap:            { type: 'number',  default: 20 },
			borderRadius:   { type: 'number',  default: 12 },
			autoplay:       { type: 'boolean', default: true },
			autoplaySpeed:  { type: 'number',  default: 4000 },
			pauseOnHover:   { type: 'boolean', default: true },
			animation:      { type: 'string',  default: 'slide' },
			animationSpeed: { type: 'number',  default: 600 },
			showArrows:     { type: 'boolean', default: true },
			showDots:       { type: 'boolean', default: true },
			arrowStyle:     { type: 'string',  default: 'circle' },
			showCaption:    { type: 'boolean', default: true },
			captionPosition:{ type: 'string',  default: 'center' },
			titleSize:      { type: 'number',  default: 42 },
			titleColor:     { type: 'string',  default: '#ffffff' },
			descSize:       { type: 'number',  default: 18 },
			descColor:      { type: 'string',  default: 'rgba(255,255,255,0.9)' },
			btnBg:          { type: 'string',  default: '#6c63ff' },
			btnColor:       { type: 'string',  default: '#ffffff' },
			btnRadius:      { type: 'number',  default: 50 },
			dotColor:       { type: 'string',  default: '#ffffff' },
		},
		edit: Edit,
		save: () => null,
	} );

} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.apiFetch
);
