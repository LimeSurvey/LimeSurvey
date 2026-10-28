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

    /**
     * Moves the keyboard focus to the first element of the menu matching the selector
     * Replacing the menu content destroys the focused link, which would otherwise drop the focus to the page body
     * @param {string} selector
     */
    var focusMenuItem = function focusMenuItem(selector) {
        var item = document.querySelector('#main-dropdown ' + selector);
        if (item) {
            item.focus();
        }
    };

    var initNavbarEvents = function () {
        // Submenu that is currently shown, so the focus can return to its opener on "Back"
        var openedSubmenuId = null;

        $(document).on('click', '[data-navtargetid]', function(event) {
            event.preventDefault();
            openedSubmenuId = $(this).data('navtargetid');
            replaceContent(getContentByElementId('main-dropdown'), 'back-content');
            //replace menu content with submenu content
            replaceContent(getContentByElementId(openedSubmenuId), 'main-dropdown');
            focusMenuItem('.back-link');
        });
        $(document).on('click', '.back-link', function(event) {
            event.preventDefault();
            // switch menu content back to original content (this currently works only with one level nested elements)
            replaceContent(getContentByElementId('back-content'), 'main-dropdown');
            if (openedSubmenuId) {
                focusMenuItem('[data-navtargetid="' + openedSubmenuId + '"]');
            }
            openedSubmenuId = null;
        });
    };

    return {
        initNavbarEvents: initNavbarEvents,
    };
};
// register to global scope
window.NavbarScripts = NavbarScripts;