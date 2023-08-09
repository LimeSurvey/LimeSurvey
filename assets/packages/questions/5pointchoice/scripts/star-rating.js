/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Update answers part for Star rating
 *
 * @author Denis Chenu (Shnoulle)
 * @author Markus Fluer (lacrioque)
 *
 * @param {number} qId The qid of the question where apply.
 */
function doRatingStar(qID) {

    // get Item to be extended
    var answersList = $('#question' + qID + ' .answers-list.radio-list:not(.starred-list)');
    //Close method if no item is found
    if (answersList.length < 1) {
        return;
    }
    // Check if the question is mandatory
    var isMandatory = $('#question' + qID).hasClass('mandatory');

    //Get number of Items
    var numberOfPossibleAnswers = $('#question' + qID).find('input[type=radio]').length;

    //This is deprecated and should be removed, but stays for backwards compatibility
    if ((!$.support.opacity && !$.support.style)) try {
        document.execCommand("BackgroundImageCache", false, true)
    } catch (e) {};

    //Check if there is a "no answer" option
    var itemNoAnswer = $('#question' + qID).find('.noanswer-item').length > 0;

    //Define stars-element container
    var starsHtmlElement = $("<div class='stars-list answers-list' aria-hidden='true'></div>");

    //Check if there is a given answer
    var openValue = null;
    answersList.find("input[type=radio]").each(function (i, item) {
        if ($(item).prop('checked')) {
            openValue = $(item).val();
        }
    });

    //Reset openValue to null, when no Answer is chosen
    if (openValue == numberOfPossibleAnswers && !isMandatory) {
        openValue = null;
    }

    //Add no-answer-option to stars List
    if (itemNoAnswer) {
        starsHtmlElement
            .append("<div class='star-rating star-cancel' data-star='" + (numberOfPossibleAnswers) + "' title='" + $('#question' + qID + ' .noanswer-item label').html() + "'><i class='fa fa-ban ri-forbid-2-line'></i></div>");
    } else {
        numberOfPossibleAnswers++;
    }

    //Add stars to the container
    for (i = 1; i < numberOfPossibleAnswers; i++) {
        //if there is a selected answer, add the fitting classes
        var classes = openValue != null ? "star-rated star-rating star " : "star-rating star ";
        //light all stars lower thgan the selected
        if (i < openValue) {
            classes += " star-rated-on";
        }
        //Add this-rated class to selected star
        if (i == openValue) {
            classes += " star-rated-on star-thisrated";
        }
        //append the element
        starsHtmlElement.append("<div class='star-" + i + " " + classes + "' data-star='" + i + "' title='" + i + "'><i class='fa fa-star ri-star-fill'></i></div>");
    }

    answersList.after(starsHtmlElement);
    //get all stars
    var starElements = starsHtmlElement.find('.star-rating')
        //Define the animation on mouseover
        .on("mouseenter", function () {
            var thisnum = $(this).data('star');
            //mar the current star
            $(this).addClass("star-drained").addClass("star-hover");
            //add/remove classes from sibling-elements
            $(this).siblings('.star-rating').each(function () {
                //smaller than the chosen and not "no answer" => add class to emphasize them
                if ($(this).data('star') < thisnum && thisnum != numberOfPossibleAnswers) {
                    $(this).addClass("star-drained");
                } else {
                    $(this).addClass("star-stub");
                }
            });
        })
        //define animation on mouseleave
        .on("mouseleave", function () {
            var thisnum = $(this).data('star');
            //remove hover-classes from this element
            $(this).removeClass("star-drained star-hover star-stub");
            //remove the selector classes from the siblings
            $(this).siblings('.star-rating').each(function () {
                $(this).removeClass("star-stub");
                $(this).removeClass("star-drained");
            });
        })
        //define the click-event
        .on("click", function (event) {
            var thischoice = $(this).data('star');
            //toggle the em-action on the hidden input
            answersList.find("input[type=radio]").prop('checked', false);
            answersList.find("input[value='" + thischoice + "']").prop('checked', true).trigger('change');
            //clean up classes
            $(this).siblings('.star-rating').removeClass("star-thisrated").removeClass("star-rated").removeClass("star-rated-on");
            //mark the chosen star
            $(this).addClass("star-rated").addClass("star-thisrated").addClass("star-rated-on");
            //iterate through the siblings to mark the stars lower than the current
            $(this).siblings('.star-rating').each(function () {
                if ($(this).data("star") < thischoice) {
                    $(this).addClass("star-rated").addClass("star-rated-on");
                }
            });
            // if cancel, remove all classes
            if ($(this).hasClass('star-cancel')) {
                $(this).siblings('.star-rating').removeClass("star-rated-on").removeClass("star-rated");
                answersList.find('.noanswer-item').find("input[type=radio]").prop('checked', true).trigger('change');
            }

        });

    //hide the standard-items
    answersList.addClass("starred-list visually-hidden");
}
