$(document).ready(function(){
    function addtoCPDB(token_id) 
    {
                  $("#addcpdb").load(postUrl, {
                  participantid:token_id},function(){
                  $(location).attr('href',attMapUrl+'/'+survey_id);
                  });
                  
               
            //$("#processing").load(addtocpdbUrl, { tokenid:token_id,surveyid:survey_id,attributeid:survey_id  }, function(msg){alert(msg);
            //$(this).dialog("close");
	        //});
    }
$('#addtocpdb').click(function()
    {
        var dialog_buttons={};
        var token = [];
        $(":checked").each(function() {
        token.push($(this).attr('name'));
        });
        if(token.length==0)
        {		/* build an array containing the various button functions */
                /* Needed because it's the only way to label a button with a variable */
            
            dialog_buttons[okBtn]=function(){
	        $( this ).dialog( "close" );
            };
            /* End of building array for button functions */
            $('#norowselected').dialog({
	        modal: true,
	        buttons: dialog_buttons
            });
	    }
        else
        {
           addtoCPDB(token);
        }    
    }); 
});