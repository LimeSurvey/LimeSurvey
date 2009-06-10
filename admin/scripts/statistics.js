$(document).ready(function(){
     $('#filterinc').change(function(){
         if ($('#filterinc').val()=="filter") {
            $('#noncompleted').attr("checked", "");
             $('#vertical_slide').slideUp('normal'); 
         }
         else
         {
             $('#vertical_slide').slideDown('normal'); 
         }
     })
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
            $('#filterchoices').hide();
        }
        else
        {
            $('#filterchoices').show();
            
        }
     })
     $('#hidefilter').click( function(){
            $('#filtersettings').slideUp();
     })
     $('#showfilter').click( function(){
            $('#filtersettings').slideDown();
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

