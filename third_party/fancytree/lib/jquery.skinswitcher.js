/*******************************************************************************
 * jQuery.skinswitcher plugin.
 *
 * Change CSS include when combobox selection changes.
 * Copyright (c) 2012 Martin Wendt
 *
 * Usage:
	$("select#skinswitcher").skinswitcher({
		base: "../src/",
		choices: [{name: "XP", value: "xp", href: "skin/ui.dynatree.css"},
				  {name: "Vista", value: "vista", href: "skin-vista/ui.dynatree.css"},
				  {name: "Lion", value: "lion", href: "skin-lion/ui.dynatree.css"}
				  ],
		init: "lion"
	});
 */

(function( $ ) {
	var PLUGIN_NAME = "skinswitcher",
		defaultOptions = {
			/**RegEx that returns prefix, tag, and suffix of the CSS href.*/
			skinPattern: "^(\W/skin-)().css$",
			mode: "combo", // {String} mode 'combo' or 'radio'
			base: "",
			choices: []
		},
		methods = {
			init: function(options) {
				var opts = $.extend({}, defaultOptions, options),
					hrefs = [],
					$link = null,
					initialChoice = undefined;
				// Find <link> tag, figure out current setting and mark for
				// later access
				$.each(opts.choices, function(){
					hrefs.push(this.href.toLowerCase());
				});
				$("head link").each(function(){
					for(var i=0; i<hrefs.length; i++){
						if(this.href.toLowerCase().indexOf(hrefs[i]) >= 0){
							$link = $(this);
							$link.addClass(PLUGIN_NAME);
							initialChoice = opts.choices[i];
						}
					}
				});
				return this.each(function() {
					// Add options to dropdown list
					var $combo = $(this);
					$combo.empty();
					$.each(opts.choices, function(i, choice){
						var $opt = $("<option>", {
								text: choice.name,
								value: choice.value
							}).data("choice", choice);
						$combo.append($opt);
					});
					// Switch include
					$combo.change(function(){
						var choice = $(":selected", this).data("choice");
						$("link.skinswitcher").attr("href", opts.base + choice.href);
					});
					if(opts.init){
						$combo.val(opts.init).change();
					}else{
						// select combobox value to match current <link> tag
						$combo.val(initialChoice.value);
					}
				});
			},
			change: function(href) {
				this.val("");
			},
			reset: function() {
				this.val("");
			}
		};

	$.fn[PLUGIN_NAME] = function(method) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === "object" || ! method ) {
			return methods.init.apply(this, arguments);
		} else {
			$.error("Method " +  method + " does not exist on jQuery." + PLUGIN_NAME);
		}
	};
})( jQuery );
