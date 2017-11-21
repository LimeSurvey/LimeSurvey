'use strict';

var switchInnerHTML = function (oldEl, newEl, opt) {
        opt = opt || {};
        oldEl.innerHTML = ' ';
        oldEl.innerHTML = newEl.innerHTML;
        this.onSwitch();
    },
    singletonPjax = function () {
        console.log('createing a Pjax instance on the window object');
        window.activePjax = window.activePjax || null;

        if (window.activePjax === null) {
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
                debug: true
            });
        }

        return window.activePjax;
    },
    forceRefreshPjax = function () {
        window.activePjax = null;
        singletonPjax();
    };

window.singletonPjax = singletonPjax;

window.addEventListener('pjax:reload', forceRefreshPjax);
window.addEventListener('pjax:create', singletonPjax);

window.addEventListener('pjax:refresh', function () {
    singletonPjax().parseDom(document);
});

window.addEventListener('pjax:load', function (url) {
    singletonPjax().loadUrl(url, singletonPjax().options);
});

