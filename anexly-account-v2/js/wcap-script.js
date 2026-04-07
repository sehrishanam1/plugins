/**
 * WC Auth Popup — wcap-script.js
 * v1.4.0 — Bug fixes: JS scope, email blur, back button, password reset, rate limit UX
 */
(function ($) {
    'use strict';

    /* ============================================================
       Helpers
    ============================================================ */
    function setMsg($el, msg, type) {
        $el.text(msg).removeClass('error success').addClass(type);
    }
    function clearMsg($el) { $el.text('').removeClass('error success'); }

    function setFieldState($input, state) {
        $input.removeClass('wcap-error wcap-valid').addClass(
            state === 'error' ? 'wcap-error' : state === 'valid' ? 'wcap-valid' : ''
        );
    }

    function setServerMsg($el, msg, type) {
        $el.text(msg).removeClass('error success').addClass(type);
    }

    function startLoading($btn) {
        $btn.prop('disabled', true).find('.wcap-btn-text').hide();
        $btn.find('.wcap-spinner').show();
    }
    function stopLoading($btn) {
        $btn.prop('disabled', false).find('.wcap-btn-text').show();
        $btn.find('.wcap-spinner').hide();
    }

    function validateEmail(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    }

    /* ============================================================
       Modal open / close
    ============================================================ */
    var $overlay = $('#wcap-overlay');

    function openModal(tab) {
        $overlay = $('#wcap-overlay'); // re-query in case DOM updated
        $overlay.addClass('wcap-open');
        $('body').css('overflow', 'hidden');
        if (tab) switchPanel(tab);
        setTimeout(function () {
            $overlay.find('input:visible').first().focus();
        }, 280);
    }

    function closeModal() {
        $overlay.removeClass('wcap-open');
        $('body').css('overflow', '');
        resetForms();
    }

    $(document).on('click', '#wcap-open-btn', function () { openModal('login'); });

    /* ============================================================
       Intercept Wishlist / My-Account icon-button
       - Guest:     strip href on DOM ready so browser never navigates,
                    then open auth popup on click
       - Logged in: leave href intact — browser navigates normally
    ============================================================ */
    function setupAccountLinkIntercept() {
        if (wcap_ajax.logged_in === '1') return; // logged-in: do nothing

        // Find every matching anchor and neutralize its href immediately
        // so the browser never gets a chance to navigate — even on fast clicks.
        $('a.icon-button.favorites-global, a[href*="/my-accounts/"], a[href*="/my-account/"]').each(function () {
            var $a = $(this);
            if ($a.closest('#wcap-overlay').length) return; // skip links inside the popup
            // Store original href so we can restore it after login if needed
            $a.attr('data-wcap-href', $a.attr('href'));
            $a.attr('href', 'javascript:void(0)');
        });
    }

    // Run immediately (scripts load in footer so DOM is ready) and also on
    // any dynamic content updates (page builders, Ajax-loaded menus, etc.)
    setupAccountLinkIntercept();
    $(document).on('ajaxComplete', setupAccountLinkIntercept);

    // Now the click handler is just for opening the popup — no href to fight
    $(document).on('click', 'a[data-wcap-href*="/my-accounts/"], a[data-wcap-href*="/my-account/"], a.icon-button.favorites-global', function (e) {
        if ($(this).closest('#wcap-overlay').length) return true;
        if (wcap_ajax.logged_in === '1') return true;
        e.preventDefault();
        openModal('login');
        return false;
    });

    $(document).on('click', '#wcap-close', closeModal);
    $(document).on('click', '#wcap-forgot-close', closeModal);
    $(document).on('click', '.wcap-overlay', function (e) {
        if ($(e.target).is('.wcap-overlay')) closeModal();
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $overlay.hasClass('wcap-open')) closeModal();
    });

    /* ============================================================
       Tabs & Panel switching
    ============================================================ */
    function switchPanel(target) {
        $('.wcap-tab').removeClass('active').attr('aria-selected', 'false');
        $('.wcap-panel').hide();

        if (target === 'forgot' || target === 'reset') {
            $('#wcap-panel-' + target).show();
            $('.wcap-tabs').hide();
        } else {
            $('.wcap-tabs').show();
            $('[data-tab="' + target + '"]').addClass('active').attr('aria-selected', 'true');
            $('#wcap-panel-' + target).show();
        }
        resetForms();
    }

    $(document).on('click', '.wcap-tab', function () { switchPanel($(this).data('tab')); });
    $(document).on('click', '.wcap-switch', function () { switchPanel($(this).data('target')); });
    $(document).on('click', '#wcap-forgot-link', function () { switchPanel('forgot'); });

    /* ============================================================
       Reset forms
    ============================================================ */
    function resetForms() {
        $('form[id^="wcap-"]').each(function () { this.reset(); });
        $('.wcap-field-msg').text('').removeClass('error success');
        $('.wcap-server-msg').text('').removeClass('error success').hide();

        // BUG FIX 4 — password visible hone ke baad bhi sahi reset ho
        $('input').removeClass('wcap-error wcap-valid');
        $('.wcap-password-wrap').each(function () {
            var $wrap  = $(this);
            var $input = $wrap.find('input');
            // Type wapas password kar do chahe text tha
            $input[0].setAttribute('type', 'password');
            $wrap.find('.wcap-eye-icon').show();
            $wrap.find('.wcap-eye-off-icon').hide();
        });

        $('#wcap-strength-wrap').hide();
        $('#wcap-strength-fill').css({ width: '0', background: '' });
        $('#wcap-strength-label').text('');
    }

    /* ============================================================
       Password toggle
    ============================================================ */
    $(document).on('click', '.wcap-toggle-pw', function () {
        var $btn   = $(this);
        var $input = $btn.closest('.wcap-password-wrap').find('input');
        var type   = $input[0].getAttribute('type') === 'password' ? 'text' : 'password';
        $input[0].setAttribute('type', type);
        $btn.find('.wcap-eye-icon').toggle(type === 'password');
        $btn.find('.wcap-eye-off-icon').toggle(type === 'text');
    });

    /* ============================================================
       Password Strength
    ============================================================ */
    function checkStrength(pw) {
        var score = 0;
        if (pw.length >= 8)           score++;
        if (pw.length >= 12)          score++;
        if (/[A-Z]/.test(pw))         score++;
        if (/[0-9]/.test(pw))         score++;
        if (/[^A-Za-z0-9]/.test(pw))  score++;
        return score;
    }

    $(document).on('input', '#wcap-signup-password', function () {
        var pw   = $(this).val();
        var wrap = $('#wcap-strength-wrap');
        if (!pw.length) { wrap.hide(); return; }
        wrap.show();

        var score  = checkStrength(pw);
        var pct    = Math.min(score / 5 * 100, 100);
        var colors = ['#e05252','#e07852','#e0c352','#52c069','#28a745'];
        var labels = ['Weak','Fair','Good','Strong','Very Strong'];
        var idx    = Math.max(0, Math.min(score - 1, 4));

        $('#wcap-strength-fill').css({ width: pct + '%', background: colors[idx] });
        $('#wcap-strength-label').text(labels[idx]).css('color', colors[idx]);
    });

    /* ============================================================
       BUG FIX 2 — Email blur validation — specific fields only
       Pehle: blur on ALL email inputs (cross-panel interference)
       Ab:    har field apne panel ka message update karta hai
    ============================================================ */
    var emailBlurMap = {
        'wcap-login-email'  : '#wcap-login-email-msg',
        'wcap-signup-email' : '#wcap-signup-email-msg',
        'wcap-forgot-email' : '#wcap-forgot-email-msg',
    };

    $(document).on('blur', '#wcap-login-email, #wcap-signup-email, #wcap-forgot-email', function () {
        var $input = $(this);
        var msgSel = emailBlurMap[$input.attr('id')];
        if (!msgSel) return;
        var $msg = $(msgSel);
        var val  = $input.val().trim();

        if (!val) {
            setFieldState($input, 'error');
            setMsg($msg, 'Email is required.', 'error');
        } else if (!validateEmail(val)) {
            setFieldState($input, 'error');
            setMsg($msg, 'Invalid email address', 'error');
        } else {
            setFieldState($input, 'valid');
            setMsg($msg, 'Looks good!', 'success');
        }
    });

    /* ============================================================
       LOGIN FORM
    ============================================================ */
    $(document).on('submit', '#wcap-login-form', function (e) {
        e.preventDefault();

        var $btn     = $('#wcap-login-submit');
        var $srv     = $('#wcap-login-server-msg');
        var email    = $('#wcap-login-email').val().trim();
        var password = $('#wcap-login-password').val();
        var valid    = true;

        if (!email || !validateEmail(email)) {
            setFieldState($('#wcap-login-email'), 'error');
            setMsg($('#wcap-login-email-msg'), 'Please enter a valid email.', 'error');
            valid = false;
        } else {
            setFieldState($('#wcap-login-email'), 'valid');
            clearMsg($('#wcap-login-email-msg'));
        }

        if (!password) {
            setFieldState($('#wcap-login-password'), 'error');
            setMsg($('#wcap-login-password-msg'), 'Password is required.', 'error');
            valid = false;
        } else {
            setFieldState($('#wcap-login-password'), 'valid');
            clearMsg($('#wcap-login-password-msg'));
        }

        if (!valid) return;

        startLoading($btn);
        $srv.removeClass('error success').hide();

        $.post(wcap_ajax.ajax_url, {
            action:   'wcap_login',
            nonce:    wcap_ajax.nonce,
            email:    email,
            password: password,
        }, function (res) {
            stopLoading($btn);
            if (res.success) {
                setServerMsg($srv, res.data.message, 'success');
                $srv.show();
                setTimeout(function () {
                    window.location.href = res.data.redirect || wcap_ajax.redirect;
                }, 1000);
            } else {
                setServerMsg($srv, res.data.message, 'error');
                $srv.show();
            }
        }).fail(function () {
            stopLoading($btn);
            setServerMsg($srv, 'Server error. Please try again.', 'error');
            $srv.show();
        });
    });

    /* ============================================================
       SIGN UP FORM
    ============================================================ */
    $(document).on('submit', '#wcap-signup-form', function (e) {
        e.preventDefault();

        var $btn     = $('#wcap-signup-submit');
        var $srv     = $('#wcap-signup-server-msg');
        var name     = $('#wcap-signup-name').val().trim();
        var email    = $('#wcap-signup-email').val().trim();
        var password = $('#wcap-signup-password').val();
        var confirm  = $('#wcap-signup-confirm').val();
        var terms    = $('#wcap-terms').is(':checked');
        var valid    = true;

        if (!name) {
            setFieldState($('#wcap-signup-name'), 'error');
            setMsg($('#wcap-signup-name-msg'), 'Full name is required.', 'error');
            valid = false;
        } else {
            setFieldState($('#wcap-signup-name'), 'valid');
            clearMsg($('#wcap-signup-name-msg'));
        }

        if (!email || !validateEmail(email)) {
            setFieldState($('#wcap-signup-email'), 'error');
            setMsg($('#wcap-signup-email-msg'), 'Please enter a valid email.', 'error');
            valid = false;
        } else {
            setFieldState($('#wcap-signup-email'), 'valid');
            clearMsg($('#wcap-signup-email-msg'));
        }

        if (password.length < 8) {
            setFieldState($('#wcap-signup-password'), 'error');
            setMsg($('#wcap-signup-password-msg'), 'Min. 8 characters required.', 'error');
            valid = false;
        } else {
            setFieldState($('#wcap-signup-password'), 'valid');
            clearMsg($('#wcap-signup-password-msg'));
        }

        if (!confirm || confirm !== password) {
            setFieldState($('#wcap-signup-confirm'), 'error');
            setMsg($('#wcap-signup-confirm-msg'), 'Passwords do not match.', 'error');
            valid = false;
        } else {
            setFieldState($('#wcap-signup-confirm'), 'valid');
            clearMsg($('#wcap-signup-confirm-msg'));
        }

        if (!terms) {
            setMsg($('#wcap-terms-msg'), 'You must agree to the Terms & Conditions.', 'error');
            valid = false;
        } else {
            clearMsg($('#wcap-terms-msg'));
        }

        if (!valid) return;

        startLoading($btn);
        $srv.removeClass('error success').hide();

        $.post(wcap_ajax.ajax_url, {
            action:           'wcap_register',
            nonce:            wcap_ajax.nonce,
            full_name:        name,
            email:            email,
            password:         password,
            confirm_password: confirm,
            terms:            terms ? '1' : '',
        }, function (res) {
            stopLoading($btn);
            if (res.success) {
                setServerMsg($srv, res.data.message, 'success');
                $srv.show();
                setTimeout(function () {
                    window.location.href = res.data.redirect || wcap_ajax.redirect;
                }, 1200);
            } else {
                setServerMsg($srv, res.data.message, 'error');
                $srv.show();
            }
        }).fail(function () {
            stopLoading($btn);
            setServerMsg($srv, 'Server error. Please try again.', 'error');
            $srv.show();
        });
    });

    /* ============================================================
       FORGOT PASSWORD FORM
    ============================================================ */
    $(document).on('submit', '#wcap-forgot-form', function (e) {
        e.preventDefault();

        var $btn  = $('#wcap-forgot-submit');
        var $srv  = $('#wcap-forgot-server-msg');
        var email = $('#wcap-forgot-email').val().trim();
        var valid = true;

        if (!email || !validateEmail(email)) {
            setFieldState($('#wcap-forgot-email'), 'error');
            setMsg($('#wcap-forgot-email-msg'), 'Please enter a valid email.', 'error');
            valid = false;
        } else {
            setFieldState($('#wcap-forgot-email'), 'valid');
            clearMsg($('#wcap-forgot-email-msg'));
        }

        if (!valid) return;

        startLoading($btn);
        $srv.removeClass('error success').hide();

        $.post(wcap_ajax.ajax_url, {
            action: 'wcap_forgot_password',
            nonce:  wcap_ajax.nonce,
            email:  email,
        }, function (res) {
            stopLoading($btn);
            if (res.success) {
                setServerMsg($srv, res.data.message, 'success');
                $srv.show();
            } else {
                setServerMsg($srv, res.data.message, 'error');
                $srv.show();
            }
        }).fail(function () {
            stopLoading($btn);
            setServerMsg($srv, 'Server error. Please try again.', 'error');
            $srv.show();
        });
    });

    /* ============================================================
       BUG FIX 1 — Social Login Click Handler
       PEHLE: closure ke BAHAR tha — $ undefined error
       AB: closure ke ANDAR — sahi kaam karta hai
    ============================================================ */
    $(document).on('click', '.wcap-social-btn', function (e) {
        var $btn = $(this);
        var href = $btn.attr('href');

        if (href === '#wcap-google-not-configured' || href === '#wcap-facebook-not-configured') {
            e.preventDefault();
            var which = href.indexOf('google') > -1 ? 'Google' : 'Facebook';
            var msg   = which + ' login is not configured yet.\n\n'
                      + 'Please install "Nextend Social Login" plugin (free)\n'
                      + 'OR go to: WordPress Admin → Settings → WC Auth Popup\n'
                      + 'to enter your ' + which + ' credentials.';
            alert(msg);
        }
        // Configured hai → normal href redirect
    });



})(jQuery);