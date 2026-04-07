( function () {
    'use strict';

    function initSlider( wrap ) {
        var config     = JSON.parse( wrap.getAttribute('data-config') || '{}' );
        var track      = wrap.querySelector('.sg-ss__track');
        var viewport   = wrap.querySelector('.sg-ss__viewport');
        var btnPrev    = wrap.querySelector('.sg-ss__arrow--prev');
        var btnNext    = wrap.querySelector('.sg-ss__arrow--next');

        if ( ! track ) return;

        var cards      = Array.prototype.slice.call( track.children );
        var total      = cards.length;
        if ( total === 0 ) return;

        var visible    = Math.min( config.visibleCards || 3, total );
        var current    = 0;
        var autoTimer  = null;
        var isAnimating = false;

        // Set CSS variable for card width calc
        track.style.setProperty('--visible', visible);

        function getCardWidth() {
            return viewport.offsetWidth / visible;
        }

        function goTo( index, animate ) {
            if ( isAnimating ) return;
            isAnimating = true;

            // Clamp
            if ( index < 0 ) index = total - visible;
            if ( index > total - visible ) index = 0;
            current = index;

            var offset = -( current * getCardWidth() );
            track.style.transition = animate === false ? 'none' : 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            track.style.transform  = 'translateX(' + offset + 'px)';

            setTimeout( function () { isAnimating = false; }, 520 );

            updateArrows();
        }

        function updateArrows() {
            if ( ! btnPrev || ! btnNext ) return;
            btnPrev.style.opacity = current === 0 ? '0.3' : '1';
            btnNext.style.opacity = current >= total - visible ? '0.3' : '1';
        }

        function startAuto() {
            if ( ! config.autoPlay ) return;
            stopAuto();
            autoTimer = setInterval( function () {
                var next = current + 1;
                if ( next > total - visible ) next = 0;
                goTo( next, true );
            }, config.autoPlayDelay || 3000 );
        }

        function stopAuto() {
            if ( autoTimer ) { clearInterval( autoTimer ); autoTimer = null; }
        }

        // Arrow buttons
        if ( btnPrev ) {
            btnPrev.addEventListener('click', function () {
                stopAuto();
                goTo( current - 1, true );
                startAuto();
            });
        }
        if ( btnNext ) {
            btnNext.addEventListener('click', function () {
                stopAuto();
                goTo( current + 1, true );
                startAuto();
            });
        }

        // Pause on hover
        wrap.addEventListener('mouseenter', stopAuto );
        wrap.addEventListener('mouseleave', startAuto );

        // Touch / swipe support
        var touchStartX = 0;
        var touchEndX   = 0;
        viewport.addEventListener('touchstart', function(e){ touchStartX = e.changedTouches[0].screenX; }, { passive:true });
        viewport.addEventListener('touchend', function(e){
            touchEndX = e.changedTouches[0].screenX;
            var diff  = touchStartX - touchEndX;
            if ( Math.abs(diff) > 40 ) {
                stopAuto();
                goTo( diff > 0 ? current + 1 : current - 1, true );
                startAuto();
            }
        });

        // Recalc on resize
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout( resizeTimer );
            resizeTimer = setTimeout( function () {
                goTo( current, false );
            }, 100 );
        });

        // Init
        goTo( 0, false );
        startAuto();
    }

    function initAll() {
        var sliders = document.querySelectorAll('.sg-ss__slider-wrap');
        sliders.forEach( function( wrap ) {
            initSlider( wrap );
        });
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener('DOMContentLoaded', initAll );
    } else {
        initAll();
    }

} )();
