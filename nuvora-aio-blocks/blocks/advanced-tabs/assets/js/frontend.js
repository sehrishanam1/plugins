/**
 * Nuvora Advanced Tabs – Frontend
 */
( function () {
	'use strict';

	function initTabs( block ) {
		const buttons  = block.querySelectorAll( '.nuvora-tab-btn' );
		const panels   = block.querySelectorAll( '.nuvora-tab-panel' );
		const layout   = block.className.match( /nuvora-tabs-layout-(\S+)/ )?.[1] || 'style1';
		const activeBg     = block.dataset.activeBg    || '#6c63ff';
		const activeText   = block.dataset.activeText  || '#ffffff';
		const inactiveBg   = block.dataset.inactiveBg  || '#f5f5f5';
		const inactiveText = block.dataset.inactiveText || '#555555';

		function activate( index ) {
			buttons.forEach( ( btn, i ) => {
				const isActive = i === index;
				btn.classList.toggle( 'active', isActive );
				btn.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );

				switch ( layout ) {
					case 'style2':
						btn.style.color       = isActive ? activeBg : inactiveText;
						btn.style.borderBottom = isActive ? `2px solid ${activeBg}` : '2px solid transparent';
						break;
					case 'style3':
					case 'style4':
					default:
						btn.style.background = isActive ? activeBg : inactiveBg;
						btn.style.color      = isActive ? activeText : inactiveText;
				}
			} );

			panels.forEach( ( panel, i ) => {
				panel.classList.toggle( 'active', i === index );
			} );
		}

		buttons.forEach( ( btn, i ) => {
			btn.addEventListener( 'click', () => activate( i ) );
		} );

		// Keyboard navigation
		buttons.forEach( ( btn, i ) => {
			btn.addEventListener( 'keydown', e => {
				if ( e.key === 'ArrowRight' ) { activate( ( i + 1 ) % buttons.length ); buttons[ ( i + 1 ) % buttons.length ].focus(); }
				if ( e.key === 'ArrowLeft' )  { activate( ( i - 1 + buttons.length ) % buttons.length ); buttons[ ( i - 1 + buttons.length ) % buttons.length ].focus(); }
			} );
		} );
	}

	function init() {
		document.querySelectorAll( '.nuvora-tabs-block' ).forEach( initTabs );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
