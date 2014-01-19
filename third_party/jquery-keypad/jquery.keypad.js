/* http://keith-wood.name/keypad.html
   Keypad field entry extension for jQuery v1.5.1.
   Written by Keith Wood (kbwood{at}iinet.com.au) August 2008.
   Available under the MIT (https://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt) license. 
   Please attribute the author if you use it. */
   
(function($) { // hide the namespace

/* Keypad manager.
   Use the singleton instance of this class, $.keypad, to interact with the plugin.
   Settings for keypad fields are maintained in instance objects,
   allowing multiple different settings on the same page. */
function Keypad() {
	this._curInst = null; // The current instance in use
	this._disabledFields = []; // List of keypad fields that have been disabled
	this._keypadShowing = false; // True if the popup panel is showing , false if not
	this._keyCode = 0;
	this._specialKeys = [];
	this.addKeyDef('CLOSE', 'close', function(inst) {
		plugin._curInst = (inst._inline ? inst : plugin._curInst);
		plugin._hidePlugin();
	});
	this.addKeyDef('CLEAR', 'clear', function(inst) { plugin._clearValue(inst); });
	this.addKeyDef('BACK', 'back', function(inst) { plugin._backValue(inst); });
	this.addKeyDef('SHIFT', 'shift', function(inst) { plugin._shiftKeypad(inst); });
	this.addKeyDef('SPACE_BAR', 'spacebar', function(inst) { plugin._selectValue(inst, ' '); }, true);
	this.addKeyDef('SPACE', 'space');
	this.addKeyDef('HALF_SPACE', 'half-space');
	this.addKeyDef('ENTER', 'enter', function(inst) { plugin._selectValue(inst, '\x0D'); }, true);
	this.addKeyDef('TAB', 'tab', function(inst) { plugin._selectValue(inst, '\x09'); }, true);
	// Standard US keyboard alphabetic layout
	this.qwertyAlphabetic = ['qwertyuiop', 'asdfghjkl', 'zxcvbnm'];
	// Standard US keyboard layout
	this.qwertyLayout = ['!@#$%^&*()_=' + this.HALF_SPACE + this.SPACE + this.CLOSE,
		this.HALF_SPACE + '`~[]{}<>\\|/' + this.SPACE + '789',
		'qwertyuiop\'"' + this.HALF_SPACE + '456',
		this.HALF_SPACE + 'asdfghjkl;:' + this.SPACE + '123',
		this.SPACE + 'zxcvbnm,.?' + this.SPACE + this.HALF_SPACE + '-0+',
		'' + this.TAB + this.ENTER + this.SPACE_BAR + this.SHIFT +
		this.HALF_SPACE + this.BACK + this.CLEAR];
	this.regional = []; // Available regional settings, indexed by language code
	this.regional[''] = { // Default regional settings
		buttonText: '...', // Display text for trigger button
		buttonStatus: 'Open the keypad', // Status text for trigger button
		closeText: 'Close', // Display text for close link
		closeStatus: 'Close the keypad', // Status text for close link
		clearText: 'Clear', // Display text for clear link
		clearStatus: 'Erase all the text', // Status text for clear link
		backText: 'Back', // Display text for back link
		backStatus: 'Erase the previous character', // Status text for back link
		spacebarText: '&nbsp;', // Display text for space bar
		spacebarStatus: 'Space', // Status text for space bar
		enterText: 'Enter', // Display text for carriage return
		enterStatus: 'Carriage return', // Status text for carriage return
		tabText: '→', // Display text for tab
		tabStatus: 'Horizontal tab', // Status text for tab
		shiftText: 'Shift', // Display text for shift link
		shiftStatus: 'Toggle upper/lower case characters', // Status text for shift link
		alphabeticLayout: this.qwertyAlphabetic, // Default layout for alphabetic characters
		fullLayout: this.qwertyLayout, // Default layout for full keyboard
		isAlphabetic: this.isAlphabetic, // Function to determine if character is alphabetic
		isNumeric: this.isNumeric, // Function to determine if character is numeric
		toUpper: this.toUpper, // Function to convert characters to upper case
		isRTL: false // True if right-to-left language, false if left-to-right
	};
	this._defaults = { // Global defaults for all the keypad instances
		showOn: 'focus', // 'focus' for popup on focus,
			// 'button' for trigger button, or 'both' for either
		buttonImage: '', // URL for trigger button image
		buttonImageOnly: false, // True if the image appears alone, false if it appears on a button
		showAnim: 'show', // Name of jQuery animation for popup
		showOptions: {}, // Options for enhanced animations
		duration: 'normal', // Duration of display/closure
		appendText: '', // Display text following the text field, e.g. showing the format
		useThemeRoller: false, // True to add ThemeRoller classes
		keypadClass: '', // Additional CSS class for the keypad for an instance
		prompt: '', // Display text at the top of the keypad
		layout: ['123' + this.CLOSE, '456' + this.CLEAR, '789' + this.BACK, this.SPACE + '0'], // Layout of keys
		separator: '', // Separator character between keys
		target: null, // Input target for an inline keypad
		keypadOnly: true, // True for entry only via the keypad, false for real keyboard too
		randomiseAlphabetic: false, // True to randomise the alphabetic key positions, false to keep in order
		randomiseNumeric: false, // True to randomise the numeric key positions, false to keep in order
		randomiseOther: false, // True to randomise the other key positions, false to keep in order
		randomiseAll: false, // True to randomise all key positions, false to keep in order
		beforeShow: null, // Callback before showing the keypad
		onKeypress: null, // Callback when a key is selected
		onClose: null // Callback when the panel is closed
	};
	$.extend(this._defaults, this.regional['']);
	this.mainDiv = $('<div class="' + this._mainDivClass + '" style="display: none;"></div>');
}

$.extend(Keypad.prototype, {
	/* Class name added to elements to indicate already configured with keypad. */
	markerClassName: 'hasKeypad',
	/* Name of the data property for instance settings. */
	propertyName: 'keypad',

	_mainDivClass: 'keypad-popup', // The main keypad division class
	_inlineClass: 'keypad-inline', // The inline marker class
	_appendClass: 'keypad-append', // The append marker class
	_triggerClass: 'keypad-trigger', // The trigger marker class
	_disableClass: 'keypad-disabled', // The disabled covering marker class
	_inlineEntryClass: 'keypad-keyentry', // The inline entry marker class
	_rtlClass: 'keypad-rtl', // The right-to-left marker class
	_rowClass: 'keypad-row', // The keypad row marker class
	_promptClass: 'keypad-prompt', // The prompt marker class
	_specialClass: 'keypad-special', // The special key marker class
	_namePrefixClass: 'keypad-', // The key name marker class prefix
	_keyClass: 'keypad-key', // The key marker class
	_keyDownClass: 'keypad-key-down', // The key down marker class

	/* Override the default settings for all keypad instances.
	   @param  settings  (object) the new settings to use as defaults
	   @return  (Keypad) this object */
	setDefaults: function(settings) {
		$.extend(this._defaults, settings || {});
		return this;
	},

	/* Add the definition of a special key.
	   @param  id           (string) the identifier for this key - access via $.keypad.<id>
	   @param  name         (string) the prefix for localisation strings and
	                        the suffix for a class name
	   @param  action       (function) the action performed for this key -
	                        receives inst as a parameter
	   @param  noHighlight  (boolean) true to suppress highlight when using ThemeRoller
	   @return  (Keypad) this object */
	addKeyDef: function(id, name, action, noHighlight) {
		if (this._keyCode == 32) {
			throw 'Only 32 special keys allowed';
		}
		this[id] = String.fromCharCode(this._keyCode++);
		this._specialKeys.push({code: this[id], id: id, name: name,
			action: action, noHighlight: noHighlight});
		return this;
	},

	/* Attach the keypad to a jQuery selection.
	   @param  target   (element) the control to affect
	   @param  options  (object) the custom options for this instance */
	_attachPlugin: function(target, options) {
		target = $(target);
		if (target.hasClass(this.markerClassName)) {
			return;
		}
		var inline = !target[0].nodeName.toLowerCase().match(/input|textarea/);
		var inst = {options: $.extend({}, this._defaults, options), _inline: inline,
			_mainDiv: (inline ? $('<div class="' + this._inlineClass + '"></div>') : plugin.mainDiv),
			ucase: false};
		this._setInput(target, inst);
		this._connectKeypad(target, inst);
		if (inline) {
			target.append(inst._mainDiv).
				bind('click.' + this.propertyName, function() { inst._input.focus(); });
			this._updateKeypad(inst);
		}
		else if (target.is(':disabled')) {
			this._disablePlugin(target);
		}
	},

	/* Determine the input field for the keypad.
	   @param  target  (jQuery) the target control
	   @param  inst    (object) the instance settings */
	_setInput: function(target, inst) {
		inst._input = $(!inst._inline ? target : inst.options.target ||
			'<input type="text" class="' + this._inlineEntryClass + '" disabled="disabled"/>');
		if (inst._inline) {
			target.find('input').remove();
			if (!inst.options.target) {
				target.append(inst._input);
			}
		}
	},

	/* Attach the keypad to a text field.
	   @param  target  (jQuery) the target text field
	   @param  inst    (object) the instance settings */
	_connectKeypad: function(target, inst) {
		target = $(target);
		var appendText = inst.options.appendText;
		if (appendText) {
			target[inst.options.isRTL ? 'before' : 'after'](
				'<span class="' + this._appendClass + '">' + appendText + '</span>');
		}
		if (!inst._inline) {
			if (inst.options.showOn == 'focus' || inst.options.showOn == 'both') {
				// pop-up keypad when in the marked field
				target.bind('focus.' + this.propertyName, this._showPlugin).
					bind('keydown.' + this.propertyName, this._doKeyDown);
			}
			if (inst.options.showOn == 'button' || inst.options.showOn == 'both') {
				// pop-up keypad when button clicked
				var buttonStatus = inst.options.buttonStatus;
				var buttonImage = inst.options.buttonImage;
				var trigger = $(inst.options.buttonImageOnly ? 
					$('<img src="' + buttonImage + '" alt="' +
					buttonStatus + '" title="' + buttonStatus + '"/>') :
				$('<button type="button" title="' + buttonStatus + '"></button>').
					html(buttonImage == '' ? inst.options.buttonText :
					$('<img src="' + buttonImage + '" alt="' +
					buttonStatus + '" title="' + buttonStatus + '"/>')));
				target[inst.options.isRTL ? 'before' : 'after'](trigger);
				trigger.addClass(this._triggerClass).click(function() {
					if (plugin._keypadShowing && plugin._lastField == target[0]) {
						plugin._hidePlugin();
					}
					else {
						plugin._showPlugin(target[0]);
					}
					return false;
				});
			}
		}
		inst.saveReadonly = target.attr('readonly');
		target.addClass(this.markerClassName).
			data(this.propertyName, inst)
			[inst.options.keypadOnly ? 'attr' : 'removeAttr']('readonly', true).
			bind('setData.' + this.propertyName, function(event, key, value) {
				inst.options[key] = value;
			}).bind('getData.' + this.propertyName, function(event, key) {
				return inst.options[key];
			});
	},

	/* Retrieve or reconfigure the settings for a control.
	   @param  target   (element) the control to affect
	   @param  options  (object) the new options for this instance or
	                    (string) an individual property name
	   @param  value    (any) the individual property value (omit if options
	                    is an object or to retrieve the value of a setting)
	   @return  (any) if retrieving a value */
	_optionPlugin: function(target, options, value) {
		target = $(target);
		var inst = target.data(this.propertyName);
		if (!options || (typeof options == 'string' && value == null)) { // Get option
			var name = options;
			options = (inst || {}).options;
			return (options && name ? options[name] : options);
		}

		if (!target.hasClass(this.markerClassName)) {
			return;
		}
		options = options || {};
		if (typeof options == 'string') {
			var name = options;
			options = {};
			options[name] = value;
		}
		if (this._curInst == inst) {
			this._hidePlugin();
		}
		$.extend(inst.options, options);
		this._setInput(target, inst);
		this._updateKeypad(inst);
	},

	/* Detach keypad from its control.
	   @param  target  (element) the target text field */
	_destroyPlugin: function(target) {
		target = $(target);
		if (!target.hasClass(this.markerClassName)) {
			return;
		}
		var inst = target.data(this.propertyName);
		if (this._curInst == inst) {
			this._hidePlugin();
		}
		target.siblings('.' + this._appendClass).remove().end().
			siblings('.' + this._triggerClass).remove().end().
			prev('.' + this._inlineEntryClass).remove();
		target.removeClass(this.markerClassName).empty().
			unbind('.' + this.propertyName).
			removeData(this.propertyName)
			[inst.saveReadonly ? 'attr' : 'removeAttr']('readonly', true);
		inst._input.removeData(this.propertyName);
	},

	/* Enable the keypad for a jQuery selection.
	   @param  target  (element) the target text field */
	_enablePlugin: function(target) {
		target = $(target);
		if (!target.hasClass(this.markerClassName)) {
			return;
		}
		var nodeName = target[0].nodeName.toLowerCase();
		if (nodeName.match(/input|textarea/)) {
			target[0].disabled = false;
			target.siblings('button.' + this._triggerClass).
				each(function() { this.disabled = false; }).end().
				siblings('img.' + this._triggerClass).
				css({opacity: '1.0', cursor: ''});
		}
		else if (nodeName.match(/div|span/)) {
			target.children('.' + this._disableClass).remove();
			var inst = target.data(this.propertyName);
			inst._mainDiv.find('button').removeAttr('disabled');
		}
		this._disabledFields = $.map(this._disabledFields,
			function(value) { return (value == target[0] ? null : value); }); // delete entry
	},

	/* Disable the keypad for a jQuery selection.
	   @param  target  (element) the target text field */
	_disablePlugin: function(target) {
		target = $(target);
		if (!target.hasClass(this.markerClassName)) {
			return;
		}
		var nodeName = target[0].nodeName.toLowerCase();
		if (nodeName.match(/input|textarea/)) {
			target[0].disabled = true;
			target.siblings('button.' + this._triggerClass).
				each(function() { this.disabled = true; }).end().
				siblings('img.' + this._triggerClass).
				css({opacity: '0.5', cursor: 'default'});
		}
		else if (nodeName.match(/div|span/)) {
			var inline = target.children('.' + this._inlineClass);
			var offset = inline.offset();
			var relOffset = {left: 0, top: 0};
			inline.parents().each(function() {
				if ($(this).css('position') == 'relative') {
					relOffset = $(this).offset();
					return false;
				}
			});
			target.prepend('<div class="' + this._disableClass + '" style="width: ' +
				inline.outerWidth() + 'px; height: ' + inline.outerHeight() +
				'px; left: ' + (offset.left - relOffset.left) +
				'px; top: ' + (offset.top - relOffset.top) + 'px;"></div>');
			var inst = target.data(this.propertyName);
			inst._mainDiv.find('button').attr('disabled', 'disabled');
		}
		this._disabledFields = $.map(this._disabledFields,
			function(value) { return (value == target[0] ? null : value); }); // delete entry
		this._disabledFields[this._disabledFields.length] = target[0];
	},

	/* Is the text field disabled as a keypad?
	   @param  target  (element) the target text field
	   @return  (boolean) true if disabled, false if enabled */
	_isDisabledPlugin: function(target) {
		return (target && $.inArray(target, this._disabledFields) > -1);
	},

	/* Pop-up the keypad for a given text field.
	   @param  field  (element) the text field attached to the keypad or
	                  (event) if triggered by focus */
	_showPlugin: function(field) {
		field = field.target || field;
		if (plugin._isDisabledPlugin(field) ||
				plugin._lastField == field) { // already here
			return;
		}
		var inst = $.data(field, plugin.propertyName);
		plugin._hidePlugin(null, '');
		plugin._lastField = field;
		plugin._pos = plugin._findPos(field);
		plugin._pos[1] += field.offsetHeight; // add the height
		var isFixed = false;
		$(field).parents().each(function() {
			isFixed |= $(this).css('position') == 'fixed';
			return !isFixed;
		});
		var offset = {left: plugin._pos[0], top: plugin._pos[1]};
		plugin._pos = null;
		// determine sizing offscreen
		inst._mainDiv.css({position: 'absolute', display: 'block', top: '-1000px', width: 'auto'});
		plugin._updateKeypad(inst);
		// and adjust position before showing
		offset = plugin._checkOffset(inst, offset, isFixed);
		inst._mainDiv.css({position: (isFixed ? 'fixed' : 'absolute'), display: 'none',
			left: offset.left + 'px', top: offset.top + 'px'});
		var duration = inst.options.duration;
		var showAnim = inst.options.showAnim;
		var postProcess = function() {
			plugin._keypadShowing = true;
		};
		if ($.effects && ($.effects[showAnim] || ($.effects.effect && $.effects.effect[showAnim]))) {
			var data = inst._mainDiv.data(); // Update old effects data
			for (var key in data) {
				if (key.match(/^ec\.storage\./)) {
					data[key] = inst._mainDiv.css(key.replace(/ec\.storage\./, ''));
				}
			}
			inst._mainDiv.data(data).show(showAnim,
				inst.options.showOptions, duration, postProcess);
		}
		else {
			inst._mainDiv[showAnim || 'show']((showAnim ? duration : 0), postProcess);
		}
		if (inst._input[0].type != 'hidden') {
			inst._input[0].focus();
		}
		plugin._curInst = inst;
	},

	/* Generate the keypad content.
	   @param  inst  (object) the instance settings */
	_updateKeypad: function(inst) {
		var borders = this._getBorders(inst._mainDiv);
		inst._mainDiv.empty().append(this._generateHTML(inst)).
			removeClass().addClass(inst.options.keypadClass +
				(inst.options.useThemeRoller ? ' ui-widget ui-widget-content' : '') +
				(inst.options.isRTL ? ' ' + this._rtlClass : '') + ' ' +
				(inst._inline ? this._inlineClass : this._mainDivClass));
		if ($.isFunction(inst.options.beforeShow)) {
			inst.options.beforeShow.apply((inst._input ? inst._input[0] : null),
				[inst._mainDiv, inst]);
		}
	},

	/* Retrieve the size of left and top borders for an element.
	   @param  elem  (jQuery object) the element of interest
	   @return  (number[2]) the left and top borders */
	_getBorders: function(elem) {
		var convert = function(value) {
			return {thin: 1, medium: 3, thick: 5}[value] || value;
		};
		return [parseFloat(convert(elem.css('border-left-width'))),
			parseFloat(convert(elem.css('border-top-width')))];
	},

	/* Check positioning to remain on screen.
	   @param  inst    (object) the instance settings
	   @param  offset  (object) the current offset
	   @param  isFixed  (boolean) true if the text field is fixed in position
	   @return  (object) the updated offset */
	_checkOffset: function(inst, offset, isFixed) {
		var pos = inst._input ? this._findPos(inst._input[0]) : null;
		var browserWidth = window.innerWidth || document.documentElement.clientWidth;
		var browserHeight = window.innerHeight || document.documentElement.clientHeight;
		var scrollX = document.documentElement.scrollLeft || document.body.scrollLeft;
		var scrollY = document.documentElement.scrollTop || document.body.scrollTop;
		// recalculate width as otherwise set to 100%
		var width = 0;
		inst._mainDiv.find(':not(div)').each(function() {
			width = Math.max(width, this.offsetLeft + $(this).outerWidth(true));
		});
		inst._mainDiv.css('width', width + 1);
		// reposition keypad panel horizontally if outside the browser window
		if (inst.options.isRTL ||
				(offset.left + inst._mainDiv.outerWidth() - scrollX) > browserWidth) {
			offset.left = Math.max((isFixed ? 0 : scrollX),
				pos[0] + (inst._input ? inst._input.outerWidth() : 0) -
				(isFixed ? scrollX : 0) - inst._mainDiv.outerWidth());
		}
		else {
			offset.left = Math.max((isFixed ? 0 : scrollX), offset.left - (isFixed ? scrollX : 0));
		}
		// reposition keypad panel vertically if outside the browser window
		if ((offset.top + inst._mainDiv.outerHeight() - scrollY) > browserHeight) {
			offset.top = Math.max((isFixed ? 0 : scrollY),
				pos[1] - (isFixed ? scrollY : 0) - inst._mainDiv.outerHeight());
		}
		else {
			offset.top = Math.max((isFixed ? 0 : scrollY), offset.top - (isFixed ? scrollY : 0));
		}
		return offset;
	},
	
	/* Find an object's position on the screen.
	   @param  obj  (element) the element to find the position for
	   @return  (int[2]) the element's position */
	_findPos: function(obj) {
        while (obj && (obj.type == 'hidden' || obj.nodeType != 1)) {
            obj = obj.nextSibling;
        }
        var position = $(obj).offset();
	    return [position.left, position.top];
	},

	/* Hide the keypad from view.
	   @param  field     (element) the text field attached to the keypad
	   @param  duration  (string) the duration over which to close the keypad */
	_hidePlugin: function(field, duration) {
		var inst = this._curInst;
		if (!inst || (field && inst != $.data(field, this.propertyName))) {
			return;
		}
		if (this._keypadShowing) {
			duration = (duration != null ? duration : inst.options.duration);
			var showAnim = inst.options.showAnim;
			if ($.effects && ($.effects[showAnim] || ($.effects.effect && $.effects.effect[showAnim]))) {
				inst._mainDiv.hide(showAnim, inst.options.showOptions, duration);
			}
			else {
				inst._mainDiv[(showAnim == 'slideDown' ? 'slideUp' :
					(showAnim == 'fadeIn' ? 'fadeOut' : 'hide'))](showAnim ? duration : 0);
			}
		}
		if ($.isFunction(inst.options.onClose)) {
			inst.options.onClose.apply((inst._input ? inst._input[0] : null),  // trigger custom callback
				[inst._input.val(), inst]);
		}
		if (this._keypadShowing) {
			this._keypadShowing = false;
			this._lastField = null;
		}
		if (inst._inline) {
			inst._input.val('');
		}
		this._curInst = null;
	},

	/* Handle keystrokes.
	   @param  e  (event) the key event */
	_doKeyDown: function(e) {
		if (e.keyCode == 9) { // Tab out
			plugin.mainDiv.stop(true, true);
			plugin._hidePlugin();
		}
	},

	/* Close keypad if clicked elsewhere.
	   @param  event  (event) the mouseclick details */
	_checkExternalClick: function(event) {
		if (!plugin._curInst) {
			return;
		}
		var target = $(event.target);
		if (!target.parents().andSelf().hasClass(plugin._mainDivClass) &&
				!target.hasClass(plugin.markerClassName) &&
				!target.parents().andSelf().hasClass(plugin._triggerClass) &&
				plugin._keypadShowing) {
			plugin._hidePlugin();
		}
	},

	/* Toggle between upper and lower case.
	   @param  inst  (object) the instance settings */
	_shiftKeypad: function(inst) {
		inst.ucase = !inst.ucase;
		this._updateKeypad(inst);
		inst._input.focus(); // for further typing
	},

	/* Erase the text field.
	   @param  inst  (object) the instance settings */
	_clearValue: function(inst) {
		this._setValue(inst, '', 0);
		this._notifyKeypress(inst, plugin.DEL);
	},

	/* Erase the last character.
	   @param  inst  (object) the instance settings */
	_backValue: function(inst) {
		var field = inst._input[0];
		var value = inst._input.val();
		var range = [value.length, value.length];
		if (field.setSelectionRange) { // Mozilla
			range = (inst._input.attr('readonly') || inst._input.attr('disabled') ?
				range : [field.selectionStart, field.selectionEnd]);
		}
		else if (field.createTextRange) { // IE
			range = (inst._input.attr('readonly') || inst._input.attr('disabled') ?
				range : this._getIERange(field));
		}
		this._setValue(inst, (value.length == 0 ? '' :
			value.substr(0, range[0] - 1) + value.substr(range[1])), range[0] - 1);
		this._notifyKeypress(inst, plugin.BS);
	},

	/* Update the text field with the selected value.
	   @param  inst   (object) the instance settings
	   @param  value  (string) the new character to add */
	_selectValue: function(inst, value) {
		this.insertValue(inst._input[0], value);
		this._setValue(inst, inst._input.val());
		this._notifyKeypress(inst, value);
	},

	/* Update the text field with the selected value.
	   @param  input  (element) the input field or
	                  (jQuery) jQuery collection
	   @param  value  (string) the new character to add */
	insertValue: function(input, value) {
		input = (input.jquery ? input : $(input));
		var field = input[0];
		var newValue = input.val();
		var range = [newValue.length, newValue.length];
		if (field.setSelectionRange) { // Mozilla
			range = (input.attr('readonly') || input.attr('disabled') ?
				range : [field.selectionStart, field.selectionEnd]);
		}
		else if (field.createTextRange) { // IE
			range = (input.attr('readonly') || input.attr('disabled') ?
				range : this._getIERange(field));
		}
		input.val(newValue.substr(0, range[0]) + value + newValue.substr(range[1]));
		pos = range[0] + value.length;
		if (input.is(':visible')) {
			input.focus(); // for further typing
		}
		if (field.setSelectionRange) { // Mozilla
			if (input.is(':visible')) {
				field.setSelectionRange(pos, pos);
			}
		}
		else if (field.createTextRange) { // IE
			range = field.createTextRange();
			range.move('character', pos);
			range.select();
		}
	},

	/* Get the coordinates for the selected area in the text field in IE.
	   @param  field  (element) the target text field
	   @return  (int[2]) the start and end positions of the selection */
	_getIERange: function(field) {
		field.focus();
		var selectionRange = document.selection.createRange().duplicate();
		// Use two ranges: before and selection
		var beforeRange = this._getIETextRange(field);
		beforeRange.setEndPoint('EndToStart', selectionRange);
		// Check each range for trimmed newlines by shrinking the range by one
		// character and seeing if the text property has changed. If it has not
		// changed then we know that IE has trimmed a \r\n from the end.
		var checkCRLF = function(range) {
			var origText = range.text;
			var text = origText;
			var finished = false;
			while (true) {
				if (range.compareEndPoints('StartToEnd', range) == 0) {
					break;
				} 
				else {
					range.moveEnd('character', -1);
					if (range.text == origText) {
						text += '\r\n';
					} 
					else {
						break;
					}
				}
			}
			return text;
		};
		var beforeText = checkCRLF(beforeRange);
		var selectionText = checkCRLF(selectionRange);
		return [beforeText.length, beforeText.length + selectionText.length];
	},

	/* Create an IE text range for the text field.
	   @param  field  (element) the target text field
	   @return  (object) the corresponding text range */
	_getIETextRange: function(field) {
		var isInput = (field.nodeName.toLowerCase() == 'input');
		var range = (isInput ? field.createTextRange() : document.body.createTextRange());
		if (!isInput) {
			range.moveToElementText(field); // Selects all the text for a textarea
		}
		return range;
	},

	/* Set the text field to the selected value,
	   and trigger any on change event.
	   @param  inst   (object) the instance settings
	   @param  value  (string) the new value for the text field */
	_setValue: function(inst, value) {
		var maxlen = inst._input.attr('maxlength');
		if (maxlen > -1) {
			value = value.substr(0, maxlen);
		}
		inst._input.val(value);
		if (!$.isFunction(inst.options.onKeypress)) {
			inst._input.trigger('change'); // fire the change event
		}
	},

	_notifyKeypress: function(inst, key) {
		if ($.isFunction(inst.options.onKeypress)) { // trigger custom callback
			inst.options.onKeypress.apply((inst._input ? inst._input[0] : null),
				[key, inst._input.val(), inst]);
		}
	},

	/* Generate the HTML for the current state of the keypad.
	   @param  inst  (object) the instance settings
	   @return  (jQuery) the HTML for this keypad */
	_generateHTML: function(inst) {
		var html = (!inst.options.prompt ? '' : '<div class="' + this._promptClass +
			(inst.options.useThemeRoller ? ' ui-widget-header ui-corner-all' : '') + '">' +
			inst.options.prompt + '</div>');
		var layout = this._randomiseLayout(inst);
		for (var i = 0; i < layout.length; i++) {
			html += '<div class="' + this._rowClass + '">';
			var keys = layout[i].split(inst.options.separator);
			for (var j = 0; j < keys.length; j++) {
				if (inst.ucase) {
					keys[j] = inst.options.toUpper(keys[j]);
				}
				var keyDef = this._specialKeys[keys[j].charCodeAt(0)];
				if (keyDef) {
					html += (keyDef.action ? '<button type="button" class="' + this._specialClass +
						' ' + this._namePrefixClass + keyDef.name +
						(inst.options.useThemeRoller ? ' ui-corner-all ui-state-default' +
						(keyDef.noHighlight ? '' : ' ui-state-highlight') : '') +
						'" title="' + inst.options[keyDef.name + 'Status'] + '">' +
						(inst.options[keyDef.name + 'Text'] || '&nbsp;') + '</button>' :
						'<div class="' + this._namePrefixClass + keyDef.name + '"></div>');
				}
				else {
					html += '<button type="button" class="' + this._keyClass +
						(inst.options.useThemeRoller ? ' ui-corner-all ui-state-default' : '') + '">' +
						(keys[j] == ' ' ? '&nbsp;' : keys[j]) + '</button>';
				}
			}
			html += '</div>';
		}
		html = $(html);
		var thisInst = inst;
		var activeClasses = this._keyDownClass + (inst.options.useThemeRoller ? ' ui-state-active' : '');
		html.find('button').mousedown(function() { $(this).addClass(activeClasses); }).
			mouseup(function() { $(this).removeClass(activeClasses); }).
			mouseout(function() { $(this).removeClass(activeClasses); }).
			filter('.' + this._keyClass).click(function() { plugin._selectValue(thisInst, $(this).text()); });
		$.each(this._specialKeys, function(i, keyDef) {
			html.find('.' + plugin._namePrefixClass + keyDef.name).click(function() {
				keyDef.action.apply(thisInst._input, [thisInst]);
			});
		});
		return html;
	},

	/* Check whether characters should be randomised,
	   and, if so, produce the randomised layout.
	   @param  inst  (object) the instance settings
	   @return  (string[]) the layout with any requested randomisations applied */
	_randomiseLayout: function(inst) {
		if (!inst.options.randomiseNumeric && !inst.options.randomiseAlphabetic &&
				!inst.options.randomiseOther && !inst.options.randomiseAll) {
			return inst.options.layout;
		}
		var numerics = [];
		var alphas = [];
		var others = [];
		var newLayout = [];
		// Find characters of different types
		for (var i = 0; i < inst.options.layout.length; i++) {
			newLayout[i] = '';
			var keys = inst.options.layout[i].split(inst.options.separator);
			for (var j = 0; j < keys.length; j++) {
				if (this._isControl(keys[j])) {
					continue;
				}
				if (inst.options.randomiseAll) {
					others.push(keys[j]);
				}
				else if (inst.options.isNumeric(keys[j])) {
					numerics.push(keys[j]);
				}
				else if (inst.options.isAlphabetic(keys[j])) {
					alphas.push(keys[j]);
				}
				else {
					others.push(keys[j]);
				}
			}
		}
		// Shuffle them
		if (inst.options.randomiseNumeric) {
			this._shuffle(numerics);
		}
		if (inst.options.randomiseAlphabetic) {
			this._shuffle(alphas);
		}
		if (inst.options.randomiseOther || inst.options.randomiseAll) {
			this._shuffle(others);
		}
		var n = 0;
		var a = 0;
		var o = 0;
		// And replace them in the layout
		for (var i = 0; i < inst.options.layout.length; i++) {
			var keys = inst.options.layout[i].split(inst.options.separator);
			for (var j = 0; j < keys.length; j++) {
				newLayout[i] += (this._isControl(keys[j]) ? keys[j] :
					(inst.options.randomiseAll ? others[o++] :
					(inst.options.isNumeric(keys[j]) ? numerics[n++] :
					(inst.options.isAlphabetic(keys[j]) ? alphas[a++] :
					others[o++])))) + inst.options.separator;
			}
		}
		return newLayout;
	},

	/* Is a given character a control character?
	   @param  ch  (char) the character to test
	   @return  (boolean) true if a control character, false if not */
	_isControl: function(ch) {
		return ch < ' ';
	},

	/* Is a given character alphabetic?
	   @param  ch  (char) the character to test
	   @return  (boolean) true if alphabetic, false if not */
	isAlphabetic: function(ch) {
		return (ch >= 'A' && ch <= 'Z') || (ch >= 'a' && ch <= 'z');
	},

	/* Is a given character numeric?
	   @param  ch  (char) the character to test
	   @return  (boolean) true if numeric, false if not */
	isNumeric: function(ch) {
		return (ch >= '0' && ch <= '9');
	},

	/* Convert a character to upper case.
	   @param  ch  (char) the character to convert
	   @return  (char) its uppercase version */
	toUpper: function(ch) {
		return ch.toUpperCase();
	},
	/* Randomise the contents of an array.
	   @param  values  (string[]) the array to rearrange */
	_shuffle: function(values) {
		for (var i = values.length - 1; i > 0; i--) {
			var j = Math.floor(Math.random() * values.length);
			var ch = values[i];
			values[i] = values[j];
			values[j] = ch;
		}
	}
});

// The list of commands that return values and don't permit chaining
var getters = ['isDisabled'];

/* Determine whether a command is a getter and doesn't permit chaining.
   @param  command    (string, optional) the command to run
   @param  otherArgs  ([], optional) any other arguments for the command
   @return  true if the command is a getter, false if not */
function isNotChained(command, otherArgs) {
	if (command == 'option' && (otherArgs.length == 0 ||
			(otherArgs.length == 1 && typeof otherArgs[0] == 'string'))) {
		return true;
	}
	return $.inArray(command, getters) > -1;
}

/* Invoke the keypad functionality.
   @param  options  (object) the new settings to use for these instances (optional) or
                    (string) the command to run (optional)
   @return  (jQuery) for chaining further calls or
            (any) getter value */
$.fn.keypad = function(options) {
	var otherArgs = Array.prototype.slice.call(arguments, 1);
	if (isNotChained(options, otherArgs)) {
		return plugin['_' + options + 'Plugin'].apply(plugin, [this[0]].concat(otherArgs));
	}
	return this.each(function() {
		if (typeof options == 'string') {
			if (!plugin['_' + options + 'Plugin']) {
				throw 'Unknown command: ' + options;
			}
			plugin['_' + options + 'Plugin'].apply(plugin, [this].concat(otherArgs));
		}
		else {
			plugin._attachPlugin(this, options || {});
		}
	});
};

/* Initialise the keypad functionality. */
var plugin = $.keypad = new Keypad(); // Singleton instance

// Add the keypad division and external click check
$(function() {
	$(document.body).append(plugin.mainDiv).
		mousedown(plugin._checkExternalClick);
});

})(jQuery);
