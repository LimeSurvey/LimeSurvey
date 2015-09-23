$(document).ready(function() {
    // Code for AJAX download
    jQuery.download = function(url, data, method){
        //url and data options required
        if( url && data ){
            //data can be string of parameters or array/object
            data = typeof data == 'string' ? data : jQuery.param(data);
            //split params into form inputs
            var inputs = '<input type="hidden" name="YII_CSRF_TOKEN" value="'+LS.data.csrfToken+'">';
            jQuery.each(data.split('&'), function(){
                var pair = this.split('=');
                inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'">';
            });
            //send request
            jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
            .appendTo('body').submit().remove();
        };
    };


    $('#export').click(function(){
        var dialog_buttons={};
        dialog_buttons[exportBtn]=function(){
            $.download(exportToCSVURL,{ attributes: $('#attributes').val().join(' ') },"POST");
            $(this).dialog("close");
        };            
        dialog_buttons[cancelBtn]=function(){
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
                }
                else
                {
                    $('#exportcsv').dialog({
                        modal: true,
                        title: count,
                        buttons: dialog_buttons,
                        width : 600,
                        height : 300,
                        open: function(event, ui) {
                            $('#attributes').multiselect({ includeSelectAllOption:true, 
                                                           selectAllValue: '0',
                                                           selectAllText: sSelectAllText,
                                                           nonSelectedText: sNonSelectedText,
                                                           nSelectedText: sNSelectedText,
                                                           maxHeight: 140 });
                        }
                    });

                    /* $.download(exporttocsvall,'searchcondition=dummy',$('#exportcsvallprocessing').dialog("close"));*/
                }
        });
    });
});
