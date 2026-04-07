/* Anexly Cart Quantity — targets Ohio theme quantity buttons */
(function(){
    'use strict';

    var timers = {};

    document.addEventListener('click', function(e){
        var btn = e.target.closest('.quantity-button');
        if ( !btn ) return;

        // Must be inside our cart item
        var cartItem = btn.closest('.anxa-cart-item');
        if ( !cartItem ) return;

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var cartItemKey = cartItem.getAttribute('data-cart-item-key');
        var input       = cartItem.querySelector('input.qty');
        if ( !input || !cartItemKey ) return;

        var val  = parseInt(input.value, 10);
        if ( isNaN(val) || val < 0 ) val = 0;

        var step    = parseInt(input.getAttribute('step'), 10) || 1;
        var minAttr = parseInt(input.getAttribute('min'),  10);
        var maxAttr = parseInt(input.getAttribute('max'),  10);
        var min = isNaN(minAttr) ? 0    : minAttr;
        var max = (isNaN(maxAttr) || maxAttr < 0) ? 9999 : maxAttr;

        var newVal = btn.classList.contains('quantity-down')
            ? Math.max(min, val - step)
            : Math.min(max, val + step);

        if ( newVal === val ) return;
        input.value = newVal;

        // Optimistic price update
        var priceEl   = cartItem.querySelector('.anxa-item-price');
        var unitPrice = priceEl ? parseFloat(priceEl.getAttribute('data-unit-price')) : NaN;
        var symbol    = priceEl ? (priceEl.getAttribute('data-currency-symbol') || '$') : '$';
        if ( priceEl && !isNaN(unitPrice) ) {
            priceEl.textContent = symbol + (unitPrice * newVal).toFixed(2);
        }

        // Debounced AJAX
        clearTimeout(timers[cartItemKey]);
        timers[cartItemKey] = setTimeout(function(){

            var totalsEl = document.querySelector('.anxa-cart-totals');
            if ( totalsEl ) totalsEl.style.opacity = '0.4';

            var formData = new FormData();
            formData.append('action',        'anxa_update_cart_qty');
            formData.append('nonce',         (typeof anxaCart !== 'undefined') ? anxaCart.nonce : '');
            formData.append('cart_item_key', cartItemKey);
            formData.append('quantity',      newVal);

            var ajaxUrl = (typeof anxaCart !== 'undefined') ? anxaCart.ajaxUrl : '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method      : 'POST',
                body        : formData,
                credentials : 'same-origin'
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if ( totalsEl ) totalsEl.style.opacity = '';
                if ( !res.success ) return;

                var data = res.data || {};
                if ( totalsEl && data.totals_html ) {
                    totalsEl.innerHTML = data.totals_html;
                }
                if ( priceEl && data.item_html ) {
                    priceEl.innerHTML = data.item_html;
                }
                if ( data.removed ) {
                    cartItem.style.transition = 'opacity 0.3s';
                    cartItem.style.opacity    = '0';
                    setTimeout(function(){ cartItem.remove(); }, 300);
                }
            })
            .catch(function(){
                if ( totalsEl ) totalsEl.style.opacity = '';
            });

        }, 500);

    }, true); // capture phase — runs before Ohio/WC listeners

})();
