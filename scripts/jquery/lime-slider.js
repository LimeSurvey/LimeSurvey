// This file will auto convert slider divs to sliders
$(document).ready(function(){
	// call the init slider routine for each element of the .multinum-slider class
	$(".multinum-slider").each(function(i,e) {
		var basename = e.id.substr(10);

		//$("#slider-"+basename).addClass('ui-slider-2');
		//$("#slider-handle-"+basename).addClass('ui-slider-handle2');
		var slider_divisor = $('#slider-param-divisor-' + basename).attr('value');
		var slider_min = $('#slider-param-min-' + basename).attr('value');
		var slider_max = $('#slider-param-max-' + basename).attr('value');
		var slider_stepping = $('#slider-param-stepping-' + basename).attr('value');
		var slider_startvalue = $('#slider-param-startvalue-' + basename).attr('value');
		var slider_onchange = $('#slider-onchange-js-' + basename).attr('value');
		var slider_prefix = $('#slider-prefix-' + basename).attr('value');
		var slider_suffix = $('#slider-suffix-' + basename).attr('value');
		var sliderparams = Array();

		sliderparams['min'] = slider_min;
		sliderparams['max'] = slider_max;
		// not using the stepping param because it is not smooth
		// using Math.round workaround instead
		//sliderparams['stepping'] = slider_stepping;
		//sliderparams['animate'] = true;
		if (slider_startvalue != 'NULL')
		{
			sliderparams['startValue']= slider_startvalue;
		}
		sliderparams['slide'] = function(e, ui) {
				//var thevalue = ui.value / slider_divisor;
				var thevalue = slider_stepping * Math.round(ui.value / slider_stepping) / slider_divisor;
				$('#slider-callout-'+basename).css('left', $(ui.handle).css('left')).text(slider_prefix + thevalue + slider_suffix);
			};
		sliderparams['stop'] = function(e, ui) {
				//var thevalue = ui.value / slider_divisor;
				var thevalue = slider_stepping * Math.round(ui.value / slider_stepping) / slider_divisor;
				$('#slider-callout-'+basename).css('left', $(ui.handle).css('left')).text(slider_prefix + thevalue + slider_suffix);
			};

		sliderparams['change'] = function(e, ui) {
				//var thevalue = ui.value / slider_divisor;
				var thevalue = slider_stepping * Math.round(ui.value / slider_stepping) / slider_divisor;
				$('#answer'+basename).val(thevalue);
				checkconditions( thevalue,'#answer'+basename,'text');
				eval(slider_onchange);	
			};


		$('#slider-'+basename).slider(sliderparams);

		
		if (slider_startvalue != 'NULL')
		{
				var thevalue = $('#slider-'+basename).slider('value') / slider_divisor;
				$('#slider-callout-'+basename).css('left', $('#slider-handle-'+basename).css('left')).text(slider_prefix + thevalue + slider_suffix);
		}
	})
});
