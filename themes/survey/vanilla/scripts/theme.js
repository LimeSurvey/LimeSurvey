/*
    LimeSurvey
    Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
    All rights reserved.
    License: GNU/GPL License v3 or later, see LICENSE.php
    LimeSurvey is free software. This version may have been modified pursuant
    to the GNU General Public License, and as distributed it includes or
    is derivative of works licensed under the GNU General Public License or
    other free or open source software licenses.
    See COPYRIGHT.php for copyright notices and details.


    (¯`·._.·(¯`·._.·  Theme main JavaScript   ·._.·´¯)·._.·´¯)

     Javascript file for this template.

     You'd rather not touch it. This file can be overwritten by an update.


     ██████╗  ██████╗     ███╗   ██╗ ██████╗ ████████╗    ████████╗ ██████╗ ██╗   ██╗ ██████╗██╗  ██╗    ██╗
     ██╔══██╗██╔═══██╗    ████╗  ██║██╔═══██╗╚══██╔══╝    ╚══██╔══╝██╔═══██╗██║   ██║██╔════╝██║  ██║    ██║
     ██║  ██║██║   ██║    ██╔██╗ ██║██║   ██║   ██║          ██║   ██║   ██║██║   ██║██║     ███████║    ██║
     ██║  ██║██║   ██║    ██║╚██╗██║██║   ██║   ██║          ██║   ██║   ██║██║   ██║██║     ██╔══██║    ╚═╝
     ██████╔╝╚██████╔╝    ██║ ╚████║╚██████╔╝   ██║          ██║   ╚██████╔╝╚██████╔╝╚██████╗██║  ██║    ██╗
     ╚═════╝  ╚═════╝     ╚═╝  ╚═══╝ ╚═════╝    ╚═╝          ╚═╝    ╚═════╝  ╚═════╝  ╚═════╝╚═╝  ╚═╝    ╚═╝

     Please, use custom.js

*/


/**
 * The general Template closure.
 * This is to capsule eventual errors inside of the template function, so the general script all run as the should
 */
var ThemeScripts = function(){
    var logObject = console.ls ? (window.debugState.frontend ? console.ls : console.ls.silent) : console;
    /**
     * The function focusFirst puts the Focus on the first non-hidden element in the Survey.
     * Normally this is the first input field (the first answer).
     */
    var focusFirst = function focusFirst(Event)
    {
        $('#limesurvey :input:visible:enabled:first').focus();
    };

    /**
     * fix padding of body according to navbar-fixed-top
     * in endpage and in $(window).resize
     */
    var fixBodyPadding = function fixBodyPadding(){
        /* The 60 px is fixed in template.css */
        $("body").css("padding-top",$(".navbar-fixed-top").height()+"px")
    }
    /**
     * Set suffix/prefix clone for little screen (at top)
     */
    var sliderSuffixClone = function sliderSuffixClone(){
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
    var hideEmptyPart = function hideEmptyPart()
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

    var initLanguageChanger = function(selectorItem, selectorGlobalForm){
        $(selectorItem).on('change',function() {
            var lang = $(this).val();
            logObject.log(lang, 'changed');
            // If there are no form : we can't use it */
            /* No form, not targeturl : just see what happen */
            var target = window.location.href;
            $("<form>", {
                "class":'ls-js-hidden',
                "html": '<input type="hidden" name="lang" value="' + lang + '" />',
                "action": target,
                "method": 'get'
            }).appendTo('body').submit();
        });
    };

    var initTopMenuLanguageChanger = function(selectorItem, selectorGlobalForm){
        // $(selectorContainer).height($('#main-row').height());
        $(selectorItem).on('click', function(){
            var lang = $(this).data('limesurvey-lang');
            /* The limesurvey form exist in document, move select and button inside and click */
            $(selectorGlobalForm+" [name='lang']").remove();                        // Remove existing lang selector
            $("<input type='hidden'>").attr('name','lang').val(lang).appendTo($(selectorGlobalForm));
            $('#changlangButton').clone().appendTo($(selectorGlobalForm)).click();

        });
    };

    var init = function(){

        /**
         * Code included inside this will only run once the page Document Object Model (DOM) is ready for JavaScript code to execute
         * @see https://learn.jquery.com/using-jquery-core/document-ready/
         * Also it will run on a complete pageload via the internal pjax system
         */
        $(document).on('ready pjax:scriptcomplete',function()
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
    };

    var initUserForms = function(){
        sliderSuffixClone();
        fixBodyPadding();
        hideEmptyPart();
    };
    var initGlobal = function(){
        sliderSuffixClone();
        fixBodyPadding();
        hideQuestionWithRelevanceSubQuestion();
        hideEmptyPart();
    };

    return {
        init: init,
        initUserForms: initUserForms,
        initGlobal: initGlobal,
        focusFirst: focusFirst,
        sliderSuffixClone : sliderSuffixClone,
        fixBodyPadding : fixBodyPadding,
        hideQuestionWithRelevanceSubQuestion : hideQuestionWithRelevanceSubQuestion,
        hideEmptyPart : hideEmptyPart,
        initLanguageChanger: initLanguageChanger,
        initTopMenuLanguageChanger: initTopMenuLanguageChanger,
        log: logObject
    }

}
