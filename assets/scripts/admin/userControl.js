$(document).ready(function() {
$( "#tabs" ).tabs();
});
$(document).on('pjax:end',function() {
$( "#tabs" ).tabs();
});