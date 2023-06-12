/* http://keith-wood.name/keypad.html
   Keypad field entry extension for jQuery v2.0.1.
   Written by Keith Wood (kbwood{at}iinet.com.au) August 2008.
   Available under the MIT (https://github.com/jquery/jquery/blob/master/LICENSE.txt) license. 
   Please attribute the author if you use it. */
   
(function($) { // hide the namespace

	var pluginName = 'keypad';
	
	var layoutStandard = ['  BSCECA', '_1_2_3_+@X', '_4_5_6_-@U', '_7_8_9_*@E', '_0_._=_/'];

	/** Create the keypad plugin.
		<p>Sets an input field to popup a keypad for keystroke entry,
			or creates an inline keypad in a <code>div</code> or <code>span</code>.</p>
		<p>Expects HTML like:</p>
		<pre>&lt;input type="text"> or
&lt;div&gt;&lt;/div&gt;</pre>
		<p>Provide inline configuration like:</p>
		<pre>&lt;input type="text" data-keypad="name: 'value'"/></pre>
	 	@module Keypad
		@augments JQPlugin
		@example $(selector).keypad() */
	$.JQPlugin.createPlugin({
	
		/** The name of the plugin. */
		name: pluginName,
			
		/** Keypad before show callback.
			Triggered before the keypad is shown.
			@callback beforeShowCallback
			@param div {jQuery} The div to be shown.
			@param inst {object} The current instance settings. */
			
		/** Keypad on keypress callback.
			Triggered when a key on the keypad is pressed.
			@callback keypressCallback
			@param key {string} The key just pressed.
			@param value {string} The full value entered so far.
			@param inst {object} The current instance settings. */
			
		/** Keypad on close callback.
			Triggered when the keypad is closed.
			@callback closeCallback
			@param value {string} The full value entered so far.
			@param inst {object} The current instance settings. */
			
		/** Keypad is alphabetic callback.
			Triggered when an alphabetic key needs to be identified.
			@callback isAlphabeticCallback
			@param ch {string} The key to check.
			@return {boolean} True if this key is alphabetic, false if not.
			@example isAlphabetic: function(ch) {
	return (ch >= 'A' && ch <= 'Z') || (ch >= 'a' && ch <= 'z');
 } */
			
		/** Keypad is numeric callback.
			Triggered when an numeric key needs to be identified.
			@callback isNumericCallback
			@param ch {string} The key to check.
			@return {boolean} True if this key is numeric, false if not.
			@example isNumeric: function(ch) {
	return (ch >= '0' && ch <= '9');
 } */
			
		/** Keypad to upper callback.
			Triggered to convert keys to upper case.
			@callback toUpperCallback
			@param ch {string} The key to convert.
			@return {string} The upper case version of this key.
			@example toUpper: function(ch) {
	return ch.toUpperCase();
 } */
			
		/** Default settings for the plugin.
			@property [showOn='focus'] {string} 'focus' for popup on focus, 'button' for trigger button, or 'both' for either.
			@property [buttonImage=''] {string} URL for trigger button image.
			@property [buttonImageOnly=false] {boolean} True if the image appears alone, false if it appears on a button.
			@property [showAnim='show'] {string} Name of jQuery animation for popup.
			@property [showOptions=null] {object} Options for enhanced animations.
			@property [duration='normal'] {string|number} Duration of display/closure.
			@property [appendText=''] {string} Display text following the text field, e.g. showing the format.
			@property [useThemeRoller=false] {boolean} True to add ThemeRoller classes.
			@property [keypadClass=''] {string} Additional CSS class for the keypad for an instance.
			@property [prompt=''] {string} Display text at the top of the keypad.
			@property [layout=this.numericLayout] {string} Layout of keys.
			@property [separator=''] {string} Separator character between keys.
			@property [target=null] {string|jQuery|Element} Input target for an inline keypad.
			@property [keypadOnly=true] {boolean} True for entry only via the keypad, false for real keyboard too.
			@property [randomiseAlphabetic=false] {boolean} True to randomise the alphabetic key positions, false to keep in order.
			@property [randomiseNumeric=false] {boolean} True to randomise the numeric key positions, false to keep in order.
			@property [randomiseOther=false] {boolean} True to randomise the other key positions, false to keep in order.
			@property [randomiseAll=false] {boolean} True to randomise all key positions, false to keep in order.
			@property [beforeShow=null] {beforeShowCallback} Callback before showing the keypad.
			@property [onKeypress=null] {keypressCallback} Callback when a key is selected.
			@property [onClose=null] {closeCallback} Callback when the panel is closed. */
		defaultOptions: {
			showOn: 'focus',
			buttonImage: '',
			buttonImageOnly: false,
			showAnim: 'show',
			showOptions: null,
			duration: 'normal',
			appendText: '',
			useThemeRoller: false,
			keypadClass: '',
			prompt: '',
			layout: [], // Set at the end
			separator: '',
			target: null,
			keypadOnly: true,
			randomiseAlphabetic: false,
			randomiseNumeric: false,
			randomiseOther: false,
			randomiseAll: false,
			beforeShow: null,
			onKeypress: null,
			onClose: null
		},

		/** Localisations for the plugin.
			Entries are objects indexed by the language code ('' being the default US/English).
			Each object has the following attributes.
			@property [buttonText='...'] {string} Display text for trigger button.
			@property [buttonStatus='Open the keypad'] {string} Status text for trigger button.
			@property [closeText='Close'] {string} Display text for close link.
			@property [closeStatus='Close the keypad'] {string} Status text for close link.
			@property [clearText='Clear'] {string} Display text for clear link.
			@property [clearStatus='Erase all the text'] {string} Status text for clear link.
			@property [backText='Back'] {string} Display text for back link.
			@property [backStatus='Erase the previous character'] {string} Status text for back link.
			@property [spacebarText='&#160;'] {string} Display text for space bar.
			@property [spacebarStatus='Space'] {string} Status text for space bar.
			@property [enterText='Enter'] {string} Display text for carriage return.
			@property [enterStatus='Carriage return'] {string} Status text for carriage return.
			@property [tabText='→'] {string} Display text for tab.
			@property [tabStatus='Horizontal tab'] {string} Status text for tab.
			@property [shiftText='Shift'] {string} Display text for shift link.
			@property [shiftStatus='Toggle upper/lower case characters'] {string} Status text for shift link.
			@property [alphabeticLayout=this.qwertyAlphabetic] {string} Default layout for alphabetic characters.
			@property [fullLayout=this.qwertyLayout] {string} Default layout for full keyboard.
			@property [isAlphabetic=this.isAlphabetic] {isAlphabeticCallback} Function to determine if character is alphabetic.
			@property [isNumeric=this.isNumeric] {isNumericCallback} Function to determine if character is numeric.
			@property [toUpper=this.toUpper] {toUpperCallback} Function to convert characters to upper case.
			@property [isRTL=false] {boolean} True if right-to-left language, false if left-to-right. */
		regionalOptions: { // Available regional settings, indexed by language/country code
			'': { // Default regional settings - English/US
				buttonText: '...',
				buttonStatus: 'Open the keypad',
				closeText: 'Close',
				closeStatus: 'Close the keypad',
				clearText: 'Clear',
				clearStatus: 'Erase all the text',
				backText: 'Back',
				backStatus: 'Erase the previous character',
				spacebarText: '&#160;',
				spacebarStatus: 'Space',
				enterText: 'Enter',
				enterStatus: 'Carriage return',
				tabText: '→',
				tabStatus: 'Horizontal tab',
				shiftText: 'Shift',
				shiftStatus: 'Toggle upper/lower case characters',
				alphabeticLayout: [], // Set at the end
				fullLayout: [],
				isAlphabetic: null,
				isNumeric: null,
				toUpper: null,
				isRTL: false
			}
		},
		
		/** Names of getter methods - those that can't be chained. */
		_getters: ['isDisabled'],

		_curInst: null, // The current instance in use
		_disabledFields: [], // List of keypad fields that have been disabled
		_keypadShowing: false, // True if the popup panel is showing , false if not
		_keyCode: 0,
		_specialKeys: [],
		
		_mainDivClass: pluginName + '-popup', // The main keypad division class
		_inlineClass: pluginName + '-inline', // The inline marker class
		_appendClass: pluginName + '-append', // The append marker class
		_triggerClass: pluginName + '-trigger', // The trigger marker class
		_disableClass: pluginName + '-disabled', // The disabled covering marker class
		_inlineEntryClass: pluginName + '-keyentry', // The inline entry marker class
		_rtlClass: pluginName + '-rtl', // The right-to-left marker class
		_rowClass: pluginName + '-row', // The keypad row marker class
		_promptClass: pluginName + '-prompt', // The prompt marker class
		_specialClass: pluginName + '-special', // The special key marker class
		_namePrefixClass: pluginName + '-', // The key name marker class prefix
		_keyClass: pluginName + '-key', // The key marker class
		_keyDownClass: pluginName + '-key-down', // The key down marker class

		// Standard US keyboard alphabetic layout
		qwertyAlphabetic: ['qwertyuiop', 'asdfghjkl', 'zxcvbnm'],
		// Standard US keyboard layout
		qwertyLayout: ['!@#$%^&*()_=' + this.HALF_SPACE + this.SPACE + this.CLOSE,
			this.HALF_SPACE + '`~[]{}<>\\|/' + this.SPACE + '789',
			'qwertyuiop\'"' + this.HALF_SPACE + '456',
			this.HALF_SPACE + 'asdfghjkl;:' + this.SPACE + '123',
			this.SPACE + 'zxcvbnm,.?' + this.SPACE + this.HALF_SPACE + '-0+',
			'' + this.TAB + this.ENTER + this.SPACE_BAR + this.SHIFT +
			this.HALF_SPACE + this.BACK + this.CLEAR],

		/** Add the definition of a special key.
			@param id {string} The identifier for this key - access via <code>$.keypad.xxx</code>.<id>.
			@param name {string} The prefix for localisation strings and the suffix for a class name.
			@param action {function} The action performed for this key - receives <code>inst</code> as a parameter.
			@param noHighlight {boolean} True to suppress highlight when using ThemeRoller.
			@return {Keypad} The keypad object for chaining further calls.
			@example $.keypad.addKeyDef('CLEAR', 'clear', function(inst) { plugin._clearValue(inst); }); */
		addKeyDef: function(id, name, action, noHighlight) {
			if (this._keyCode == 32) {
				throw 'Only 32 special keys allowed';
			}
			this[id] = String.fromCharCode(this._keyCode++);
			this._specialKeys.push({code: this[id], id: id, name: name,
				action: action, noHighlight: noHighlight});
			return this;
		},

		/** Additional setup for the keypad.
			Create popup div. */
		_init: function() {
			this.mainDiv = $('<div class="' + this._mainDivClass + '" style="display: none;"></div>');
			this._super();
		},

		_instSettings: function(elem, options) {
			var inline = !elem[0].nodeName.toLowerCase().match(/input|textarea/);
			return {_inline: inline, ucase: false,
				_mainDiv: (inline ? $('<div class="' + this._inlineClass + '"></div>') : plugin.mainDiv)};
		},

		_postAttach: function(elem, inst) {
			if (inst._inline) {
				elem.append(inst._mainDiv).
					on('click.' + inst.name, function() { inst._input.focus(); });
				this._updateKeypad(inst);
			}
			else if (elem.is(':disabled')) {
				this.disable(elem);
			}
		},

		/** Determine the input field for the keypad.
			@private
			@param elem {jQuery} The target control.
			@param inst {object} The instance settings. */
		_setInput: function(elem, inst) {
			inst._input = $(!inst._inline ? elem : inst.options.target ||
				'<input type="text" class="' + this._inlineEntryClass + '" disabled/>');
			if (inst._inline) {
				elem.find('input').remove();
				if (!inst.options.target) {
					elem.append(inst._input);
				}
			}
		},

		_optionsChanged: function(elem, inst, options) {
			$.extend(inst.options, options);
			elem.off('.' + inst.name).
				siblings('.' + this._appendClass).remove().end().
				siblings('.' + this._triggerClass).remove();
			var appendText = inst.options.appendText;
			if (appendText) {
				elem[inst.options.isRTL ? 'before' : 'after'](
					'<span class="' + this._appendClass + '">' + appendText + '</span>');
			}
			if (!inst._inline) {
				if (inst.options.showOn == 'focus' || inst.options.showOn == 'both') {
					// pop-up keypad when in the marked field
					elem.on('focus.' + inst.name, this.show).
						on('keydown.' + inst.name, this._doKeyDown);
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
						elem[inst.options.isRTL ? 'before' : 'after'](trigger);
					trigger.addClass(this._triggerClass).click(function() {
						if (plugin._keypadShowing && plugin._lastField == elem[0]) {
							plugin.hide();
						}
						else {
							plugin.show(elem[0]);
						}
						return false;
					});
				}
			}
			inst.saveReadonly = elem.attr('readonly');
			elem[inst.options.keypadOnly ? 'attr' : 'removeAttr']('readonly', true).
				on('setData.' + inst.name, function(event, key, value) {
					inst.options[key] = value;
				}).
				on('getData.' + inst.name, function(event, key) {
					return inst.options[key];
				});
			this._setInput(elem, inst);
			this._updateKeypad(inst);
		},

		_preDestroy: function(elem, inst) {
			if (this._curInst == inst) {
				this.hide();
			}
			elem.siblings('.' + this._appendClass).remove().end().
				siblings('.' + this._triggerClass).remove().end().
				prev('.' + this._inlineEntryClass).remove();
			elem.empty().off('.' + inst.name)
				[inst.saveReadonly ? 'attr' : 'removeAttr']('readonly', true);
			inst._input.removeData(inst.name);
		},

		/** Enable the keypad for a jQuery selection.
			@param elem {Element} The target text field.
			@example $(selector).keypad('enable'); */
		enable: function(elem) {
			elem = $(elem);
			if (!elem.hasClass(this._getMarker())) {
				return;
			}
			var nodeName = elem[0].nodeName.toLowerCase();
			if (nodeName.match(/input|textarea/)) {
				elem.prop('disabled', false).
					siblings('button.' + this._triggerClass).prop('disabled', false).end().
					siblings('img.' + this._triggerClass).css({opacity: '1.0', cursor: ''});
			}
			else if (nodeName.match(/div|span/)) {
				elem.children('.' + this._disableClass).remove();
				this._getInst(elem)._mainDiv.find('button').prop('disabled', false);
			}
			this._disabledFields = $.map(this._disabledFields,
				function(value) { return (value == elem[0] ? null : value); }); // delete entry
		},

		/** Disable the keypad for a jQuery selection.
			@param elem {Element} The target text field.
			@example $(selector).keypad('disable'); */
		disable: function(elem) {
			elem = $(elem);
			if (!elem.hasClass(this._getMarker())) {
				return;
			}
			var nodeName = elem[0].nodeName.toLowerCase();
			if (nodeName.match(/input|textarea/)) {
				elem.prop('disabled', true).
					siblings('button.' + this._triggerClass).prop('disabled', true).end().
					siblings('img.' + this._triggerClass).css({opacity: '0.5', cursor: 'default'});
			}
			else if (nodeName.match(/div|span/)) {
				var inline = elem.children('.' + this._inlineClass);
				var offset = inline.offset();
				var relOffset = {left: 0, top: 0};
				inline.parents().each(function() {
					if ($(this).css('position') == 'relative') {
						relOffset = $(this).offset();
						return false;
					}
				});
				elem.prepend('<div class="' + this._disableClass + '" style="width: ' +
					inline.outerWidth() + 'px; height: ' + inline.outerHeight() +
					'px; left: ' + (offset.left - relOffset.left) +
					'px; top: ' + (offset.top - relOffset.top) + 'px;"></div>');
				this._getInst(elem)._mainDiv.find('button').prop('disabled', true);
			}
			this._disabledFields = $.map(this._disabledFields,
				function(value) { return (value == elem[0] ? null : value); }); // delete entry
			this._disabledFields[this._disabledFields.length] = elem[0];
		},

		/** Is the text field disabled as a keypad?
			@param elem {Element} The target text field.
			@return {boolean} True if disabled, false if enabled.
			@example var disabled = $(selector).keypad('isDisabled'); */
		isDisabled: function(elem) {
			return (elem && $.inArray(elem, this._disabledFields) > -1);
		},

		/** Pop-up the keypad for a given text field.
			@param elem {Element|Event} The text field attached to the keypad or event if triggered by focus.
			@example $(selector).keypad('show'); */
		show: function(elem) {
			elem = elem.target || elem;
			if (plugin.isDisabled(elem) || plugin._lastField == elem) { // already here
				return;
			}
			var inst = plugin._getInst(elem);
			plugin.hide(null, '');
			plugin._lastField = elem;
			plugin._pos = plugin._findPos(elem);
			plugin._pos[1] += elem.offsetHeight; // add the height
			var isFixed = false;
			$(elem).parents().each(function() {
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
					inst.options.showOptions || {}, duration, postProcess);
			}
			else {
				inst._mainDiv[showAnim || 'show']((showAnim ? duration : 0), postProcess);
			}
			if (inst._input[0].type != 'hidden') {
				inst._input[0].focus();
			}
			plugin._curInst = inst;
		},

		/** Generate the keypad content.
			@private
			@param inst {object} The instance settings. */
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

		/** Retrieve the size of left and top borders for an element.
			@private
			@param elem {jQuery} The element of interest.
			@return {number[]} The left and top borders. */
		_getBorders: function(elem) {
			var convert = function(value) {
				return {thin: 1, medium: 3, thick: 5}[value] || value;
			};
			return [parseFloat(convert(elem.css('border-left-width'))),
				parseFloat(convert(elem.css('border-top-width')))];
		},

		/** Check positioning to remain on screen.
			@private
			@param inst {object} The instance settings.
			@param offset {object} The current offset.
			@param isFixed {boolean} True if the text field is fixed in position.
			@return {object} The updated offset. */
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
	
		/** Find an object's position on the screen.
			@private
			@param obj {Element} The element to find the position for.
			@return {number[]} The element's position. */
		_findPos: function(obj) {
			while (obj && (obj.type == 'hidden' || obj.nodeType != 1)) {
				obj = obj.nextSibling;
			}
			var position = $(obj).offset();
			return [position.left, position.top];
		},

		/** Hide the keypad from view.
			@param elem {Element} The text field attached to the keypad.
			@param duration {string} The duration over which to close the keypad.
			@example $(selector).keypad('hide') */
		hide: function(elem, duration) {
			var inst = this._curInst;
			if (!inst || (elem && inst != $.data(elem, this.name))) {
				return;
			}
			if (this._keypadShowing) {
				duration = (duration != null ? duration : inst.options.duration);
				var showAnim = inst.options.showAnim;
				if ($.effects && ($.effects[showAnim] || ($.effects.effect && $.effects.effect[showAnim]))) {
					inst._mainDiv.hide(showAnim, inst.options.showOptions || {}, duration);
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

		/** Handle keystrokes.
			@private
			@param event {Event} The key event. */
		_doKeyDown: function(event) {
			if (event.keyCode == 9) { // Tab out
				plugin.mainDiv.stop(true, true);
				plugin.hide();
			}
		},

		/** Close keypad if clicked elsewhere.
			@private
			@param event {Event} The mouseclick details. */
		_checkExternalClick: function(event) {
			if (!plugin._curInst) {
				return;
			}
			var target = $(event.target);
			if (target.closest('.' + plugin._mainDivClass).length === 0 &&
					!target.hasClass(plugin._getMarker()) &&
					target.closest('.' + plugin._triggerClass).length === 0 &&
					plugin._keypadShowing) {
				plugin.hide();
			}
		},

		/** Toggle between upper and lower case.
			@private
			@param inst {object} The instance settings. */
		_shiftKeypad: function(inst) {
			inst.ucase = !inst.ucase;
			this._updateKeypad(inst);
			inst._input.focus(); // for further typing
		},

		/** Erase the text field.
			@private
			@param inst {object} The instance settings. */
		_clearValue: function(inst) {
			this._setValue(inst, '', 0);
			this._notifyKeypress(inst, plugin.DEL);
		},

		/** Erase the last character.
			@private
			@param inst {object} The instance settings. */
		_backValue: function(inst) {
			var elem = inst._input[0];
			var value = inst._input.val();
			var range = [value.length, value.length];
			range = (inst._input.prop('readonly') || inst._input.prop('disabled') ? range :
				(elem.setSelectionRange /* Mozilla */ ? [elem.selectionStart, elem.selectionEnd] :
				(elem.createTextRange /* IE */ ? this._getIERange(elem) : range)));
			this._setValue(inst, (value.length == 0 ? '' :
				value.substr(0, range[0] - 1) + value.substr(range[1])), range[0] - 1);
			this._notifyKeypress(inst, plugin.BS);
		},

		/** Update the text field with the selected value.
			@private
			@param inst {object} The instance settings.
			@param value {string} The new character to add. */
		_selectValue: function(inst, value) {
			this.insertValue(inst._input[0], value);
			this._setValue(inst, inst._input.val());
			this._notifyKeypress(inst, value);
		},

		/** Update the text field with the selected value.
			@param input {string|Element|jQuery} The jQuery selector, input field, or jQuery collection.
			@param value {string} The new character to add.
			@example $.keypad.insertValue(field, 'abc'); */
		insertValue: function(input, value) {
			input = (input.jquery ? input : $(input));
			var elem = input[0];
			var newValue = input.val();
			var range = [newValue.length, newValue.length];
			range = (input.attr('readonly') || input.attr('disabled') ? range :
				(elem.setSelectionRange /* Mozilla */ ? [elem.selectionStart, elem.selectionEnd] :
				(elem.createTextRange /* IE */ ? this._getIERange(elem) : range)));
			input.val(newValue.substr(0, range[0]) + value + newValue.substr(range[1]));
			pos = range[0] + value.length;
			if (input.is(':visible')) {
				input.focus(); // for further typing
			}
			if (elem.setSelectionRange) { // Mozilla
				if (input.is(':visible')) {
						elem.setSelectionRange(pos, pos);
				}
			}
			else if (elem.createTextRange) { // IE
				range = elem.createTextRange();
				range.move('character', pos);
				range.select();
			}
		},

		/** Get the coordinates for the selected area in the text field in IE.
			@private
			@param elem {Element} The target text field.
			@return {number[]} The start and end positions of the selection. */
		_getIERange: function(elem) {
			elem.focus();
			var selectionRange = document.selection.createRange().duplicate();
			// Use two ranges: before and selection
			var beforeRange = this._getIETextRange(elem);
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

		/** Create an IE text range for the text field.
			@private
			@param elem {Element} The target text field.
			@return {object} The corresponding text range. */
		_getIETextRange: function(elem) {
			var isInput = (elem.nodeName.toLowerCase() == 'input');
			var range = (isInput ? elem.createTextRange() : document.body.createTextRange());
			if (!isInput) {
				range.moveToElementText(elem); // Selects all the text for a textarea
			}
			return range;
		},

		/** Set the text field to the selected value, and trigger any on change event.
			@private
			@param inst {object} The instance settings.
			@param value {string} The new value for the text field. */
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

		/** Notify clients of a keypress.
			@private
			@param inst {object} The instance settings.
			@param key {string} The character pressed. */
		_notifyKeypress: function(inst, key) {
			if ($.isFunction(inst.options.onKeypress)) { // trigger custom callback
				inst.options.onKeypress.apply((inst._input ? inst._input[0] : null),
					[key, inst._input.val(), inst]);
			}
		},

		/** Generate the HTML for the current state of the keypad.
			@private
			@param inst {object} The instance settings.
			@return {jQuery} The HTML for this keypad. */
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
						html += (keyDef.action ? '<button type="button" class="' +
							this._specialClass + ' ' + this._namePrefixClass + keyDef.name +
							(inst.options.useThemeRoller ? ' ui-corner-all ui-state-default' +
							(keyDef.noHighlight ? '' : ' ui-state-highlight') : '') +
							'" title="' + inst.options[keyDef.name + 'Status'] + '">' +
							(inst.options[keyDef.name + 'Text'] || '&#160;') + '</button>' :
							'<div class="' + this._namePrefixClass + keyDef.name + '"></div>');
					}
					else {
						html += '<button type="button" class="' + this._keyClass +
							(inst.options.useThemeRoller ? ' ui-corner-all ui-state-default' : '') +
							'">' + (keys[j] == ' ' ? '&#160;' : keys[j]) + '</button>';
					}
				}
				html += '</div>';
			}
			html = $(html);
			var thisInst = inst;
			var activeClasses = this._keyDownClass +
				(inst.options.useThemeRoller ? ' ui-state-active' : '');
			html.find('button').mousedown(function() { $(this).addClass(activeClasses); }).
				mouseup(function() { $(this).removeClass(activeClasses); }).
				mouseout(function() { $(this).removeClass(activeClasses); }).
				filter('.' + this._keyClass).
				click(function() { plugin._selectValue(thisInst, $(this).text()); });
			$.each(this._specialKeys, function(i, keyDef) {
				html.find('.' + plugin._namePrefixClass + keyDef.name).click(function() {
					keyDef.action.apply(thisInst._input, [thisInst]);
				});
			});
			return html;
		},

		/** Check whether characters should be randomised, and, if so, produce the randomised layout.
			@private
			@param inst {object} The instance settings.
			@return {string[]} The layout with any requested randomisations applied. */
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

		/** Is a given character a control character?
			@private
			@param ch {string} The character to test.
			@return {boolean} True if a control character, false if not. */
		_isControl: function(ch) {
			return ch < ' ';
		},

		/** Is a given character alphabetic?
			@param ch {string} The character to test.
			@return {boolean} True if alphabetic, false if not. */
		isAlphabetic: function(ch) {
			return (ch >= 'A' && ch <= 'Z') || (ch >= 'a' && ch <= 'z');
		},

		/** Is a given character numeric?
			@param ch {string} The character to test.
			@return {boolean} True if numeric, false if not. */
		isNumeric: function(ch) {
			return (ch >= '0' && ch <= '9');
		},

		/** Convert a character to upper case.
			@param ch {string} The character to convert.
			@return {string} Its uppercase version. */
		toUpper: function(ch) {
			return ch.toUpperCase();
		},
		
		/** Randomise the contents of an array.
			@private
			@param values {string[]} The array to rearrange. */
		_shuffle: function(values) {
			for (var i = values.length - 1; i > 0; i--) {
				var j = Math.floor(Math.random() * values.length);
				var ch = values[i];
				values[i] = values[j];
				values[j] = ch;
			}
		}
	});

	var plugin = $.keypad;

	// Initialise the key definitions
	plugin.addKeyDef('CLOSE', 'close', function(inst) {
		plugin._curInst = (inst._inline ? inst : plugin._curInst);
		plugin.hide();
	});
	plugin.addKeyDef('CLEAR', 'clear', function(inst) { plugin._clearValue(inst); });
	plugin.addKeyDef('BACK', 'back', function(inst) { plugin._backValue(inst); });
	plugin.addKeyDef('SHIFT', 'shift', function(inst) { plugin._shiftKeypad(inst); });
	plugin.addKeyDef('SPACE_BAR', 'spacebar', function(inst) { plugin._selectValue(inst, ' '); }, true);
	plugin.addKeyDef('SPACE', 'space');
	plugin.addKeyDef('HALF_SPACE', 'half-space');
	plugin.addKeyDef('ENTER', 'enter', function(inst) { plugin._selectValue(inst, '\x0D'); }, true);
	plugin.addKeyDef('TAB', 'tab', function(inst) { plugin._selectValue(inst, '\x09'); }, true);

	// Initialise the layouts and settings
	plugin.numericLayout = ['123' + plugin.CLOSE, '456' + plugin.CLEAR, '789' + plugin.BACK, plugin.SPACE + '0'];
	plugin.qwertyLayout = ['!@#$%^&*()_=' + plugin.HALF_SPACE + plugin.SPACE + plugin.CLOSE,
		plugin.HALF_SPACE + '`~[]{}<>\\|/' + plugin.SPACE + '789',
		'qwertyuiop\'"' + plugin.HALF_SPACE + '456',
		plugin.HALF_SPACE + 'asdfghjkl;:' + plugin.SPACE + '123',
		plugin.SPACE + 'zxcvbnm,.?' + plugin.SPACE + plugin.HALF_SPACE + '-0+',
		'' + plugin.TAB + plugin.ENTER + plugin.SPACE_BAR + plugin.SHIFT +
		plugin.HALF_SPACE + plugin.BACK + plugin.CLEAR],
	$.extend(plugin.regionalOptions[''],
		{alphabeticLayout: plugin.qwertyAlphabetic, fullLayout: plugin.qwertyLayout,
		isAlphabetic: plugin.isAlphabetic, isNumeric: plugin.isNumeric, toUpper: plugin.toUpper});
	plugin.setDefaults($.extend({layout: plugin.numericLayout}, plugin.regionalOptions['']));

	// Add the keypad division and external click check
	$(function() {
		$(document.body).append(plugin.mainDiv).
			on('mousedown.' + pluginName, plugin._checkExternalClick);
	});

})(jQuery);
