/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Update answers part for rating slider
 *
 * @author Denis Chenu (Shnoulle)
 * @param {number} qId The qid of the question where apply.
 */
function doRatingSlider(qID) {

  var answersList=$('#question'+qID+' .answers-list.radio-list:not(.slidered-list)');
  if(!answersList){return;}
  // See to http://www.visualjquery.com/rating/rating_redux.html
  if ((!$.support.opacity && !$.support.style)) try { document.execCommand("BackgroundImageCache", false, true)} catch(e) { };

  var openValue=answersList.find("input:radio:checked").val();
  var sliderHtmlElement = "<div class='slider-wrapper slider-5'><div class='slider-labels'>";
  for (i=1; i<6; i++) {
    sliderHtmlElement= sliderHtmlElement+"<div class='slider-label slider-label-"+i+"'>"+i+"</div>";
  }
  sliderHtmlElement= sliderHtmlElement+"</div>"
    + "<div class='slider-background'><div class='slider slider-rating'></div></div>"
    + "</div>"
    + "<div class='slider-emoticon-wrapper'><div class='slider-emoticon'></div></div>";
  answersList.after(sliderHtmlElement);

  $("#question"+qID+" .slider").slider({
    min: 1,
    max: 5,
    range: "min",
    step: 1,
    value: openValue,
    slide: function( event, ui ) {
        $('#question'+qID+' .answers-list.radio-list').find(".radio[value='"+ui.value+"']").click();
        $('#question'+qID+' .slider-emoticon').attr('class', 'slider-emoticon slider-emoticon-'+ui.value);
    }
  });
  answersList.addClass("slidered-list hide read");
  if(openValue){
    $('#question'+qID+' .slider-emoticon').attr('class', 'slider-emoticon slider-emoticon-'+openValue);
    }
    
    $('#question'+qID).find('.ui-slider-handle').css({left: 0});
}
