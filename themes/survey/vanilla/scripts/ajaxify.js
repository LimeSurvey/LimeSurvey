/*
    LimeSurvey

    Copyright (C) 2007-2017 The GititSurvey Project Team / Louis Gac
    All rights reserved.

    License: GNU/GPL License v2 or later, see LICENSE.php
    GititSurvey is free software. This version may have been modified pursuant
    to the GNU General Public License, and as distributed it includes or
    is derivative of works licensed under the GNU General Public License or
    other free or open source software licenses.
    See COPYRIGHT.php for copyright notices and details.


    (¯`·._.·(¯`·._.·   Ajax Mode ·._.·´¯)·._.·´¯)

    This script deal with the new optional ajax system.


*/

//Check if we have to work on IE10 *sigh*
var isIE10 = false;
/*@cc_on
    if (/^10/.test(@_jscript_version)) {
        isIE10 = true;
    }
@*/
console.ls.log("isIE10: ", isIE10);

// Submit the form with Ajax
var AjaxSubmitObject = function () {
    var activeSubmit = false;
    // First we get the value of the button clicked  (movenext, submit, prev, etc)
    var move = '';

    var startLoadingBar = function () {
        //Scroll to the top of the page
        window.scrollTo(0, 0);
        $('#ajax_loading_indicator').css('display', 'block').find('#ajax_loading_indicator_bar').css({
            'width': '20%',
            'display': 'block'
        });
    };


    var endLoadingBar = function () {
        $('#ajax_loading_indicator').css('opacity', '0').find('#ajax_loading_indicator_bar').css('width', '100%');
        setTimeout(function () {
            $('#ajax_loading_indicator').css({
                'display': 'none',
                'opacity': 1
            }).find('#ajax_loading_indicator_bar').css({
                'width': '0%',
                'display': 'none'
            });
        }, 1800);
    };

    var checkScriptNotLoaded = function (scriptNode) {
        if (scriptNode.src) {
            return ($('head').find('script[src="' + scriptNode.src + '"]').length > 0);
        }
        return true;
    };

    var bindActions = function () {
        var logFunction = new ConsoleShim('PJAX-LOG', (LSvar.debugMode < 1));

        var pjaxErrorHandler = function (href, options, requestData) {
            logFunction.log('requestData', requestData);
            if (requestData.status >= 500) {
                document.getElementsByTagName('html')[0].innerHTML = requestData.responseText;
                throw new Error(JSON.stringify({
                    state: requestData.status,
                    message: 'Error in PHP!',
                    data: requestData
                }));
            }

            if (requestData.status >= 404) {
                window.location.href = href;
                return false;
            }
            if (requestData.status >= 300 || requestData.status == 0) {
                logFunction.log('responseURL', requestData.responseURL);
                var responseHeaders = requestData.getAllResponseHeaders().trim().split(/[\r\n]+/);
                var headerMap = {};
                responseHeaders.forEach(function (line) {
                    var parts = line.split(': ');
                    var header = parts.shift();
                    var value = parts.join(': ');
                    headerMap[header.toLowerCase()] = value;
                });
                window.location = headerMap['x-redirect'] || headerMap.location || href;
                return false;
            }
        };

        var globalPjax = new Pjax({
            elements: ['form#limesurvey'], // default is "a[href], form[action]"
            selectors: ['#dynamicReloadContainer', '#beginScripts', '#bottomScripts'],
            debug: true,
            forceRedirectOnFail: true,
            pjaxErrorHandler: pjaxErrorHandler,
            reRenderCSS: true,
            logObject: logFunction,
            scriptloadtimeout: 1500,
        });
        // Always bind to document to not need to bind again
        // Restrict to [type=submit]:not([data-confirmedby])
        // - :submit is the default if button don't have type (reset button on slider for example),
        // - confirmedby have their own javascript system
        $(document).on('click', '.action--ls-button-submit, .action--ls-button-previous', function (e) {
            $('#limesurvey').append('<input id="onsubmitbuttoninput" name=\'' + $(this).attr('name') + '\' value=\'' + $(this).attr('value') + '\' type=\'hidden\' />');
            if (isIE10 || /Edge\/\d+\.\d+/.test(navigator.userAgent)) {
                e.preventDefault();
                $('#limesurvey').trigger('submit');
                return false;
            }
        });
        
        // If the user try to submit the form
        // Always bind to document to not need to bind again
        $(document).on('submit', '#limesurvey', function (e) {
            // Prevent multiposting
            //Check if there is an active submit
            //If there is -> return immediately
            if (activeSubmit) {
                e.preventDefault();
                return false;
            }
            //block further submissions
            activeSubmit = true;
            $('.action--ls-button-submit, .action--ls-button-previous').prop('disabled', true).addClass('btn-disabled');
            if ($('#onsubmitbuttoninput').length == 0) {
                $('#limesurvey').append('<input id="onsubmitbuttoninput" name=\'' + $('#limesurvey [type=submit]:not([data-confirmedby])').attr('name') + '\' value=\'' + $('#limesurvey [type=submit]:not([data-confirmedby])').attr('value') + '\' type=\'hidden\' />');
            }
            //start the loading animation
            startLoadingBar();

            $(document).on('pjax:scriptcomplete.onreload', function () {
                // We end the loading animation
                endLoadingBar();
                //free submitting again
                activeSubmit = false;
                $('.action--ls-button-submit, .action--ls-button-previous').prop('disabled', false).removeClass('btn-disabled');

                if (/<###begin###>/.test($('#beginScripts').text())) {
                    $('#beginScripts').text('');
                }
                if (/<###end###>/.test($('#bottomScripts').text())) {
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
        unsetSubmit: function () {
            activeSubmit = false;
        },
        blockSubmit: function () {
            activeSubmit = true;
        }
    };
};
