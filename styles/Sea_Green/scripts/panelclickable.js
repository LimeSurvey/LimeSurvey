/**
 * Panel Clickable
 * Like in front page, or quick actions
 */
$(document).ready(function(){
    $(".panel-clickable").click(function(){
        $that = $(this);
        if($that.data('url')!=''){
            window.location.href = $that.data('url');
        }
    });
});
