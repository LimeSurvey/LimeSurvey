// $Id: surveytoolbar.js 9648 2011-01-07 13:06:39Z c_schmitz $
// based on TTabs from http://interface.eyecon.ro/

$(document).ready(function(){
    // Load the superfish menu 
    $('ul.sf-menu').superfish({
        speed:'fast'
    });
    //Load the special tooltips for the surveybar
    $('.surveybar img[alt]').each(function() {
        if($(this).attr('alt') != '')
        {
             $(this).qtip({
               style: { name: 'cream',
                        tip:true, 
                        color:'#1D2D45', 
                        border: {
                             width: 1,
                             radius: 5,
                             color: '#EADF95'}
                       },  
               position: { adjust: { 
                        screen: true, scroll:true },
                        corner: {
                                target: 'topRight',
                                tooltip: 'bottomLeft'}       
                        },
                show: {effect: { length:50},
                       delay:1000
                      },
                hide: { when: 'mouseout' },
                api: { onRender: function() {$(this.options.hide.when.target).bind('click', this.hide);}}

               });
        }
    });       
});
