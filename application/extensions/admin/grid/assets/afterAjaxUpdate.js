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
 * Grids refreshed by a filter or page size change, whose result summary has to be announced.
 * @type {Object.<string, boolean>}
 */
LS.gridView.pendingSummaryAnnouncement = LS.gridView.pendingSummaryAnnouncement || {};

jQuery(document).on('change keydown', '.grid-view-ls .filters input, .grid-view-ls .filters select, .grid-view-ls .changePageSize', function (e) {
    'use strict';
    if (e.type === 'keydown' && e.key !== 'Enter') {
        return;
    }
    var gridId = jQuery(this).closest('.grid-view-ls').attr('id');
    if (gridId) {
        LS.gridView.pendingSummaryAnnouncement[gridId] = true;
    }
});

/**
 * Announce the result summary (e.g. "Displaying 1-5 of 6 result(s).") through a polite live region
 * after a filter or page size change. An empty result is announced by announceEmptyMessage instead.
 *
 * @param {string} gridId The grid element ID.
 */
LS.gridView.announceSummary = function (gridId) {
    'use strict';
    if (!LS.gridView.pendingSummaryAnnouncement[gridId]) {
        return;
    }
    delete LS.gridView.pendingSummaryAnnouncement[gridId];
    // The live region lives outside the grid, which is replaced on every update
    var status = document.getElementById('ls-grid-status');
    var summary = document.querySelector('#' + gridId + ' .summary');
    var text = summary && summary.firstChild && summary.firstChild.nodeType === Node.TEXT_NODE
        ? summary.firstChild.textContent.trim()
        : '';
    if (!text || document.getElementById(gridId + '-empty-message')) {
        if (status) {
            status.textContent = '';
        }
        return;
    }
    if (!status) {
        status = document.createElement('div');
        status.id = 'ls-grid-status';
        status.className = 'visually-hidden';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.setAttribute('aria-atomic', 'true');
        document.body.appendChild(status);
    }
    status.textContent = '';
    setTimeout(function () {
        status.textContent = text;
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
    LS.gridView.announceEmptyMessage(id);
    LS.gridView.announceSummary(id);
};
