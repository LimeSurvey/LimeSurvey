/**
 * Survey list Widget, Ajax Reloader
 *
 * This JavaScript will reload the grid on page size change
 */

$(document).ready(function() {
    jQuery(function($)
    {
        jQuery(document).on("change", '#pageSize', function()
        {
            $.fn.yiiGridView.update('survey-grid',{ data:{ pageSize: $(this).val() }});
        });
    });
});
