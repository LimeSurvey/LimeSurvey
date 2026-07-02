/**
 * Floating Actions Widget – JavaScript
 *
 * Provides LS.floatingActions:
 *   - Shows a context bar with action buttons just above the pagination row of
 *     a CLSGridView whenever one or more row-checkboxes are checked.
 *   - The bar is injected into the grid DOM just before .grid-view-ls-footer and
 *     re-injected automatically after every AJAX grid update so that it always
 *     sits above the pager regardless of page changes.
 *
 * Cross-page selection tracking is intentionally NOT implemented here;
 * it will be added in a separate branch.
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
     * Count checked row-checkboxes in the current page and refresh the bar.
     * @param {string} gridId
     * @param {string} pk  Primary key column name (without [])
     */
    function _updateBar(gridId, pk) {
        var $bar = _bar(gridId);
        if (!$bar.length) { return; }

        var pkCol = (pk || $bar.data('pk')) + '[]';
        var count = $('#' + gridId)
            .find('.table tbody input[name="' + pkCol + '"]:checked').length;

        $bar.find('.floating-actions-count-number').text(count);

        if (count > 0) {
            $bar.addClass('floating-actions-bar--visible');
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
     * (Re-)inject the bar directly before .grid-view-ls-footer inside the grid
     * container.  The element reference is stable even when the grid replaces its
     * own innerHTML via AJAX, so this works both on first init and after updates.
     *
     * @param {string} gridId
     */
    function _injectBar(gridId) {
        var $bar    = _bar(gridId);
        var $footer = $('#' + gridId).find('.grid-view-ls-footer');
        if ($bar.length && $footer.length) {
            $footer.before($bar);
        }
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

        // Get selected items from the current page only
        var checkedItems     = $grid.yiiGridView('getChecked', pk);
        var checkedItemsJson = JSON.stringify(checkedItems);

        // ---- redirect action ------------------------------------------
        if (actionType === 'redirect') {
            var newForm = $('<form>', {
                action : actionUrl,
                target : $that.data('target') || '_blank',
                method : 'POST',
            }).append($('<input>', {
                name  : $that.data('input-name'),
                value : checkedItems.join($that.data('input-separator') || '|'),
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
                $grid.yiiGridView('update');
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
                        /* eslint-disable-next-line no-eval */
                        var func = eval(onSuccess);
                        func(html);
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
    // Extend LS.gridView.afterAjaxUpdate
    // Re-inject the bar and refresh its state after each AJAX grid update.
    // -------------------------------------------------------------------------
    (function () {
        var _orig = LS.gridView.afterAjaxUpdate;
        LS.gridView.afterAjaxUpdate = function (id, data) {
            if (_orig) { _orig.call(this, id, data); }

            if (!_barRefs[id]) { return; }

            // The grid's innerHTML was just replaced; re-inject bar above pager
            _injectBar(id);

            // Refresh visibility / count from the freshly rendered checkboxes
            _updateBar(id, _barRefs[id].data('pk'));
        };
    }());

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

            var pkCol = pk + '[]';
            var barSelector = '#floating-actions-bar-' + gridId;

            // ---- Checkbox changes (delegate from stable grid element) -------

            // Row checkboxes
            $grid.on('change.floatingActions', 'input[name="' + pkCol + '"]', function () {
                _updateBar(gridId, pk);
            });

            // Header "select all" checkbox – rows are set programmatically,
            // so we sync after a small timeout
            $grid.on('change.floatingActions', 'input[type="checkbox"][id$="_all"]', function () {
                setTimeout(function () { _updateBar(gridId, pk); }, 50);
            });

            // ---- Bar controls (delegate from stable grid element) -----------

            // Close / deselect-all
            $grid.on('click.floatingActions', barSelector + ' .floating-actions-close',
                function () {
                    // Uncheck all visible row checkboxes
                    $('#' + gridId)
                        .find('.table tbody input[name="' + pkCol + '"]:checked')
                        .prop('checked', false);
                    // Also uncheck the header "select all" if present
                    $('#' + gridId)
                        .find('input[type="checkbox"][id$="_all"]')
                        .prop('checked', false);
                    _updateBar(gridId, pk);
                }
            );

            // Direct action buttons
            $grid.on('click.floatingActions',
                barSelector + ' .floating-actions-btn:not(.dropdown-toggle)',
                function (e) { _onClick.call(this, e, gridId, pk); }
            );

            // Dropdown sub-items
            $grid.on('click.floatingActions',
                barSelector + ' .floating-actions-item',
                function (e) { _onClick.call(this, e, gridId, pk); }
            );

            // ---- Initial state ---------------------------------------------
            _updateBar(gridId, pk);
        },

        /** Manually refresh the bar state (e.g. after an external grid update). */
        updateBar: _updateBar,
    };
}());


