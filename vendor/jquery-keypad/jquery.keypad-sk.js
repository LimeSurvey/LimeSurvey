/* http://keith-wood.name/keypad.html
   Slovak initialisation for the jQuery keypad extension
   Written by Peter Čáni - inac.sk. */
(function($) { // hide the namespace
	'use strict';
	$.keypad.regionalOptions.sk = {
		buttonText: '...',
		buttonStatus: 'Otvoriť',
		closeText: 'Zatvoriť',
		closeStatus: 'Zatvoriť klávesnicu',
		clearText: 'Vymazať',
		clearStatus: 'Vymazať text',
		backText: 'Vymazať',
		backStatus: 'Vymazať posledné písmeno',
		shiftText: 'Veľkosť',
		shiftStatus: 'Nastaví veľké/malé písmená',
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
	$.keypad.setDefaults($.keypad.regionalOptions.sk);
})(jQuery);
