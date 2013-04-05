/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Update answers part for Star rating
 *
 * @author Denis Chenu (Shnoulle)
 * @param {number} qId The qid of the question where apply.
 */
function doRatingStar(qID) {


  // Return quick
  var answersList=$('#question'+qID+' .answers-list.radio-list:not(.starred-list)');
  if(!answersList){return;}
  // See to http://www.visualjquery.com/rating/rating_redux.html
  if ((!$.support.opacity && !$.support.style)) try { document.execCommand("BackgroundImageCache", false, true)} catch(e) { };


  var asNoAnswer=$('#question'+qID+' .noanswer-item input.radio').length;
  var starsHtmlElement="<div class='stars-list answers-list noread'>";
  if(asNoAnswer){ starsHtmlElement= starsHtmlElement+"<div class='star-rating star-cancel' title='"+$('#question'+qID+' .noanswer-item label').html()+"'></div>";}
  for (i=1; i<6; i++) {
    starsHtmlElement= starsHtmlElement+"<div class='star-rating star star-"+i+"' title='"+i+"'></div>"
  }
  starsHtmlElement= starsHtmlElement+"</div>";
  answersList.after(starsHtmlElement);

  var starsElement=$('#question'+qID+' .stars-list');
  starsElement.on("mouseout mouseover", ".star-rating", function(event){
    var thisnum=$(this).index();
    if(event.type=='mouseover'){
      starsElement.children('.star-rating').removeClass("star-rated-on");
      starsElement.children('.star-rating:lt('+thisnum+')').addClass("star-drained");
      starsElement.children('.star-rating:eq('+thisnum+')').addClass("star-drained star-hover");
    }else{
      starsElement.children('.star-rated').addClass("star-rated-on");
      starsElement.children('.star-rating:lt('+thisnum+')').removeClass("star-drained");
      starsElement.children('.star-rating:eq('+thisnum+')').removeClass("star-drained star-hover");
    }
  });
  starsElement.on("click", ".star-rating.star", function(event){
    var thischoice=thisnum=$(this).index();
    if(!asNoAnswer){thischoice++;}
    answersList.find("input.radio[value='"+thischoice+"']").click();
    starsElement.children('.star-rating').removeClass("star-rated")
    starsElement.children('.star-rating:lt('+thisnum+')').addClass("star-rated");
    starsElement.children('.star-rating:eq('+thisnum+')').addClass("star-rated star-thisrated");
  });
  starsElement.on("click", ".star-rating.star-cancel", function(event){
    starsElement.children('.star-rating').removeClass("star-rated")
    answersList.find("input.radio[value='']").click();

  });
  answersList.addClass("starred-list hide read");
  var openValue=answersList.find("input:radio:checked").val();
  if(openValue){
    var thisnum=openValue-1;
    if(asNoAnswer){thisnum++;}
    starsElement.children('.star-rating:lt('+thisnum+')').addClass("star-rated");
    starsElement.children('.star-rating:eq('+thisnum+')').addClass("star-rated star-thisrated");
    starsElement.children('.star-rated').addClass("star-rated-on");
  }
}
