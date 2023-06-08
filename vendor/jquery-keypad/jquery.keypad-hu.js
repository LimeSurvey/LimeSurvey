/* http://keith-wood.name/keypad.html
   Hungarian localisation for the jQuery keypad extension
   Written by Uwe Jakobs(u.jakobs{at}imageco.de) September 2009. */
(function($) { // hide the namespace
	'use strict';

	function isAlphabetic(ch) {
		return ($.keypad.isAlphabetic(ch) || 'áéíőúű'.indexOf(ch) > -1);
	}

	$.keypad.qwertzAlphabetic = ['qwertzuiopőú', 'asdfghjkléáű', 'íyxcvbnm'];
	$.keypad.qwertzLayout = 
		['!"§$%&/()=?`' + $.keypad.BACK + $.keypad.HALF_SPACE + '$£€/',
		'<>°^@{[]}\\~´;:' + $.keypad.HALF_SPACE + '789*',
		$.keypad.qwertzAlphabetic[0] + '+*' + $.keypad.HALF_SPACE + '456-',
		$.keypad.HALF_SPACE + $.keypad.qwertzAlphabetic[1] + '#\'' + $.keypad.SPACE + '123+',
		'|' + $.keypad.qwertzAlphabetic[2] + 'µ,.-_' + $.keypad.SPACE + $.keypad.HALF_SPACE +'.0,=', 
		$.keypad.SHIFT + $.keypad.SPACE + $.keypad.SPACE_BAR + $.keypad.SPACE + $.keypad.SPACE + $.keypad.SPACE +
		$.keypad.CLEAR + $.keypad.SPACE + $.keypad.SPACE + $.keypad.HALF_SPACE + $.keypad.CLOSE];
	$.keypad.regionalOptions.hu = {
		buttonText: '...',
		buttonStatus: 'Megnyit',
		closeText: 'Bezár',
		closeStatus: 'Bezár',
		clearText: 'Töröl',
		clearStatus: 'Teljes szöveg törlés',
		backText: 'Vissza',
		backStatus: 'Utolsó karakter törlése',
		shiftText: 'Nagybetű',
		shiftStatus: 'Kis- és nagybetűs üzemmód váltása',
		spacebarText: '&nbsp;',
		spacebarStatus: '',
		enterText: 'Enter',
		enterStatus: '',
		tabText: '→',
		tabStatus: '',
		alphabeticLayout: $.keypad.qwertzAlphabetic,
		fullLayout: $.keypad.qwertzLayout,
		isAlphabetic: isAlphabetic,
		isNumeric: $.keypad.isNumeric,
		toUpper: $.keypad.toUpper,
		isRTL: false
	};
	$.keypad.setDefaults($.keypad.regionalOptions.hu);

})(jQuery);
