/**
 * Panel Clickable
 * Like in front page, or quick actions
 */
export default function panelClickable () {
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
};
