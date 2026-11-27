/**
 * "Skip to main content" link of the admin pages (WCAG 2.4.1 Bypass Blocks).
 *
 * The link targets #in_survey_common_action, but the main content container depends on the layout
 * (e.g. #in_survey_common or #pjax-content inside a survey). Move the focus to the first one found.
 *
 * Safe to call multiple times — the listener is only attached once per page load.
 */
const MAIN_CONTENT_SELECTORS = ['#in_survey_common_action', '#in_survey_common', '#pjax-content', 'main', '[role="main"]'];

const skipToMainContent = () => {
    if (window.LS && LS._skipToMainContentBound) {
        return;
    }
    LS._skipToMainContentBound = true;
    document.addEventListener('click', function (e) {
        const link = e.target.closest ? e.target.closest('.ls-skip-link') : null;
        if (!link) {
            return;
        }
        const target = MAIN_CONTENT_SELECTORS
            .map((selector) => document.querySelector(selector))
            .find((element) => element !== null);
        if (!target) {
            return;
        }
        e.preventDefault();
        if (!target.hasAttribute('tabindex')) {
            target.setAttribute('tabindex', '-1');
        }
        target.focus();
    });
};

export default skipToMainContent;
