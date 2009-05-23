// $Id$
// based on TTabs from http://interface.eyecon.ro/

$(document).ready(function(){
    editAreaLoader.init({
        language: adminlanguage,
        id : "changes"        // textarea id
        ,syntax: highlighter            // syntax to be uses for highgliting
        ,font_size: 8
        ,allow_toggle: false
        ,word_wrap: true
        ,start_highlight: true        // to display with highlight mode on start-up
    });
});
