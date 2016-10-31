/**
 * @file Do your own javascript function here
 */
$(document).ready(function(){
  /* Remove the input error if finally all answers is OK */
  $('.question-wrapper.mandatory.panel-warning .ls-answers').on('blur',':not(other-text-item) input:text',function(){
    if($(this).closest(".ls-answers").find(":not(other-text-item) input:text[value='']").filter(function(){ return !$(this).val(); }).length==0){
      $(this).closest(".question-wrapper").find(".ls-question-mandatory").removeClass("text-danger");
      if($(this).closest(".question-wrapper").find(".ls-em-error").length==0){
        $(this).closest(".question-wrapper").removeClass("panel-warning");
      }
    };
  });
  $('.question-wrapper.mandatory .ls-answers').on('change','input:radio',function(){

  });
  $('.question-wrapper.panel-warning .help-wrapper').on('classChangeGood',function(){
    if(!$(this).find(".ls-em-error").length && !$(this).find(".ls-question-mandatory.text-danger").length){
      $(this).closest(".question-wrapper").removeClass("panel-warning");
    }
  });
});
