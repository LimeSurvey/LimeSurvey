/* http://keith-wood.name/keypad.html
   dutch initialisation for the jQuery keypad extension
   Written by Michiel Mussies (mail{at}webcrafts.nl) November 2009. */
(function($) { // hide the namespace
    $.keypad.regional['nl'] = {
        buttonText: '...', buttonStatus: 'Open',
        closeText: 'Sluit', closeStatus: 'Sluit',
        clearText: 'Wissen', clearStatus: 'Wis alle tekens',
        backText: 'Terug', backStatus: 'Wis laatste teken',
        shiftText: 'Shift', shiftStatus: 'Activeer hoofd-/kleine letters',
        alphabeticLayout: $.keypad.qwertyAlphabetic,
        fullLayout: $.keypad.qwertyLayout,
        isAlphabetic: $.keypad.isAlphabetic,
        isNumeric: $.keypad.isNumeric,
        isRTL: false};
    $.keypad.setDefaults($.keypad.regional['nl']);
})(jQuery);
