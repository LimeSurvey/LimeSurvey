/* http://keith-wood.name/keypad.html
   Czech initialisation for the jQuery keypad extension
   Written by Amadeo Mareš. */
(function($) { // hide the namespace
    $.keypad.regionalOptions['cs'] = {
        buttonText: '...', buttonStatus: 'Otevřít',
        closeText: 'Zavřít', closeStatus: 'Zavře klávesnici',
        clearText: 'Vymazat', clearStatus: 'Smaže text',
        backText: 'Smazat', backStatus: 'Smaže poslední písmeno',
        shiftText: 'Velikost', shiftStatus: 'Nastaví velká/malá písmena',
		spacebarText: '&nbsp;', spacebarStatus: '',
		enterText: 'Enter', enterStatus: '',
		tabText: '→', tabStatus: '',
        alphabeticLayout: $.keypad.qwertyAlphabetic,
        fullLayout: $.keypad.qwertyLayout,
        isAlphabetic: $.keypad.isAlphabetic,
        isNumeric: $.keypad.isNumeric,
		toUpper: $.keypad.toUpper,
        isRTL: false};
    $.keypad.setDefaults($.keypad.regionalOptions['cs']);
})(jQuery);
