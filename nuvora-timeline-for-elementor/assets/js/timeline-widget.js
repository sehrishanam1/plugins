(function ($) {
    'use strict';

    /*
     * ANIMATION SEQUENCE
     * ─────────────────────────────────────────────────────────────────
     * PHASE 1 – Boxes appear one by one
     *   Each box fades+slides up using its own Elementor "Animation Delay"
     *   setting.  Boxes can overlap (e.g. item 1 at 0 s, item 2 at 1 s …)
     *
     * PHASE 2 – Lines travel the full journey, item by item
     *   Starts only after the LAST box has fully appeared.
     *   For each item (0 → N-1, skipping the last item's ::before):
     *     a) Horizontal line draws from icon outward          (LINE_H ms)
     *     b) Vertical line drops from icon down to next item  (LINE_V ms)
     *   The vertical line of item N connects visually to item N+1,
     *   so we add a brief pause before the next iteration to feel natural.
     *
     * ELEMENTOR EDITOR
     *   Everything is shown in its final state instantly (no animation).
     */

    var isEditor = document.body.classList.contains('elementor-editor-active');

    /* ── Timing constants (keep in sync with CSS transition durations) ── */
    var BOX_DUR    = 600;   // ms  — box fade-up duration  (CSS: 0.6s)
    var LINE_H_DUR = 450;   // ms  — horizontal line draw  (CSS: 0.45s)
    var LINE_V_DUR = 550;   // ms  — vertical line draw    (CSS: 0.55s)
    var LINE_PAUSE = 80;    // ms  — tiny breath between items

    /* Map Elementor delay class → milliseconds */
    var DELAY_MAP = {
        ''         :    0,
        'delay-1s' : 1000,
        'delay-2s' : 2000,
        'delay-3s' : 3000,
        'delay-4s' : 4000
    };

    /* ── SVG fix ──────────────────────────────────────────────────── */
    function fixSVGs($scope) {
        $scope.find('.timeline__event__icon').each(function () {
            var $svg = $(this).find('svg');
            if (!$svg.length) return;
            $svg.attr({ width: '32', height: '32',
                        viewBox: $svg.attr('viewBox') || '0 0 512 512' })
                .css({ display: 'block', width: '32px', height: '32px', fill: 'currentColor' });
            $svg.find('path,circle,rect,polygon,polyline').css({ fill: 'currentColor' });
        });
    }

    /* ── Editor: instant final state ─────────────────────────────── */
    function initEditorTimeline($tl) {
        $tl.find('.timeline__event').addClass('tl-box-done tl-line-h tl-line-v');
    }

    /* ── Frontend: full sequenced animation ──────────────────────── */
    function initFrontendTimeline($tl) {
        if ($tl.hasClass('timeline-no-animation')) return;

        var $items = $tl.find('.timeline__event');
        if (!$items.length) return;

        /* Fallback for very old browsers */
        if (!window.IntersectionObserver) {
            $items.addClass('tl-box-done tl-line-h tl-line-v');
            return;
        }

        /* ── PHASE 1: boxes ─────────────────────────────────────────
         * We use a single observer that fires when the TIMELINE WRAPPER
         * enters the viewport (rather than each individual item) so the
         * whole sequence starts at the right moment.
         * Once triggered we schedule every box using its delay value.
         */
        var triggered = false;

        var wrapObserver = new IntersectionObserver(function (entries) {
            if (triggered) return;
            var entry = entries[0];
            if (!entry.isIntersecting) return;
            triggered = true;
            wrapObserver.disconnect();

            /* Collect each item's delay and calculate when it finishes */
            var itemData   = [];   // [{$el, startMs, endMs}, …]
            var latestEnd  = 0;

            $items.each(function () {
                var $item      = $(this);
                var cls        = $item.data('delay') || '';
                var startMs    = (DELAY_MAP[cls] !== undefined) ? DELAY_MAP[cls] : 0;
                var endMs      = startMs + BOX_DUR;
                if (endMs > latestEnd) latestEnd = endMs;
                itemData.push({ $el: $item, startMs: startMs });
            });

            /* Show boxes */
            itemData.forEach(function (d) {
                setTimeout(function () {
                    d.$el.addClass('tl-box-done');
                }, d.startMs);
            });

            /* ── PHASE 2: lines ─────────────────────────────────────
             * Start after ALL boxes have finished appearing.
             * Walk items in order: draw horizontal line, then vertical,
             * then move to the next item.  Last item gets no vertical.
             */
            setTimeout(function () {
                runLineChain($items, 0, 0);
            }, latestEnd + 100); /* +100 ms grace after last box */

        }, { threshold: 0.1 });

        wrapObserver.observe($tl[0]);
    }

    /* Recursively animate lines item by item */
    function runLineChain($items, index, t) {
        if (index >= $items.length) return;

        var $item    = $($items[index]);
        var isLast   = (index === $items.length - 1);

        /* Draw horizontal line from this icon outward */
        setTimeout(function () {
            $item.addClass('tl-line-h');

            if (isLast) {
                /* Last item: no vertical line needed */
                return;
            }

            /* Draw vertical line down from this icon to next */
            setTimeout(function () {
                $item.addClass('tl-line-v');

                /* After vertical finishes, start the next item */
                setTimeout(function () {
                    runLineChain($items, index + 1, 0);
                }, LINE_V_DUR + LINE_PAUSE);

            }, LINE_H_DUR + LINE_PAUSE);

        }, t);
    }

    /* ── Init all timelines in scope ─────────────────────────────── */
    function initAll($scope) {
        fixSVGs($scope);
        $scope.find('.timeline-style1').each(function () {
            var $tl = $(this);
            isEditor ? initEditorTimeline($tl) : initFrontendTimeline($tl);
        });
    }

    /* ── Entry points ────────────────────────────────────────────── */
    $(document).ready(function () { initAll($('body')); });

    $(window).on('elementor/frontend/init', function () {
        if (window.elementorFrontend && window.elementorFrontend.hooks) {
            window.elementorFrontend.hooks.addAction(
                'frontend/element_ready/timeline_widget.default',
                function ($scope) {
                    fixSVGs($scope);
                    $scope.find('.timeline-style1').each(function () {
                        var $tl = $(this);
                        isEditor ? initEditorTimeline($tl) : initFrontendTimeline($tl);
                    });
                }
            );
        }
    });

})(jQuery);
