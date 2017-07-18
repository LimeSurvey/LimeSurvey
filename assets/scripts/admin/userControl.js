$(document).on('ready pjax:completed', function() {
$( "#tabs" ).tabs();
});
$(document).on('pjax:completed',function() {
$( "#tabs" ).tabs();
});