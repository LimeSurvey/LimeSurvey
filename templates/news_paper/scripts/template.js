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



function correctPNG() // correctly handle PNG transparency in Win IE 5.5 & 6.
{
   var arVersion = navigator.appVersion.split("MSIE")
   var version = parseFloat(arVersion[1])
   if ((version >= 5.5) && (version<7) && (document.body.filters))
   {
      for(var i=0; i<document.images.length; i++)
      {
         var img = document.images[i]
         var imgName = img.src.toUpperCase()
         if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
         {
            var imgID = (img.id) ? "id='" + img.id + "' " : "";
            var imgClass = (img.className) ? "class='" + img.className + "' " : "";
            var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' ";
            var imgStyle = "display:inline-block;" + img.style.cssText;
            if (img.align == "left") imgStyle = "float:left;" + imgStyle;
            if (img.align == "right") imgStyle = "float:right;" + imgStyle;
            if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle;
            var strNewHTML = "<span " + imgID + imgClass + imgTitle
            + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
            + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
            + "(src='" + img.src + "', sizingMethod='scale');\"></span>"
            img.outerHTML = strNewHTML
            i = i-1
         }
      }
   }
}

$(document).ready(function(){

    if($(window).width() < 800)
    {
        if($('.no-more-tables').length > 0)
        {
            $('.no-more-tables').find('td').each(function(){
                $that = $(this);
                $label = $that.data('title');
                $input = $that.find('input');
                if($input.is(':checkbox'))
                {
                    $that.find('label').removeClass('hide');
                }
                else
                {
                    $that.find('label').prepend($label);
                }

            });
        }
    }

    //var outerframeDistanceFromTop = 50;
    //topsurveymenubar
    var topsurveymenubarHeight = $('#topsurveymenubar').innerHeight();
    var outerframeDistanceFromTop = topsurveymenubarHeight;
    // Manage top container
    if(!$.trim($('#topContainer .container').html()))
    {
        $('#topContainer').hide();
    }
    else
    {
        $('#topContainer').css({
            top: topsurveymenubarHeight+'px',
        });

        $topContainerHeight = $('#topContainer').height();
        outerframeDistanceFromTop += $topContainerHeight;
    }

    if(!$.trim($('#surveynametitle').html()))
    {
        if(!$.trim($('#surveydescription').html()))
        {
            $('#survey-header').hide();
        }
    }

    $('#outerframeContainer').css({marginTop:outerframeDistanceFromTop+'px'});

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

    $('.questionhelp').each(function(){
        $that = $(this);
        if(!$.trim($that.html()))
        {
            $that.hide();
        }
    });


    // Load survey button
    if ($('#loadallbtnlink').length > 0){
        $('#loadallbtnlink').on('click', function()
        {
            $('#loadallbtn').trigger('click');
        });
    }

    // Save survey button
    if ($('#saveallbtnlink').length > 0){
        $('#saveallbtnlink').on('click', function()
        {
            $('#saveallbtn').trigger('click');
        });
    }

    // clearall
    if ($('#clearallbtnlink').length > 0){
        $('#clearallbtnlink').on('click', function()
        {
            $('#clearall').trigger('click');
        });
    }

    // Question index
    if($('.linkToButton').length > 0){
        $('.linkToButton').on('click', function()
        {
            $btnToClick = $($(this).attr('data-button-to-click'));
            $btnToClick.trigger('click');
            return false;
        });
    }

    if($('.emtip').length>0)
    {
        // On Document Load
        $('.emtip').each(function(){
            if($(this).hasClass('error'))
            {
                $(this).parents('div.alert.questionhelp').removeClass('alert-info').addClass('alert-danger');
                $(this).addClass('strong');
            }
        });

        // On em change
        $('.emtip').each(function(){

            $(this).on('classChangeError', function() {
                $parent = $(this).parent('div.alert.questionhelp');
                $parent.removeClass('alert').removeClass('alert-info',1);
                $parent.addClass('alert-danger',1).addClass('alert');

                if ($parent.hasClass('hide-tip'))
                {
                    $parent.removeClass('hide-tip',1);
                    $parent.addClass('tip-was-hidden',1);
                }

                $(this).addClass('strong');


            });

            $(this).on('classChangeGood', function() {
                $parent = $(this).parents('div.alert.questionhelp');
                $parent.removeClass('alert-danger');
                $(this).removeClass('strong');
                $parent.addClass('alert-info');
                if ($parent.hasClass('tip-was-hidden'))
                {
                    $parent.removeClass('tip-was-hidden').addClass('hide-tip');
                }

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
});
