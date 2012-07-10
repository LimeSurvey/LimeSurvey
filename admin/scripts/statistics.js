$(document).ready(function(){
     $('#usegraph').click( function(){
        if ($('#grapherror').length>0)
        { 
            $('#grapherror').show();
            $('#usegraph').attr('checked',false);
        }
     })
     $('#viewsummaryall').click( function(){
        if ($('#viewsummaryall').attr('checked')==true)
        { 
            $('#filterchoices input[type=checkbox]').attr('checked', true);
        }
        else
        {
            $('#filterchoices input[type=checkbox]').attr('checked', false);
            
        }
     })
     $('#hidefilter').click( function(){
            $('#filterchoices').hide();
            $('#filterchoice_state').val('1');
            $('#vertical_slide2').hide();               
     })
     $('#showfilter').click( function(){
            $('#filterchoices').show();
            $('#filterchoice_state').val('');
            $('#vertical_slide2').show();               
     })
     
     
     
});

function showhidefilters(value) {
 if(value == true) {
   hide('filterchoices');
 } else {
   show('filterchoices');
 }
}

function selectCheckboxes(Div, CheckBoxName, Button)
{	
	var aDiv = document.getElementById(Div);
	var nInput = aDiv.getElementsByTagName("input");
	var Value = document.getElementById(Button).checked;
	//alert(Value);
	
	for(var i = 0; i < nInput.length; i++)
	{
		if(nInput[i].getAttribute("name")==CheckBoxName)
		nInput[i].checked = Value;
	}
}
function nographs()
{
	document.getElementById('usegraph').checked = false;
}
