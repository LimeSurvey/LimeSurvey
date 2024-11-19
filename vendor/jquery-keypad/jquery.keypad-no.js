/* http://keith-wood.name/keypad.html
   Norwegian initialisation for the jQuery keypad extension
   Written by Tom egil Stanghelle (post@stanghelle.net). */
(function($) { // hide the namespace
	'use strict';
	$.keypad.regionalOptions.no = {
		buttonText: '...',
		buttonStatus: 'Åpne tastaturet',
		closeText: 'Lukk',
		closeStatus: 'Lukke tastaturet',
		clearText: 'Fjerne',
		clearStatus: 'Slett all tekst',
		backText: 'Tilbake',
		backStatus: 'Slett forrige tegn',
		shiftText: 'Shift',
		shiftStatus: 'Endre store / små bokstaver',
		spacebarText: '&nbsp;',
		spacebarStatus: '',
		enterText: 'Enter',
		enterStatus: '',
		tabText: '→',
		tabStatus: '',
		alphabeticLayout: $.keypad.qwertyAlphabetic,
		fullLayout: $.keypad.qwertyLayout,
		isAlphabetic: $.keypad.isAlphabetic,
		isNumeric: $.keypad.isNumeric,
		toUpper: $.keypad.toUpper,
		isRTL: false	};
	$.keypad.setDefaults($.keypad.regionalOptions.no);
})(jQuery);
