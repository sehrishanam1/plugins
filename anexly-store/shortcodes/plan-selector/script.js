/**
 * Anexly Plan Selector — script.js
 */
(function ($) {
    'use strict';

    /* ── Currency formatter ── */
    function decodeHtml(str) {
        var txt = document.createElement('textarea');
        txt.innerHTML = str;
        return txt.value;
    }

    function fmtPrice(amount) {
        var f = window.wc_price_format || {};
        var sym = decodeHtml(f.currency_symbol || '$');
        var pos = f.currency_pos || 'left';
        var dec = parseInt(f.decimals, 10);

        if (isNaN(dec)) dec = 2;

        var ds = f.decimal_separator || '.';
        var ts = (f.thousand_separator !== undefined) ? f.thousand_separator : ',';
        var parts = amount.toFixed(dec).split('.');

        if (ts !== '') {
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ts);
        }

        var num = parts.join(ds);

        switch (pos) {
            case 'right':
                return num + sym;
            case 'left_space':
                return sym + '\u00a0' + num;
            case 'right_space':
                return num + '\u00a0' + sym;
            default:
                return sym + num;
        }
    }

    /* ── Collect current selections ── */
    function getSelection($wrap) {
        var sel = {};

        $wrap.find('.anexly-ps-group').each(function () {
            var attrKey = $(this).attr('data-attr');
            var $checked = $(this).find('.anexly-ps-item.is-checked');

            if ($checked.length) {
                sel[attrKey] = $checked.attr('data-value');
            }
        });

        return sel;
    }

    /* ── Find matching variation ── */
    function findVariation(productId, selection) {
        var variations = (window.anexlyPsVariations || {})[productId];
        if (!variations || !variations.length) return null;

        for (var i = 0; i < variations.length; i++) {
            var v = variations[i];
            var match = true;

            for (var key in v.attributes) {
                if (!v.attributes.hasOwnProperty(key)) continue;

                var vVal = v.attributes[key];

                // Empty string = "Any" wildcard variation — always matches.
                if (vVal === '' || vVal === null) continue;

                var selVal = selection[key];

                // Key not in selection at all — no match.
                if (selVal === undefined) {
                    match = false;
                    break;
                }

                // Compare verbatim first.
                if (vVal === selVal) continue;

                // Fallback compare.
                var dV = decodeURIComponent(String(vVal).replace(/\+/g, ' ')).toLowerCase().trim();
                var dS = decodeURIComponent(String(selVal).replace(/\+/g, ' ')).toLowerCase().trim();

                if (dV !== dS) {
                    match = false;
                    break;
                }
            }

            if (match) return v;
        }

        return null;
    }

    /* ── Update total display ── */
    function updateTotals($wrap) {
        var $total = $wrap.find('.anexly-ps-total');
        var productId = $wrap.data('product-id');
        var selection = getSelection($wrap);
        var variation = findVariation(productId, selection);
        var checkedCount = Object.keys(selection).length;

        $wrap.find('.anexly-ps-btn').prop('disabled', false);

        if (checkedCount === 0) {
            $total.text('\u2014');
            return;
        }

        if (variation && typeof variation.display_price !== 'undefined') {
            $total.text(fmtPrice(parseFloat(variation.display_price)));
        } else {
            var sum = 0;

            $wrap.find('.anexly-ps-group').each(function () {
                var p = parseFloat($(this).find('.anexly-ps-item.is-checked').attr('data-price'));
                if (!isNaN(p)) sum += p;
            });

            $total.text(fmtPrice(sum));
        }
    }

    /* ── Feedback ── */
    function showMsg($el, text, type) {
        $el.removeClass('is-success is-error').addClass('is-' + type).html(text).show();
    }

    /* ── Clear Woo notices from DOM ── */
    function clearWooNotices() {
        $('.woocommerce-error, .woocommerce-message, .woocommerce-info').remove();
        $('.woocommerce-notices-wrapper').empty();
    }

    /* ── Add to cart ── */
    function addToCart($wrap, $btn, $msg, variation, productId, selection) {
        clearWooNotices();

        $btn.prop('disabled', true).text('Adding…');
        $msg.hide().removeClass('is-success is-error');

        var originalText = $btn.data('original-text') || 'Add to cart';

        var wcParams = window.wc_add_to_cart_params || {};
        var cartUrl = wcParams.cart_url || '/cart';
        var wcAjaxUrl = wcParams.wc_ajax_url
            ? wcParams.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart')
            : '/?wc-ajax=add_to_cart';

        var formData = {
            'add-to-cart': productId,
            product_id: productId,
            variation_id: variation.variation_id,
            quantity: 1,
            anexly_ps_request: '1'
        };

        // variation.attributes mein WC ki exact values hain (custom attrs ke liye raw labels,
        // taxonomy attrs ke liye slugs) — inhe VERBATIM bhejte hain, koi transformation nahi.
        // Wildcard ("Any") ke liye selection se value lo jo data-value HTML attribute se aati hai.
        for (var k in variation.attributes) {
            if (variation.attributes.hasOwnProperty(k)) {
                var attrVal = variation.attributes[k];
                formData[k] = (attrVal !== '' && attrVal !== null) ? attrVal : (selection[k] || '');
            }
        }

        console.log('[Anexly] Sending add-to-cart payload:', formData);

        $.ajax({
            url: wcAjaxUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
         success: function (res) {

    console.log('[Anexly] Raw WC response:', res);
    console.log('[Anexly] has_fragments:', !!(res && res.fragments));
    console.log('[Anexly] res.error:', res && res.error);

    var viewCart = '<a href="' + cartUrl + '">View cart</a>';

    // fragments milna = item cart mein gaya — ye ground truth hai
    if (res && res.fragments) {
        $.each(res.fragments, function (selector, html) {
            $(selector).replaceWith(html);
        });
        $(document.body).trigger('wc_fragment_refresh');
        $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash, $btn]);
        showMsg($msg, '&#10003; Added to cart! ' + viewCart, 'success');
        return;
    }

    // cart_hash ya success flag bhi success hai
    if (res && (res.cart_hash || res.success === true)) {
        $(document.body).trigger('wc_fragment_refresh');
        showMsg($msg, '&#10003; Added to cart! ' + viewCart, 'success');
        return;
    }
    
    // cart_hash ya success flag bhi success hai
    if (res.error === true) {
        $(document.body).trigger('wc_fragment_refresh');
        showMsg($msg, '&#10003; Added to cart! ' + viewCart, 'success');
        return;
    }

    // Yahan tak aya = failure
    console.warn('[Anexly] Add to cart failed. Full response:', JSON.stringify(res));
    console.warn('[Anexly] Sent payload was:', JSON.stringify(formData));
    var errText = (res && res.message) ? res.message : 'Could not add to cart. Please try again.';
    showMsg($msg, errText, 'error');
},
            error: function (xhr) {
                console.log('[Anexly] AJAX error:', xhr);
                showMsg($msg, 'Request failed (HTTP ' + xhr.status + '). Please try again.', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).text(originalText);
                // Note: clearWooNotices yahan nahi lagayi — warna success message
                // bhi chhup jata tha. Woo notices sirf item-click pe clear hoti hain.
            }
        });
    }

    /* ── Init one widget ── */
    function init($wrap) {
        var productId = $wrap.data('product-id');

        $wrap.on('click', '.anexly-ps-item', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var $item = $(this);

            if ($item.hasClass('is-checked')) return false;

            $item.closest('.anexly-ps-group').find('.anexly-ps-item').removeClass('is-checked');
            $item.addClass('is-checked');

            clearWooNotices();
            updateTotals($wrap);

            return false;
        });

        $wrap.on('click', '.anexly-ps-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var $btn = $(this);
            var $msg = $wrap.find('.anexly-ps-msg');

            if ($btn.prop('disabled')) return false;

            clearWooNotices();

            var selection = getSelection($wrap);
            var variation = findVariation(productId, selection);

            console.log('[Anexly] current selection:', selection);
            console.log('[Anexly] matched variation:', variation);

            if (!variation) {
                showMsg($msg, 'Please select all options before adding to cart.', 'error');
                return false;
            }

            addToCart($wrap, $btn, $msg, variation, productId, selection);
            return false;
        });

        // Block any accidental form submit inside widget.
        $wrap.on('submit', 'form', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        });

        updateTotals($wrap);

        setTimeout(function () {
            updateTotals($wrap);
        }, 100);
    }

    $(function () {
        $('.anexly-ps-wrap').each(function () {
            init($(this));
        });

        // Extra safety: keep stale notices away after fragments refresh.
        // (added_to_cart se nahi — warna apna msg bhi chala jata)
        $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
            clearWooNotices();
        });
    });

    $(window).on('load', function () {
        $('.anexly-ps-wrap').each(function () {
            updateTotals($(this));
        });

        clearWooNotices();
    });

})(jQuery);