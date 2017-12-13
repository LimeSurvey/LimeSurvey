/*
    LimeSurvey

    Copyright (C) 2007-2017 The LimeSurvey Project Team / Louis Gac
    All rights reserved.

    License: GNU/GPL License v2 or later, see LICENSE.php
    LimeSurvey is free software. This version may have been modified pursuant
    to the GNU General Public License, and as distributed it includes or
    is derivative of works licensed under the GNU General Public License or
    other free or open source software licenses.
    See COPYRIGHT.php for copyright notices and details.


    (¯`·._.·(¯`·._.·   Ajax Mode ·._.·´¯)·._.·´¯)

    This script deal with the new optional ajax system.


*/

// Submit the form with Ajax
var AjaxSubmitObject = function () {
    var activeSubmit = false;
    // First we get the value of the button clicked  (movenext, submit, prev, etc)
    var move = "";

    var startLoadingBar = function () {
        $('#ajax_loading_indicator').css('display','block').find('#ajax_loading_indicator_bar').css({
            'width': '20%',
            'display': 'block'
        });
    };


    var endLoadingBar = function () {
        $('#ajax_loading_indicator').css('opacity','0').find('#ajax_loading_indicator_bar').css('width', '100%');
        setTimeout(function () {
            $('#ajax_loading_indicator').css({'display': 'none', 'opacity': 1}).find('#ajax_loading_indicator_bar').css({
                'width': '0%',
                'display': 'none'
            });
        }, 1800);
    };

    var checkScriptNotLoaded = function(scriptNode){
        if(!!scriptNode.src){
            return ($('head').find('script[src="'+scriptNode.src+'"]').length > 0);
        }
        return true;
    }

    var appendScript = function(scriptText, scriptPosition, src){
        src = src || '';
        scriptPosition = scriptPosition || null;
        var scriptNode = document.createElement('script');
        scriptNode.type  = "text/javascript";
        if(src != false){
            scriptNode.src   = src;
        }
        scriptNode.text  = scriptText;
        scriptNode.attributes.class = "toRemoveOnAjax";
        switch(scriptPosition) {
            case "head": if(checkScriptNotLoaded(scriptNode)){ document.head.appendChild(scriptNode); } break;
            case "body": document.body.appendChild(scriptNode); break;
            case "beginScripts": document.getElementById('beginScripts').appendChild(scriptNode); break;
            case "bottomScripts": //fallthrough
            default: document.getElementById('bottomScripts').appendChild(scriptNode); break;

        }
    };

    var bindActions = function () {
        var globalPjax = new Pjax({
            elements: "#limesurvey", // default is "a[href], form[action]"
            selectors: ["#dynamicReloadContainer", "#beginScripts", "#bottomScripts"],
            debug: window.debugState>1,
            forceRedirectOnFail: true,
            reRenderCSS : true,
            scriptloadtimeout: 1500
        });
        // Always bind to document to not need to bind again
        $(document).on("click", ".ls-move-btn",function () {
            $("#limesurvey").append("<input name='"+$(this).attr("name")+"' value='"+$(this).attr("value")+"' type='hidden' />");
        });

        // If the user try to submit the form
        // Always bind to document to not need to bind again
        $(document).on("submit", "#limesurvey", function (e) {
            // Prevent multiposting
            //Check if there is an active submit
            //If there is -> return immediately
            if(activeSubmit) return;
            //block further submissions
            activeSubmit = true;
            //start the loading animation
            startLoadingBar();

            // We add the value of the button clicked to the post request
            // aPost += "&move=" + move;

            // $.ajax({
            //     url: sUrl,
            //     type: 'POST',
            //     dataType: 'html',
            //     data: aPost,

            //     success: function (body_html, status, request) {

            //         $('.toRemoveOnAjax').each(function () {
            //             $(this).remove();
            //         });

            //         var currentUrl = window.location.href;
            //         var cleanUrl = currentUrl.replace("&newtest=Y", "").replace(/\?newtest=Y(\&)?/, '?');

            //         if (currentUrl != cleanUrl) {
            //             window.history.pushState({
            //                 "html": body_html,
            //                 "pageTitle": request.getResponseHeader('title')
            //             }, "", cleanUrl);
            //         }
            //         var fragment = document.createElement('html');
            //         fragment.innerHTML = body_html;
            //         console.log(fragment);
            //         var newDocument =  $(fragment);
            //         console.log(newDocument);
            //         var $newBody = $(newDocument).find('body');
            //         var $newHead = $(newDocument).find('head');
            //         console.log($newHead, $newBody );
            //         var $replaceableContainer = $newBody.find('div#dynamicReloadContainer').html();
            //         var $bodyDataScripts = $newBody.children('script');
            //         var $headDataScripts = $newHead.children('script');
            //         console.log($bodyDataScripts);
            //         console.log($headDataScripts);
            //         var $replaceableTopScriptContainer = $newBody.find('#beginScripts');
            //         var $replaceableBottomScriptContainer = $newBody.find('#bottomScripts');


            //         $headDataScripts.each(function () {
            //             appendScript($(this).html(), 'head', $(this).attr('src'));
            //         });
            //         $bodyDataScripts.each(function () {
            //             appendScript($(this).html(), 'body', $(this).attr('src'));
            //         });

            //         $("#beginScripts").empty();
            //         $replaceableTopScriptContainer.find('script').each(function(i,scriptTag){
            //             appendScript($(this).html(), 'beginScripts', $(this).attr('src'));
            //             //$("#beginScripts").append(scriptTag);
            //         });

            //         $("#dynamicReloadContainer").empty().html($replaceableContainer);

            //         $replaceableBottomScriptContainer.find('script').each(function(i,scriptTag){
            //             appendScript($(this).html(), 'bottomScripts', $(this).attr('src'));
            //             // $("#bottomScripts").append(scriptTag);
            //         });



            //         //also trigger the pjax:scriptcomplete event to load all adherent scripts

            //     },

            //     error: function (result, status, error) {
            //         alert("ERROR");
            //         console.log(result);
            //     }

            $(document).on('pjax:scriptcomplete.onreload', function(){
                // We end the loading animation
                endLoadingBar();
                //free submitting again
                activeSubmit = false;
                if (/<###begin###>/.test($('#beginScripts').text())) {
                    $('#beginScripts').text("");
                }
                if (/<###end###>/.test($('#bottomScripts').text())){
                    $('#bottomScripts').text("");
                }

                $(document).off('pjax:scriptcomplete.onreload');
            });

        });
        return globalPjax;
    };

    return {
        bindActions: bindActions,
        startLoadingBar: startLoadingBar,
        endLoadingBar: endLoadingBar,
        unsetSubmit: function(){activeSubmit = false;},
        blockSubmit: function(){activeSubmit = true;}
    }
}
