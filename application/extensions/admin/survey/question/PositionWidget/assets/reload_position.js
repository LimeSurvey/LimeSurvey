/**
 * Position Widget, Ajax Reloader
 *
 * This JavaScript will load the position widget on page load, and reload it at each change of the group selector set in question_position_container
 */

/**
 * Function to load a Position Widget
 */
function loadPositionWidget()
{
    $elPositionInput = $('#question_position_container');                       // The hidden input containing the necessary datas for the widget
    $url             = $elPositionInput.data('url');                            // The url to call via Ajax to get the Widget Html
    $gid             = $elPositionInput.data('gid');                            // The question group to load
    $classes         = $elPositionInput.data('classes');

    $datas           = 'gid='+$gid;

    if ($classes!='')
    {
        $datas      += '&classes='+$classes+''
    }

    $.ajax({
        type: "GET",
        url: $url,
        data: $datas,
        success: function(html) {
            $elPositionInput.after(html);                                       // Insert the HTML after the hidden input
        },
        error :  function(html, statut){
            console.log('position widget, ajax error: ');
            console.log(html);
            console.log($url);
        }
    });
}

$(document).on('ready  pjax:scriptcomplete', function() {
    // First, we load the position widget
    loadPositionWidget();

    $elPositionInput = $('#question_position_container');                       // The hidden input
    $elGroupSelector = $('#'+$elPositionInput.data('group-selector-id'));       // The Group Selector

    // If the group selector changes, we reload the widget
    $elGroupSelector.on('change', function(){
        $('#PositionWidget').remove();                                          // Remove the previous selector
        $gid = $(this).val();                                                   // Get the new requested gid from Group Selector
        $elPositionInput.data('gid', $gid);                                     // Set the new gid data in hidden input
        loadPositionWidget();                                                   // load position widget
    });
});
