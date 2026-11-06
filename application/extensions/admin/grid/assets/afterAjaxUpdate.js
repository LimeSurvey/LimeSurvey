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
 * Grids refreshed by a filter or page size change, whose result summary has to be announced.
 * @type {Object.<string, boolean>}
 */
LS.gridView.pendingSummaryAnnouncement =
    LS.gridView.pendingSummaryAnnouncement || {};

jQuery(document).on(
    "change keydown",
    ".grid-view-ls .filters input, .grid-view-ls .filters select, .grid-view-ls .changePageSize",
    function (e) {
        "use strict";
        if (e.type === "keydown" && e.key !== "Enter") {
            return;
        }
        var gridId = jQuery(this).closest(".grid-view-ls").attr("id");
        if (gridId) {
            LS.gridView.pendingSummaryAnnouncement[gridId] = true;
        }
    },
);

/**
 * Announce the result summary (e.g. "Displaying 1-5 of 6 result(s).") through a polite live region
 * after a filter or page size change. An empty result is announced by announceEmptyMessage instead.
 *
 * @param {string} gridId The grid element ID.
 */
LS.gridView.announceSummary = function (gridId) {
    "use strict";
    if (!LS.gridView.pendingSummaryAnnouncement[gridId]) {
        return;
    }
    delete LS.gridView.pendingSummaryAnnouncement[gridId];
    // The live region lives outside the grid, which is replaced on every update
    var status = document.getElementById("ls-grid-status");
    var summary = document.querySelector("#" + gridId + " .summary");
    // The result sentence is the first non-empty text, which may be nested (e.g. in a layout div).
    // Text inside the page size select (its option labels) is skipped.
    var text = "";
    if (summary) {
        var walker = document.createTreeWalker(summary, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                return node.parentNode.closest("select") ||
                    !node.textContent.trim()
                    ? NodeFilter.FILTER_REJECT
                    : NodeFilter.FILTER_ACCEPT;
            },
        });
        var textNode = walker.nextNode();
        text = textNode ? textNode.textContent.trim() : "";
    }
    LS.gridView.summaryAnnouncementTimeouts =
        LS.gridView.summaryAnnouncementTimeouts || {};
    window.clearTimeout(LS.gridView.summaryAnnouncementTimeouts[gridId]);
    if (!text || document.getElementById(gridId + "-empty-message")) {
        if (status) {
            status.textContent = "";
        }
        return;
    }
    if (!status) {
        status = document.createElement("div");
        status.id = "ls-grid-status";
        status.className = "visually-hidden";
        status.setAttribute("role", "status");
        status.setAttribute("aria-live", "polite");
        status.setAttribute("aria-atomic", "true");
        document.body.appendChild(status);
    }
    status.textContent = "";
    LS.gridView.summaryAnnouncementTimeouts[gridId] = window.setTimeout(
        function () {
            status.textContent = text;
        },
        100,
    );
};

/**
 * Standard afterAjaxUpdate handler for CLSGridView grids.
 * Called after every AJAX grid refresh.
 *
 * @param {string} id   The grid element ID passed by yiiGridView.
 * @param {*}      data The raw AJAX response data.
 */
LS.gridView.afterAjaxUpdate = function (id, data) {
    "use strict";
    if (LS.actionDropdown && LS.actionDropdown.create) {
        LS.actionDropdown.create();
    }
    if (LS.rowlink && LS.rowlink.create) {
        LS.rowlink.create();
    }
    if (typeof initColumnFilter === "function") {
        initColumnFilter();
    }

    // accessibility features
    if (LS.gridView.restoreSortLinkFocus) {
        LS.gridView.restoreSortLinkFocus(id);
    }
    // After the sort link restore, so it only takes over when the focus is still lost
    var filterFocused = LS.gridView.restoreFilterFocus ? LS.gridView.restoreFilterFocus(id) : false;
    LS.gridView.announceEmptyMessage(id, filterFocused);
    LS.gridView.announceSummary(id);
};
