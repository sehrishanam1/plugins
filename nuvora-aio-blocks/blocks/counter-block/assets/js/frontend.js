/**
 * Nuvora Counter Block - Frontend Animation Script
 */
( function () {
	'use strict';

	/**
	 * Easing functions
	 */
	const easings = {
		'linear':    t => t,
		'ease-out':  t => 1 - Math.pow( 1 - t, 3 ),
		'ease-in':   t => t * t * t,
		'ease-in-out': t => t < 0.5 ? 4 * t * t * t : 1 - Math.pow( -2 * t + 2, 3 ) / 2,
		'bounce':    t => {
			const n1 = 7.5625, d1 = 2.75;
			if ( t < 1 / d1 )      return n1 * t * t;
			else if ( t < 2 / d1 ) return n1 * ( t -= 1.5 / d1 ) * t + 0.75;
			else if ( t < 2.5 / d1 ) return n1 * ( t -= 2.25 / d1 ) * t + 0.9375;
			else                   return n1 * ( t -= 2.625 / d1 ) * t + 0.984375;
		},
		'elastic': t => {
			if ( t === 0 || t === 1 ) return t;
			return Math.pow( 2, -10 * t ) * Math.sin( ( t * 10 - 0.75 ) * ( 2 * Math.PI ) / 3 ) + 1;
		},
	};

	/**
	 * Format number with separator and decimals
	 */
	function formatNumber( num, decimals, separator, prefix, suffix ) {
		let formatted;
		if ( decimals > 0 ) {
			formatted = num.toFixed( decimals );
		} else {
			formatted = Math.round( num ).toString();
		}

		if ( separator ) {
			const parts = formatted.split( '.' );
			parts[0] = parts[0].replace( /\B(?=(\d{3})+(?!\d))/g, separator );
			formatted = parts.join( '.' );
		}

		return prefix + formatted + suffix;
	}

	/**
	 * Animate a single counter element
	 */
	function animateCounter( el ) {
		const end      = parseFloat( el.dataset.end )      || 0;
		const start    = parseFloat( el.dataset.start )    || 0;
		const duration = parseInt( el.dataset.duration )   || 2000;
		const sep      = el.dataset.separator              || '';
		const decimals = parseInt( el.dataset.decimals )   || 0;
		const easing   = el.dataset.easing                 || 'ease-out';
		const easeFn   = easings[ easing ] || easings['ease-out'];

		// Read prefix/suffix from current text
		const fullText = el.textContent;
		// prefix = everything before first digit
		const prefixMatch = fullText.match( /^([^\d]*)/ );
		const suffixMatch = fullText.match( /([^\d\.,]*)$/ );
		const prefix = prefixMatch ? prefixMatch[1] : '';
		const suffix = suffixMatch ? suffixMatch[1] : '';

		const startTime = performance.now();

		function update( now ) {
			const elapsed  = now - startTime;
			const progress = Math.min( elapsed / duration, 1 );
			const eased    = easeFn( progress );
			const current  = start + ( end - start ) * eased;

			el.textContent = formatNumber( current, decimals, sep, prefix, suffix );

			if ( progress < 1 ) {
				requestAnimationFrame( update );
			} else {
				el.textContent = formatNumber( end, decimals, sep, prefix, suffix );
			}
		}

		requestAnimationFrame( update );
	}

	/**
	 * Observe and trigger counters when they enter viewport
	 */
	function initCounters() {
		const blocks = document.querySelectorAll( '.nuvora-counter-block.nuvora-animate' );

		if ( ! blocks.length ) return;

		if ( 'IntersectionObserver' in window ) {
			const observer = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting && ! entry.target.classList.contains( 'nuvora-counted' ) ) {
						entry.target.classList.add( 'nuvora-counted' );
						const counter = entry.target.querySelector( '.nuvora-counter-number' );
						if ( counter ) animateCounter( counter );
						observer.unobserve( entry.target );
					}
				} );
			}, { threshold: 0.3 } );

			blocks.forEach( function ( block ) {
				observer.observe( block );
			} );
		} else {
			// Fallback: run all immediately
			blocks.forEach( function ( block ) {
				block.classList.add( 'nuvora-counted' );
				const counter = block.querySelector( '.nuvora-counter-number' );
				if ( counter ) animateCounter( counter );
			} );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initCounters );
	} else {
		initCounters();
	}
} )();
