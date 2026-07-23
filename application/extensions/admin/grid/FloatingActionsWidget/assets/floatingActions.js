/**
 * Floating Actions Widget – JavaScript
 *
 * Provides LS.floatingActions:
 *   - Shows a context bar with action buttons at the bottom of the viewport
 *     whenever one or more row-checkboxes are checked in a CLSGridView.
 *   - The bar lives in <body> (position:fixed) so it is never removed by
 *     yiiGridView's replaceWith() operation on pagination.
 *   - Uses LS.gridSelection.count() for cross-page selection tracking when
 *     LS.gridSelection is available (registered by CLSGridView via gridSelection.js).
 *   - CLSGridView calls LS.floatingActions.refresh(id) explicitly after every
 *     AJAX update to keep the count in sync.
 */
/* global $, bootstrap, LS */
window.LS = window.LS || {};
window.LS.gridView = window.LS.gridView || {};
LS.floatingActions = (function () {
    'use strict';
    /**
     * Stored jQuery references to each bar element, keyed by gridId.
     * We keep these references even after the grid AJAX update removes the bar
     * from the DOM, so we can re-inject it without querying the (now missing) ID.
     * @type {Object.<string, jQuery>}
     */
    var _barRefs = {};
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    /** Return the stored bar reference for a grid (or query the DOM as fallback). */
    function _bar(gridId) {
        return _barRefs[gridId] || $('#floating-actions-bar-' + gridId);
    }
    /**
     * Count selected rows and update the floating bar visibility.
     * Uses LS.gridSelection.count() (cross-page) when available,
     * falling back to visible DOM checkboxes for grids without gridSelection.
     *
     * @param {string} gridId
     * @param {string} pk  Primary key column name (without [])
     */
    function _updateBar(gridId, pk) {
        var $bar = _bar(gridId);
        if (!$bar.length) { return; }
        // Prefer cross-page count from LS.gridSelection if available
        var count;
        if (window.LS && LS.gridSelection && typeof LS.gridSelection.count === 'function') {
            count = LS.gridSelection.count(gridId);
        } else {
            var pkCol = (pk || $bar.data('pk')) + '[]';
            count = $('#' + gridId)
                .find('.table tbody input[name="' + pkCol + '"]:checked').length;
        }
        $bar.find('.floating-actions-count-number').text(count);
        if (count > 0) {
            $bar.addClass('floating-actions-bar--visible');
            // Sync position every time the bar becomes visible so it is always
            // rendered at the correct location regardless of scroll state.
            _syncBarPosition(gridId);
        } else {
            $bar.removeClass('floating-actions-bar--visible');
        }
        // Keep the legacy MassiveActionsWidget button in sync as well
        var $massive = $('.massiveAction');
        if (count > 0) {
            $massive.removeClass('disabled').removeAttr('disabled');
        } else {
            $massive.addClass('disabled').attr('disabled', 'disabled');
        }
    }
    /**
     * Keeps the bar's position:fixed 'bottom' offset in sync with the scroll
     * position so the bar visually sticks to the table's lower edge when the
     * table bottom is in the viewport, and floats at the default offset otherwise.
     *
     * Called on init, after every grid AJAX update, and on window scroll.
     *
     * @param {string} gridId
     */
    function _syncBarPosition(gridId) {
        var $bar     = _bar(gridId);
        var $grid    = $('#' + gridId);
        var $wrapper = $grid.find('.scrolling-wrapper');
        if (!$bar.length || !$wrapper.length) { return; }
        var fixedBottom   = 16;   // keep in sync with CSS bottom: 16px
        var viewportH     = window.innerHeight || document.documentElement.clientHeight;
        var wrapperBottom = $wrapper[0].getBoundingClientRect().bottom;
        // The bar always uses position:fixed (viewport-relative), which avoids
        // any dependency on the grid container as a CSS containing block.
        // When the table bottom is visible we adjust 'bottom' dynamically so
        // the bar pins to the table's lower edge; otherwise we use the fixed offset.
        if (wrapperBottom > 0 && wrapperBottom <= (viewportH - fixedBottom)) {
            // Table bottom visible: pin bar flush to the table's lower edge.
            $bar.css({
                position : 'fixed',
                bottom   : (viewportH - wrapperBottom) + 'px'
            });
        } else {
            // Table bottom off-screen (above or below): float at viewport bottom.
            $bar.css({
                position : 'fixed',
                bottom   : fixedBottom + 'px'
            });
        }
    }
    /**
     * Ensure the bar is attached to <body>.
     * Since the bar uses position:fixed it does not need to live inside the grid
     * container – it only needs to be somewhere in the document.  Keeping it in
     * <body> means it is never removed by yiiGridView's replaceWith() operation,
     * so there is no detach/re-inject cycle to manage.
     *
     * @param {string} gridId
     */
    function _injectBar(gridId) {
        var $bar = _bar(gridId);
        if (!$bar.length) { return; }
        // Move to body once (or whenever it has been removed from the document).
        if (!$.contains(document.body, $bar[0])) {
            $('body').append($bar);
        }
        _syncBarPosition(gridId);
    }
    // -------------------------------------------------------------------------
    // Action click handler
    // -------------------------------------------------------------------------
    /**
     * Handle a click on a floating action button or dropdown item.
     * Called with `this` = the clicked element via grid-level event delegation.
     *
     * @param {jQuery.Event} e
     * @param {string}       gridId
     * @param {string}       pk
     */
    function _onClick(e, gridId, pk) {
        e.preventDefault();
        var $that      = $(this);
        var actionUrl  = $that.data('url');
        var actionType = $that.data('action-type');
        var onSuccess  = $that.data('on-success');
        var gridReload = $that.data('grid-reload');
        var $grid      = $('#' + gridId);
        // Get selected items – use cross-page store when available, fall back to DOM
        var checkedItems;
        if (window.LS && LS.gridSelection && typeof LS.gridSelection.getAll === 'function') {
            checkedItems = LS.gridSelection.getAll(gridId);
        } else {
            checkedItems = $grid.yiiGridView('getChecked', pk);
        }
        var checkedItemsJson = JSON.stringify(checkedItems);
        // ---- redirect action ------------------------------------------
        if (actionType === 'redirect') {
            var newForm = $('<form>', {
                action : actionUrl,
                target : $that.data('target') || '_blank',
                method : 'POST',
            }).append($('<input>', {
                name  : $that.data('input-name'),
                value : checkedItems.join($that.data('input-separator') || ''),
                type  : 'hidden',
            })).append($('<input>', {
                name  : LS.data.csrfTokenName,
                value : LS.data.csrfToken,
                type  : 'hidden',
            })).appendTo('body');
            newForm.submit();
            return;
        }
        // ---- modal action ---------------------------------------------
        var modalId = $that.data('modal-id');
        if (!modalId) { return; }
        var $modal = $('#' + modalId);
        if (!$modal.length) { return; }
        var $modalTitle      = $modal.find('.modal-title');
        var $modalBody       = $modal.find('.modal-body-text');
        var $modalButton     = $modal.find('.btn-ok');
        var $modalClose      = $modal.find('.modal-footer-close');
        var $ajaxLoader      = $('#ajaxContainerLoading');
        var $oldModalTitle   = $modalTitle.text();
        var $oldModalBody    = $modalBody.html();
        var $oldModalButtons = $modal.find('.modal-footer-buttons');
        var $selectedList    = $modal.find('.selected-items-list');
        var showSelected = $modal.data('show-selected');
        var selectedUrl  = $modal.data('selected-url');
        // Show a preview of the selected items inside the modal body
        if (showSelected === 'yes' && selectedUrl) {
            var csrfToken    = $('meta[name="csrf-token"]').attr('content');
            var $grididvalue   = gridId;
            var $oCheckedItems = checkedItemsJson;
            $selectedList.empty();
            $.ajax({
                url:  selectedUrl,
                type: 'POST',
                data: { $grididvalue: $grididvalue, $oCheckedItems: $oCheckedItems, csrfToken: csrfToken },
                success: function (html) { $selectedList.html(html); },
                error:   function (req, err) { console.log(err); },
            });
        }
        // Reset modal to original state on close
        $modal.off('hidden.bs.modal.floating').on('hidden.bs.modal.floating', function () {
            $modalTitle.text($oldModalTitle);
            $modalBody.empty().append($oldModalBody);
            $modalClose.hide();
            $oldModalButtons.show();
            if (gridReload === 'yes') {
                // Clear cross-page selection: deleted (or modified) rows no longer exist.
                if (window.LS && LS.gridSelection && typeof LS.gridSelection.clear === 'function') {
                    LS.gridSelection.clear(gridId);
                }
                // Uncheck all visible checkboxes on the current page
                $('#' + gridId + ' tbody .massiveActionsCheckbox').prop('checked', false);
                $('#' + gridId + ' thead input[type="checkbox"]').prop('checked', false);
                $('#' + gridId).yiiGridView('update');
                setTimeout(function () { $(document).trigger('actions-updated'); }, 500);
            }
        });
        // Confirm / OK button
        $modalButton.off('click.floating').on('click.floating', function () {
            var $form = $modal.find('form');
            if ($form.data('trigger-validation') && !$form[0].reportValidity()) {
                return;
            }
            var postData = { sItems: checkedItemsJson };
            $modal.find('.custom-data').each(function () {
                if ($(this).hasClass('btn-group')) {
                    $(this).find('input:checked').each(function () {
                        postData[$(this).attr('name')] = $(this).val();
                    });
                } else if ($(this).attr('type') === 'checkbox') {
                    if ($(this).prop('checked')) {
                        postData[$(this).attr('name')] = $(this).val();
                    }
                } else {
                    postData[$(this).attr('name')] = $(this).val();
                }
            });
            var aAttributesToUpdate = [];
            $modal.find('.attributes-to-update').each(function () {
                aAttributesToUpdate.push($(this).attr('name') || $(this).attr('id'));
            });
            postData.aAttributesToUpdate = JSON.stringify(aAttributesToUpdate);
            postData.grididvalue = gridId;
            $modal.find('input.post-value, select.post-value').each(function () {
                postData[$(this).attr('name')] = $(this).val();
            });
            $modalBody.empty();
            $oldModalButtons.hide();
            $modalClose.show();
            $ajaxLoader.show();
            $selectedList.empty();
            $.ajax({
                url:  actionUrl,
                type: 'POST',
                data: postData,
                success: function (html) {
                    $ajaxLoader.hide();
                    if ($modal.data('keepopen') !== 'yes') {
                        $modal.modal('hide');
                    } else {
                        $modalBody.empty().html(html);
                        if (typeof syncMassiveActionResultsTableCaption === 'function') {
                            syncMassiveActionResultsTableCaption($modal, $modalBody);
                        }
                    }
                    if (onSuccess) {
                        var func = typeof window[onSuccess] === 'function' ? window[onSuccess] : null;
                        if (func) { func(html); }
                    }
                },
                error: function (data) {
                    $ajaxLoader.hide();
                    if (data && data.responseJSON && data.responseJSON.success === false
                        && data.responseJSON.message
                    ) {
                        $modal.modal('hide');
                        if (LS.LsGlobalNotifier) {
                            LS.LsGlobalNotifier.createAlert(
                                data.responseJSON.message, 'danger', { showCloseButton: true }
                            );
                        }
                    } else {
                        $modal.find('.modal-body-text').empty().html(data.responseText);
                    }
                },
            });
        });
        // Open the modal
        var modalEl = document.getElementById(modalId);
        if (!modalEl) { return; }
        modalEl.setAttribute('tabindex', '-1');
        var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl, {});
        modalEl.addEventListener('shown.bs.modal', function () {
            modalEl.focus({ preventScroll: true });
        }, { once: true });
        bsModal.show();
    }
    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------
    return {
        /**
         * Initialise the floating bar for one grid.
         *
         * @param {string} gridId  ID of the CLSGridView container element
         * @param {string} pk      Primary key column name (without [])
         */
        init: function (gridId, pk) {
            var $bar  = $('#floating-actions-bar-' + gridId);
            var $grid = $('#' + gridId);
            if (!$bar.length || !$grid.length) { return; }
            // Store reference so it survives the grid's AJAX innerHTML replacement
            _barRefs[gridId] = $bar;
            // Place bar directly above the pagination/summary row
            _injectBar(gridId);
            // yiiGridView uses replaceWith() on AJAX updates, so the original
            // $grid element becomes detached after every page change. We therefore
            // delegate ALL events from document, using the grid-id and bar-id as
            // part of the selector so they remain scoped to the right widgets.
            var pkCol       = pk + '[]';
            var barSelector = '#floating-actions-bar-' + gridId;
            var gridSelector = '#' + gridId;
            // Unique per-grid event namespace so multiple grids don't interfere.
            var ns = '.floatingActions.' + gridId.replace(/-/g, '_');
            // ---- Checkbox changes (delegated from document) -----------------
            // Row checkboxes
            $(document).on('change' + ns, gridSelector + ' input[name="' + pkCol + '"]', function () {
                _updateBar(gridId, pk);
            });
            // Header "select all" checkbox - rows are toggled programmatically,
            // so we sync after a short timeout.
            $(document).on('change' + ns, gridSelector + ' input[type="checkbox"][id$="_all"]', function () {
                setTimeout(function () { _updateBar(gridId, pk); }, 50);
            });
            // ---- Bar controls (delegated from document) ---------------------
            // Close / deselect-all: clear cross-page store AND uncheck visible checkboxes
            $(document).on('click' + ns, barSelector + ' .floating-actions-close', function () {
                // Clear cross-page selection store and update UI (header checkbox, massive action button)
                if (window.LS && LS.gridSelection && typeof LS.gridSelection.clear === 'function') {
                    LS.gridSelection.clear(gridId);
                }
                // Uncheck visible checkboxes on the current page
                $('#' + gridId + ' tbody .massiveActionsCheckbox').prop('checked', false);
                $('#' + gridId + ' thead input[type="checkbox"]').prop('checked', false);
                _updateBar(gridId, pk);
            });
            // Direct action buttons
            $(document).on('click' + ns,
                barSelector + ' .floating-actions-btn:not(.dropdown-toggle)',
                function (e) { _onClick.call(this, e, gridId, pk); }
            );
            // Dropdown sub-items
            $(document).on('click' + ns,
                barSelector + ' .floating-actions-item',
                function (e) { _onClick.call(this, e, gridId, pk); }
            );
            // ---- Scroll: keep bar position in sync with table position ---------
            $(window).on('scroll' + ns, function () {
                _syncBarPosition(gridId);
            });
            // ---- Initial state ---------------------------------------------
            _updateBar(gridId, pk);
            _syncBarPosition(gridId);
        },
        /** Manually refresh the bar state (e.g. after an external grid update). */
        updateBar: _updateBar,
        /**
         * Re-ensure the bar is in the DOM and refresh the selection count.
         * Called explicitly by CLSGridView after every AJAX pagination update.
         * Since the bar now lives in <body> (not inside the grid), this is mainly
         * a count refresh, but _injectBar also re-attaches if somehow removed.
         *
         * @param {string} gridId
         */
        refresh: function (gridId) {
            if (!_barRefs[gridId]) { return; }
            _injectBar(gridId);
            _updateBar(gridId, _barRefs[gridId].data('pk'));
        },
    };
}());
