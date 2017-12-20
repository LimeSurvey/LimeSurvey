/*
 * LimeSurvey
 * Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v3 or later, see LICENSE.php
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
 */


/**
 * The function focusFirst puts the Focus on the first non-hidden element in the Survey.
 * Normally this is the first input field (the first answer).
 */
function focusFirst(Event)
{
    $('#limesurvey :input:visible:enabled:first').focus();
}

/**
 * Code included inside this will only run once the page Document Object Model (DOM) is ready for JavaScript code to execute
 * @see https://learn.jquery.com/using-jquery-core/document-ready/
 */
$(document).ready(function()
{
    /* Uncomment below if you want to use the focusFirst function */
    //focusFirst();
    /* Some function are launched in endpage.pstpl */
    hideEmptyPart();
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
/**
 * Code included inside this will run each time windows is resized
 * @see https://api.jquery.com/resize/
 */
$(window).resize(function () {
    fixBodyPadding();
});

/**
 * Replace all existing alert default javascript function
 */
//~ window.alert = function(message, title) {
    //~ $(function() {
        //~ $("#bootstrap-alert-box-modal .modal-header .h4").text(title || "");
        //~ $("#bootstrap-alert-box-modal .modal-body").html("<p>"+message+"</p>" || "");
        //~ $("#bootstrap-alert-box-modal").modal('show');
    //~ });
//~ };


/**
 * fix padding of body according to navbar-fixed-top
 * in endpage and in $(window).resize
 */
function fixBodyPadding(){
    /* The 60 px is fixed in template.css */
    $("body").css("padding-top",$(".navbar-fixed-top").height()+"px")
}
/**
 * Set suffix/prefix clone for little screen (at top)
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

/**
 * Hide some part if empty
 * Some can be needed if contain only js
 * Some are not really needed : little margin only is shown
 */
function hideEmptyPart()
{
    $(".question-help-container").each(function(){
        if($(this).text().trim()==""){/* Only if have only script tag inside or empty tag */
            $(this).addClass("hidden");
        }
    });
    $(".group-description").each(function(){
        if($(this).text().trim()==""){/* Only if have only script tag inside or empty tag */
            $(this).addClass("hidden");
        }
    });
    $(".question-help-container.hidden").on("html:updated",function(){
        if($(this).text().trim()!=""){
            $(this).removeClass("hidden");
        }
    });
    $(".question-help-container").on("html:updated",function(){ // .question-help-container:not(.hidden) don't work ?
        if($(this).text().trim()==""){
            $(this).addClass("hidden");
        }
    });
}
