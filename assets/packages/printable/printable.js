// $Id: printablesurvey.js 8633 2010-04-25 12:57:33Z c_schmitz $
// Namespace
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready pjax:scriptcomplete', function(){
    $('[class^=max]').each(function(){
       var arrayOfClasses = $(this).attr('class').split(' '); 
       charcount=arrayOfClasses[0].substr(10);
       $(this).find('div.input-text').width(charcount*2+'em');
    }
    )
});
