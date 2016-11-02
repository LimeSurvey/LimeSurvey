/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 * Description: Javascript file for templates. Put JS-functions for your template here.
 *
 *
 * $Id:$
 */


/*
 * The function focusFirst puts the Focus on the first non-hidden element in the Survey.
 *
 * Normally this is the first input field (the first answer).
 */
function focusFirst(Event)
{
    $('#limesurvey :input:visible:enabled:first').focus();
}
/*
 * The focusFirst function is added to the eventlistener, when the page is loaded.
 *
 * This can be used to start other functions on pageload as well. Just put it inside the 'ready' function block
 */

/* Uncomment below if you want to use the focusFirst function */
/*
$(document).ready(function(){
    focusFirst();
});
*/


$(document).ready(function()
{
    fixBodyPadding();
    // If list of nav-bar action is empty: remove it (else .navbar-toggle is shown on small screen) //
    if(!$("#navbar li").length){
        $("#navbar").remove();
        $("[data-target='#navbar']").remove();
    }
    // Scroll to first error
    if($(".input-error").length > 0) {
        $('#bootstrap-alert-box-modal').on('hidden.bs.modal', function () {
            $firstError = $(".input-error").first();
            $pixToScroll = ( $firstError.offset().top - 100 );
            $('html, body').animate({
                 scrollTop: $pixToScroll + 'px'
             }, 'fast');
        });
    }

    $('.language-changer').each(function(){
        $that = $(this);
        if(!$.trim($that.children('div').html()))
        {
            $that.hide();
        }
    });

    $('.group-description-container').each(function(){
        $that = $(this);
        if(!$.trim($that.children('div').html()))
        {
            $that.hide();
        }
    });

    // Hide question help container if empty
    $('.questionhelp').each(function(){
        $that = $(this);
        if(!$.trim($that.html()))
        {
            $that.hide();
        }
    });

    // Errors
    if($('.ls-em-tip').length>0)
    {
        // On Document Load (the EM js is done before ? */
        $('.ls-em-tip').each(function(){
            if($(this).hasClass('ls-em-error'))
            {
                $(this).parents('div.questionhelp').removeClass('text-info').addClass('text-danger');
            }
        });

        // On em change
        $('.ls-em-tip').each(function(){
            $(this).on('classChangeError', function() {
                $parent = $(this).parent('div.questionhelp');
                $parent.removeClass('text-info',1);
                $parent.addClass('text-danger',1);

                if ($parent.hasClass('hide-tip'))
                {
                    $parent.removeClass('hide-tip',1);
                    $parent.addClass('tip-was-hidden',1);
                }

                $questionContainer = $(this).parents('div.question-container');
                $questionContainer.addClass('input-error');
            });

            $(this).on('classChangeGood', function() {
                $parent = $(this).parents('div.questionhelp');
                $parent.removeClass('text-danger');
                $parent.addClass('text-info');
                if ($parent.hasClass('tip-was-hidden'))
                {
                    $parent.removeClass('tip-was-hidden').addClass('hide-tip');
                }
                $questionContainer = $(this).parents('div.question-container');
                $questionContainer.removeClass('input-error');
            });
        });
    }

    // Hide the menu buttons at the end of the Survey
    if($(".hidemenubutton").length>0)
    {
        $('.navbar-right').hide();
    }

    // Survey list footer
    if($('#surveyListFooter').length>0)
    {
        $surveyListFooter = $('#surveyListFooter');
        $('#outerframeContainer').after($surveyListFooter);
    }

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })


});
$(window).resize(function () {
    fixBodyPadding();
});
//~ /**
 //~ * showStartPopups : replace core function : allow HTML and use it.
 //~ */
function showStartPopups(){
    if(LSvar.showpopup && $(LSvar.startPopups).length){
        startPopup=LSvar.startPopups.map(function(text) {
            return "<p>"+text+"</p>";
        });
        $("#bootstrap-alert-box-modal .modal-body").html(startPopup);
        $("#bootstrap-alert-box-modal").modal('show');
    }
}

window.alert = function(message, title) {
    $("#bootstrap-alert-box-modal .modal-header h4").text(title || "");
    $("#bootstrap-alert-box-modal .modal-body p").text(message || "");

    $(document).ready(function()
    {
        $("#bootstrap-alert-box-modal").modal('show');
    });
};

/**
 * fix padding of body according to navbar-fixed-top
 */
function fixBodyPadding(){
    /* The 50 px is fixed in template.css */
    $("body").css("padding-top",$(".navbar-fixed-top").height+"px")
}
/**
 * fix padding of body according to navbar-fixed-top
 */
function sliderSuffixClone(){
$(".numeric-multi .slider-item .slider-right").each(function(){
    if($(this).closest(".slider-item").find(".slider-left").length){
        var colWidth="6";
    }else{
        var colWidth="12";
    }
    $(this).clone().removeClass("col-xs-12").addClass("visible-xs-block col-xs-"+colWidth).prop("aria-hidden",true).insertBefore($(this).prev(".slider-container"));
    $(this).addClass("hidden-xs");
    $(this).closest(".slider-item").find(".slider-left").removeClass("col-xs-12").addClass("col-xs-6");
});
}
//Hide the Answer and the helper field
$(document).ready(
    function(){
        $('.question-container').each(function(){
            if($(this).find('div.answer-container').find('input').length == 1)
            {
                if($(this).find('div.answer-container').find('input[type=hidden]').length >0
                    && $(this).find('div.answer-container').find('select').length < 1)
                {
                    $(this).find('div.answer-container').css({display: 'none'});
                }
                if(trim($(this).find('div.question-help-container').find('div').html()) == "")
                {
                    $(this).find('div.question-help-container').css({display: 'none'});
                }
            }
        });
    }
);
