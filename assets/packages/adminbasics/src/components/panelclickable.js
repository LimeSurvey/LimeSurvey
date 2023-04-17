/**
 * Panel Clickable
 * Like in front page, or quick actions
 */
export default function panelClickable () {
    $(".card-clickable").on('click',function(e){
        const self = $(this);
        if(self.data('url')!=''){
        	if(self.data('target') === '_blank') {
        		window.open(self.data('url'))
            }
            else {
            	window.location.href = self.data('url');
            }
        }
    });
};
