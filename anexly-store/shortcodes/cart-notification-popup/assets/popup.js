/**
 * Leads Popup — Anexly Store
 *
 * Rules:
 *  - Only shows when cart has items (PHP already verified this)
 *  - Triggers on scroll — at a RANDOM scroll % (between 20–60%) chosen fresh each page load
 *  - Respects max-shows limit and cooldown via localStorage
 *
 * localStorage keys:
 *  alp_show_count  — total times shown to this browser
 *  alp_last_shown  — timestamp (ms) of last show
 */
(function ($) {
    'use strict';

    var cfg   = window.ALP_DATA || {};
    var items = cfg.cartItems   || [];

    // PHP already checks cart, but double-guard in JS too
    if ( ! items.length ) return;

    var STORE_COUNT = 'alp_show_count';
    var STORE_LAST  = 'alp_last_shown';

    /* ---- localStorage helpers ------------------------------------ */
    function lsGet(k)    { try { return localStorage.getItem(k); }  catch(e){ return null; } }
    function lsSet(k, v) { try { localStorage.setItem(k, v); }      catch(e){} }

    /* ---- frequency ---------------------------------------- */
    function getCount()  { return parseInt( lsGet(STORE_COUNT) || '0', 10 ); }
    function getLastMs() { return parseInt( lsGet(STORE_LAST)  || '0', 10 ); }

    function canShow() {
        if ( getCount() >= cfg.maxShows ) return false;
        var cooldownMs = (cfg.cooldownMin || 30) * 60 * 1000;
        if ( getLastMs() > 0 && ( Date.now() - getLastMs() ) < cooldownMs ) return false;
        return true;
    }

    function recordShow() {
        lsSet( STORE_COUNT, getCount() + 1 );
        lsSet( STORE_LAST,  Date.now() );
    }

    /* ---- build HTML ---------------------------------------------- */
    function buildHTML() {
        var rows = '';
        items.forEach(function(item) {
            var thumb = item.image
                ? '<img class="alp-item-img" src="' + esc(item.image) + '" alt="' + esc(item.name) + '">'
                : '<div class="alp-item-icon">🛒</div>';

            var variationHtml = item.variation ? ' <span style="font-weight: normal; color: #666;">' + esc(item.variation) + '</span>' : '';
            var descHtml = item.desc ? '<div style="font-size: 0.85em; color: #777; margin-top: 5px; line-height: 1.3;">' + esc(item.desc) + '</div>' : '';
            var qtyHtml = item.qty > 1 ? '<div style="font-size: 0.8em; margin-top: 3px; color: #888;">Qty: ' + item.qty + '</div>' : '';

            rows +=
                '<div class="alp-item">' +
                    thumb +
                    '<div class="alp-item-info" style="align-self: center; margin-left: 10px; max-width: 65%;">' +
                        '<div style="font-size: 14px; margin-bottom: 2px;">' +
                            '<strong>' + esc(item.name) + '</strong>' + variationHtml +
                        '</div>' +
                        descHtml +
                        qtyHtml +
                    '</div>' +
                    '<div class="alp-item-price" style="margin-left: auto; align-self: center;">' + item.price + '</div>' +
                '</div>';
        });

        var topIconHtml = cfg.iconImg 
            ? '<div class="alp-icon-wrap" style="background:transparent;"><img src="' + esc(cfg.iconImg) + '" style="width:100%; object-fit:contain; border-radius:50%;" alt="Icon"></div>'
            : '<div class="alp-icon-wrap">&#128717;</div>';

        return (
            '<div id="alp-overlay"></div>' +
            '<div id="alp-modal" role="dialog" aria-modal="true" aria-label="Cart Reminder">' +
                '<button id="alp-close" aria-label="Close">&#x2715;</button>' +
                topIconHtml +
                '<h2>You Have Items<br>In Your Cart!</h2>' +
                '<p class="alp-sub">Complete your purchase before they sell out.</p>' +
                '<div class="alp-items">' + rows + '</div>' +
                '<a href="' + esc(cfg.checkoutUrl) + '" class="alp-btn">Complete Purchase</a>' +
                '<p class="alp-dismiss"><a href="#" id="alp-no-thanks">No thanks</a></p>' +
            '</div>'
        );
    }

    function esc(str) {
        return $('<div>').text(String(str || '')).html();
    }

    /* ---- show / hide --------------------------------------------- */
    var popupBuilt   = false;
    var popupVisible = false;

    function ensureBuilt() {
        if ( popupBuilt ) return;
        $('body').append( buildHTML() );
        popupBuilt = true;

        $(document).on('click', '#alp-close, #alp-overlay, #alp-no-thanks', function(e) {
            e.preventDefault();
            hidePopup();
        });
        $(document).on('keydown', function(e) {
            if ( e.key === 'Escape' ) hidePopup();
        });
    }

    function showPopup() {
        if ( popupVisible ) return;
        if ( ! canShow() )  return;
        ensureBuilt();
        $('#alp-overlay, #alp-modal').fadeIn(280);
        popupVisible = true;
        recordShow();
        $(window).off('scroll.alp'); // unbind after first show
    }

    function hidePopup() {
        $('#alp-overlay, #alp-modal').fadeOut(200);
        popupVisible = false;
    }

    /* ---- random scroll trigger ----------------------------------- */
    // Pick a fresh random scroll % between 20 and 60 on every page load
    var triggerPct = Math.floor( Math.random() * 41 ) + 20; // 20–60

    $(document).ready(function () {
        if ( ! canShow() ) return;

        $(window).on('scroll.alp', function() {
            var docH    = $(document).height();
            var winH    = $(window).height();
            var scrolled = $(window).scrollTop();

            // Avoid divide-by-zero on short pages
            if ( docH <= winH ) {
                showPopup();
                return;
            }

            var pct = ( scrolled / ( docH - winH ) ) * 100;
            if ( pct >= triggerPct ) {
                showPopup();
            }
        });
    });

})(jQuery);