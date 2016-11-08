/**
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Change multi numeric question type to slider question type
 *
 * @param {number} qId The qid of the question where apply.
 */

function doNumericSlider(qID,options) {
  $("#vmsg_"+qID+"_default").text(sliderTranslation.help);
  $("#question"+qID+" .slider-container").each(function()
  {
    var inputEl = $(this).find("input:text");
    var myfname = $(inputEl).attr("name");
    var prefix = $(inputEl).data('slider-prefix');
    var suffix = $(inputEl).data('slider-suffix');
    var dispVal= $(inputEl).data('slider-value');
    var separator = $(inputEl).data('separator');
    var sliderNoActionEl = $('#slider_user_no_action_' + myfname);
    /* need to fix actual value : force to number */
    dispVal = Number(dispVal.toString().replace(separator,'.'));
    // We start the slider, and provide it the formated value with prefix and suffix for its tooltip
    // Use closure for namespace, so we can use theSlider variable for all sliders.
    //~ (function () {
      var theSlider = $(inputEl).bootstrapSlider({
          //~ value : dispVal,
          formatter: function (value) {
              if($(sliderNoActionEl).val()=="1"){
                 return null;
              }
              displayValue = value.toString().replace('.',separator);
              return prefix + displayValue + suffix;
          }
      });
      $(this).find(".slider-handle").addClass("bg-primary");// bg-info is not dark enough


      // When user change the value of the slider :
      // we need to show the tooltip (if it was hidden)
      // and to update the value of the input element with correct format
      theSlider.on('slideStart', function(){
          $('#javatbd' + myfname).find('div.tooltip').show(); // Show the tooltip
          $(sliderNoActionEl).val(0); // The user did an action
          value = $(inputEl).val(); // We get the current value of the bootstrapSlider
          displayValue = value.toString().replace('.',separator); // We format it with the right separator
          $(inputEl).val(displayValue); // We parse it to the element
      });
      theSlider.on('change', function(event) {
      });
      theSlider.on('slideStop', function(event) {
          $(inputEl).val(event.value.toString().replace('.',separator)).trigger('keyup');// We call the EM by the event
      });

      // If user no action is on, we hide the tooltip
      // And we set the value to null
      // Fix it : must be the default value
      if($(sliderNoActionEl).val()=="1")
      {
          $('#javatbd' + myfname).find('div.tooltip').hide();
          $(inputEl).val(null); // .trigger('keyup');
      }

      // Click the reset button
      $('#answer' + myfname + '_resetslider').on('click', function() {
          $('#javatbd' + myfname).find('div.tooltip').hide();
          // Pretend user didn't do anything
          $(sliderNoActionEl).val("1");
          // Position slider button at beginning
          theSlider.bootstrapSlider('setValue', null);
          // Set value to null
          $(inputEl).val(null).trigger('keyup');
      });

      // On form submission, if user action is still on,
      // we must force the value of the input to ''
      // and force the thousand separator (this bug still affect 2.06)
      $("form").submit(function (e) {
          if($(sliderNoActionEl).val()=="1")
          {
              $(inputEl).val("");
          }
      });
    //~ });
  });

}
/*
var myfname = '<?php echo $myfname; ?>';
var $inputEl = $('#answer' + myfname);
var $sliderNoActionEl = $('#slider_user_no_action_' + myfname);
var $prefix = $inputEl.data('slider-prefix');
var $suffix = $inputEl.data('slider-suffix');
// We start the slider, and provide it the formated value with prefix and suffix for its tooltip
// Use closure for namespace, so we can use theSlider variable for all sliders.
(function () {




*/
