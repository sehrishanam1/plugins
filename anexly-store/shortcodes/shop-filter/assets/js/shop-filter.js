/**
 * Shop Filter - shop-filter.js
 * Handles: AJAX filter options load, AJAX product fetch,
 *          price range, accordion, mobile panels, view toggle, load more
 */
(function ($) {
    'use strict';

    /* ================================================
       STATE
    ================================================ */
    var SF = {
        paged: 1,
        total: 0,
        maxPages: 1,
        isLoading: false,
        currency: '$',
        priceMin: 0,
        priceMax: 1000,
        absMin: 0,
        absMax: 1000,
        orderby: 'popular',
        perPage: 6,
    };

    /* ================================================
       INIT
    ================================================ */
    $(document).ready(function () {
        if (!$('#shopFilterWrapper').length) return;

        var wrapper = $('#shopFilterWrapper');
        SF.perPage = parseInt(wrapper.data('per-page')) || 6;
        SF.orderby = wrapper.data('orderby') || 'popular';
        SF.currency = (typeof ShopFilterData !== 'undefined') ? ShopFilterData.currency_symbol : '$';

        sfLoadFilterOptions();

        // Sort change
        $(document).on('click', '.sort-item', function () {
            var $item = $(this);
            SF.orderby = $item.data('value');
            SF.paged = 1;
            $('#sfSortSelected').text($item.text());
            $('.sort-item').removeClass('active');
            $item.addClass('active');
            $('#sfSortDropdown').removeClass('open');
            sfFetchProducts();
        });
        
        $('#sfSortDropdown').on('click', function (e) {
            if (!$(e.target).hasClass('sort-item')) {
                $(this).toggleClass('open');
            }
        });
        
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#sfSortDropdown').length) {
                $('#sfSortDropdown').removeClass('open');
            }
        });


        $(document).on('click', '.sf-show-more', function () {
            var $btn = $(this);
            var group = $btn.data('group');
            var $hidden = $btn.siblings('.sf-term-hidden');
            if ($hidden.length) {
                $hidden.removeAttr('style').removeClass('sf-term-hidden');
                $btn.text('Show less');
            } else {
                $btn.closest('.filter-section-body').find('label.filter-checkbox').each(function (i) {
                    if (i >= 5) {
                        $(this).addClass('sf-term-hidden').hide();
                    }
                });
                var total = $btn.closest('.filter-section-body').find('label.filter-checkbox').length - 1;
                $btn.text('Show more (' + (total - 5) + ')');
            }
        });
    });

    /* ================================================
       LOAD FILTER OPTIONS (categories, brands, duration, price range)
    ================================================ */
    function sfLoadFilterOptions() {
        $.post(ShopFilterData.ajaxurl, {
            action: 'shop_filter_options',
            nonce: ShopFilterData.nonce,
        }, function (res) {
            if (!res.success) return;
            var d = res.data;

            // Set price range
            SF.absMin = Math.floor(d.min_price);
            SF.absMax = Math.ceil(d.max_price);
            SF.priceMin = SF.absMin;
            SF.priceMax = SF.absMax;
            sfInitPriceSliders(SF.absMin, SF.absMax);

            // Render categories
            sfRenderCheckboxList('#sfCatList', '#sfMCatList', d.categories, 'sf_cat');
            sfRenderCheckboxList('#sfBrandList', '#sfMBrandList', d.brands, 'sf_brand');
            sfRenderCheckboxList('#sfDurList', '#sfMDurList', d.duration, 'sf_dur');

            // Initial product load
            sfFetchProducts();
        });
    }

    /* ================================================
       RENDER CHECKBOX LIST
    ================================================ */
    function sfRenderCheckboxList(desktopSel, mobileSel, terms, name) {
        var LIMIT = 5;
        var html = '';
        if (!terms || terms.length === 0) {
            html = '<p class="sf-no-terms">No options found.</p>';
        } else {
            if (name === 'sf_cat') {
                html += '<label class="filter-checkbox checked sf-all-option" data-group="' + name + '">' +
                    '<input type="checkbox" name="' + name + '_all" value="all" checked> ' +
                    '<span class="checkmark"></span> All' +
                    '</label>';
            }

            $.each(terms, function (i, term) {
                var hidden = i >= LIMIT ? ' sf-term-hidden" style="display:none' : '';
                html += '<label class="filter-checkbox' + hidden + '">' +
                    '<input type="checkbox" name="' + name + '" value="' + term.slug + '"> ' +
                    '<span class="checkmark"></span> ' + term.name +
                    '</label>';
            });

            if (terms.length > LIMIT) {
                html += '<button type="button" class="sf-show-more" data-group="' + name + '">' +
                    'Show more (' + (terms.length - LIMIT) + ')' +
                    '</button>';
            }
        }
        $(desktopSel).html(html);
        $(mobileSel).html(html);
        sfBindCheckboxEvents();
        if (name === 'sf_cat') sfBindAllOptionEvents(name);
    }

    /* ================================================
       "ALL" OPTION LOGIC
       - Clicking "All" unchecks all individual options
       - Checking any individual option unchecks "All"
       - If all individual options unchecked → re-check "All"
    ================================================ */
    function sfBindAllOptionEvents(name) {
        // When "All" is clicked
        $(document).off('change.sfAll_' + name, 'input[name="' + name + '_all"]')
            .on('change.sfAll_' + name, 'input[name="' + name + '_all"]', function () {
                if (this.checked) {
                    // Uncheck all individual
                    $('input[name="' + name + '"]').prop('checked', false)
                        .closest('.filter-checkbox').removeClass('checked');
                    $(this).closest('.filter-checkbox').addClass('checked');
                } else {
                    // Don't allow "All" to be unchecked manually — keep it checked if nothing else is
                    var anyChecked = $('input[name="' + name + '"]:checked').length > 0;
                    if (!anyChecked) {
                        $(this).prop('checked', true);
                        $(this).closest('.filter-checkbox').addClass('checked');
                    }
                }
            });

        // When individual option is clicked
        $(document).off('change.sfItem_' + name, 'input[name="' + name + '"]')
            .on('change.sfItem_' + name, 'input[name="' + name + '"]', function () {
                var anyChecked = $('input[name="' + name + '"]:checked').length > 0;
                if (anyChecked) {
                    // Uncheck "All"
                    $('input[name="' + name + '_all"]').prop('checked', false)
                        .closest('.filter-checkbox').removeClass('checked');
                } else {
                    // No individual selected → re-check "All"
                    $('input[name="' + name + '_all"]').prop('checked', true)
                        .closest('.filter-checkbox').addClass('checked');
                }
                $(this).closest('.filter-checkbox').toggleClass('checked', this.checked);
            });
    }

    /* ================================================
       CHECKBOX EVENTS
    ================================================ */
    function sfBindCheckboxEvents() {
        $('.filter-checkbox input[type="checkbox"]').off('change.sf').on('change.sf', function () {
            var $label = $(this).closest('.filter-checkbox');
            $label.toggleClass('checked', this.checked);
        });
        $('.filter-checkbox input[type="radio"]').off('change.sf').on('change.sf', function () {
            var name = this.name;
            $('input[name="' + name + '"]').each(function () {
                $(this).closest('.filter-checkbox').toggleClass('checked', this.checked);
            });
        });
    }

    /* ================================================
       PRICE SLIDER
    ================================================ */
    function sfInitPriceSliders(min, max) {
        // Desktop
        sfSetupSlider('sfRangeMin', 'sfRangeMax', 'sfRangeFill', 'sfPriceMin', 'sfPriceMax', min, max);
        // Mobile
        sfSetupSlider('sfMRangeMin', 'sfMRangeMax', 'sfMRangeFill', 'sfMPriceMin', 'sfMPriceMax', min, max);
    }

    function sfSetupSlider(minId, maxId, fillId, labelMinId, labelMaxId, min, max) {
        var $rMax = $('#' + maxId);
        if (!$rMax.length) return;

        $rMax.attr({ min: min, max: max, value: max });

        function update() {
            var vMax = parseInt($rMax.val());
            var pMax = max - min === 0 ? 0 : ((vMax - min) / (max - min)) * 100;
            $('#' + fillId).css({ left: '0%', width: pMax + '%' });
            $('#' + labelMinId).text(SF.currency + min);
            $('#' + labelMaxId).text(SF.currency + vMax);
            SF.priceMin = min;
            SF.priceMax = vMax;
        }

        $rMax.on('input', function () {
            update();
        });

        update();
    }

    /* ================================================
       COLLECT FILTER VALUES
    ================================================ */
    function sfGetFilters() {
        var cats = [];
        var brands = [];
        var duration = [];

        // Only collect if "All" is NOT checked — otherwise send empty (= no filter)
        if (!$('input[name="sf_cat_all"]').is(':checked')) {
            $('input[name="sf_cat"]:checked').each(function () { cats.push($(this).val()); });
        }
        if (!$('input[name="sf_brand_all"]').is(':checked')) {
            $('input[name="sf_brand"]:checked').each(function () { brands.push($(this).val()); });
        }
        if (!$('input[name="sf_dur_all"]').is(':checked')) {
            $('input[name="sf_dur"]:checked').each(function () { duration.push($(this).val()); });
        }

        // Mobile sort
        var mobileSort = $('input[name="sfSortMobile"]:checked').val();
        if (mobileSort) SF.orderby = mobileSort;

        return {
            categories: cats,
            brands: brands,
            duration: duration,
            price_min: SF.priceMin,
            price_max: SF.priceMax,
            orderby: SF.orderby,
        };
    }

    /* ================================================
       FETCH PRODUCTS
    ================================================ */
    function sfFetchProducts(append) {
        if (SF.isLoading) return;
        SF.isLoading = true;

        var filters = sfGetFilters();
        var $grid = $('#sfProductsGrid');
        var $loader = $('#sfProductsLoader');
        var $noRes = $('#sfNoResults');
        var $lmWrap = $('#sfLoadMoreWrap');

        if (!append) {
            $grid.css('opacity', 0);
            $loader.addClass('visible');
            $noRes.hide();
            $lmWrap.hide();
        } else {
            $('#sfLoadMoreBtn').addClass('loading');
        }

        $.post(ShopFilterData.ajaxurl, $.extend({
            action: 'shop_filter_products',
            nonce: ShopFilterData.nonce,
            paged: SF.paged,
            per_page: SF.perPage,
        }, filters), function (res) {
            SF.isLoading = false;

            if (!res.success) return;
            var d = res.data;

            SF.total = d.total;
            SF.maxPages = d.max_pages;
            SF.currency = d.currency;

            // Results count
            var showing = Math.min(SF.paged * SF.perPage, SF.total);
            $('#sfResultsCount').text('Showing 1-' + showing + ' of ' + SF.total + ' products');

            if (!append) {
                $grid.html('');
            }

            if (d.products.length === 0 && !append) {
                $noRes.show();
            } else {
                $.each(d.products, function (i, p) {
                    $grid.append(sfRenderCard(p, i));
                });
            }

            $loader.removeClass('visible');
            $grid.css('opacity', 1);
            $('#sfLoadMoreBtn').removeClass('loading');

            // Load more button
            if (SF.paged < SF.maxPages) {
                $lmWrap.show();
            } else {
                $lmWrap.hide();
            }
        });
    }

    /* ================================================
       RENDER PRODUCT CARD
    ================================================ */
    function sfRenderCard(p, index) {
        var delay = (index * 0.06).toFixed(2);
        var badge = p.badge ? '<div class="product-badge badge-' + p.badge + '">' + sfCapitalize(p.badge) + '</div>' : '';
        var stars = sfRenderStars(p.rating, p.review_count);
        var price = sfRenderPrice(p);
        var imgHtml = p.image
            ? '<div class="product-icon-wrap"><img src="' + p.image + '" alt="' + sfEscape(p.name) + '" loading="lazy"></div>'
            : '<div class="product-icon-placeholder" style="background:#f3f4f6">📦</div>';

        return '<div class="product-card" style="animation-delay:' + delay + 's">' +
            badge +
            imgHtml +
            stars +
            '<div class="product-info">' +
            '<div class="product-name"><a href="' + p.permalink + '" class="sf-product-name-link">' + sfEscape(p.name) + '</a></div>' +
            price +
            '</div>' +
            '<a href="' + p.add_to_cart_url + '" class="product-cta">' + p.add_to_cart_text + '</a>' +
            '</div>';
    }

    function sfRenderStars(rating, count) {
        var html = '<div class="product-stars">';
        for (var i = 1; i <= 5; i++) {
            html += '<span class="star' + (i <= Math.round(rating) ? '' : ' empty') + '">★</span>';
        }
        if (count > 0) html += '<span class="review-count">/' + count + '</span>';
        html += '</div>';
        return html;
    }

    function sfRenderPrice(p) {
        if (p.on_sale && p.regular_price) {
            return '<div class="product-price">' +
                '<span class="price-old">' + SF.currency + parseFloat(p.regular_price).toFixed(2) + '</span> ' +
                SF.currency + parseFloat(p.price).toFixed(2) + ' <span>/month</span>' +
                '</div>';
        }
        return '<div class="product-price">' + SF.currency + parseFloat(p.price).toFixed(2) + ' <span>/month</span></div>';
    }

    function sfCapitalize(str) { return str.charAt(0).toUpperCase() + str.slice(1); }
    function sfEscape(str) { return $('<div>').text(str).html(); }

    /* ================================================
       PUBLIC FUNCTIONS (called from HTML onclick)
    ================================================ */
    window.sfApplyFilters = function () {
        SF.paged = 1;
        sfFetchProducts(false);
    };

    window.sfLoadMore = function () {
        SF.paged++;
        sfFetchProducts(true);
    };

    window.sfToggleSection = function (id) {
        $('#' + id).toggleClass('collapsed');
    };

    window.sfOpenMobileFilter = function () {
        $('#sfMobileOverlay, #sfMobileFilterPanel').addClass('active');
        $('body').css('overflow', 'hidden');
    };

    window.sfOpenSortPanel = function () {
        $('#sfMobileOverlay, #sfMobileSortPanel').addClass('active');
        $('body').css('overflow', 'hidden');
    };

    window.sfCloseMobilePanels = function () {
        $('#sfMobileOverlay, #sfMobileFilterPanel, #sfMobileSortPanel').removeClass('active');
        $('body').css('overflow', '');
    };

    /* ================================================
       VIEW TOGGLE
    ================================================ */
    $(document).on('click', '.view-btn', function () {
        var view = $(this).data('view');
        $('.view-btn').removeClass('active');
        $('[data-view="' + view + '"]').addClass('active');
        if (view === 'list') {
            $('#sfProductsGrid').addClass('list-view');
        } else {
            $('#sfProductsGrid').removeClass('list-view');
        }
    });

})(jQuery);