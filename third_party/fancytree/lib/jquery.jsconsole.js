/*************************************************************************
	jquery.jsconsole.js
	Convert a div tag in to a log window.

	Copyright (c) 2012, Martin Wendt (http://wwWendt.de)
	Dual licensed under the MIT or GPL Version 2 licenses.
	http://code.google.com/p/dynatree/wiki/LicenseInfo

	A current version and some documentation is available at
		http://dynatree.googlecode.com/

	${Version}
	${Revision}


	Usage:
		logMsg("%o was toggled", this);

	@depends: jquery.js
*************************************************************************/

/*jslint laxbreak: true, browser: true, indent: 0, white: false, onevar: false */


// Start of local namespace
(function($) {
	var LOGLEVEL_Error = 3,
		LOGLEVEL_Warn = 2,
		LOGLEVEL_Info = 1,
		LOGLEVEL_Debug = 0,
		LEVEL_NAMES = {3: 'error', 2: 'warn', 1:'info', 0:'debug'},
		wc = window.console;

	var methods = {
		init: function(options) {
			// Create some defaults, extending them with any options that were provided
			var opts = $.extend( {
				logLevel: LOGLEVEL_Debug,
				logLevelWC: LOGLEVEL_Debug
			}, options);
			opts.count = 0;
			return this.each(function(){
//				$(window).bind("keydown.jsconsole", methods._keydown);
				var $this = $(this),
					data = $this.data("jsconsole");
				// If the plugin hasn't been initialized yet
				if ( ! data ) {
					$this.data("jsconsole", opts);
				}
			});
		},

		destroy: function( ) {
			return this.each(function(){
				$(window).unbind(".jsconsole");
				$(this).removeData("jsconsole");
			})
		},

		_log: function(args) {
			var $this = $(this),
				data = $this.data("jsconsole"),
				level = arguments[0],
				levelName = LEVEL_NAMES[level],
				args = Array.prototype.slice.call(arguments), // Copy into a real array
				prefix = levelName + " - ";

			data.count += 1;
			if(true){
				// Prepend timestamp
				var dt = new Date(),
					tag = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds() + "." + dt.getMilliseconds();
				prefix += tag;
			}
			// Replace the first argument (level), with prefix
			args[0] = prefix;
			// Log to window.console
			if(wc && level >= data.logLevelWC){
				try {
					switch( level ) {
					case LOGLEVEL_Info:
						wc.info.apply(wc, args);
						break;
					case LOGLEVEL_Warn:
						wc.warn.apply(wc, args);
						break;
					case LOGLEVEL_Error:
						wc.error.apply(wc, args);
						break;
					default:
						wc.log.apply(wc, args);
					}
				} catch(e) {
				}
			}
			if(level >= data.logLevel){
				// For html output we must concatenate single args as strings
				for(var i=0; i<args.length; i++){
					args[i] = "" + args[i];
				}
				$("<div/>", {
					text: args.join(" "),
					"class": "logEntry " + levelName
				}).appendTo($this);
			}
		},
		debug: function(msg) {
			Array.prototype.unshift.call(arguments, LOGLEVEL_Debug); // prepend level
			methods._log.apply(this, arguments);
		},
		info: function(msg) {
			Array.prototype.unshift.call(arguments, LOGLEVEL_Info); // prepend level
			methods._log.apply(this, arguments);
		},
		warn: function(msg) {
			Array.prototype.unshift.call(arguments, LOGLEVEL_Warn); // prepend level
			methods._log.apply(this, arguments);
		},
		error: function(msg) {
			Array.prototype.unshift.call(arguments, LOGLEVEL_Error); // prepend level
			methods._log.apply(this, arguments);
		}
	};
	// Handle $('selector').jsconsole('method') calls
	$.fn.jsconsole = function( method ) {
		// Note: this is already a jQuery object
		if ( methods[method] ) {
			return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === "object" || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error("Method " +  method + " does not exist on jQuery.jsconsole");
		}
	};

// -----------------------------------------------------------------------------
})(jQuery);
