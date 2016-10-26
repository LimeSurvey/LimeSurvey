/*************************************************************************
	(c) 2008-2012 Martin Wendt
 *************************************************************************/

/*******************************************************************************
 * jQuery.skinswitcher plugin.
 *
 * Change CSS include when combobox selection changes.
 * Copyright (c) 2012 Martin Wendt
 *
 * Usage:
	$("select#skinswitcher").skinswitcher({
		base: "../src/",
		choices: [{name: "XP", value: "xp", href: "skin/ui.fancytree.css"},
				  {name: "Vista", value: "vista", href: "skin-vista/ui.fancytree.css"},
				  {name: "Lion", value: "lion", href: "skin-lion/ui.fancytree.css"}
				  ],
		init: "lion"
	});
 */
/*globals alert, prettyPrint */

(function( $ ) {
	var PLUGIN_NAME = "skinswitcher",
		defaultOptions = {
			/**RegEx that returns prefix, tag, and suffix of the CSS href.*/
			// skinPattern: "^(\W/skin-)().css$",
			// mode: "combo", // {String} mode 'combo' or 'radio'
			base: "",
			choices: [],
			// extraChoices: [],
			// Events:
			change: $.noop
		},
		methods = {
			init: function(options) {
				var i,
					opts = $.extend({}, defaultOptions, options),
					hrefs = [],
					$link = null,
					initialChoice;
				// $('').skinswitcher did not match a selector
				if( !this.length ){
					return this;
				}
				// Attach options to skinswitcher combobox for later access
				this.data("options", opts);
				// Find the <link> tag that is used to includes our skin CSS.
				// Add a class for later access.
				$.each(opts.choices, function(){
					hrefs.push(this.href.toLowerCase());
				});
				$("head link").each(function(){
					for(i=0; i<hrefs.length; i++){
						if(this.href.toLowerCase().indexOf(hrefs[i]) >= 0){
							$link = $(this);
							$link.addClass(PLUGIN_NAME);
							initialChoice = opts.choices[i];
						}
					}
				});
				if( !$link ){
					$link = $("link." + PLUGIN_NAME);
				}
				if( !$link.length ){
					$.error("Unable to find <link> tag for skinswitcher. Either set `href` to a known skin url or add a `skinswitcher` class.");
				}
				//
				return this.each(function() {
					// Add options to dropdown list
					var $combo = $(this);
					$combo
						.empty()
						.skinswitcher("addChoices", opts.choices)
						.change(function(event){
							var choice = $(":selected", this).data("choice");
							$("link." + PLUGIN_NAME).attr("href", opts.base + choice.href);
							opts.change(choice);
						});
					// Find out initial selection
					if(opts.init){
						$combo.val(opts.init).change();
					}else if (initialChoice){
						// select combobox value to match current <link> tag
						// decouple this call to prevent IE6 exception
						setTimeout(function(){
							$combo.val(initialChoice.value);
							opts.change(initialChoice);
						}, 100);
					}
				});
			},
			option: function(name, value) {
				var opts = this.data("options");
				if(typeof value !== "undefined"){
					opts[name] = value;
					return this;
				}else{
					return opts[name];
				}
			},
			addChoices: function(choices) {
				var $combo = $(this);
				if( $.isPlainObject(choices) ){
					choices = [ choices ];
				}
				$.each(choices, function(i, choice){
					var $opt = $("<option>", {
							text: choice.name,
							value: choice.value
						}).data("choice", choice);
					$combo.append($opt);
				});
				return this;
			},
			change: function(value) {
				$(this).val(value).change();
				return this;
			},
			reset: function() {
				$(this).val("").change();
				return this;
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


/**
 * Replacement for $().toggle(func1, func2), which was deprecated with jQuery 1.8
 * and removed in 1.9.;
 * Taken from http://stackoverflow.com/a/4911660/19166
 * By Felix Kling
 */
(function($) {

var _gaq = _gaq || [],
	SAMPLE_BUTTON_DEFAULTS = {
		id: undefined,
		label: "Sample",
		newline: true,
		code: function(){ alert("not implemented"); }
	};

$.fn.clickToggle = function(func1, func2) {
	var funcs = [func1, func2];
	this.data("toggleclicked", 0);
	this.click(function() {
		var data = $(this).data(),
			tc = data.toggleclicked;
		$.proxy(funcs[tc], this)();
		data.toggleclicked = (tc + 1) % 2;
	});
	return this;
};


window.addSampleButton = function(options) {
	var sourceCode,
		opts = $.extend({}, SAMPLE_BUTTON_DEFAULTS, options),
		$buttonBar = $("#sampleButtons"),
		$container = $("<span />", {
			"class": "sampleButtonContainer"
		});

	$("<button />", {
		id: opts.id,
		title: opts.tooltip,
		text: opts.label
	}).click(function(e){
		e.preventDefault();
		opts.code();
	}).appendTo($container);

	$("<a />", {
		text: "Source code",
		href: "#",
		"class": "showCode"
	}).appendTo($container)
	.click(function(e){
		try {
			prettyPrint();
		} catch (e2) {
			alert(e2);
		}
		var $pre = $container.find("pre");
		if($pre.is(":visible")){
			$(this).text("Source code");
		}else{
			$(this).text("Hide source");
		}
		$pre.toggle("slow");
		return false;
	});
	sourceCode = "" + opts.code;
	// Remove outer function(){ CODE }
//    sourceCode = sourceCode.match(/[]\{(.*)\}/);
	sourceCode = sourceCode.substring(
		sourceCode.indexOf("{") + 1,
		sourceCode.lastIndexOf("}"));
//    sourceCode = $.trim(sourceCode);
	// Reduce tabs from 8 to 2 characters
	sourceCode = sourceCode.replace(/\t/g, "  ");
	// Format code samples

	$("<pre />", {
		text: sourceCode,
		"class": "prettyprint"
	}).hide().appendTo($container);
	if(opts.newline){
		$container.append($("<br />"));
	}
	if(opts.header){
		$("<h5 />", {text: opts.header}).appendTo($("p#sampleButtons"));
	}
	if( !$("#sampleButtons").length ){
		$.error("addSampleButton() needs a container with id #sampleButtons");
	}
	$container.appendTo($buttonBar);
};


function initCodeSamples() {
	var info,
		$source = $("#sourceCode");

	$("#codeExample").clickToggle(
		function(){
			$source.show("fast");
			if( !this.old ){
				this.old = $(this).html();
				$.get(this.href, function(code){
					// Remove <!-- Start_Exclude [...] End_Exclude --> blocks:
					code = code.replace(/<!-- Start_Exclude(.|\n|\r)*?End_Exclude -->/gi, "<!-- (Irrelevant source removed.) -->");
					// Reduce tabs from 8 to 2 characters
					code = code.replace(/\t/g, "  ");
					$source.text(code);
					// Format code samples
					try {
						prettyPrint();
					} catch (e) {
						alert(e);
					}
				}, "html");
			}
			$(this).html("Hide source code");
		},
		function(){
			$(this).html(this.old);
			$source.hide("fast");
		}
	);
	if(jQuery.ui){
		info = "Fancytree " + jQuery.ui.fancytree.version +
			", jQuery UI " + jQuery.ui.version +
			", jQuery " + jQuery.fn.jquery;
/*
		info += "\n<br>";
		info += "document.compatMode: " + document.compatMode + "\n";
		for(e in jQuery.support){
			info += "<br>\n" + e + ": " + jQuery.support[e];
		}
*/
		$("p.sample-links").after("<p class='version-info'>" + info + "</p>");
	}
}

$(function(){
	// Log to Google Analytics, when not running locally
	if ( document.URL.toLowerCase().indexOf("wwwendt.de/") >= 0 ) {
		_gaq.push(["_setAccount", "UA-316028-1"]);
		_gaq.push(["_trackPageview"]);

		(function() {
			var s, ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;
			ga.src = ("https:" === document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
			s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);
		})();
	}

	// Show some elements only, if (not) inside the Example Browser
	if (top.location === window.location){
		$(".hideOutsideFS").hide();
	}else{
		$(".hideInsideFS").hide();
	}
	initCodeSamples();

	$("select#skinswitcher").skinswitcher({
		base: "../src/",
		choices: [{name: "XP", value: "xp", href: "skin-xp/ui.fancytree.css"},
				  {name: "Vista (classic Dynatree)", value: "vista", href: "skin-vista/ui.fancytree.css"},
				  {name: "Win7", value: "win7", href: "skin-win7/ui.fancytree.css"},
				  {name: "Win8", value: "win8", href: "skin-win8/ui.fancytree.css"},
				  {name: "Win8-N", value: "win8n", href: "skin-win8-n/ui.fancytree.css"},
				  {name: "Win8 xxl", value: "win8xxl", href: "skin-win8-xxl/ui.fancytree.css"},
				  {name: "Lion", value: "lion", href: "skin-lion/ui.fancytree.css"}
				  ],
		change: function(choice) {
			// console.log("choice: " + choice.value)
			$("#connectorsSwitch").toggle(choice.value === "win8");
		}
	}).after($("<label id='connectorsSwitch'><input name='cbConnectors' type='checkbox'>Connectors</label>"));

	$("input[name=cbConnectors]").on("change", function(e){
		$(".fancytree-container").toggleClass("fancytree-connectors", $(this).is(":checked"));
	});
});

}(jQuery));
