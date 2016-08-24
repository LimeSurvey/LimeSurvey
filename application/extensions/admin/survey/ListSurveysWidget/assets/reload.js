/**
 * Position Widget, Ajax Reloader
 *
 * This JavaScript will load the position widget on page load, and reload it at each change of the group selector set in question_position_container
 */

$(document).ready(function() {
    jQuery(function($)
    {
        jQuery(document).on("change", '#pageSize', function()
        {
            console.log("page size changed");
            console.log($(this).val());
            $.fn.yiiGridView.update('survey-grid',{ data:{ pageSize: $(this).val() }});
        });
    });
});
