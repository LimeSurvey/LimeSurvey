/* http://keith-wood.name/keypad.html
   Polish initialisation for the jQuery keypad extension
   Written by Grzegorz Gębczyński February 2014. */
(function($) { // hide the namespace
	'use strict';
	$.keypad.regionalOptions.pl = {
		buttonText: '...',
		buttonStatus: 'Pokaż klawiaturę',
		closeText: 'Zamknij',
		closeStatus: 'Zamknij klawiaturę',
		clearText: 'Czyść',
		clearStatus: 'Usuń cały tekst',
		backText: 'Del',
		backStatus: 'Usuń poprzedni znak',
		shiftText: 'Shift',
		shiftStatus: 'Duże litery / Małe litery',
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
		isRTL: false
	};
	$.keypad.setDefaults($.keypad.regionalOptions.pl);
})(jQuery);
