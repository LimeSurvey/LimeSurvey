/*******************************************************************************
 * jQuery.configurator plugin.
 *
 * Expose the available options of a jQueryUI widget and let the user modify
 * them (useful to create live demos).
 *
 * Copyright (c) 2008-2016, Martin Wendt (http://wwWendt.de)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://code.google.com/p/fancytree/wiki/LicenseInfo
 *
 * @see http://wwwendt.de/tech/fancytree/demo/sample-configurator.html
 */

(function ($) {
	function _typed(val){
		if(typeof val != "string"){
			return val;
		}
		if(!isNaN(parseInt(val, 10))){
			return parseInt(val, 10);
		}
		switch(val) {
		case "true":
			return true;
		case "false":
			return false;
		case "null":
			return null;
		case "undefined":
			return undefined;
		}
		return val;
	}


	$.widget("ui.configurator", {
		options: {
			pluginName: null,
			optionTarget: null,
			sourceTarget: null,
			optionList: [
				{name: "enabled", value: false,
				 description: "Enables/disables the plugin"}
			],
			hideDefaults: false,
			showComments: true,
			// Events:
			init: $.noop,
			render: $.noop,
			beforeChange: $.noop,
			change: $.noop
		},
		_create: function () {
			this.element.addClass("ui-configurator-target");
			$(this.options.optionTarget)
				.addClass("ui-configurator-options")
				.empty()
				.delegate("input,select", "change", $.proxy(this._onOptionChange, this));
			$(this.options.sourceTarget)
				.addClass("ui-configurator-source")
				.empty();
			this._renderOptions();
			this.renderCode();
			this._trigger("init");
		},
		/**
		 * Handle option changes in the configurator panel (checkboxes, comboboxes, ...)
		 * @param event
		 */
		_onOptionChange: function(event){
			var $ctrl = $(event.target),
				name = $ctrl.attr("name"),
				value = _typed($ctrl.val());

			if($ctrl.is(":checkbox")){
				value = $ctrl.is(":checked");
			}
			this.element[this.options.pluginName]("option", name, value);
			this.renderCode();
			this._trigger("change", name, value);
		},
		/**
		 * Handle $().configurator("option", ...) calls.
		 */
		_setOption: function(key, value){
			$.Widget.prototype._setOption.apply(this, arguments);
			this.renderCode();
		},
		/**
		 * Render checkboxes and other controls into configurator panel.
		 */
		_renderOptions: function(){
			var plugin = $(this.element).data("ui-" + this.options.pluginName) || $(this.element).data(this.options.pluginName),
				opts = this.options,
				actualOpts = plugin.options,
				availOptList = opts.optionList,
				$opts = $(this.options.optionTarget).empty(),
				$ul = $("<ul>").appendTo($opts),
				$li = null;
			$.each(availOptList, function(i, o){
				if( typeof o.value === "boolean"){
					$li = $("<li>").append(
						$("<input>", {
							id: o.name,
							name: o.name,
							type: "checkbox",
							checked: !!actualOpts[o.name]  //o.value
						})
					).append(
						$("<label>", {"for": o.name, text: " " + o.hint})
					);
				}else if( typeof o.value === "number" || typeof o.value === "string"){
					$li = $("<li>").append(
							$("<label>", {"for": o.name, text: " " + o.name})
						).append(
							$("<input>", {
								id: o.name,
								name: o.name,
								type: "text",
								value: actualOpts[o.name] //o.value
							})
						);
				}else if( $.isArray(o.value) ){
					$li = $("<li>")
						.append(
							$("<label>", {"for": o.name, text: " " + o.name})
						).append(
							$("<select>", {
								id: o.name,
								name: o.name,
								size: 1
							})
						);
					var $sel = $li.find("select");
					$.each(o.value, function(){
						$sel.append($("<option>", {
							value: this.value,
							text: this.name,
							selected: (actualOpts[o.name] == this.value)  //this.selected
						}));
					});
				}
				$ul.append($li);
			});
		},
		/**
		 * Render source code into source panel.
		 */
		renderCode: function(){
			var plugin = $(this.element).data("ui-" + this.options.pluginName) || $(this.element).data(this.options.pluginName),
				opts = this.options,
				actualOpts = plugin.options,
				availOptList = opts.optionList,
				lines = [],
				header = opts.header || '$("#selector").' + opts.pluginName + "({",
				footer = "});",
				i;
			// Make a filtered copy of the option list (so we get the last ',' right)
			if(this.options.hideDefaults){
				availOptList = [];
				for(i=0; i<this.options.optionList.length; i++){
					var o = this.options.optionList[i],
						n = o.name,
						defVal = o.value;
					if($.isArray(defVal)){
						defVal = $.map(defVal, function(e){ return e.selected ? e.value : null;})[0];
					}
					if( actualOpts[n] !== defVal /*&& actualOpts[n] !== undefined*/){
						availOptList.push(o);
					}
				}
			}
			//
			lines.push(header);
			for(i=0; i<availOptList.length; i++){
				var o = availOptList[i],
					actualVal = actualOpts[o.name],
					line = "    " + o.name + ": ";
				if(typeof actualVal === "string"){
					line += '"' + actualVal + '"';
				}else if($.isPlainObject(actualVal)){
					line += JSON.stringify(actualVal);
				}else{
					line += "" + actualVal;
				}
				if( i < (availOptList.length - 1) ){
					line += ",";
				}
				if( opts.showComments ){
					line += " // " + o.hint;
				}
				lines.push(line);
			}
			lines.push(footer);
			$(opts.sourceTarget).addClass("ui-configurator-source").text(lines.join("\n"));
			this._trigger("render");
		}
	});
} (jQuery));
