window.LS = window.LS || {};
LS.gridView = LS.gridView || {};

/**
 * Announce the grid empty-state message to screen readers when present.
 *
 * @param {string}  gridId    The grid element ID.
 * @param {boolean} keepFocus True to leave the focus where it is (e.g. on the filter field) and only announce
 *                            the message through its live region, false to move the focus to the message.
 * @return {void}
 */
LS.gridView.announceEmptyMessage = function (gridId, keepFocus) {
    'use strict';
    var emptyEl = document.getElementById(gridId + '-empty-message');
    if (!emptyEl) {
        return;
    }
    if (!keepFocus) {
        emptyEl.focus();
        return;
    }
    // A live region inserted together with its text is often not announced: Set the text again after a moment
    var text = emptyEl.textContent;
    emptyEl.textContent = '';
    setTimeout(function () {
        emptyEl.textContent = text;
    }, 100);
};

/**
 * Standard afterAjaxUpdate handler for CLSGridView grids.
 * Called after every AJAX grid refresh.
 *
 * @param {string} id   The grid element ID passed by yiiGridView.
 * @param {*}      data The raw AJAX response data.
 */
LS.gridView.afterAjaxUpdate = function (id, data) {
    'use strict';
    if (LS.actionDropdown && LS.actionDropdown.create) {
        LS.actionDropdown.create();
    }
    if (LS.rowlink && LS.rowlink.create) {
        LS.rowlink.create();
    }
    if (typeof initColumnFilter === 'function') {
        initColumnFilter();
    }

    // accessibility features
    if (LS.gridView.restoreSortLinkFocus) {
        LS.gridView.restoreSortLinkFocus(id);
    }
    // After the sort link restore, so it only takes over when the focus is still lost
    var filterFocused = LS.gridView.restoreFilterFocus ? LS.gridView.restoreFilterFocus(id) : false;
    LS.gridView.announceEmptyMessage(id, filterFocused);
};
