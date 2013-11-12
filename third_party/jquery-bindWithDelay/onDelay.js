/* onDelay: THIS FILE IS STILL A WORK IN PROGRESS.
See: http://github.com/bgrins/bindWithDelay for current version
*/

(function($) {

$.fn.onDelay = function(events, selector, data, handler, timeout, throttle) {

    // (evt, handler, timeout)
    if ($.isFunction(selector)) {
        throttle = handler;
        timeout = data;
        handler = selector;
        data = undefined;
        selector = undefined;
    }
    // (evt, selector, handler, timeout) OR (evt, data, handler, timeout)
    else if ($.isFunction(data)) {
        throttle = timeout;
        timeout = handler;
        handler = data;
        data = undefined;

        // (evt, data, handler, timeout)
        if ( typeof selector !== "string" ) {
            data = selector;
            selector = undefined;
        }
    }

    // Allow delayed function to be removed with handler in unbind function
    handler.guid = handler.guid || ($.guid && $.guid++);

    // Bind each separately so that each element has its own delay
    return this.each(function() {
        var wait = null;

        function callback() {
            var event = $.extend(true, { }, arguments[0]);
            var that = this;
            var throttler = function() {
                wait = null;
                handler.apply(that, [event]);
            };

            if (!throttle) { clearTimeout(wait); wait = null; }
            if (!wait) { wait = setTimeout(throttler, timeout); }
        }

        callback.guid = handler.guid;
        $(this).on(events, selector, data, callback);
    });
};

})(jQuery);