var AJAX,
	AJAXNS = '.qtip-ajax',
	RSCRIPT = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;

function Ajax(api)
{
	var self = this,
		tooltip = api.elements.tooltip,
		opts = api.options.content.ajax,
		defaults = QTIP.defaults.content.ajax,
		first = TRUE,
		stop = FALSE,
		xhr;

	api.checks.ajax = {
		'^content.ajax': function(obj, name, v) {
			// If content.ajax object was reset, set our local var
			if(name === 'ajax') { opts = v; }

			if(name === 'once') {
				self.init();
			}
			else if(opts && opts.url) {
				self.load();
			}
			else {
				tooltip.unbind(AJAXNS);
			}
		}
	};

	$.extend(self, {
		init: function() {
			// Make sure ajax options are enabled and bind event
			if(opts && opts.url) {
				tooltip.unbind(AJAXNS)[ opts.once ? 'one' : 'bind' ]('tooltipshow'+AJAXNS, self.load);
			}

			return self;
		},

		load: function(event) {
			if(stop) {stop = FALSE; return; }

			var hasSelector = opts.url.lastIndexOf(' '),
				url = opts.url,
				selector,
				hideFirst = !opts.loading && first;

			// If loading option is disabled, prevent the tooltip showing until we've completed the request
			if(hideFirst) { try{ event.preventDefault(); } catch(e) {} }

			// Make sure default event hasn't been prevented
			else if(event && event.isDefaultPrevented()) { return self; }

			// Cancel old request
			if(xhr && xhr.abort) { xhr.abort(); }
			
			// Check if user delcared a content selector like in .load()
			if(hasSelector > -1) {
				selector = url.substr(hasSelector);
				url = url.substr(0, hasSelector);
			}

			// Define common after callback for both success/error handlers
			function after() {
				var complete;

				// Don't proceed if tooltip is destroyed
				if(api.destroyed) { return; }

				// Set first flag to false
				first = FALSE;

				// Re-display tip if loading and first time, and reset first flag
				if(hideFirst) { stop = TRUE; api.show(event.originalEvent); }

				// Call users complete method if it was defined
				if((complete = defaults.complete || opts.complete) && $.isFunction(complete)) {
					complete.apply(opts.context || api, arguments);
				}
			}

			// Define success handler
			function successHandler(content, status, jqXHR) {
				var success;

				// Don't proceed if tooltip is destroyed
				if(api.destroyed) { return; }

				// If URL contains a selector
				if(selector && 'string' === typeof content) {
					// Create a dummy div to hold the results and grab the selector element
					content = $('<div/>')
						// inject the contents of the document in, removing the scripts
						// to avoid any 'Permission Denied' errors in IE
						.append(content.replace(RSCRIPT, ""))
						
						// Locate the specified elements
						.find(selector);
				}

				// Call the success function if one is defined
				if((success = defaults.success || opts.success) && $.isFunction(success)) {
					success.call(opts.context || api, content, status, jqXHR);
				}

				// Otherwise set the content
				else { api.set('content.text', content); }
			}

			// Error handler
			function errorHandler(xhr, status, error) {
				if(api.destroyed || xhr.status === 0) { return; }
				api.set('content.text', status + ': ' + error);
			}

			// Setup $.ajax option object and process the request
			xhr = $.ajax(
				$.extend({
					error: defaults.error || errorHandler,
					context: api
				},
				opts, { url: url, success: successHandler, complete: after })
			);
		},

		destroy: function() {
			// Cancel ajax request if possible
			if(xhr && xhr.abort) { xhr.abort(); }

			// Set api.destroyed flag
			api.destroyed = TRUE;
		}
	});

	self.init();
}

AJAX = PLUGINS.ajax = function(api)
{
	var self = api.plugins.ajax;
	
	return 'object' === typeof self ? self : (api.plugins.ajax = new Ajax(api));
};

AJAX.initialize = 'render';

// Setup plugin sanitization
AJAX.sanitize = function(options)
{
	var content = options.content, opts;
	if(content && 'ajax' in content) {
		opts = content.ajax;
		if(typeof opts !== 'object') { opts = options.content.ajax = { url: opts }; }
		if('boolean' !== typeof opts.once && opts.once) { opts.once = !!opts.once; }
	}
};

// Extend original api defaults
$.extend(TRUE, QTIP.defaults, {
	content: {
		ajax: {
			loading: TRUE,
			once: TRUE
		}
	}
});

