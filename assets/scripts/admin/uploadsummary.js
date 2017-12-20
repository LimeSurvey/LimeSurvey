

$(document).on('ready pjax:scriptcomplete',(function(){
   $('#pppanel').click(function() {
       $(location).attr('href',redUrl);
       
   }); 
});