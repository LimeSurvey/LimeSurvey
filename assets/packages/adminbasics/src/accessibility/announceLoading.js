/**
 * Announces the loading overlay (#ls-loading) to screen readers (WCAG 4.1.3 Status Messages).
 *
 * The overlay is a status region with a static, visually hidden "Loading..." text that is only
 * shown and hidden ($('#ls-loading').show()). Showing an unchanged live region is not announced
 * reliably, so its text is set again every time the overlay becomes visible.
 *
 * Safe to call multiple times — the observer is only attached once per overlay element.
 */
const announceLoading = () => {
    const loading = document.getElementById('ls-loading');
    if (!loading || loading.dataset.announceLoadingBound === '1' || typeof MutationObserver === 'undefined') {
        return;
    }
    const textElement = loading.querySelector('.visually-hidden');
    if (!textElement) {
        return;
    }
    loading.dataset.announceLoadingBound = '1';
    const text = textElement.textContent;
    let wasVisible = window.getComputedStyle(loading).display !== 'none';

    new MutationObserver(() => {
        const isVisible = window.getComputedStyle(loading).display !== 'none';
        if (isVisible && !wasVisible) {
            textElement.textContent = '';
            window.setTimeout(() => {
                textElement.textContent = text;
            }, 100);
        }
        wasVisible = isVisible;
    }).observe(loading, {attributes: true, attributeFilter: ['style', 'class']});
};

export default announceLoading;
