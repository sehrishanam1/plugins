/* Anexly My Account — JavaScript */
(function ($) {
    'use strict';

    /* ---- Mobile Sidebar Toggle ---- */
    const hamburger = document.getElementById('anx-hamburger');
    const sidebar = document.getElementById('anx-sidebar');
    const overlay = document.getElementById('anx-overlay');

    if (hamburger && sidebar) {
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        const sidebarClose = document.getElementById('anx-sidebar-close');
        if (sidebarClose) {
            sidebarClose.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    }

    /* ---- Order Row Toggle (expand/collapse) ---- */
    $(document).on('click', '.anx-toggle-btn', function () {
        const orderId = $(this).data('order');
        const detailRow = $('#anx-detail-' + orderId);
        const isOpen = detailRow.is(':visible');

        // Close all
        $('.anx-order-detail-row').hide();
        $('.anx-toggle-btn').removeClass('open');
        $('.anx-order-row').removeClass('anx-row-expanded');

        if (!isOpen) {
            detailRow.show();
            $(this).addClass('open');
            $(this).closest('tr').addClass('anx-row-expanded');
        }
    });

    /* ---- Credentials Modal ---- */
    function anxParseCredentials(rawText) {
        let username = '';
        let password = '';

        if (!rawText) {
            return { username, password };
        }

        const lines = String(rawText).split(/\r?\n/);

        lines.forEach(line => {
            const clean = line.trim();

            if (!username && /^(user(name)?|email)\s*:/i.test(clean)) {
                username = clean.replace(/^(user(name)?|email)\s*:/i, '').trim();
            }

            if (!password && /^password\s*:/i.test(clean)) {
                password = clean.replace(/^password\s*:/i, '').trim();
            }
        });

        if (!username && lines[0]) {
            username = lines[0].trim();
        }

        if (!password && lines[1]) {
            password = lines[1].trim();
        }

        return { username, password };
    }

    $(document).on('click', '.anx-show-creds', function () {
        const encoded = $(this).data('creds');
        if (!encoded) return;

        let raw = '';

        try {
            const decoded = atob(encoded);
            const parsed = JSON.parse(decoded);
            raw = parsed.raw || '';
        } catch (e) {
            return;
        }

        const creds = anxParseCredentials(raw);

        $('#anx-creds-username').val(creds.username);
        $('#anx-creds-password').val(creds.password);
        $('#anx-creds-modal').fadeIn(180);
        $('body').addClass('anx-creds-open');
    });

    $(document).on('click', '.anx-creds-close, .anx-creds-backdrop', function () {
        $('#anx-creds-modal').fadeOut(180);
        $('body').removeClass('anx-creds-open');
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $('#anx-creds-modal').fadeOut(180);
            $('body').removeClass('anx-creds-open');
        }
    });

    $(document).on('click', '.anx-creds-copy', function () {
        const target = $(this).data('copy-target');
        const value = $(target).val() || '';

        if (!value) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value);
        } else {
            const temp = document.createElement('input');
            temp.value = value;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
        }
    });

    /* ---- Password Visibility Toggle ---- */
    $(document).on('click', '.anx-eye-btn', function () {
        const targetId = $(this).data('target');
        let input;
        if (targetId) {
            input = $('#' + targetId);
        } else {
            input = $(this).siblings('.anx-input');
        }
        if (!input.length) {
            input = $(this).prev('.anx-input');
        }
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);

        // Toggle eye icon
        if (type === 'text') {
            $(this).css('opacity', '1');
        } else {
            $(this).css('opacity', '0.5');
        }
    });

    /* ---- Password Strength Meter ---- */
    $(document).on('input', '#anx_new_password_field', function () {
        const val = $(this).val();
        const fill = $(this).closest('.anx-form').find('.anx-strength-fill');
        let strength = 0;

        if (val.length >= 8) strength += 25;
        if (/[A-Z]/.test(val)) strength += 25;
        if (/[0-9]/.test(val)) strength += 25;
        if (/[^A-Za-z0-9]/.test(val)) strength += 25;

        fill.css('width', strength + '%');

        if (strength < 50) fill.css('background', '#EF4444');
        else if (strength < 75) fill.css('background', '#F59E0B');
        else fill.css('background', '#10B981');
    });

    /* ---- Mobile Orders: Build card view from table ---- */
    function buildMobileOrders() {
        if (window.innerWidth > 768) return;

        const tables = document.querySelectorAll('.anx-orders .anx-table');
        tables.forEach(table => {
            if (table.dataset.mobileBuilt) return;
            table.dataset.mobileBuilt = '1';

            const wrap = table.closest('.anx-table-wrap');
            const mobileList = document.createElement('div');
            mobileList.className = 'anx-orders-mobile-list';

            const rows = table.querySelectorAll('tbody tr.anx-order-row');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const orderId = cells[0]?.textContent?.trim() || '';
                const date = cells[1]?.textContent?.trim() || '';
                const product = cells[2]?.textContent?.trim() || '';
                const statusEl = cells[3]?.querySelector('.anx-badge');
                const total = cells[4]?.textContent?.trim() || '';
                const oid = row.dataset.orderId;

                const card = document.createElement('div');
                card.className = 'anx-mobile-order-card';

                const statusHtml = statusEl ? statusEl.outerHTML : '';

                card.innerHTML = `
                    <div class="card-head">
                        <div class="card-head-content">
                            <div class="card-head-content-order">${orderId}</div>
                            <div class="card-head-content-date">${date}</div>
                        </div>
                        ${statusHtml}
                    </div>
                    <div class="card-title-area">
                        <h3 class="card-title">${product}</h3>
                        <div class="card-title-price">${total}</div>
                    </div>
                        <button class="anx-btn anx-btn-outline anx-mobile-details-btn" data-order="${oid}">Details</button>
                    </div>
                    <div class="anx-mobile-detail-body" id="anx-mob-detail-${oid}"></div>
                `;

                mobileList.appendChild(card);
            });

            wrap.after(mobileList);
        });
    }

    /* Mobile detail toggle */
    $(document).on('click', '.anx-mobile-details-btn', function () {
        const oid = $(this).data('order');
        const detailBody = $('#anx-mob-detail-' + oid);
        const srcDetail = $('#anx-detail-' + oid + ' .anx-order-detail');

        if (detailBody.is(':visible')) {
            detailBody.slideUp(200);
            $(this).text('Details');
        } else {
            if (!detailBody.html().trim() && srcDetail.length) {
                detailBody.html(srcDetail.html());
            }
            detailBody.slideDown(200);
            $(this).text('Show less');
        }
    });

    /* Run on load & resize */
    buildMobileOrders();
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(buildMobileOrders, 200);
    });

    /* ---- Auto-dismiss notices ---- */
    setTimeout(() => {
        $('.anx-notice').fadeOut(600);
    }, 5000);

})(jQuery);