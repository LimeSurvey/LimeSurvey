
$(document).ready(function(){
   $('#pppanel').click(function() {
       $(location).attr('href',redUrl);
       
   }); 
});
$(document).on('pjax:end',(function(){
   $('#pppanel').click(function() {
       $(location).attr('href',redUrl);
       
   }); 
});