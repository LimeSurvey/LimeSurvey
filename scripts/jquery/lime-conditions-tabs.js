$(document).ready(function(){
	$('#conditiontarget > ul').tabs({
		fx: {
			opacity: 'toggle'
		},
		select:  
			function(event, ui) {
				//alert(ui.panel.id);
				// on select, empty the other tab input element
				if (ui.panel.id == 'CANSWERSTAB')
				{
					$('#ValOrRegEx').val('');
				}
				else if (ui.panel.id == 'CONST_RGX')
				{
					$('#canswers option').each(function(i){$(this).attr("selected", "");});
				}
				return true;
			
		}
	});
});


