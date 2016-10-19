/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Update answers part for rating slider
 *
 * @author Markus Flür (lacrioque)
 * @TODO: Make switches for mandatory-question-taking
 * @TODO: Make sizes responsive
 * @param {number} qId The qid of the question where apply.
 */
function doRatingSlider(qID) {
  //Find the basic php-generated part of the question
  var answersList=$('#question'+qID+' .answers-list.radio-list:not(.slidered-list)');
  if(!answersList){return;} //if the generated part is not available => jump out of this method

  var openValue=answersList.find("input:radio:checked").val() || 6, //Select either noAnswer, or the preselected
      sliderHtmlElement = $("<div class='slider-wrapper'></div>"), //wrapper for the slider parts
      sliderInnerHtmlElement = $("<div class='slider-labels'></div>"), //the labels of the wrapper
      sliderGrabContainer = $("<div class='slider-grab-container'></div>"), //the container for the handle and the colorline
      sliderHandle = $("<div class='slider-handle'></div>"), //the handle
      sliderPosition = 23, //the basic position is 23 pixels left @TODO make that responsive
      //map to emojiclasses based on position
      mapEmojiToValue = {1: "emoji-sad",2: "emoji-mildlyunamused",3: "emoji-whatever",4: "emoji-smile",5: "emoji-grin-eyes",6: "emoji-grin",7: "emoji-bigsmile"},
      //the map to the relative position based on the container
      mapToStickToSelection = { 1 : 23 , 2 : 114, 3 : 205, 4 : 296, 5 : 387, 6 : 478};
      //Method to set the colored emoji based on the index, also it triggers the change in the base elemenst which triggers the EM
      setValueAndColorize = function(index){
        answersList.find('input[type=radio][value='+index+']').prop('checked',true).trigger('click');
        sliderHtmlElement.find(".slider-label").find('i').removeClass('emoji-color'); //remove all other color-classes
        if(index==6){ //if it is the "no Answer" set, add text-danger instead of emoji-color
          sliderHtmlElement.find(".slider-label-"+index).find('i').addClass('text-danger');
        } else {
          sliderHtmlElement.find(".slider-label-6").find('i').removeClass('text-danger');
          sliderHtmlElement.find(".slider-label-"+index).find('i').addClass('emoji-color');
        }
      },
      //this methods writes the slider position to the preregistered variable and triggers the colorchange in the emojis 
      //@TODO: Make this responsive
      setSliderPosition = function(rawOffsetX,containerOffset,containerWidth){

        var offsetX = (rawOffsetX)-containerOffset.left;
        if(offsetX > 0 && offsetX <= 68) {
          sliderPosition = 23;
          setValueAndColorize(1);
        } else if(offsetX > 68 && offsetX <= 159) {
          setValueAndColorize(2);
          sliderPosition = 114;
        } else if(offsetX > 159 && offsetX <= 250) {
          setValueAndColorize(3);
          sliderPosition = 205;
        } else if(offsetX > 250 && offsetX <= 341) {
          setValueAndColorize(4);
          sliderPosition = 296;
        } else if(offsetX > 341 && offsetX <= 432) {
          setValueAndColorize(5);
          sliderPosition = 387;
        } else if(offsetX > 432 && offsetX <= 523) {
          setValueAndColorize(6);
          sliderPosition = 478;
        } 

        return sliderPosition;
      },
      //the Method to be called when either the mousebutton is freed, or the cursor leaves the wrapper
      onEndDrag = function(e){
          sliderHandle.css('left', sliderPosition+"px");
          sliderGrabContainer.off("mousemove.drag");
          sliderGrabContainer.off("mouseup.drag");
          sliderHtmlElement.off("mouseleave.drag");
      },
      //method to set the handle and therefore register a change event
      onSetHandlePosition = function(e){
        setSliderPosition(e.screenX, sliderGrabContainer.offset(),sliderGrabContainer.width());
        sliderHandle.css('left', sliderPosition+"px");
      };


//create 5 Emojis and append them as labels to the label-row
  for (i=1; i<=5; i++) {
    sliderInnerHtmlElement.append("<div class='slider-label slider-label-"+i+"' data-position='"+i+"'><i class='emoji emoji-enormous "+mapEmojiToValue[i]+"'></i></div>");
  }
//Add the "no answer" label
//@TODO: create a method for mandatories
    sliderInnerHtmlElement.append("<div class='slider-label slider-label-6' data-position='6'><i class='fa fa-ban' style='font-size:28px;'></i></div>");

//Append things to the grab-container
  //append the colored line on the bottom
    sliderGrabContainer.append("<div class='slider-line'></div>");
  //append the handle  
    sliderGrabContainer.append(sliderHandle);

//append the labels
  sliderHtmlElement.append(sliderInnerHtmlElement);
//append the grabContainer (handle + colored baseline)
  sliderHtmlElement.append(sliderGrabContainer);

  sliderGrabContainer.on('click',function(e){
    setSliderPosition(e.screenX, sliderGrabContainer.offset(),sliderGrabContainer.width());
    sliderHandle.css('left', sliderPosition+"px");
  });

  sliderHandle.on("mousedown.drag", function(e){

    sliderGrabContainer.on("mousemove.drag", function(e){
    setSliderPosition(e.screenX, sliderGrabContainer.offset(),sliderGrabContainer.width());
    sliderHandle.css('left', sliderPosition+"px");
    });

    sliderHtmlElement.on("mouseleave.drag", onEndDrag);
    sliderGrabContainer.on("mouseup.drag", onEndDrag);
  });

  answersList.after(sliderHtmlElement);

  $('.slider-label').on('click',function(){
    setValueAndColorize($(this).data('position'));
    sliderHandle.css("left",mapToStickToSelection[$(this).data('position')]);
  });
 
  //answersList.addClass("slidered-list hide read");
  if(openValue){
    setValueAndColorize(openValue);
    sliderHandle.css("left",mapToStickToSelection[openValue]);
    }
    
}
