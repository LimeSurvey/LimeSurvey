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

        $(document).on('click', '[data-navtargetid]', function() {
            openedSubmenuId = $(this).data('navtargetid');
            replaceContent(getContentByElementId('main-dropdown'), 'back-content');
            //replace menu content with submenu content
            replaceContent(getContentByElementId(openedSubmenuId), 'main-dropdown');
            focusMenuItem('.back-link');
        });
        // Submenu openers that are still links with role="button" (not native buttons): no navigation to href="#"
        $(document).on('click', 'a[data-navtargetid]', function(event) {
            event.preventDefault();
        });
        // and browsers only activate links with Enter, so Space has to open them like a button
        $(document).on('keydown', 'a[data-navtargetid]', function(event) {
            if (event.key === ' ') {
                event.preventDefault();
                $(this).trigger('click');
            }
        });
        $(document).on('click', '.back-link', function() {
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