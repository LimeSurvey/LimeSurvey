// This file will auto convert slider divs to sliders
$(document).ready(function(){
	// call the init slider routine for each element of the .multinum-slider class
	$(".popupdate").each(function(i,e) { 
        var basename = e.id.substr(6);         
        format=$('#dateformat'+basename).val();
        language=$('#datelanguage'+basename).val();
        $(e).datepicker({ dateFormat: format,  
                          showOn: 'both',
                          changeYear: true, 
                          changeMonth: true, 
                          duration: 'fast',
                        }, $.datepicker.regional[language]);
    });
});
