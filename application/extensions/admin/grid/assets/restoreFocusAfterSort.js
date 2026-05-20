/**
 * Keep focus on the clicked sort column header link after a CGridView AJAX
 * update (e.g. sorting by "Code") instead of letting focus jump to the first
 * focusable element (checkbox).
 *
 * This is grid-agnostic: it tracks sort-link clicks on any registered grid
 * and restores focus to the correct column header after the grid re-renders.
 *
 * Usage (from PHP view, in lsAfterAjaxUpdate):
 *   'lsAfterAjaxUpdate' => ['LS.restoreFocusAfterSort("my-grid-id");']
 *
 * The grid is automatically observed once LS.restoreFocusAfterSort() is called
 * for a given gridId, so no separate initialization step is needed.
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

// Return public functions for this module
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


