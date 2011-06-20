/* http://keith-wood.name/keypad.html
   German localisation for the jQuery keypad extension
   Written by Uwe Jakobs(u.jakobs{at}imageco.de) September 2009. */
(function($) { // hide the namespace

$.keypad.qwertzAlphabetic = ['qwertzuiopüß', 'asdfghjklöä', 'yxcvbnm'];
$.keypad.qwertzLayout = 
	['!"§$%&/()=?`' + $.keypad.BACK + $.keypad.HALF_SPACE + '$£/',
	'<>°^@{[]}\\~´;:' + $.keypad.HALF_SPACE + '789*',
	$.keypad.qwertzAlphabetic[0] + '+*' +
	$.keypad.HALF_SPACE + '456-',
	$.keypad.HALF_SPACE + $.keypad.qwertzAlphabetic[1] +
	'#\'' + $.keypad.SPACE + '123+',
	'|' + $.keypad.qwertzAlphabetic[2] + 'µ,.-_' +
	$.keypad.SPACE + $.keypad.HALF_SPACE +'.0,=', 
	$.keypad.SHIFT + $.keypad.SPACE + $.keypad.SPACE_BAR +
	$.keypad.SPACE + $.keypad.SPACE + $.keypad.SPACE + $.keypad.CLEAR +
	$.keypad.SPACE + $.keypad.SPACE + $.keypad.HALF_SPACE + $.keypad.CLOSE];
$.keypad.regional['de'] = {
	buttonText: '...', buttonStatus: 'Öffnen',
	closeText: 'schließen', closeStatus: 'schließen',
	clearText: 'löschen', clearStatus: 'Gesamten Inhalt löschen',
	backText: 'zurück', backStatus: 'Letzte Eingabe löschen',
	shiftText: 'umschalten', shiftStatus: 'Zwischen Groß- und Kleinschreibung wechseln',
	alphabeticLayout: $.keypad.qwertzAlphabetic,
	fullLayout: $.keypad.qwertzLayout,
	isAlphabetic: $.keypad.isAlphabetic,
	isNumeric: $.keypad.isNumeric,
	isRTL: false};
$.keypad.setDefaults($.keypad.regional['de']);

})(jQuery);
