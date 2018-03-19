/**
 * Survey list Widget, Ajax Reloader
 *
 * This JavaScript will reload the grid on page size change
 */

$(document).on('ready  pjax:scriptcomplete', function() {
    jQuery(function($)
    {
        jQuery(document).on("change", '#pageSize', function()
        {
            $('#survey-grid').yiiGridView('update',{ data:{ pageSize: $(this).val() }});
        });
    });
});
