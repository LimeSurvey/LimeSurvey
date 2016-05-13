$(document).ready(function() {
    $elPositionInput = $('#question_position_container');
    $url             = $elPositionInput.data('url');
    $datas           = 'gid='+$elPositionInput.data('gid');
    $.ajax({
        type: "GET",
        url: $url,
        data: $datas,
        success: function(html) {

            $elPositionInput.after(html);                                  // We insert the HTML after
        },
        error :  function(html, statut){
            console.log('position widget, ajax error: ');
            console.log(html);
            console.log($url)
            alert(html);
        }
    });
});
