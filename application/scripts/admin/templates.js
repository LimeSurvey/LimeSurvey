// $Id: templates.js 9680 2011-01-13 19:26:43Z shnoulle $
// based on TTabs from http://interface.eyecon.ro/

$(document).ready(function(){
    if($("#changes").length > 0) {
        editAreaLoader.init({
            language: adminlanguage,
            id : "changes"        // textarea id
            ,syntax: highlighter            // syntax to be uses for highgliting
            ,font_size: 8
            ,allow_toggle: false
            ,word_wrap: true
            ,start_highlight: true        // to display with highlight mode on start-up
        });
    }
    $('#iphone').click(function(){
      $('#previewiframe').css("width", "320px");
      $('#previewiframe').css("height", "396px");
    });
    $('#x640').click(function(){
      $('#previewiframe').css("width", "640px");
      $('#previewiframe').css("height", "480px");
    });
    $('#x800').click(function(){
      $('#previewiframe').css("width", "800px");
      $('#previewiframe').css("height", "600px");
    });
    $('#x1024').click(function(){
      $('#previewiframe').css("width", "1024px");
      $('#previewiframe').css("height", "768px");
    });
    $('#full').click(function(){
      $('#previewiframe').css("width", "95%");
      $('#previewiframe').css("height", "768px");
    });
});
