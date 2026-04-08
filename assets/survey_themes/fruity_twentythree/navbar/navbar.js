export var NavbarScripts = function () {
    var getContentByElementId = function getContentByElementId(elementId) {
        var targetHtml = document.getElementById(elementId);
        return targetHtml.innerHTML;
    };

    var replaceContent = function replaceContent(replacementContent,targetElementId) {
        var element = document.getElementById(targetElementId);
        element.innerHTML = '';
        element.innerHTML = replacementContent;
    };

    var initNavbarEvents = function () {

        $(document).on('click', '[data-navtargetid]', function() {
            replaceContent(getContentByElementId('main-dropdown'), 'back-content');
            //replace menu content with submenu content
            replaceContent(getContentByElementId($(this).data('navtargetid')), 'main-dropdown');
        });
        $(document).on('click', '.back-link', function() {
            // switch menu content back to original content (this currently works only with one level nested elements)
            replaceContent(getContentByElementId('back-content'), 'main-dropdown');
        });
    };

    return {
        initNavbarEvents: initNavbarEvents,
    };
};
// register to global scope
window.NavbarScripts = NavbarScripts;