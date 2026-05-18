/**
 * Keep focus on the clicked sort column header link after a CGridView AJAX
 * update (e.g. sorting by "Code") instead of letting focus jump to the first
 * focusable element (checkbox).
 *
 * This is grid-agnostic: it tracks sort-link clicks on any registered grid
 * and restores focus to the correct column header after the grid re-renders.
 *
 * Usage (from PHP view):
 *   'lsAfterAjaxUpdate' => ['restoreFocusAfterSortInGrid("my-grid-id");']
 *
 * The grid is automatically observed once restoreFocusAfterSortInGrid() is called
 * for a given gridId, so no separate initialization step is needed.
 */

/**
 * Per-grid state: stores the last clicked column index for each grid id.
 * @type {Object.<string, number|null>}
 */
const gridState = {};

const sortFocusCaptureHandler = (e) => {
    const link = e.target.closest && e.target.closest('a.sort-link');
    if (link) {
        const th = link.closest('th');
        const grid = link.closest('.grid-view') || link.closest('table')?.parentElement;
        if (th && grid && grid.id) {
            gridState[grid.id] = Array.prototype.indexOf.call(th.parentNode.children, th);
        }
    }
};

/**
 * Attach the click-capture listener to a specific grid element.
 * Safe to call multiple times — duplicates are prevented.
 *
 * @param {string} gridId - The DOM id of the grid container.
 */
const attachSortFocusCapture = (gridId) => {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    // Use a data attribute to avoid attaching duplicate listeners
    if (grid.dataset.sortFocusBound) return;
    grid.dataset.sortFocusBound = 'true';
    grid.addEventListener('click', sortFocusCaptureHandler, true);
};

/**
 * Restore focus to the previously clicked sort column in the given grid,
 * then re-attach the capture listener (since the grid DOM is replaced on
 * AJAX update).
 *
 * @param {string} gridId - The DOM id of the grid container.
 */
const restoreFocusAfterSortInGrid = (gridId) => {
    if (gridState[gridId] != null) {
        const $th = jQuery('#' + gridId + ' table thead th').eq(gridState[gridId]);
        const $link = $th.find('a.sort-link');
        if ($link.length) {
            $link[0].focus();
        }
        gridState[gridId] = null;
    }
    // Grid DOM was replaced by AJAX — re-enable listener
    const grid = document.getElementById(gridId);
    if (grid) {
        delete grid.dataset.sortFocusBound;
        attachSortFocusCapture(gridId);
    }
};

// Expose globally so CGridView's lsAfterAjaxUpdate callback can invoke it
window.restoreFocusAfterSortInGrid = restoreFocusAfterSortInGrid;

export { restoreFocusAfterSortInGrid, attachSortFocusCapture };
export default restoreFocusAfterSortInGrid;

