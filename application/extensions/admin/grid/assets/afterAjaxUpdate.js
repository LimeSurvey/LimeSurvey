window.LS = window.LS || {};
LS.gridView = LS.gridView || {};

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
};
