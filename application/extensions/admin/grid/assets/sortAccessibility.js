(function ($) {
    'use strict';

    // In-memory map of gridId → data-sort-attribute for focus restoration.
    // Persists across AJAX grid refreshes (same page) without needing sessionStorage.
    var pendingFocus = {};

    /**
     * Store the data-sort-attribute of the clicked sort link so focus can be
     * restored to the same column after the AJAX grid refresh.
     *
     * @param {jQuery} $link The sort link element that was activated.
     */
    function storeSortFocus($link) {
        var gid = $link.closest('.grid-view-ls').attr('id');
        var attr = $link.attr('data-sort-attribute');
        if (gid && attr) {
            pendingFocus[gid] = attr;
        }
    }

    /**
     * Restore keyboard focus to the sort link that was active before the AJAX
     * grid refresh. Called from LS.gridView.afterAjaxUpdate after the grid DOM
     * has been replaced.
     *
     * @param {string} gridId The DOM id of the grid container.
     */
    function restoreSortLinkFocus(gridId) {
        var attr = pendingFocus[gridId];
        if (!attr) {
            return;
        }
        delete pendingFocus[gridId];
        var $link = $('#' + gridId + ' .sort-link[data-sort-attribute]').filter(function () {
            return $(this).attr('data-sort-attribute') === attr;
        });
        if ($link.length) {
            $link.first()[0].focus({ preventScroll: true });
        }
    }

    // Space must activate role="button" elements per the ARIA spec.
    // Space does not natively click <a> elements, so we prevent the default scroll and trigger click.
    // Enter natively fires click on <a href="...">, but NOT on <a> without href — handle that case.
    $(document).on('keydown', '.grid-view-ls a.sort-link[role="button"]', function (e) {
        if (e.key === ' ') {
            e.preventDefault();
            $(this)[0].click();
        } else if (e.key === 'Enter' && !$(this).attr('href')) {
            $(this)[0].click();
        }
    });

    $(document).on('click', '.grid-view-ls a.sort-link[data-sort-attribute]', function () {
        storeSortFocus($(this));
    });

    window.LS = window.LS || {};
    LS.gridView = LS.gridView || {};
    LS.gridView.restoreSortLinkFocus = restoreSortLinkFocus;
})(jQuery);
