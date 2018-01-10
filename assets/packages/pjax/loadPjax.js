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
                switches: {
                    '#bottomScripts': switchOuterHTML,
                    '#beginScripts': switchOuterHTML,
                    '#pjax-content': switchInnerHTML,
                    '#breadcrumb-container': switchOuterHTML,
                },
                debug: window.debugState.backend,
                forceRedirectOnFail: true,
                reRenderCSS : true,
                scriptloadtimeout: 1500,
                logObject : console.ls
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
    triggerLoadUrl = function(e){        
        var currentPjax = singletonPjax();
        currentPjax.loadUrl(e.url, singletonPjax().options);
    },
    reparseDocument = function(){
        var currentPjax = singletonPjax();
        currentPjax.parseDOM(document);
    };

window.singletonPjax = singletonPjax;


window.removeEventListener('pjax:reload', forceRefreshPjax);
window.removeEventListener('pjax:create', singletonPjax);
window.removeEventListener('pjax:refresh', reparseDocument);
window.removeEventListener('pjax:load', triggerLoadUrl);
window.removeEventListener('pjax:unload', unsetPjax);

window.addEventListener('pjax:reload', forceRefreshPjax);
window.addEventListener('pjax:create', singletonPjax);
window.addEventListener('pjax:refresh', reparseDocument);
window.addEventListener('pjax:load', triggerLoadUrl);
window.addEventListener('pjax:unload', unsetPjax);

