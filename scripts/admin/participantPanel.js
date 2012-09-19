$(document).ready(function() {
    jQuery.download = function(url, data, method){
	//url and data options required
	if( url && data ){ 
		//data can be string of parameters or array/object
		data = typeof data == 'string' ? data : jQuery.param(data);
		//split params into form inputs
		var inputs = '';
		jQuery.each(data.split('&'), function(){ 
			var pair = this.split('=');
			inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'" />'; 
		});
		//send request
		jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
		.appendTo('body').submit().remove();
	};
};
$('#export').click(function(){
                    var dialog_buttons={};
                    dialog_buttons[okBtn]=function(){
                    $(this).dialog("close");};
                
            $.post(exporttocsvcountall,
                            function(data) {
                                  count = data;
                                  if(count == 0)
                                  {
                                      $('#exportcsvallnorow').dialog({
                                            modal: true,
                                            title: error,
                                            buttons: dialog_buttons,
                                            width : 300,
                                            height : 160
                                        });
                 
                                     /* End of building array for button functions */
                                   }
                                   else
                                   {
                                         $('#exportcsvallprocessing').dialog({
                                            modal: true,
                                            title: count,
                                            buttons: dialog_buttons,
                                            width : 300,
                                            height : 160
                                        });

                                       $.download(exporttocsvall,'searchcondition=dummy',$('#exportcsvallprocessing').dialog("close"));
                                   }
                                  
                      
                     
                 });
        });
})