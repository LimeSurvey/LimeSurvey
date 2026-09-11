/**
 * Keep focus on the clicked sort column header link after a CGridView AJAX
 * update (e.g. sorting by "Code") instead of letting focus jump to the first
 * focusable element (checkbox).
 *
 * This is grid-agnostic: it tracks sort-link clicks on any registered grid
 * and restores focus to the correct column header after the grid re-renders.
 *
 * Usage (from PHP):
 *   Register in lsAfterAjaxUpdate:
 *     'lsAfterAjaxUpdate' => ['LS.restoreFocusAfterSort("my-grid-id");']
 *
 *   The capture listener is automatically attached to all .grid-view-ls grids
 *   on page load, so the very first sort click is already tracked.
 */

// Namespace
var LS = LS || {};

/**
 * Per-grid state: stores the last clicked column index for each grid id.
 * @type {Object.<string, number|null>}
 */
var gridFocusState = {};

var sortFocusCaptureHandler = function (e) {
    var link = e.target.closest && e.target.closest('a.sort-link');
    if (link) {
        var th = link.closest('th');
        var grid = e.currentTarget;
        if (th && grid && grid.id) {
            gridFocusState[grid.id] = Array.prototype.indexOf.call(th.parentNode.children, th);
        }
    }
};

/**
 * Attach the click-capture listener to a specific grid element.
 * Safe to call multiple times — duplicates are prevented via a data attribute.
 *
 * @param {string} gridId - The DOM id of the grid container.
 */
var attachSortFocusCapture = function (gridId) {
    var grid = document.getElementById(gridId);
    if (!grid) return;
    if (grid.dataset.sortFocusBound) return;
    grid.dataset.sortFocusBound = 'true';
    grid.addEventListener('click', sortFocusCaptureHandler, true);
};

/**
 * Initialize sort-focus capture on all .grid-view-ls grids.
 * Called automatically on DOMContentLoaded so the first sort click is tracked.
 */
LS.initSortFocusCapture = function () {
    'use strict';
    var grids = document.querySelectorAll('.grid-view-ls');
    for (var i = 0; i < grids.length; i++) {
        if (grids[i].id) {
            attachSortFocusCapture(grids[i].id);
        }
    }
};

/**
 * Restore focus to the previously clicked sort column in the given grid,
 * then re-attach the capture listener (since the grid DOM is replaced on
 * AJAX update).
 *
 * @param {string} gridId - The DOM id of the grid container.
 */
LS.restoreFocusAfterSort = function (gridId) {
    'use strict';
    if (gridFocusState[gridId] != null) {
        var $th = jQuery('#' + gridId + ' table thead th').eq(gridFocusState[gridId]);
        var $link = $th.find('a.sort-link');
        if ($link.length) {
            $link[0].focus();
        }
        gridFocusState[gridId] = null;
    }
    // Grid DOM was replaced by AJAX — re-enable listener
    var grid = document.getElementById(gridId);
    if (grid) {
        delete grid.dataset.sortFocusBound;
        attachSortFocusCapture(gridId);
    }
};

// Auto-init on page load so the very first sort click is captured
jQuery(document).ready(function () {
    LS.initSortFocusCapture();
});


