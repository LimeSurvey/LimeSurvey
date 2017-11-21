"use strict";
const env = process.env.NODE_ENV;

const switchInnerHTML = function (oldEl, newEl, opt) {
        opt = opt || {};
        oldEl.innerHTML = ' ';
        oldEl.innerHTML = newEl.innerHTML;
        this.onSwitch();
    },
    singletonPjax = function () {
        window.activePjax = window.activePjax || null;

        if (window.activePjax !== null) {
            window.activePjax = new Pjax({
                elements: ['a.pjax', 'form.pjax'], // default is "a[href], form[action]"
                selectors: [
                    '#pjax-content',
                    '#breadcrumb-container',
                    '#bottomScripts',
                    '#beginScripts'
                ],
                switches: {
                    '#bottomScripts': switchInnerHTML,
                    '#beginScripts': switchInnerHTML,
                    '#pjax-content': switchInnerHTML,
                    '#breadcrumb-container': switchInnerHTML,
                },
                debug: (env === 'developement')
            });
        }
        return window.activePjax;
    },
    forceRefreshPjax = function () {
        window.activePjax = null;
        singletonPjax();
    };

document.addEventListener('pjax:refresh', function () {
    singletonPjax().parseDom(document);
});
document.addEventListener('pjax:reload', function () {
    forceRefreshPjax();
});
document.addEventListener('pjax:create', function () {
    singletonPjax();
});
document.addEventListener('pjax:load', function (url) {
    singletonPjax().loadUrl(url, singletonPjax().options);
});
