/* Manage javascript for expression administration
 * @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
 */
$(document).ready(function(){
    // Tooltip inside em-var
    $(".em-expression").tooltip();
});
$(document).on('click','.em-expression a',function(e){
    e.preventDefault();
    window.open($(this).attr('href'));
});
