/**
 * Anexly AJAX Filter — filter.js
 * Slider: Auto slide + Mouse drag + Touch swipe
 */
(function ($) {
    'use strict';

    /* ── State ── */
    const state = {
        category: 'all',
        search: '',
        loading: false,
        currentSlide: 0,
        cardsPerView: 4,
        autoTimer: null,
        origCount: 0,
        prependCount: 0,
        isAnimating: false,
    };

    /* ── DOM refs ── */
    const $grid = $('#anexly-products-grid');
    const $viewport = $('#anexly-slider-viewport');
    const $loader = $('#anexly-loader');
    const $noResults = $('#anexly-no-results');
    const $dotsWrap = $('#anexly-slider-dots');
    const $search = $('#anexly-search-input');
    const $catBtns = $('.anexly-cat-btn');
    const $reset = $('#anexly-reset-btn');

    /* ─────────────────────────────
       RESPONSIVE: cards per view
    ───────────────────────────── */
    function isMobile() {
        return window.innerWidth <= 768;
    }

    function getCardsPerView() {
        const w = window.innerWidth;
        if (w < 480) return 1;
        if (w < 768) return 2;
        if (w < 1024) return 3;
        return 4;
    }

    /* ─────────────────────────────
       SET CARD WIDTHS
    ───────────────────────────── */
    function setCardWidths() {
        if (isMobile()) return; // mobile par CSS handle karega
        state.cardsPerView = getCardsPerView();
        const gap = 20;
        const vpWidth = $viewport.width();
        const cardWidth = (vpWidth - (gap * (state.cardsPerView - 1))) / state.cardsPerView;
        $grid.find('.anexly-product-card').css('width', cardWidth + 'px');
        updateSliderPosition(false);
    }

    /* ─────────────────────────────
       INFINITE LOOP SETUP
    ───────────────────────────── */
    function setupInfiniteLoop($grid, $originals) {
        state.origCount = $originals.length;
        
        // Remove any existing clones in case this is called on resize
        $grid.find('.clone').remove();
        
        state.cardsPerView = getCardsPerView();

        if (state.origCount <= state.cardsPerView) {
            state.prependCount = 0;
            state.currentSlide = 0;
            return;
        }

        const $prependClones = $originals.clone().addClass('clone').attr('data-clone', 'true');
        const $appendClones = $originals.clone().addClass('clone').attr('data-clone', 'true');
        
        $grid.prepend($prependClones);
        $grid.append($appendClones);
        
        state.prependCount = state.origCount;
        state.currentSlide = state.prependCount;
    }

    /* ─────────────────────────────
       BUILD DOTS
    ───────────────────────────── */
    function buildDots() {
        if (isMobile()) { $dotsWrap.empty(); return; }
        $dotsWrap.empty();
        
        // If we don't have enough items to slide, don't show dots
        if (state.origCount <= state.cardsPerView) return;

        for (let i = 0; i < state.origCount; i++) {
            const $dot = $('<button class="slider-dot"></button>');
            if (i === (state.currentSlide % state.origCount)) $dot.addClass('active');
            $dot.on('click', function () {
                if (state.isAnimating) return;
                goToSlide(state.prependCount + i, true);
                resetAutoSlide();
            });
            $dotsWrap.append($dot);
        }
    }

    /* ─────────────────────────────
       GO TO SLIDE
    ───────────────────────────── */
    function goToSlide(index, animate = true) {
        if (!state.origCount || state.origCount <= state.cardsPerView) return;
        if (state.isAnimating && animate) return;
        state.currentSlide = index;
        updateSliderPosition(animate);
        updateDots();
    }

    function updateSliderPosition(animate) {
        if (!state.origCount) return;
        const gap = 20;
        const cardWidth = parseFloat($grid.find('.anexly-product-card').first().css('width')) || 0;
        const offset = state.currentSlide * (cardWidth + gap);

        if (animate) {
            state.isAnimating = true;
            $grid.css('transition', 'transform 0.45s cubic-bezier(.4,0,.2,1)');
            $grid.css('transform', 'translateX(-' + offset + 'px)');
            
            setTimeout(function() {
                state.isAnimating = false;
                checkWrapAround();
            }, 450);
        } else {
            $grid.css('transition', 'none');
            $grid.css('transform', 'translateX(-' + offset + 'px)');
        }
    }

    function checkWrapAround() {
        if (!state.origCount || state.origCount <= state.cardsPerView) return;
        
        const appendStart = state.prependCount + state.origCount;
        if (state.currentSlide >= appendStart) {
            state.currentSlide -= state.origCount;
            updateSliderPosition(false);
        } else if (state.currentSlide < state.prependCount) {
            state.currentSlide += state.origCount;
            updateSliderPosition(false);
        }
    }

    function updateDots() {
        if (!state.origCount || state.origCount <= state.cardsPerView) return;
        let dotIndex = Math.abs(state.currentSlide % state.origCount);
        if (state.currentSlide < 0) {
            dotIndex = (state.origCount - (Math.abs(state.currentSlide) % state.origCount)) % state.origCount;
        }
        $dotsWrap.find('.slider-dot').removeClass('active').eq(dotIndex).addClass('active');
    }

    /* ─────────────────────────────
       AUTO SLIDE (every 3 seconds)
    ───────────────────────────── */
    function startAutoSlide() {
        if (isMobile()) return;
        stopAutoSlide();
        state.autoTimer = setInterval(function () {
            if (!state.isAnimating && state.origCount > state.cardsPerView) {
                goToSlide(state.currentSlide + 1, true);
            }
        }, 3000);
    }

    function stopAutoSlide() {
        if (state.autoTimer) {
            clearInterval(state.autoTimer);
            state.autoTimer = null;
        }
    }

    function resetAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
    }

    /* ─────────────────────────────
       MOUSE DRAG (Desktop)
    ───────────────────────────── */
    let isDragging = false;
    let dragStartX = 0;
    let dragCurrentX = 0;
    let dragStartSlide = 0;

    $viewport.on('mousedown', function (e) {
        if (isMobile() || state.origCount <= state.cardsPerView) return;
        isDragging = true;
        dragStartX = e.clientX;
        dragStartSlide = state.currentSlide;
        $viewport.addClass('is-dragging');
        $grid.css('transition', 'none');
        stopAutoSlide();
        e.preventDefault();
    });

    $(document).on('mousemove', function (e) {
        if (!isDragging) return;
        dragCurrentX = e.clientX;
        const diff = dragStartX - dragCurrentX;
        const cardWidth = parseFloat($grid.find('.anexly-product-card').first().css('width')) || 0;
        const gap = 20;
        const baseOffset = dragStartSlide * (cardWidth + gap);
        $grid.css('transform', 'translateX(-' + (baseOffset + diff) + 'px)');
    });

    $(document).on('mouseup', function (e) {
        if (!isDragging) return;
        isDragging = false;
        $viewport.removeClass('is-dragging');

        const diff = dragStartX - e.clientX;
        if (Math.abs(diff) > 60) {
            diff > 0
                ? goToSlide(dragStartSlide + 1, true)
                : goToSlide(dragStartSlide - 1, true);
        } else {
            goToSlide(dragStartSlide, true); // snap back
        }
        resetAutoSlide();
    });

    // Prevent image drag interference
    $viewport.on('dragstart', function (e) { e.preventDefault(); });

    /* ─────────────────────────────
       TOUCH / SWIPE (Mobile)
    ───────────────────────────── */
    let touchStartX = 0;
    let touchStartSlide = 0;

    $viewport[0].addEventListener('touchstart', function (e) {
        if (state.origCount <= state.cardsPerView) return;
        touchStartX = e.touches[0].clientX;
        touchStartSlide = state.currentSlide;
        $grid.css('transition', 'none');
        stopAutoSlide();
    }, { passive: true });

    $viewport[0].addEventListener('touchmove', function (e) {
        if (state.origCount <= state.cardsPerView) return;
        const diff = touchStartX - e.touches[0].clientX;
        const cardWidth = parseFloat($grid.find('.anexly-product-card').first().css('width')) || 0;
        const gap = 20;
        const baseOffset = touchStartSlide * (cardWidth + gap);
        $grid.css('transform', 'translateX(-' + (baseOffset + diff) + 'px)');
    }, { passive: true });

    $viewport[0].addEventListener('touchend', function (e) {
        if (state.origCount <= state.cardsPerView) return;
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            diff > 0
                ? goToSlide(touchStartSlide + 1, true)
                : goToSlide(touchStartSlide - 1, true);
        } else {
            goToSlide(touchStartSlide, true);
        }
        resetAutoSlide();
    }, { passive: true });

    /* ─────────────────────────────
       LOADER
    ───────────────────────────── */
    function showLoader() {
        state.loading = true;
        $loader.addClass('visible');
        $grid.addClass('is-loading');
    }

    function hideLoader() {
        state.loading = false;
        $loader.removeClass('visible');
        $grid.removeClass('is-loading');
    }

    /* ─────────────────────────────
       FETCH PRODUCTS
    ───────────────────────────── */
    function fetchProducts() {
        if (state.loading) return;
        stopAutoSlide();
        showLoader();

        $.ajax({
            url: AnexlyFilter.ajax_url,
            method: 'POST',
            data: {
                action: 'anexly_filter_products',
                nonce: AnexlyFilter.nonce,
                category: state.category,
                search: state.search,
                page: 1,
                per_page: 10,
            },
            success: function (response) {
                hideLoader();

                if (!response.success || !response.data.html || response.data.html.trim() === '') {
                    $grid.html('');
                    $noResults.show();
                    $dotsWrap.empty();
                    state.origCount = 0;
                    return;
                }

                $noResults.hide();
                $grid.css('transition', 'none');
                $grid.css('transform', 'translateX(0)');
                $grid.html(response.data.html);

                const $originals = $grid.find('.anexly-product-card');
                setupInfiniteLoop($grid, $originals);

                setTimeout(function () {
                    setCardWidths();
                    buildDots();
                    updateSliderPosition(false);
                    startAutoSlide();
                }, 50);
            },
            error: function () {
                hideLoader();
                $grid.html('');
                $noResults.show();
            },
        });
    }

    /* ─────────────────────────────
       CATEGORY CLICK
    ───────────────────────────── */
    $catBtns.on('click', function () {
        const $btn = $(this);
        if ($btn.hasClass('active')) return;
        $catBtns.removeClass('active');
        $btn.addClass('active');
        state.category = $btn.data('cat') || 'all';
        fetchProducts();
    });

    /* ─────────────────────────────
       SEARCH
    ───────────────────────────── */
    let searchTimer;
    $search.on('input', function () {
        clearTimeout(searchTimer);
        const val = $(this).val().trim();
        searchTimer = setTimeout(function () {
            state.search = val;
            fetchProducts();
        }, 420);
    });

    $search.on('keydown', function (e) {
        if (e.key === 'Enter') {
            clearTimeout(searchTimer);
            state.search = $(this).val().trim();
            fetchProducts();
        }
    });

    $('#anexly-search-btn').on('click', function () {
        clearTimeout(searchTimer);
        state.search = $search.val().trim();
        fetchProducts();
    });

    /* ─────────────────────────────
       RESET
    ───────────────────────────── */
    $reset.on('click', function () {
        state.category = 'all';
        state.search = '';
        $catBtns.removeClass('active').filter('[data-cat="all"]').addClass('active');
        $search.val('');
        $noResults.hide();
        fetchProducts();
    });

    /* ─────────────────────────────
       RESIZE
    ───────────────────────────── */
    let resizeTimer;
    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            const $originals = $grid.find('.anexly-product-card:not(.clone)');
            setupInfiniteLoop($grid, $originals);
            setCardWidths();
            buildDots();
            updateSliderPosition(false);
        }, 200);
    });

    /* ─────────────────────────────
       INIT
    ───────────────────────────── */
    setTimeout(function () {
        const $originals = $grid.find('.anexly-product-card');
        setupInfiniteLoop($grid, $originals);

        setCardWidths();
        buildDots();
        updateSliderPosition(false);
        startAutoSlide();
    }, 100);

})(jQuery);