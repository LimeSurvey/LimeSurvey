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
    var move = '';

    var startLoadingBar = function () {
        //Scroll to the top of the page
        window.scrollTo(0, 0);
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
        if(scriptNode.src){
            return ($('head').find('script[src="'+scriptNode.src+'"]').length > 0);
        }
        return true;
    };

    var appendScript = function(scriptText, scriptPosition, src){
        src = src || '';
        scriptPosition = scriptPosition || null;
        var scriptNode = document.createElement('script');
        scriptNode.type  = 'text/javascript';
        if(src != false){
            scriptNode.src   = src;
        }
        scriptNode.text  = scriptText;
        scriptNode.attributes.class = 'toRemoveOnAjax';
        switch(scriptPosition) {
        case 'head': if(checkScriptNotLoaded(scriptNode)){ document.head.appendChild(scriptNode); } break;
        case 'body': document.body.appendChild(scriptNode); break;
        case 'beginScripts': document.getElementById('beginScripts').appendChild(scriptNode); break;
        case 'bottomScripts': //fallthrough
        default: document.getElementById('bottomScripts').appendChild(scriptNode); break;

        }
    };

    var bindActions = function () {
        var globalPjax = new Pjax({
            elements: '#limesurvey', // default is "a[href], form[action]"
            selectors: ['#dynamicReloadContainer', '#beginScripts', '#bottomScripts'],
            debug: window.debugState.frontend,
            forceRedirectOnFail: true,
            reRenderCSS : true,
            logObject : console.ls ? (window.debugState.frontend ? console.ls : console.ls.silent) : console,
            scriptloadtimeout: 1500
        });
        // Always bind to document to not need to bind again
        $(document).on('click', '.ls-move-btn',function (e) {
            e.preventDefault();    
            $('#limesurvey').append('<input name=\''+$(this).attr('name')+'\' value=\''+$(this).attr('value')+'\' type=\'hidden\' />');
            $('#limesurvey').trigger('submit');
            return false;
        });

        // If the user try to submit the form
        // Always bind to document to not need to bind again
        $(document).on('submit', '#limesurvey', function (e) {
            // Prevent multiposting
            //Check if there is an active submit
            //If there is -> return immediately
            if(activeSubmit) return;
            //block further submissions
            activeSubmit = true;
            //start the loading animation
            startLoadingBar();

            $(document).on('pjax:scriptcomplete.onreload', function(){
                // We end the loading animation
                endLoadingBar();
                //free submitting again
                activeSubmit = false;
                if (/<###begin###>/.test($('#beginScripts').text())) {
                    $('#beginScripts').text('');
                }
                if (/<###end###>/.test($('#bottomScripts').text())){
                    $('#bottomScripts').text('');
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
    };
};
