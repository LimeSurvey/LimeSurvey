/* http://keith-wood.name/keypad.html
   Catalan initialisation for the jQuery keypad extension
   Written by Ignasi Nogues (inogues@clickartedu.com). */
(function($) { // hide the namespace
	$.keypad.regionalOptions['ca'] = {
		buttonText: '...', buttonStatus: 'Obrir el teclat',
		closeText: 'Tancar', closeStatus: 'Tancar el teclat',
		clearText: 'Netejar', clearStatus: 'Eliminar tot el text',
		backText: 'Tornar', backStatus: 'Borrar el caràcter anterior',
		shiftText: 'Shift', shiftStatus: 'Canviar majúscules/minúscules',
		spacebarText: '&nbsp;', spacebarStatus: '',
		enterText: 'Entrar', enterStatus: '',
		tabText: '→', tabStatus: '',
		alphabeticLayout: $.keypad.qwertyAlphabetic,
		fullLayout: $.keypad.qwertyLayout,
		isAlphabetic: $.keypad.isAlphabetic,
		isNumeric: $.keypad.isNumeric,
		toUpper: $.keypad.toUpper,
		isRTL: false};
	$.keypad.setDefaults($.keypad.regionalOptions['ca']);
})(jQuery);
