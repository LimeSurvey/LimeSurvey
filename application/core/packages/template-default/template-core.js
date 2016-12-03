/**
 * @file Default template functionnality
 * @copyright LimeSurvey <http://www.limesurvey.org>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

/* document ready function */
$(function() {
{
    addHoverColumn();
    triggerEmClassChangeTemplate();
}

/**
 * Dialog and confirm
 */
/* showStartPopups : replace core function : allow HTML and use it. */
function showStartPopups(){
    if(LSvar.showpopup && $(LSvar.startPopups).length){
        startPopup=LSvar.startPopups.map(function(text) {
            return "<p>"+text+"</p>";
        });
        alertSurveyDialog(startPopup);
    }
}
/* alertSurveyDialog @see application/core/package/limesurvey */
function alertSurveyDialog(text,title)
{
    $("#bootstrap-alert-box-modal .modal-header .modal-title").text(title || "");
    $("#bootstrap-alert-box-modal .modal-body").html("<p>"+text+"</p>" || "");
    $("#bootstrap-alert-box-modal").modal('show');
}
/* confirmSurveyDialog @see application/core/package/limesurvey */
function confirmSurveyDialog(text,title,submits){
    $("#bootstrap-alert-box-modal .modal-header .modal-title").text(title);
    $("#bootstrap-alert-box-modal .modal-body").html("<p>"+text+"</p>"+"<div class='btn-group btn-group-justified' role='group'><a class='btn btn-warning btn-confirm' data-dismiss='modal'>"+LSvar.lang.yes+"</a><a class='btn btn-default btn-cancel' data-dismiss='modal'>"+LSvar.lang.no+"</a></div>");
    $("#bootstrap-alert-box-modal").modal('show');
    $("#bootstrap-alert-box-modal .btn-confirm").on('click',function(){
        $.each(submits, function(name, value) {
            $("<input/>",{
                'type':"hidden",
                'name':name,
                'value':value,
            }).appendTo('form#limesurvey');
        });
        $('form#limesurvey').submit();
    });
}

/**
 * Add class hover to column in table-col-hover
 * We can't use CSS solution : need no background
 */
function addHoverColumn(){
    $(".table-col-hover").on({
        mouseenter: function () {
            $(this).closest(".table-col-hover").find("col").eq($(this).parent(".answers-list").children().index($(this))).addClass("hover");
        },
        mouseleave: function () {
            $(this).closest(".table-col-hover").find("col").removeClass("hover");
        }
    }, ".answer-item");
}

/**
 * Update some class when em-tips is success/error
 * @see core/package/limesurvey/survey.js:triggerEmClassChange
 */
function triggerEmClassChangeTemplate(){
    $('.ls-em-tip').on('classChangeError', function() {
        /* If user choose hide-tip : leave it */
        //~ $parent = $(this).parent('div.qquestion-valid-container');
        //~ if ($parent.hasClass('hide-tip'))
        //~ {
            //~ $parent.removeClass('hide-tip',1);
            //~ $parent.addClass('tip-was-hidden',1);
        //~ }
        $questionContainer = $(this).parents('div.question-container');
        $questionContainer.addClass('input-error'); /* No difference betwwen error after submit and error before submit : think (Shnoulle) it's better to have a difference */
    });

    $('.ls-em-tip').on('classChangeGood', function() {
        /* If user choose hide-tip : leave it */
        //~ $parent = $(this).parents('div.question-valid-container');
        //~ $parent.removeClass('text-danger');
        //~ $parent.addClass('text-info');
        //~ if ($parent.hasClass('tip-was-hidden'))
        //~ {
            //~ $parent.removeClass('tip-was-hidden').addClass('hide-tip');
        //~ }
        $questionContainer = $(this).parents('div.question-container');
        $questionContainer.removeClass('input-error');/* Not working with mandatory question ... */
    });
}
/**
 * Hide question if all sub-questions is hidden
 * @see core/package/limesurvey/survey.js:triggerEmRelevanceSubQuestion
 * @see https://bugs.limesurvey.org/view.php?id=10055 (partial)
 * Must be before ready (event happen before ready)
 */
function hideQuestionWithRelevanceSubQuestion(){
    $("[id^='question']").on('relevance:on',"[id^='javatbd']",function(event,data) {
        if(event.target != this) return; // not needed now, but after (2016-11-07)
        data = $.extend({style:'hidden'}, data);
        if(data.style=='hidden'){
            $(this).closest("[id^='question']:not(.ls-unrelevant)").removeClass("ls-hidden")
        }
    });
    $("[id^='question']").on('relevance:off',"[id^='javatbd']",function(event,data) {
        if(event.target != this) return; // not needed now, but after (2016-11-07)
        data = $.extend({style:'hidden'}, data);
        if(data.style=='hidden'){
            if($(this).closest("[id^='question']").find("[id^='javatbd']:visible").length==0){
                $(this).closest("[id^='question']").addClass("ls-hidden");// ls-hidden only is used only for Equation question type actually (but think must fix and use another class)
            }
        }
    });
}
