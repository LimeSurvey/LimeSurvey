// $Id: tokens.js 8633 2010-04-25 12:57:33Z c_schmitz 
$(document).ready(function(){
    $("#bounceprocessing").change(turnoff);
    turnoff();	
    $('img#bounceprocessing').bind('click',function(){
        $("#dialog-modal").dialog({
                                    title: "Summary",
                                    modal: true,
                                    autoOpen: false,
                                    height: 200,
                                    width: 400,
                                    show: 'blind',
                                    hide: 'blind'
	                                }); 	  
 	    checkbounces(surveyid);
    }); 
    $("#filterduplicatetoken").change(function(){
        if ($("#filterduplicatetoken").attr('checked')==true)
        {
            $("#lifilterduplicatefields").slideDown(); 
        }
        else
        {
            $("#lifilterduplicatefields").slideUp(); 
        }
    }) 
	//Token checkbox toggles
	var tog=false;
	$('#tokencheckboxtoggle').click(function() {
		var selecteditems='';
	    $("input[type=checkbox]").attr("checked",!tog);
		$("input[type=checkbox]").each(function(index) {
			if($(this).attr("name") && $(this).attr("checked")) {
				selecteditems = selecteditems + "|" + $(this).attr("name");
			}
	    });
		$('#tokenboxeschecked').val(selecteditems);
		tog=!tog;
	});
	$('input[type=checkbox]').click(function() {
		var selecteditems='';
		$("input[type=checkbox]").each(function(index) {
			if($(this).attr("name") && $(this).attr("checked")) {
				selecteditems = selecteditems + "|" + $(this).attr("name");
			}
		});
		$('#tokenboxeschecked').val(selecteditems);	    
	});
});

function checkbounces(surveyid) {
$("#dialog-modal").dialog('open');
 var url = 'admin.php?action=tokens&subaction=bounceprocessing&sid='+surveyid
  $('#dialog-modal').html('<p><img style="margin-top:42px" src="../images/ajax-loader.gif" width="200" height="25" /></p>');
  $('#dialog-modal').load(url);
}

function turnoff(ui,evt)
{
  bounce_disabled=($("#bounceprocessing").val()=='N' || $("#bounceprocessing").val()=='G');
  if (bounce_disabled==true) {bounce_disabled='disabled';}
  else {bounce_disabled='';}
  $("#bounceaccounttype").attr('disabled',bounce_disabled);
  $("#bounceaccounthost").attr('disabled',bounce_disabled);
  $("#bounceaccountuser").attr('disabled',bounce_disabled);
  $("#bounceaccountpass").attr('disabled',bounce_disabled);
  $("#bounceencryption").attr('disabled',bounce_disabled);
  $("#bounceaccountencryption").attr('disabled',bounce_disabled);
}

