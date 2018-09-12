/**
 * Panel Clickable
 * Like in front page, or quick actions
 */
$(document).ready(function(){
    $(".panel-clickable").click(function(){
        $that = $(this);
        if($that.data('url')!=''){
        	if($that.data('target') === '_blank') {
        		window.open($that.data('url'))
            }
            else {
            	window.location.href = $that.data('url');
            }
        }
    });
});
