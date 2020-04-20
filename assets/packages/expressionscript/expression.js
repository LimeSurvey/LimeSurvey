/* Manage javascript for expression administration
 * @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
 */
$(document).on('ready  pjax:scriptcomplete', function(){
    // Tooltip inside em-var
    $(".em-expression").tooltip({
        placement : 'bottom',
        template : '<div class="tooltip expression-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    });
    $(".em-expression *").tooltip({
        placement : 'top',
        template : '<div class="tooltip expression-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    });
});
