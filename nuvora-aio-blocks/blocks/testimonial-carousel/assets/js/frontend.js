/**
 * Nuvora Testimonial Carousel – Frontend
 */
( function () {
	'use strict';

	function initCarousel( block ) {
		const track    = block.querySelector( '.nuvora-tc-track' );
		const slides   = block.querySelectorAll( '.nuvora-tc-slide' );
		const dots     = block.querySelectorAll( '.nuvora-tc-dot' );
		const prevBtn  = block.querySelector( '.nuvora-tc-prev' );
		const nextBtn  = block.querySelector( '.nuvora-tc-next' );
		const autoplay = block.dataset.autoplay === 'true';
		const speed    = parseInt( block.dataset.speed ) || 4000;
		let current    = 0;
		let timer      = null;

		if ( ! slides.length ) return;

		function goTo( index ) {
			current = ( index + slides.length ) % slides.length;
			track.style.transform = `translateX(-${current * 100}%)`;
			dots.forEach( ( d, i ) => d.classList.toggle( 'active', i === current ) );
		}

		if ( prevBtn ) prevBtn.addEventListener( 'click', () => { goTo( current - 1 ); resetTimer(); } );
		if ( nextBtn ) nextBtn.addEventListener( 'click', () => { goTo( current + 1 ); resetTimer(); } );
		dots.forEach( ( d, i ) => d.addEventListener( 'click', () => { goTo( i ); resetTimer(); } ) );

		// Touch / swipe
		let startX = 0;
		track.addEventListener( 'touchstart', e => { startX = e.touches[0].clientX; }, { passive: true } );
		track.addEventListener( 'touchend', e => {
			const diff = startX - e.changedTouches[0].clientX;
			if ( Math.abs( diff ) > 40 ) { goTo( diff > 0 ? current + 1 : current - 1 ); resetTimer(); }
		} );

		function startTimer() {
			if ( autoplay && slides.length > 1 ) {
				timer = setInterval( () => goTo( current + 1 ), speed );
			}
		}

		function resetTimer() {
			clearInterval( timer );
			startTimer();
		}

		goTo( 0 );
		startTimer();
	}

	function init() {
		document.querySelectorAll( '.nuvora-testimonial-block' ).forEach( initCarousel );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
