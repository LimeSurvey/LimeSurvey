/* http://keith-wood.name/keypad.html
   dutch initialisation for the jQuery keypad extension
   Written by Michiel Mussies (mail{at}webcrafts.nl) November 2009. */
(function($) { // hide the namespace
    $.keypad.regionalOptions['nl'] = {
        buttonText: '...', buttonStatus: 'Open',
        closeText: 'Sluit', closeStatus: 'Sluit',
        clearText: 'Wissen', clearStatus: 'Wis alle tekens',
        backText: 'Terug', backStatus: 'Wis laatste teken',
        shiftText: 'Shift', shiftStatus: 'Activeer hoofd-/kleine letters',
		spacebarText: '&nbsp;', spacebarStatus: '',
		enterText: 'Enter', enterStatus: '',
		tabText: '→', tabStatus: '',
        alphabeticLayout: $.keypad.qwertyAlphabetic,
        fullLayout: $.keypad.qwertyLayout,
        isAlphabetic: $.keypad.isAlphabetic,
        isNumeric: $.keypad.isNumeric,
		toUpper: $.keypad.toUpper,
        isRTL: false};
    $.keypad.setDefaults($.keypad.regionalOptions['nl']);
})(jQuery);
