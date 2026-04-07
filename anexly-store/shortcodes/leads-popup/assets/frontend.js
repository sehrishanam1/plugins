/**
 * Anexly Leads — Frontend + Admin JS
 * Handles:
 *  1. Newsletter form AJAX submission
 *  2. Popup form AJAX submission
 *  3. Smart progressive popup trigger for logged-out users
 *  4. Admin: delete lead via AJAX
 */

(function ($) {
    'use strict';

    /* -----------------------------------------------------------------------
       Shared: handle any .anexly-form submission
    ----------------------------------------------------------------------- */
    function handleFormSubmit($form) {
        var $emailInput = $form.find('.anexly-email-input');
        var $submitBtn  = $form.find('button[type="submit"]');
        var $msg        = $form.find('.anexly-msg');
        var source      = $form.data('source') || 'newsletter';

        var email = $.trim($emailInput.val());
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showMsg($msg, 'Please enter a valid email address.', 'error');
            $emailInput.focus();
            return;
        }

        var $consent = $form.find('input[name="consent"]');
        if ($consent.length && !$consent.is(':checked')) {
            showMsg($msg, 'Please accept the consent checkbox to continue.', 'error');
            return;
        }

        $submitBtn.prop('disabled', true).text(AnexlyLeads.i18n.sending);
        $msg.removeClass('success error').text('');

        $.ajax({
            url:    AnexlyLeads.ajaxUrl,
            method: 'POST',
            data: {
                action: 'anexly_subscribe',
                nonce:  AnexlyLeads.nonce,
                email:  email,
                source: source,
            },
            success: function (res) {
                if (res.success) {
                    showMsg($msg, res.data.message, 'success');
                    $form[0].reset();
                    if (source === 'popup') {
                        $(document).trigger('anexly:subscribed');
                        setTimeout(closePopup, 2000);
                    }
                } else {
                    showMsg($msg, res.data.message, 'error');
                }
            },
            error: function () {
                showMsg($msg, 'Something went wrong. Please try again.', 'error');
            },
            complete: function () {
                var label = source === 'popup'
                    ? AnexlyLeads.i18n.discount
                    : AnexlyLeads.i18n.subscribe;
                $submitBtn.prop('disabled', false).text(label);
            }
        });
    }

    function showMsg($el, text, type) {
        $el.removeClass('success error').addClass(type).text(text);
    }

    $(document).on('submit', '.anexly-form', function (e) {
        e.preventDefault();
        handleFormSubmit($(this));
    });

    /* -----------------------------------------------------------------------
       Popup: open / close helpers
    ----------------------------------------------------------------------- */
    var $overlay = null;

    function openPopup() {
        if (!$overlay) $overlay = $('#anexly-popup-overlay');
        if (!$overlay.length) return;
        $overlay.attr('aria-hidden', 'false').addClass('is-open');
        setTimeout(function () {
            $overlay.find('.anexly-email-input').focus();
        }, 300);
    }

    function closePopup() {
        if (!$overlay) $overlay = $('#anexly-popup-overlay');
        $overlay.attr('aria-hidden', 'true').removeClass('is-open');
        $(document).trigger('anexly:popupClosed');
    }

    $(document).on('click', '.anexly-popup-close', closePopup);
    $(document).on('click', '#anexly-popup-overlay', function (e) {
        if ($(e.target).is('#anexly-popup-overlay')) closePopup();
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closePopup();
    });

    /* -----------------------------------------------------------------------
       Popup: Smart Progressive Schedule (logged-out users only)

       Show 1  →  8-12 sec  after page load        (user just arrived)
       Show 2  →  3 min     after 1st close         (breathing room)
       Show 3  →  7 min     after 2nd close         (gentle nudge)
       Show 4  →  15 min    after 3rd close         (last try)
       After 4th close      → stop for this session
       On subscribe         → stop permanently
    ----------------------------------------------------------------------- */
    if (AnexlyLeads.isLoggedIn === '0') {

        var closeDelays = [
            3  * 60 * 1000,
            7  * 60 * 1000,
            15 * 60 * 1000
        ];

        var showCount  = 0;
        var subscribed = false;

        function scheduleNext(ms) {
            setTimeout(function () {
                if (!subscribed) openPopup();
            }, ms);
        }

        // First show: random 8-12 seconds
        scheduleNext(Math.floor(Math.random() * 4000) + 8000);
        showCount = 1;

        $(document).on('anexly:popupClosed', function () {
            var idx = showCount - 1;
            if (!subscribed && idx < closeDelays.length) {
                scheduleNext(closeDelays[idx]);
                showCount++;
            }
        });

        $(document).on('anexly:subscribed', function () {
            subscribed = true;
        });
    }

    /* -----------------------------------------------------------------------
       Admin: delete a lead row via AJAX
    ----------------------------------------------------------------------- */
    $(document).on('click', '.anexly-delete-lead', function () {
        var $btn  = $(this);
        var id    = $btn.data('id');
        var email = $btn.data('email');

        if (!confirm('Delete subscriber "' + email + '"? This cannot be undone.')) return;

        $btn.prop('disabled', true).text('Deleting…');

        $.ajax({
            url:    AnexlyLeads.ajaxUrl,
            method: 'POST',
            data: {
                action: 'anexly_delete_lead',
                nonce:  AnexlyLeads.nonce,
                id:     id,
            },
            success: function (res) {
                if (res.success) {
                    $('#anexly-lead-' + id).fadeOut(300, function () { $(this).remove(); });
                } else {
                    alert('Could not delete. Please try again.');
                    $btn.prop('disabled', false).text('Delete');
                }
            },
            error: function () {
                alert('Request failed. Please try again.');
                $btn.prop('disabled', false).text('Delete');
            }
        });
    });

}(jQuery));