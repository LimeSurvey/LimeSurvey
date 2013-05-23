/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Change multi numeric question type to slider question type
 *
 * @author Denis Chenu (Shnoulle)
 * @param {number} qId The qid of the question where apply.
 */
function doNumericSlider(qID,jsonOptions) {
	//console.log(jsonDatas);
	var slider_list=$("#question"+qID+" .slider-list");
	var havevalue,startvalue;
	if(slider_list)
	{
		$("#question"+qID+" .em_value_range").remove();
		$("#question"+qID+" .tip.default").remove();
		var htmlSlider="<div id='container-myfname' class='multinum-slider'>\n"
			+ "<div id='slider-myfname' class='ui-slider-1'>\n"
			+ ((jsonOptions.slider_showminmax==1)? "<div id='slider-left-myfname' class='slider_showmin slider-showmin'>"+jsonOptions.slider_mintext+"</div>\n" : "")
			+ "<div id='slider-callout-myfname' class='slider_callout slider-callout'></div>\n"
			+ "<div id='slider-handle-myfname' class='ui-slider-handle'></div>\n"
			+ ((jsonOptions.slider_showminmax==1)? "<div id='slider-right-myfname' class='slider_showmax slider-showmax'>"+jsonOptions.slider_maxtext+"</div>\n" : "")
			+ "</div>\n"
			+ "</div>\n";
		var htmlSliderResest=((jsonOptions.slider_reset==1)? "<a id='slider-reset-myfname' class='slider-reset' title='"+jsonOptions.lang.reset+"'>"+jsonOptions.lang.reset+"</a>\n" : "");
		$("#question"+qID+" .slider-list").children('.answer-item').each(function(){
			var thisinput=$(this).children(".input").children('input.text');
			var myfname=$(thisinput).attr('name');
			var actualval=$(thisinput).attr('value');
			if(actualval!=""){
				havevalue==true;
				startvalue=actualval;
			}else{
				havevalue==false;
				if(jsonOptions.slider_startvalue=="NULL"){
					startvalue=false;
				}else{
					startvalue=jsonOptions.slider_startvalue;
				}
			}
			$(this).children(".input").hide();
			$(htmlSlider.replace(/myfname/g,myfname)).insertAfter($(this).children(".input"));
			$(htmlSliderResest.replace(/myfname/g,myfname)).appendTo($(this));
			$("#container-"+myfname).slider({
				value:startvalue,
				min: jsonOptions.slider_min,
				max: jsonOptions.slider_max,
				step: jsonOptions.slider_step,
				create: function() {
					$('#slider-callout-'+myfname).appendTo($('#container-'+myfname+' .ui-slider-handle').get(0));
				},
				slide: function( event, ui ) {
					$(thisinput).val(ui.value);
					$(thisinput).triggerHandler("keyup");
					$('#slider-callout-'+myfname).text(jsonOptions.slider_prefix + ui.value + jsonOptions.slider_suffix);
				}
			});
			if(!havevalue && startvalue && jsonOptions.slider_displaycallout){
				$("#slider-callout-"+myfname).text(jsonOptions.slider_prefix + startvalue + jsonOptions.slider_suffix);
				$(thisinput).val(startvalue);
				$(function() {
					$(thisinput).triggerHandler("keyup");
				});
			}
			$(this).on("click",".slider-reset",function(){
				if(jsonOptions.slider_startvalue=="NULL"){
					$( "#container-"+myfname ).slider( "option", "value", "" );
				}else{
					$( "#container-"+myfname ).slider( "option", "value", jsonOptions.slider_startvalue );
				}
				if(jsonOptions.slider_displaycallout && jsonOptions.slider_startvalue!="NULL"){
					$('#slider-callout-'+myfname).text(jsonOptions.slider_prefix + jsonOptions.slider_startvalue + jsonOptions.slider_suffix);
					$(thisinput).val(jsonOptions.slider_startvalue);
				}else{
					$('#slider-callout-'+myfname).text("");
					$(thisinput).val("");
				}
				$(thisinput).triggerHandler("keyup");
			});
		});
	}

}

