/**
 * Panel Clickable 
 * Like in front page, or quick actions
 */
$(document).ready(function(){
	$(".panel-clickable").click(function(){
        $that = $(this);
        if($that.attr('aria-data-url')!=''){
            window.location.href = $that.attr('aria-data-url');
        }
    });

});