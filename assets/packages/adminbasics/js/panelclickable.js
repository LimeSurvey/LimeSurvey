/**
 * Panel Clickable
 * Like in front page, or quick actions
 */
$(document).on('ready pjax:scriptcomplete',function(){
    $(".panel-clickable").on('click',function(e){
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
