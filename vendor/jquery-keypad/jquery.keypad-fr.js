/* http://keith-wood.name/keypad.html
   French initialisation for the jQuery keypad extension
   Written by Keith Wood (kbwood{at}iinet.com.au) August 2008. */
(function($) { // hide the namespace
	'use strict';

	function isAlphabetic(ch) {
		return ($.keypad.isAlphabetic(ch) || 'áàäãâçéèëêíìïîóòöõôúùüû'.indexOf(ch) > -1);
	}

	$.keypad.azertyAlphabetic = ['àâçéèêîôùû', 'azertyuiop', 'qsdfghjklm', 'wxcvbn'];
	$.keypad.azertyLayout = ['&~#{([_@])}' + $.keypad.HALF_SPACE + '£$€',
		'<>|`°^!?\'"\\' + $.keypad.HALF_SPACE + '/*=',
		$.keypad.HALF_SPACE + $.keypad.azertyAlphabetic[0] + $.keypad.SPACE + '789',
		$.keypad.azertyAlphabetic[1] + '%' + $.keypad.HALF_SPACE + '456',
		$.keypad.HALF_SPACE + $.keypad.azertyAlphabetic[2] + $.keypad.SPACE + '123',
		'§' + $.keypad.azertyAlphabetic[3] + ',.;:' + $.keypad.HALF_SPACE + '-0+',
		$.keypad.SHIFT + $.keypad.SPACE_BAR + $.keypad.HALF_SPACE + $.keypad.BACK + $.keypad.CLEAR + $.keypad.CLOSE];
	$.keypad.regionalOptions.fr = {
		buttonText: '...',
		buttonStatus: 'Ouvrir',
		closeText: 'Fermer',
		closeStatus: 'Fermer le pavé numérique',
		clearText: 'Effacer',
		clearStatus: 'Effacer la valeur',
		backText: 'Défaire',
		backStatus: 'Effacer la dernière touche',
		shiftText: 'Maj',
		shiftStatus: '',
		spacebarText: '&nbsp;',
		spacebarStatus: '',
		enterText: 'Enter',
		enterStatus: '',
		tabText: '→',
		tabStatus: '',
		alphabeticLayout: $.keypad.azertyAlphabetic,
		fullLayout: $.keypad.azertyLayout,
		isAlphabetic: isAlphabetic,
		isNumeric: $.keypad.isNumeric,
		toUpper: $.keypad.toUpper,
		isRTL: false
	};
	$.keypad.setDefaults($.keypad.regionalOptions.fr);

})(jQuery);
