
$(document).ready(function(){
   $('#pppanel').click(function() {
       $(location).attr('href',redUrl);
       
   }); 
});
$(document).on('pjax:completed',(function(){
   $('#pppanel').click(function() {
       $(location).attr('href',redUrl);
       
   }); 
});