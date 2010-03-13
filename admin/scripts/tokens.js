// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
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
				/* alert(index + ': '+$(this).attr("name")); */
				$('#tokenboxeschecked').val(selecteditems);
				/* alert(selecteditems); */
			}
		});
		tog=!tog;
	});
	$('input[type=checkbox]').click(function() {
		var selecteditems='';
		$("input[type=checkbox]").each(function(index) {
			if($(this).attr("name") && $(this).attr("checked")) {
				selecteditems = selecteditems + "|" + $(this).attr("name");
				/* alert(index + ': '+$(this).attr("name")); */
				$('#tokenboxeschecked').val(selecteditems);
				/* alert(selecteditems); */
			}
		});	    
	});
});
