/**
 * Links of the admin menu that are announced as buttons (role="button") must react to Space like
 * buttons do (WCAG 2.1.1 Keyboard). Enter already works on links.
 *
 * Safe to call multiple times — the listener is only attached once per page load.
 */
const BUTTON_LINK_SELECTORS = [
    '#notification-li .admin-notification-link',
    '#notification-clear-all > a',
    '#feature-preview-button',
    '#admin-menu-user-button'
].join(', ');

/**
 * Lets Space activate the admin menu links that are announced as buttons.
 *
 * @return {void}
 */
const spaceActivatesButtonLinks = () => {
    if (window.LS && LS._spaceActivatesButtonLinksBound) {
        return;
    }
    LS._spaceActivatesButtonLinksBound = true;
    $(document).on('keydown', BUTTON_LINK_SELECTORS, function (e) {
        if (e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });
};

export default spaceActivatesButtonLinks;
