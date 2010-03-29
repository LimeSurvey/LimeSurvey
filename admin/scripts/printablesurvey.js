// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
    $('[class^=max]').each(function(){
       var arrayOfClasses = $(this).attr('class').split(' '); 
       charcount=arrayOfClasses[0].substr(10);
       $(this).find('div.input-text').width(charcount*2+'em');
    }
    )
});
