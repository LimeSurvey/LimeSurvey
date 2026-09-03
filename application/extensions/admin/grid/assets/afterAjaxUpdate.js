window.LS = window.LS || {};
LS.gridView = LS.gridView || {};

/**
 * Move screen reader focus to the grid empty-state message when present.
 *
 * @param {string} gridId The grid element ID.
 */
LS.gridView.announceEmptyMessage = function (gridId) {
    'use strict';
    var emptyEl = document.getElementById(gridId + '-empty-message');
    if (emptyEl) {
        emptyEl.focus();
    }
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

    // acessibility features
    if (LS.gridView.restoreSortLinkFocus) {
        LS.gridView.restoreSortLinkFocus(id);
    }
    LS.gridView.announceEmptyMessage(id);
};
