
$(document).on('ready pjax:complete',function(){
   $('#pppanel').click(function() {
       $(location).attr('href',redUrl);
       
   }); 
});
$(document).on('pjax:complete',(function(){
   $('#pppanel').click(function() {
       $(location).attr('href',redUrl);
       
   }); 
});