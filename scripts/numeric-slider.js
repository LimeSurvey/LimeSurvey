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

    var slider_list=$("#question"+qID+" .slider-list");
    var havevalue,startvalue;
    if(slider_list)
    {
        // Remove not needed tips
        $("#question"+qID+" .em_value_range").remove();
        // Construction of HTML
        var htmlSlider="<div id='container-myfname' class='multinum-slider'>\n"
            + "<div id='slider-myfname' class='ui-slider-1'>\n"
            + ((jsonOptions.slider_showminmax==1)? "<div id='slider-left-myfname' class='slider_showmin slider-showmin'>"+jsonOptions.slider_mintext+"</div>\n" : "")
            + "<div id='slider-callout-myfname' class='slider_callout slider-callout'></div>\n"
            + "<div id='slider-handle-myfname' class='ui-slider-handle'></div>\n"
            + ((jsonOptions.slider_showminmax==1)? "<div id='slider-right-myfname' class='slider_showmax slider-showmax'>"+jsonOptions.slider_maxtext+"</div>\n" : "")
            + "</div>\n"
            + "</div>\n";
        var htmlSliderResest=((jsonOptions.slider_reset==1)? "<a id='slider-reset-myfname' class='slider-reset' title='"+jsonOptions.lang.reset+"'>"+jsonOptions.lang.reset+"</a>\n" : "");
        // Replace each input by the slider
        $("#question"+qID).find('.slider-list').find('.answer-item').each(function(){
            var thisinput=$(this).find(".input").find('input.text').first();
            var myfname=$(thisinput).attr('name');
            if($(thisinput).attr('value'))
            {
                var actualval=$(thisinput).attr('value').replace(LSvar.sLEMradix,".");
            }
            else
            {
                var actualval=0;
            }

            var havevalue=false;
            var startvalue=false;
            if(actualval!=""){
                havevalue=true;
                startvalue=actualval;
            }else if(jsonOptions.slider_startvalue!="NULL"){
                startvalue=parseFloat(jsonOptions.slider_startvalue);
            }
            $(this).find(".input").hide();
            $(htmlSlider.replace(/myfname/g,myfname)).insertAfter($(this).find(".input"));
            $(htmlSliderResest.replace(/myfname/g,myfname)).appendTo($(this));
            // Launch slider (http://api.jqueryui.com/slider/)
            $("#container-"+myfname).slider({
                value:startvalue,
                min: parseFloat(jsonOptions.slider_min),
                max: parseFloat(jsonOptions.slider_max),
                step: parseFloat(jsonOptions.slider_step),
                create: function() {
                    $('#slider-callout-'+myfname).appendTo($('#container-'+myfname+' .ui-slider-handle').get(0));
                },
                slide: function( event, ui ) {
                    displayvalue=''+ui.value;
                    displayvalue=displayvalue.replace(/\./,LSvar.sLEMradix);
                    $(thisinput).val(displayvalue);
                    $(thisinput).triggerHandler("keyup");
                    $('#slider-callout-'+myfname).text(jsonOptions.slider_prefix + displayvalue + jsonOptions.slider_suffix);
                }
            });
            // Update the value of the input if Slider start is set
            if(havevalue || ( startvalue && jsonOptions.slider_displaycallout)){
                startvalue=''+startvalue;
                startvalue=startvalue.replace(/\./,LSvar.sLEMradix);
                $("#slider-callout-"+myfname).text(jsonOptions.slider_prefix + startvalue + jsonOptions.slider_suffix);
                $(thisinput).val(startvalue);
                $(thisinput).triggerHandler("keyup"); // Needed for EM
            }
            // Reset on click on .slider-reset
            $(this).on("click",".slider-reset",function(){
                if(jsonOptions.slider_startvalue=="NULL"){
                    $( "#container-"+myfname ).slider( "option", "value", "" );
                }else{
                    $( "#container-"+myfname ).slider( "option", "value", jsonOptions.slider_startvalue );
                }
                if(jsonOptions.slider_displaycallout && jsonOptions.slider_startvalue!="NULL"){
                    $('#slider-callout-'+myfname).text(jsonOptions.slider_prefix + jsonOptions.slider_startvalue.replace(/\./,LSvar.sLEMradix) + jsonOptions.slider_suffix);
                    $(thisinput).val(jsonOptions.slider_startvalue);
                }else{
                    $('#slider-callout-'+myfname).text("");
                    $(thisinput).val("");
                }
                $(thisinput).triggerHandler("keyup"); // Needed for EM
            });
            // Replace default em tip
            $("#question"+qID).find(".em_default").text(jsonOptions.lang.tip);
        });
    }
    //Fix buggy chrome/webkit engine which doesn't properly apply the css rules after this insertion
    $("#question"+qID).hide().show(0);
}
