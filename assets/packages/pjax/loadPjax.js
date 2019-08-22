'use strict';
var switchOuterHTML = function (oldEl, newEl, opt) {
        opt = opt || {};
        // really remove all Events!
        var parent = $(oldEl).parent();
        $(oldEl).off().remove();
        parent.append(newEl);
        this.onSwitch();
    },
    switchInnerHTML = function (oldEl, newEl, opt) {
        opt = opt || {};
        // really remove all Events!
        var parent = $(oldEl).parent();
        $(oldEl).off().html('');
        $(oldEl).html($(newEl).html());
        this.onSwitch();
    },
    singletonPjax = function () {
        window.activePjax = window.activePjax || null;
        
        if (window.activePjax === null) {
            console.ls.log('creating a Pjax instance on the window object');
            window.activePjax = new Pjax({
                elements: ['a.pjax', 'form.pjax'], // default is "a[href], form[action]"
                selectors: [
                    '#pjax-content',
                    '#breadcrumb-container',
                    '#bottomScripts',
                    '#beginScripts'
                ],
                debug: window.debugState.backend,
                forceRedirectOnFail: true,
                reRenderCSS : true,
                scriptloadtimeout: 1500,
            });
        }

        return window.activePjax;
    },
    forceRefreshPjax = function () {
        window.activePjax = null;
        singletonPjax();
    },
    unsetPjax = function (){
        window.activePjax.parseDOMtoUnload();
        $('a.pjax').off('click');
        window.activePjax = null;
    },
    triggerLoadUrl = function(e,data){      
        var currentPjax = singletonPjax();
        currentPjax.loadUrl(data.url, singletonPjax().options);
    },
    reparseDocument = function(){
        var currentPjax = singletonPjax();
        currentPjax.parseDOM(document);
    };

window.singletonPjax = singletonPjax;

$(document).off('pjax:reload');
$(document).off('pjax:create');
$(document).off('pjax:refresh');
$(document).off('pjax:load');
$(document).off('pjax:unload');

$(document).on('pjax:reload', forceRefreshPjax);
$(document).on('pjax:create', singletonPjax);
$(document).on('pjax:refresh', reparseDocument);
$(document).on('pjax:load', triggerLoadUrl);
$(document).on('pjax:unload', unsetPjax);

