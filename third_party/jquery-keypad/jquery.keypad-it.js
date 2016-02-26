/* http://keith-wood.name/keypad.html
   Italian initialisation for the jQuery keypad extension
   Written by Francesco Strappini (www.strx.it). */
(function($) { // hide the namespace
	$.keypad.regionalOptions['it'] = {
		buttonText: '...', buttonStatus: 'Visualizza Tastiera',
		closeText: 'Chiudi', closeStatus: 'Nascondi Tastiera',
		clearText: 'Pulisci', clearStatus: 'Elimina il Testo',
		backText: 'Del', backStatus: 'Elimina l\'ultimo carattere',
		shiftText: 'Shift', shiftStatus: 'Maiuscole/Minuscole',
		spacebarText: '&nbsp;', spacebarStatus: '',
		enterText: 'Enter', enterStatus: 'Ritorno a Capo',
		tabText: '→', tabStatus: '',
		alphabeticLayout: $.keypad.qwertyAlphabetic,
		fullLayout: $.keypad.qwertyLayout,
		isAlphabetic: $.keypad.isAlphabetic,
		isNumeric: $.keypad.isNumeric,
		toUpper: $.keypad.toUpper,
		isRTL: false};
	$.keypad.setDefaults($.keypad.regionalOptions['it']);
})(jQuery);
