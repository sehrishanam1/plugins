/**
 * Reading Time Estimator – Progress Bar JS
 *
 * Vanilla JS. No jQuery. No frameworks. Tiny footprint (~1.5 KB gzipped).
 * Fully accessible: ARIA progressbar role, aria-valuenow updates.
 * Respects prefers-reduced-motion via CSS and optionally via JS config.
 *
 * @author Sehrish Anam
 * @since  1.0.0
 */

( function () {
	'use strict';

	/* ── Guard ─────────────────────────────────────────── */
	var config = window.nvrtpConfig || {};

	if ( ! config.showProgress ) {
		return;
	}

	/* ── DOM refs ───────────────────────────────────────── */
	var bar     = document.getElementById( 'nvrtp-progress-bar' );
	var fill    = document.getElementById( 'nvrtp-progress-bar__fill' );
	var tooltip = document.getElementById( 'nvrtp-progress-bar__tooltip' );

	if ( ! bar || ! fill ) {
		return;
	}

	/* ── Apply CSS custom properties from PHP config ────── */
	var root = document.documentElement;

	if ( config.progressColor ) {
		root.style.setProperty( '--nvrtp-color', config.progressColor );
	}
	if ( config.progressBgColor ) {
		root.style.setProperty( '--nvrtp-color-bg', config.progressBgColor );
	}
	if ( config.progressHeight ) {
		root.style.setProperty( '--nvrtp-bar-height', config.progressHeight + 'px' );
	}

	/* ── Reduce-motion support ──────────────────────────── */
	var reduceMotion =
		config.respectReduceMotion &&
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	if ( reduceMotion ) {
		fill.style.transition = 'none';
	}

	/* ── Calculate scroll progress ──────────────────────── */
	/**
	 * Returns a value 0–100 representing the % of the page scrolled.
	 *
	 * Uses the article / main content element if found so the
	 * bar truly reflects reading progress (not page height).
	 *
	 * @returns {number}
	 */
	function getScrollPercent() {
		var docEl   = document.documentElement;
		var body    = document.body;

		// Try to find the article / main content container.
		// Supports common selectors used by major themes.
		var article =
			document.querySelector( 'article .entry-content' ) ||
			document.querySelector( '.entry-content' ) ||
			document.querySelector( 'article' ) ||
			document.querySelector( 'main' );

		var scrollTop  = docEl.scrollTop || body.scrollTop;
		var windowH    = docEl.clientHeight || window.innerHeight;

		var totalH, startOffset;

		if ( article ) {
			var rect = article.getBoundingClientRect();
			// Position relative to document (not viewport).
			startOffset = rect.top + scrollTop;
			totalH      = article.offsetHeight;
		} else {
			startOffset = 0;
			totalH      = Math.max(
				body.scrollHeight,
				body.offsetHeight,
				docEl.scrollHeight,
				docEl.offsetHeight
			) - windowH;
		}

		if ( totalH <= 0 ) {
			return 0;
		}

		var scrolled  = scrollTop - startOffset;
		var scrollEnd = totalH - ( article ? windowH : 0 );

		if ( scrollEnd <= 0 ) {
			return 100;
		}

		var percent = ( scrolled / scrollEnd ) * 100;
		return Math.min( 100, Math.max( 0, Math.round( percent ) ) );
	}

	/* ── Update DOM ─────────────────────────────────────── */
	var lastPercent = -1;

	function update() {
		var pct = getScrollPercent();

		if ( pct === lastPercent ) {
			return; // No DOM write needed.
		}
		lastPercent = pct;

		// CSS custom property drives the fill width.
		bar.style.setProperty( '--nvrtp-progress', pct + '%' );

		// ARIA.
		bar.setAttribute( 'aria-valuenow', pct );

		// Optional tooltip.
		if ( tooltip && config.showTooltip ) {
			var label = ( pct >= 100 )
				? ( config.i18n.complete || 'Complete' )
				: pct + '%';
			tooltip.textContent = label;
		}
	}

	/* ── Scroll listener with rAF throttle ─────────────── */
	var ticking = false;

	function onScroll() {
		if ( ticking ) {
			return;
		}
		ticking = true;
		requestAnimationFrame( function () {
			update();
			ticking = false;
		} );
	}

	/* ── Keyboard accessibility & Tooltip ───────────── */
	// Allow the progress bar to be read by keyboard users
	bar.setAttribute( 'tabindex', '0' );

	/* ── Tooltip follows cursor ─────────────────────── */
	if ( tooltip && config.showTooltip ) {
		var dataPosition = bar.getAttribute( 'data-position' ) || 'top';
		
		bar.addEventListener( 'mouseenter', function() {
			tooltip.style.opacity = '1';
		} );
		
		bar.addEventListener( 'mouseleave', function() {
			tooltip.style.opacity = '0';
		} );
		
		bar.addEventListener( 'mousemove', function( e ) {
			var barRect = bar.getBoundingClientRect();
			
			// Position tooltip based on bar position
			if ( dataPosition === 'bottom' ) {
				// Bar is at bottom, show tooltip ABOVE it
				// Use bottom positioning instead of top
				var distanceFromBottom = window.innerHeight - barRect.top;
				tooltip.style.bottom = ( distanceFromBottom + 8 ) + 'px';
				tooltip.style.top = 'auto';
			} else {
				// Bar is at top, show tooltip BELOW it
				tooltip.style.top = ( barRect.bottom + 8 ) + 'px';
				tooltip.style.bottom = 'auto';
			}
			
			tooltip.style.left = e.clientX + 'px';
		} );
		
		// Handle focus for keyboard users
		bar.addEventListener( 'focus', function () {
			tooltip.style.opacity = '1';
			var barRect = bar.getBoundingClientRect();
			tooltip.style.left = ( barRect.left + barRect.width / 2 ) + 'px';
			
			if ( dataPosition === 'bottom' ) {
				var distanceFromBottom = window.innerHeight - barRect.top;
				tooltip.style.bottom = ( distanceFromBottom + 8 ) + 'px';
				tooltip.style.top = 'auto';
			} else {
				tooltip.style.top = ( barRect.bottom + 8 ) + 'px';
				tooltip.style.bottom = 'auto';
			}
		} );
		
		bar.addEventListener( 'blur', function () {
			tooltip.style.opacity = '0';
		} );
	}
	/* ── Init ───────────────────────────────────────────── */
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll, { passive: true } );

	// Run once immediately in case page is loaded mid-scroll.
	update();

} )();
