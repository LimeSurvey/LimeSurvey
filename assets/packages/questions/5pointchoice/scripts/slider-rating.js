/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Update answers part for rating slider
 *
 * @author Markus Flür (lacrioque)
 * @TODO: Make sizes responsive
 * @param {number} qId The qid of the question where apply.
 */
function getRatingSlider(qID){
    //Find the basic php-generated part of the question
  var answersList=$('#question'+qID+' .answers-list.radio-list:not(.slidered-list)'),
    basicSettings = {
    },
    package = {
      sliderHtmlElement       : $("<div id='emoji_slider_container_"+qID+"' class='ls-emojislider slider-wrapper' aria-hidden='true'></div>"),               //wrapper for the slider parts
      sliderInnerHtmlElement  : $("<div class='slider-labels'></div>"),                                                    //the labels of the wrapper
      sliderGrabContainer     : $("<div id='emoji_slider_grab_container_"+qID+"' class='slider-grab-container'></div>"),   //the container for the handle and the colorline
      sliderLine              : $("<div id='slider_line_item_"+qID+"' class='slider-line'></div>"),                        //The colored baseline
      sliderHandle            : $("<div id='slider_handle_item_"+qID+"' class='slider-handle'></div>"),                    //the handle
      sliderLabelEmoji        : $("<div class='slider-label'><i class='emoji emoji-enormous'></i></div>"),
      sliderLabelNoAnswer     : $("<div class='slider-label slider-label-6' data-position='6'><i class='fa fa-ban ri-forbid-2-line' style='font-size:28px;'></i></div>"),
      sliderDummyEmoji        : $("<div class='dummy-emoji'><i class='emoji emoji-enormous emoji-color'></i></div>"),
    },
    mapEmojiToValue = function(position){
      var imageMap = {1: "emoji-sad",2: "emoji-mildlyunamused",3: "emoji-whatever",4: "emoji-smile",5: "emoji-grin-eyes",6: "emoji-grin",7: "emoji-bigsmile"};
      return imageMap[position];
    },
    calculateMapObjects = function(){
      var maps = {};
      //the map to the relative position based on the container for screens >640px
      var mapToStickToSelection = { 1 : 31 , 2 : 123, 3 : 214, 4 : 304, 5 : 397};
      //the map to the relative position based on the container for screens <640px
      var mapToStickToSelectionSmallScreen = { 1 : 16 , 2 : 72, 3 : 127, 4 : 182, 5 : 236};

      if(checkHasNoAnswerOption()) {
        mapToStickToSelection[6] = 476;
        mapToStickToSelectionSmallScreen[6] = 286;
      }

      if( $(window).width() < 640){
        maps.selection = mapToStickToSelectionSmallScreen;
      } else {
        maps.selection = mapToStickToSelection;
      }

      //the map to the field with attributes min and max for screens >640px
      var mapToStickToSelection = { 1 : {min: 0, max:78} , 2 : {min: 78, max:171}, 3 : {min: 171, max:264}, 4 : {min: 264, max:357}, 5 : {min: 357, max:543}};
      //the map to the field with attributes min and max for screens <640px
      var mapToStickToSelectionSmallScreen = { 1 : {min: 0, max:44}  , 2 : {min: 44, max:100} , 3 : {min: 100, max:156} , 4 : {min: 156, max:212} , 5 : {min: 212, max:324}};
      if(checkHasNoAnswerOption()) {
        mapToStickToSelection[6] = {min: 450, max:543};
        mapToStickToSelectionSmallScreen[6] = {min: 268, max:324};
        mapToStickToSelection[5] = {min: 357, max:450};
        mapToStickToSelectionSmallScreen[5] = {min: 212, max:268};
      }
      if( $(window).width() < 640){
        maps.borders = mapToStickToSelectionSmallScreen;
      } else {
        maps.borders = mapToStickToSelection;
      }

        return maps
    },
    //Method setting the value on the EM-calculateable part
    setValueAndColorize = function(index){
      answersList.find('input[type=radio][value='+index+']').trigger('click');
      $("#emoji_slider_container_"+qID).find(".slider-label").find('i').removeClass('emoji-color'); //remove all other color-classes
      if(index==6){ //if it is the "no Answer" set, add text-danger instead of emoji-color
         $("#emoji_slider_container_"+qID).find(".slider-label-6").find('i').addClass('text-danger');
         answersList.find('input[type=radio][value=""]').trigger('click');
      } else {
        $("#emoji_slider_container_"+qID).find(".slider-label-6").find('i').removeClass('text-danger');
        $("#emoji_slider_container_"+qID).find(".slider-label-"+index).find('i').addClass('emoji-color');
      }
    },
    //Method to unset all events on dragend
    onEndDrag = function(e){
      var element = this;
      onSetHandlePosition(e,element);
        $("#emoji_slider_container_"+qID).find('.slider-grab-container').off("mousemove.dragOn");
        $("#emoji_slider_container_"+qID).find('.slider-grab-container').off("mouseup.dragOn");
    },
    //method to set the handle and therefore register a change event
    onSetHandlePosition = function(e,element){
      var rawPosition = e.pageX,
          element = element || this;
      var position = calculateSliderPositionFromOffset(rawPosition);
      var index = getIndexFromPosition(position);
      setSliderPosition(position,element);
      setValueAndColorize(index);
    },
    checkHasNoAnswerOption = function(){
      return (answersList.find('.noanswer-item').length > 0);
    },
    checkOpenValue = function(){
      var openValue = (answersList.find("input:radio:checked").val() || 0)*1; //Select either noAnswer, or the preselected
      return openValue || false;
    },
    relativeOffset = function(rawOffset){
      var baseZero = package.sliderHtmlElement.offset();
      var relativeZero = baseZero.left;
      var offset = rawOffset-relativeZero;
      return offset;
    },
    getIndexFromPosition = function(position){
      var maps = calculateMapObjects();
      for(var i in maps.borders){
        if(maps.selection[i] == position) {
          return i;
        }
      }
    },
    calculateSliderPositionFromOffset = function(rawOffsetX){
      var offsetX = relativeOffset(rawOffsetX);
      var maps = calculateMapObjects();
      for(var i in maps.borders){
        if(offsetX >= maps.borders[i]['min'] && offsetX < maps.borders[i]['max']) {
          return maps.selection[i];
        }
      }
    },
    getSliderPositionFromIndex = function(index){
      var maps = calculateMapObjects();
      return maps.selection[index];
    },
    setSliderPosition = function(position,element){
        $(element).closest('.slider-wrapper').find('.slider-handle').css('left', position+"px");
    },
    //Register the events
    bindEventsToContainer = function(){
      //Register the click event on the slider container
      //this event will trigger when the slider area is clicked,
      //to move the slider to where the click was generated
      $("#emoji_slider_grab_container_"+qID).on('click', onSetHandlePosition );
      //Registers to the event, when the handle is dragged.
      //Emulate a dragging behaviour, that is ignoring the Y-axis and skipst through the predefined stoppoints
      $("#emoji_slider_grab_container_"+qID).find('.slider-handle').on("mousedown.drag", function(e){
        //set events on dragging, on leaving the container and on dropping the handle
        $("#emoji_slider_grab_container_"+qID).on("mousemove.dragOn", onSetHandlePosition );
        $("#emoji_slider_grab_container_"+qID).on("mouseup.dragOn", onEndDrag);
      });
    //Bind events for clicking on the labels
      $("#emoji_slider_container_"+qID).find('.slider-label').on('click',onSetHandlePosition);
    },
    pinUpHtml = function(){
      //Fill the inner Element with the labels
      //create 5 Emojis and append them as labels to the label-row
      for (i=1; i<=5; i++) {
        package.sliderLabelEmoji.clone().addClass("slider-label-"+i).data('position', i).find('i').addClass(mapEmojiToValue(i)).end().appendTo(package.sliderInnerHtmlElement);
      }
      // Add dummy element with color emoji font to make the browser load the font and avoid flickering when an emoji is selected
      package.sliderInnerHtmlElement.append(package.sliderDummyEmoji);
      //Add the "no answer" label, if the question need one, also add the mandatory class to the sliderline
      if(!checkHasNoAnswerOption()){
        package.sliderLine.addClass('mandatory');
      } else {
        package.sliderInnerHtmlElement.append(package.sliderLabelNoAnswer);
      }
      //append the colored line on the bottom
        package.sliderGrabContainer.append(package.sliderLine);
      //append the handle
        package.sliderGrabContainer.append(package.sliderHandle);

    //Append things to the main elemen
      //append the labels
      package.sliderHtmlElement.append(package.sliderInnerHtmlElement);
      //append the grabContainer (handle + colored baseline)
      package.sliderHtmlElement.append(package.sliderGrabContainer);
    //put everything on the screen
      answersList.after(package.sliderHtmlElement);
    },

    doRatingSlider = function () {
    if(!answersList){return;} //if the generated part is not available => jump out of this method
  //hide the basic radioes
    pinUpHtml();
    bindEventsToContainer();
    answersList.addClass("slidered-list visually-hidden");
  //if a value is set, set it in the emojis
  var openValue = checkOpenValue();
    if(openValue){
      setValueAndColorize(openValue);
      setSliderPosition(getSliderPositionFromIndex(openValue), package.sliderGrabContainer);
    }else if(checkHasNoAnswerOption()){
      setValueAndColorize(6);
      setSliderPosition(getSliderPositionFromIndex(6), package.sliderGrabContainer);
    }
  }

  return doRatingSlider;
}
