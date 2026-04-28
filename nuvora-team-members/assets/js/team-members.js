/**
 * Team Members – Frontend JavaScript
 *
 * Features:
 *  - Click "Read more / Show less" to expand/collapse full bio
 *  - Keyboard accessible (Enter / Space on toggle button)
 *  - No jQuery dependency
 */
( function () {
  'use strict';

  /**
   * Initialise all .tm-card__toggle buttons found in the document.
   */
  function initToggles() {
    var toggles = document.querySelectorAll( '.tm-card__toggle' );

    toggles.forEach( function ( btn ) {
      btn.addEventListener( 'click', handleToggle );
    } );
  }

  /**
   * Handle toggle click.
   *
   * @param {MouseEvent|KeyboardEvent} e
   */
  function handleToggle( e ) {
    var btn      = e.currentTarget;
    var memberId = btn.getAttribute( 'data-member-id' );
    var bio      = document.getElementById( 'tm-bio-' + memberId );

    if ( ! bio ) return;

    var isExpanded = btn.getAttribute( 'aria-expanded' ) === 'true';

    if ( isExpanded ) {
      // Collapse
      bio.classList.remove( 'is-open' );
      btn.setAttribute( 'aria-expanded', 'false' );
      btn.textContent = btn.getAttribute( 'data-label-more' ) || 'Read more';

      // Re-add hidden after the CSS transition ends
      bio.addEventListener( 'transitionend', function onEnd() {
        bio.removeEventListener( 'transitionend', onEnd );
        bio.setAttribute( 'hidden', '' );
      } );

    } else {
      // Expand – remove hidden FIRST so the element is in flow, then
      // trigger transition on next frame.
      bio.removeAttribute( 'hidden' );
      bio.removeAttribute( 'style' ); // safety reset

      // Store the "more" label if not yet saved
      if ( ! btn.getAttribute( 'data-label-more' ) ) {
        btn.setAttribute( 'data-label-more', btn.textContent.trim() );
      }

      // rAF ensures the browser has painted the non-hidden state first
      requestAnimationFrame( function () {
        bio.classList.add( 'is-open' );
        btn.setAttribute( 'aria-expanded', 'true' );
        btn.textContent = btn.getAttribute( 'data-label-less' ) || 'Show less';
      } );
    }
  }

  // ── Boot ──────────────────────────────────────────────────────────────────

  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', initToggles );
  } else {
    initToggles();
  }

  // Re-init after Elementor renders widgets in the editor
  if ( window.elementorFrontend ) {
    window.elementorFrontend.hooks.addAction(
      'frontend/element_ready/tm_team_members.default',
      initToggles
    );
  }

} )();
