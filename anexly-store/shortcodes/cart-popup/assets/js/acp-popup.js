/**
 * Anexly Cart Popup — acp-popup.js
 *
 * Uses capture-phase event listeners to intercept cart clicks
 * BEFORE the theme's own handlers run, preventing double popups.
 */
(function ($) {
  'use strict';

  /* ── State ──────────────────────────────────────────────────── */
  var cartData    = null;
  var isOpen      = false;
  var isFetching  = false;
  var isRendering = false;

  /* ── DOM refs (set after document ready) ────────────────────── */
  var $overlay, $popup, $close, $itemsWrap,
      $discBox, $barFill, $discStatus, $progLabels,
      $recList, $subtotal, $discount, $discRow, $total, $badge;

  /* ================================================================
   * CAPTURE-PHASE INTERCEPTION
   *
   * addEventListener with useCapture=true fires BEFORE any jQuery
   * .on() handlers (which are bubble-phase). This guarantees our
   * handler runs first and stopImmediatePropagation() prevents the
   * theme's mini-cart from ever receiving the event.
   * ============================================================== */
  document.addEventListener('click', function (e) {
    var el = e.target;

    // Walk up DOM to find cart trigger ancestor
    while (el && el !== document.body) {
      var dataJs = el.getAttribute && el.getAttribute('data-js');
      var isThemeCartBtn =
        dataJs === 'open-mini-cart' ||
        ( el.tagName === 'BUTTON' &&
          el.classList.contains('cart') &&
          el.classList.contains('icon-button') );

      if (isThemeCartBtn) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        openPopup();
        return;
      }
      el = el.parentElement;
    }
  }, true); // <-- CAPTURE PHASE — fires before ALL bubble-phase jQuery handlers

  /* ================================================================
   * INIT — cache DOM refs after page is ready
   * ============================================================== */
  $(function () {
    $overlay    = $('#acp-overlay');
    $popup      = $('#acp-popup');
    $close      = $('#acp-close');
    $itemsWrap  = $('#acp-items-wrap');
    $discBox    = $('#acp-discount-box');
    $barFill    = $('#acp-bar-fill');
    $discStatus = $('#acp-discount-status');
    $progLabels = $('#acp-progress-labels');
    $recList    = $('#acp-rec-list');
    $subtotal   = $('#acp-subtotal');
    $discount   = $('#acp-discount');
    $discRow    = $('#acp-discount-row');
    $total      = $('#acp-total');
    $badge      = $('#acp-count-badge');

    killThemeCartPopup();
    bindEvents();
  });

  /* ================================================================
   * KILL THEME CART POPUP
   *
   * 1. Injects CSS to permanently hide any theme mini-cart drawer.
   * 2. Replaces the cart buttons with clones (strips theme's
   *    addEventListener handlers that were bound directly to the node).
   * ============================================================== */
  function killThemeCartPopup() {

    /* 1. CSS — hide theme mini-cart containers */
    var css = [
      '.mini-cart-wrap:not(#acp-popup):not(#acp-overlay) { display:none!important; }',
      '.cart-dropdown:not(#acp-popup) { display:none!important; }',
      /* Flatsome */
      '.cart-sidebar:not(#acp-popup) { display:none!important; }',
      /* Blocksy */
      '[data-id="cart-sidebar"] { display:none!important; }',
      /* Astra */
      '.ast-mini-cart-drawer { display:none!important; }',
      /* OceanWP */
      '#oceanwp-cart-sidebar-wrap { display:none!important; }',
      /* Generic overlays */
      '.woo-overlay:not(#acp-overlay) { display:none!important; }',
      '.cart-overlay:not(#acp-overlay) { display:none!important; }',
    ].join('\n');

    var style      = document.createElement('style');
    style.id       = 'acp-kill-theme-cart';
    style.textContent = css;
    document.head.appendChild(style);

    /* 2. Clone buttons to strip any addEventListener() handlers
          that the theme attached directly to the DOM node.
          We re-attach our own capture listener on the clone.       */
    setTimeout(function () {
      var selectors = ['[data-js="open-mini-cart"]', 'button.icon-button.cart'];
      selectors.forEach(function (sel) {
        document.querySelectorAll(sel).forEach(function (btn) {
          var clone = btn.cloneNode(true);
          clone.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            openPopup();
          }, true);
          if (btn.parentNode) btn.parentNode.replaceChild(clone, btn);
        });
      });
    }, 0);
  }

  /* ================================================================
   * BIND EVENTS
   * ============================================================== */
  function bindEvents() {

    /* Close triggers */
    $(document).on('click', '#acp-close',   closePopup);
    $(document).on('click', '#acp-overlay', closePopup);
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && isOpen) closePopup();
    });
    $(document).on('click', '.acp-footer-btns a', function () {
      closePopup();
    });
    
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) { closePopup(); }
    });

    /* Our own trigger button (if present) */
    $(document).on('click', '#acp-trigger-btn', function (e) {
      e.preventDefault();
      openPopup();
    });

    /* Generic WC cart links — fallback for other themes */
    var CART_LINK_SELECTORS = [
      'a.cart-contents',
      'a.wc-block-cart-button',
      'a.woocommerce-cart-link',
      '.header-cart > a',
      '#site-header-cart > a',
    ].join(', ');

    $(document).on('click', CART_LINK_SELECTORS, function (e) {
      var href = $(this).attr('href') || '';
      if (href.indexOf('checkout')    > -1) return;
      if (href.indexOf('remove_item') > -1) return;
      if ($(this).closest('#acp-popup').length) return;
      e.preventDefault();
      e.stopImmediatePropagation();
      openPopup();
    });

    /* Delegated events inside popup */
    $(document).on('click', '#acp-popup .acp-qty-plus', function () {
      var $qty = $(this).closest('.acp-qty');
      ajaxUpdateQty($qty.data('key'), parseInt($qty.find('.acp-qty-val').text()) + 1);
    });
    $(document).on('click', '#acp-popup .acp-qty-minus', function () {
      var $qty = $(this).closest('.acp-qty');
      var cur  = parseInt($qty.find('.acp-qty-val').text());
      if (cur > 1) ajaxUpdateQty($qty.data('key'), cur - 1);
    });
    $(document).on('click', '#acp-popup .acp-remove', function () {
      ajaxRemove($(this).data('key'));
    });
    $(document).on('click', '#acp-popup .acp-add-btn', function () {
      var variationRaw = $(this).attr('data-variation') || '{}';
      var variation = {};
      try { variation = JSON.parse(variationRaw); } catch(e) {}
      ajaxAddProduct($(this).data('id'), $(this).data('vid') || 0, variation, $(this));
    });

    /* Re-fetch when WC adds to cart elsewhere */
    $(document.body).on('added_to_cart wc_fragments_refreshed', function () {
      if (isOpen && !isRendering && !isFetching) fetchCart();
    });
  }

  /* ================================================================
   * OPEN / CLOSE
   * ============================================================== */
  function openPopup() {
    // Re-cache DOM refs in case footer wasn't ready during init
    if (!$popup || !$popup.length) {
      $overlay    = $('#acp-overlay');
      $popup      = $('#acp-popup');
      $close      = $('#acp-close');
      $itemsWrap  = $('#acp-items-wrap');
      $discBox    = $('#acp-discount-box');
      $barFill    = $('#acp-bar-fill');
      $discStatus = $('#acp-discount-status');
      $progLabels = $('#acp-progress-labels');
      $recList    = $('#acp-rec-list');
      $subtotal   = $('#acp-subtotal');
      $discount   = $('#acp-discount');
      $discRow    = $('#acp-discount-row');
      $total      = $('#acp-total');
      $badge      = $('#acp-count-badge');
    }
    isOpen = true;
    $overlay.addClass('acp-active');
    $popup.addClass('acp-active');
    $('body').addClass('acp-open');
    showLoader();
    fetchCart();
  }

  function closePopup() {
    isOpen = false;
    $overlay.removeClass('acp-active');
    $popup.removeClass('acp-active');
    $('body').removeClass('acp-open');
    $('body').css('overflow', '');
  }

  /* ================================================================
   * AJAX
   * ============================================================== */
  function fetchCart() {
    if (isFetching) return;
    isFetching = true;
    $.ajax({
      url:  acpData.ajaxUrl,
      type: 'POST',
      data: { action: 'acp_get_cart', nonce: acpData.nonce },
      success: function (res) {
        if (res.success) { cartData = res.data; renderCart(); }
        else              showError();
      },
      error:    showError,
      complete: function () { isFetching = false; }
    });
  }

  function ajaxUpdateQty(cartKey, qty) {
    setLoading(true);
    $.ajax({
      url:  acpData.ajaxUrl,
      type: 'POST',
      data: { action: 'acp_update_qty', nonce: acpData.nonce, cart_key: cartKey, qty: qty },
      success: function (res) {
        if (res.success) { cartData = res.data; renderCart(); }
      },
      complete: function () { setLoading(false); }
    });
  }

  function ajaxRemove(cartKey) {
    setLoading(true);
    $.ajax({
      url:  acpData.ajaxUrl,
      type: 'POST',
      data: { action: 'acp_remove_item', nonce: acpData.nonce, cart_key: cartKey },
      success: function (res) {
        if (res.success) { cartData = res.data; renderCart(); refreshThemeCartCount(res.data.item_count); }
      },
      complete: function () { setLoading(false); }
    });
  }

  function ajaxAddProduct(productId, variationId, variation, $btn) {
    $btn.prop('disabled', true).text('…');
    $.ajax({
      url:  acpData.ajaxUrl,
      type: 'POST',
      data: { action: 'acp_add_product', nonce: acpData.nonce, product_id: productId, variation_id: variationId || 0, variation: variation || {} },
      success: function (res) {
        if (res.success) {
          cartData = res.data; renderCart(); refreshThemeCartCount(res.data.item_count);
        } else {
          $btn.prop('disabled', false).text('+');
          // Variable product — redirect to product page so user can choose options
          if (res.data && res.data.message === 'variable' && res.data.redirect) {
            window.location.href = res.data.redirect;
          }
        }
      },
      error: function () { $btn.prop('disabled', false).text('+'); }
    });
  }

  /* ================================================================
   * RENDER
   * ============================================================== */
  function renderCart() {
    if (!cartData) return;
    isRendering = true;
    var d = cartData;

    updateBadge(d.item_count);

    /* Items */
    $itemsWrap.empty();
    if (!d.items || d.items.length === 0) {
      $itemsWrap.html(
        '<div class="acp-empty">' +
          '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">' +
            '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6h13M10 21a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z"/>' +
          '</svg><p>Your cart is empty</p></div>'
      );
    } else {
      $.each(d.items, function (i, item) {
        $itemsWrap.append(
          '<div class="acp-item">' +
            '<img class="acp-item-img" src="' + escHtml(item.image) + '" alt="' + escHtml(item.name) + '" loading="lazy">' +
            '<div class="acp-item-info">' +
              '<div class="acp-item-name">' +
                (item.main_name ? '<span class="acp-item-main-name">' + escHtml(item.main_name) + '</span>' : '') +
                (item.variation_name ? ' <span class="acp-item-var-name">' + escHtml(item.variation_name) + '</span>' : (item.main_name ? '' : escHtml(item.name))) +
              '</div>' +
              '<div class="acp-item-price">' + acpData.currency + parseFloat(item.price).toFixed(2) + '</div>' +
              '<button class="acp-remove" data-key="' + escHtml(item.cart_key) + '">Remove</button>' +
            '</div>' +
            '<div class="acp-qty" data-key="' + escHtml(item.cart_key) + '">' +
              '<button class="acp-qty-btn acp-qty-minus"' + (item.qty <= 1 ? ' disabled' : '') + '>−</button>' +
              '<span class="acp-qty-val">' + parseInt(item.qty) + '</span>' +
              '<button class="acp-qty-btn acp-qty-plus">+</button>' +
            '</div>' +
          '</div>'
        );
      });
    }

    /* Discount progress */
    if (d.tiers && d.tiers.length) {
      $discBox.show();
      var maxT = d.tiers[d.tiers.length - 1].threshold;
      $barFill.css('width', Math.min((d.subtotal / maxT) * 100, 100) + '%');
      $progLabels.empty();
      $.each(d.tiers, function (i, t) {
        $progLabels.append('<div class="acp-progress-label">$' + t.threshold + ' → <span>' + t.label + ' OFF</span></div>');
      });
      var st = '';
      if (d.current_tier) {
        st = 'You\'ve unlocked <strong>' + d.current_tier.label + ' discount</strong> 🎉';
        st += d.next_tier
          ? '<br><span style="font-size:11.5px;color:var(--acp-muted)">Add $' + Math.ceil(d.next_tier.threshold - d.subtotal) + ' more to unlock ' + d.next_tier.label + ' discount</span>'
          : '<br><span style="font-size:11.5px;color:var(--acp-muted)">Maximum discount unlocked! 🏆</span>';
      } else if (d.next_tier) {
        st = 'Add <strong>$' + Math.ceil(d.next_tier.threshold - d.subtotal) + ' more</strong> to unlock ' + d.next_tier.label + ' discount';
      }
      $discStatus.html(st);
    } else {
      $discBox.hide();
    }

    /* Recommended */
    $recList.empty();
    if (d.recommended && d.recommended.length) {
      $.each(d.recommended, function (i, rec) {
        $recList.append(
          '<div class="acp-rec-item">' +
            '<img class="acp-rec-img" src="' + escHtml(rec.image) + '" alt="' + escHtml(rec.name) + '" loading="lazy">' +
            '<div class="acp-rec-info">' +
              '<div class="acp-rec-name">'  + escHtml(rec.name)  + '</div>' +
              '<div class="acp-rec-price">' + acpData.currency + parseFloat(rec.price).toFixed(2) + '</div>' +
            '</div>' +
            '<button class="acp-add-btn" data-id="' + parseInt(rec.product_id) + '" data-vid="' + parseInt(rec.variation_id || 0) + '" data-variation="' + escHtml(JSON.stringify(rec.variation || {})) + '">+</button>' +
          '</div>'
        );
      });
    }

    /* Summary */
    $subtotal.html(d.subtotal_html);
    if (d.discount_amount > 0) { $discRow.show(); $discount.html(d.discount_html); }
    else { $discRow.hide(); }
    $total.html(d.total_html);

    isRendering = false;
  }

  /* ================================================================
   * HELPERS
   * ============================================================== */
  function showLoader() {
    $itemsWrap.html('<div class="acp-loader"><div class="acp-spinner"></div></div>');
    $recList.empty();
  }
  function showError() {
    $itemsWrap.html('<div class="acp-empty"><p style="color:var(--acp-accent)">Could not load cart. Please try again.</p></div>');
  }
  function setLoading(on) {
    if (on) {
      $popup.addClass('acp-loading');
    } else {
      $popup.removeClass('acp-loading');
      $popup.css('opacity', ''); // clear any leftover inline style
    }
  }
  function updateBadge(count) {
    $badge.text(count);
    $badge.addClass('acp-bump');
    setTimeout(function () { $badge.removeClass('acp-bump'); }, 300);
    var $tc = $('#acp-trigger-btn .acp-trigger-count');
    if (count > 0) { $tc.text(count).show(); } else { $tc.hide(); }
    refreshThemeCartCount(count);
  }
  function refreshThemeCartCount(count) {
    $('.cart-contents-count,.count,.cart-count,.wcmenucart-count,[class*="cart-count"],[class*="cartcount"]').each(function () {
      if (!$(this).closest('#acp-popup').length) $(this).text(count);
    });
    if (!isRendering) $(document.body).trigger('wc_fragment_refresh');
  }
  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

})(jQuery);