/**
 * This file implements unobtrusive time limits for questions.
 *
 * @todo Include this somewhere and decide what actions we still need to implement:
 * - Disable next
 * - Disable prev
 * - Warn and move on
 * - Disable only
 * - Move without warning
 */

$(document).ready(function() {
    // Add some css.
    $('<style>').html("[data-time-limit]:before { content: attr(data-time-limit);} .expired { border: 5px solid purple; }").appendTo('head');

    // Update timers every second.
    if ($('[data-time-limit]').length > 0) {
        var interval = setInterval(function () {
            $limits = $('[data-time-limit]');
            if ($limits.length == 0) {
                clearInterval(interval);
            }
            $limits.each(function() {
                $this = $(this);
                var r = $this.attr('data-time-limit');
                if (r > 0) {
                    $this.attr('data-time-limit', r - 1);
                } else if (r == 0) {
                    $this.removeAttr('data-time-limit');
                    $this.addClass('expired');
                }
            });
        }, 1000);
    }
});
