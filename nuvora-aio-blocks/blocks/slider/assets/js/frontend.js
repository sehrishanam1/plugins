/**
 * Nuvora Slider Block – Frontend Script
 * Handles: slider, carousel, hero modes
 * Animations: slide, fade, zoom, flip
 */
( function () {
	'use strict';

	function initSlider( block ) {
		const mode        = block.classList.contains( 'nugba-mode-carousel' ) ? 'carousel'
		                  : block.classList.contains( 'nugba-mode-hero' )     ? 'hero'
		                  : 'slider';
		const animation   = block.dataset.animation   || 'slide';
		const animSpeed   = parseInt( block.dataset.animationSpeed )  || 600;
		const autoplay    = block.dataset.autoplay    === 'true';
		const autoSpeed   = parseInt( block.dataset.autoplaySpeed )   || 4000;
		const pauseHover  = block.dataset.pauseHover  === 'true';

		const track  = block.querySelector( '.nugba-slider-track' );
		const slides = Array.from( block.querySelectorAll( '.nugba-slide' ) );
		const dots   = Array.from( block.querySelectorAll( '.nugba-dot' ) );
		const prevBtn = block.querySelector( '.nugba-prev' );
		const nextBtn = block.querySelector( '.nugba-next' );

		if ( ! slides.length ) return;

		// ── Carousel setup ───────────────────────────────────────────────────
		if ( mode === 'carousel' ) {
			const cols = parseInt( block.dataset.cols ) || 3;
			const gap  = parseInt( block.dataset.gap )  || 20;
			let current = 0;
			let timer   = null;

			function setCarouselWidths() {
				const trackW = track.parentElement.offsetWidth;
				const slideW = ( trackW - gap * ( cols - 1 ) ) / cols;
				slides.forEach( s => {
					s.style.width  = slideW + 'px';
					s.style.height = block.style.height || '320px';
					s.style.borderRadius = block.style.borderRadius || '12px';
					s.style.overflow = 'hidden';
					s.style.flexShrink = '0';
				} );
				track.style.gap = gap + 'px';
				goTo( current, false );
			}

			function goTo( idx, animate ) {
				current = Math.max( 0, Math.min( idx, slides.length - cols ) );
				const slideW = slides[0] ? slides[0].offsetWidth + gap : 0;
				track.style.transition = animate !== false ? `transform ${animSpeed}ms ease` : 'none';
				track.style.transform  = `translateX(-${current * slideW}px)`;
				dots.forEach( ( d, i ) => d.classList.toggle( 'active', i === current ) );
			}

			function startTimer() {
				if ( ! autoplay ) return;
				timer = setInterval( () => {
					const next = current + 1 >= slides.length - cols + 1 ? 0 : current + 1;
					goTo( next, true );
				}, autoSpeed );
			}
			function stopTimer() { clearInterval( timer ); }

			setCarouselWidths();
			window.addEventListener( 'resize', setCarouselWidths );

			if ( prevBtn ) prevBtn.addEventListener( 'click', () => { goTo( current - 1, true ); stopTimer(); startTimer(); } );
			if ( nextBtn ) nextBtn.addEventListener( 'click', () => { goTo( current + 1, true ); stopTimer(); startTimer(); } );
			dots.forEach( ( d, i ) => d.addEventListener( 'click', () => { goTo( i, true ); stopTimer(); startTimer(); } ) );
			if ( pauseHover ) {
				block.addEventListener( 'mouseenter', stopTimer );
				block.addEventListener( 'mouseleave', startTimer );
			}
			startTimer();
			return;
		}

		// ── Slider / Hero setup ──────────────────────────────────────────────
		let current  = 0;
		let animating = false;
		let timer    = null;

		// Set transition speed on all slides
		slides.forEach( s => {
			s.style.transition = `opacity ${animSpeed}ms ease, transform ${animSpeed}ms ease`;
		} );

		function goTo( idx ) {
			if ( animating || idx === current ) return;
			animating = true;

			const leaving = slides[ current ];
			const entering = slides[ idx ];

			leaving.classList.add( 'leaving' );
			leaving.classList.remove( 'active' );

			entering.classList.add( 'active' );

			dots.forEach( ( d, i ) => d.classList.toggle( 'active', i === idx ) );
			current = idx;

			setTimeout( () => {
				leaving.classList.remove( 'leaving' );
				animating = false;
			}, animSpeed );
		}

		function next() { goTo( ( current + 1 ) % slides.length ); }
		function prev() { goTo( ( current - 1 + slides.length ) % slides.length ); }

		function startTimer() {
			if ( ! autoplay ) return;
			timer = setInterval( next, autoSpeed );
		}
		function stopTimer() { clearInterval( timer ); }

		if ( prevBtn ) prevBtn.addEventListener( 'click', () => { prev(); stopTimer(); startTimer(); } );
		if ( nextBtn ) nextBtn.addEventListener( 'click', () => { next(); stopTimer(); startTimer(); } );
		dots.forEach( ( d, i ) => d.addEventListener( 'click', () => { goTo( i ); stopTimer(); startTimer(); } ) );

		// Keyboard navigation
		block.setAttribute( 'tabindex', '0' );
		block.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'ArrowLeft' )  { prev(); stopTimer(); startTimer(); }
			if ( e.key === 'ArrowRight' ) { next(); stopTimer(); startTimer(); }
		} );

		// Touch / swipe support
		let touchStartX = 0;
		block.addEventListener( 'touchstart', ( e ) => { touchStartX = e.touches[0].clientX; }, { passive: true } );
		block.addEventListener( 'touchend', ( e ) => {
			const diff = touchStartX - e.changedTouches[0].clientX;
			if ( Math.abs( diff ) > 50 ) {
				diff > 0 ? next() : prev();
				stopTimer(); startTimer();
			}
		} );

		if ( pauseHover ) {
			block.addEventListener( 'mouseenter', stopTimer );
			block.addEventListener( 'mouseleave', startTimer );
		}

		startTimer();
	}

	// ── Init all sliders on page ─────────────────────────────────────────────
	function initAll() {
		document.querySelectorAll( '.nugba-slider-block' ).forEach( initSlider );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAll );
	} else {
		initAll();
	}
} )();
