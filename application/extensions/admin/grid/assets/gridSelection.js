/**
 * LS.gridSelection
 *
 * Tracks checkbox selections across AJAX pagination for ALL CLSGridViews.
 *
 * How it works:
 *  - A delegated change-event on `.grid-view-ls` catches every checkbox toggle,
 *    regardless of which grid or page the user is on.
 *  - The selected PKs (= checkbox values set by Yii's CCheckBoxColumn) are kept
 *    per grid-id in an in-memory Map.
 *  - After each AJAX page update CLSGridView calls restoreCheckboxes(gridId),
 *    which re-checks every checkbox whose value is in the stored Set.
 *  - MassiveActionsWidget's listActions.js calls getAll(gridId) instead of
 *    yiiGridView('getChecked') so it receives IDs from ALL pages, not just the
 *    currently visible one.
 *
 * No grid-specific configuration required – the grid-id is read from the DOM.
 */
var LS = LS || {};

LS.gridSelection = (function () {
    'use strict';

    /**
     * Storage: gridId (string) -> Set of selected PK values (strings)
     * @type {Map<string, Set<string>>}
     */
    var _store = new Map();

    /**
     * Confirmed deleted rows: gridId -> Set of PK values to remove from
     * the selection store on the next restoreCheckboxes() call.
     * Populated by markRowDeleted() on delete success; consumed and cleared in restoreCheckboxes().
     * @type {Map<string, Set<string>>}
     */
    var _deletedRows = new Map();

    /**
     * Grids currently in "select all" mode: the whole result set (all pages) is selected.
     * @type {Set<string>}
     */
    var _selectAllMode = new Set();

    /**
     * Returns (and lazily creates) the Set for a given gridId.
     * @param  {string} gridId
     * @return {Set<string>}
     */
    function _set(gridId) {
        if (!_store.has(gridId)) {
            _store.set(gridId, new Set());
        }
        return _store.get(gridId);
    }

    /**
     * Derives the grid-id by traversing from a checkbox element up to
     * the nearest `.grid-view-ls` container.
     * @param  {HTMLElement|jQuery} checkbox
     * @return {string|null}
     */
    function _gridIdFromCheckbox(checkbox) {
        var $grid = $(checkbox).closest('.grid-view-ls');
        return $grid.length ? $grid.attr('id') : null;
    }

    /**
     * Updates the selection info bar below the grid.
     * The bar shows the total number of selected PKs (across all pages) and a
     * "Deselect all" button. It is hidden when nothing is selected.
     * @param {string} gridId
     */
    function _syncSelectionBar(gridId) {
        var count = _set(gridId).size;
        // The bar element is rendered by CLSGridView's template.php with data-grid-id
        var $bar = $('.grid-selection-bar[data-grid-id="' + gridId + '"]');
        if (!$bar.length) { return; }

        if (_selectAllMode.has(gridId)) {
            var total = parseInt($bar.attr('data-total-count'), 10);
            $bar.find('.grid-selection-count').text(isNaN(total) ? count : total);
            $bar.show();
        } else if (count > 0) {
            $bar.find('.grid-selection-count').text(count);
            $bar.show();
        } else {
            $bar.find('.grid-selection-count').text(0);
            $bar.hide();
        }
    }

    // ------------------------------------------------------------------
    // Clear selection when a filter changes.
    // A filter change means a new result set – previously selected PKs that
    // are no longer part of the filtered results must not stay selected.
    // This handler fires before the grid's own AJAX-update is triggered,
    // so the store is already empty when restoreCheckboxes() runs afterwards.
    // Add keydown to the event listener and filter for Enter key only
    // to match Yii's core grid filter behavior.
    // ------------------------------------------------------------------
    $(document).on(
        'change keydown',
        '.grid-view-ls .filters input, .grid-view-ls .filters select',
        function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.keyCode !== 13) {
                return;
            }
            var gridId = $(this).closest('.grid-view-ls').attr('id');
            if (!gridId) { return; }
            _store.set(gridId, new Set());
            _selectAllMode.delete(gridId);
            _syncSelectionBar(gridId);
            _syncMassiveActionButton(gridId);
        }
    );

    // ------------------------------------------------------------------
    // "Deselect all" button – delegated so it survives AJAX replacements
    // ------------------------------------------------------------------
    $(document).on('click', '.grid-selection-bar .grid-deselect-all', function () {
        var gridId = $(this).closest('.grid-selection-bar').data('grid-id');
        if (!gridId) { return; }

        // Clear internal store
        _store.set(gridId, new Set());
        _selectAllMode.delete(gridId);

        // Uncheck only row-selection checkboxes (tbody) via .massiveActionsCheckbox and the header checkbox.
        // Using the specific class avoids accidentally clearing unrelated controls inside the grid container.
        $('#' + gridId + ' tbody .massiveActionsCheckbox').prop('checked', false);
        $('#' + gridId + ' thead input[type="checkbox"]').prop('checked', false);

        _syncSelectionBar(gridId);
        _syncMassiveActionButton(gridId);
    });

    // ------------------------------------------------------------------
    // "Select all" button selects the entire result set (all pages).
    // ------------------------------------------------------------------
    $(document).on('click', '.grid-selection-bar .grid-select-all', function () {
        var gridId = $(this).closest('.grid-selection-bar').data('grid-id');
        if (!gridId) { return; }

        _selectAllMode.add(gridId);

        // Check all visible rows and keep them in the store so that leaving
        // select-all mode (by unchecking a row) falls back to a sane selection.
        $('#' + gridId + ' tbody .massiveActionsCheckbox').each(function () {
            if (!$(this).is(':disabled')) {
                $(this).prop('checked', true);
                _set(gridId).add(String($(this).val()));
            }
        });
        $('#' + gridId + ' thead input[type="checkbox"]').prop('checked', true);

        _syncSelectionBar(gridId);
        _syncMassiveActionButton(gridId);
    });


    // ------------------------------------------------------------------
    // Delegated change handler for the header "Select all" checkbox.
    // Yii sets tbody checkboxes via .prop() (no change event fired), so we
    // must sync the store here by iterating all visible tbody checkboxes.
    // ------------------------------------------------------------------
    $(document).on('change', '.grid-view-ls thead input[type="checkbox"]', function () {
        var gridId = _gridIdFromCheckbox(this);
        if (!gridId) { return; }

        var isChecked = $(this).is(':checked');
        if (!isChecked) {
            _selectAllMode.delete(gridId);
        }

        $('#' + gridId + ' tbody .massiveActionsCheckbox').each(function () {
            var pk = String($(this).val());
            if (isChecked && !$(this).is(':disabled')) {
                _set(gridId).add(pk);
            } else {
                _set(gridId).delete(pk);
            }
        });

        _syncMassiveActionButton(gridId);
        _syncSelectionBar(gridId);
    });

    // ------------------------------------------------------------------
    // Delegated change handler – active for the lifetime of the page,
    // survives AJAX grid replacements because it is bound to `document`.
    // Only tracks tbody checkboxes (not the "select all" header checkbox).
    // ------------------------------------------------------------------
    $(document).on('change', '.grid-view-ls tbody .massiveActionsCheckbox', function () {
        var gridId = _gridIdFromCheckbox(this);
        if (!gridId) { return; }

        var pk = String($(this).val());
        if ($(this).is(':checked')) {
            _set(gridId).add(pk);
        } else {
            _set(gridId).delete(pk);
            _selectAllMode.delete(gridId);
        }

        _syncHeaderCheckbox(gridId);
        _syncMassiveActionButton(gridId);
        _syncSelectionBar(gridId);
    });

    /**
     * Keeps the header "select all" checkbox in sync with the current page.
     * The header is checked only when every tbody row on the current page
     * is present in the store (regardless of how many pages are selected overall).
     * @param {string} gridId
     */
    function _syncHeaderCheckbox(gridId) {
        var $grid    = $('#' + gridId);
        var $tbodyCb = $grid.find('tbody .massiveActionsCheckbox');
        var total    = $tbodyCb.length;

        if (total === 0) {
            $grid.find('thead input[type="checkbox"]').prop('checked', false);
            return;
        }

        var stored  = _set(gridId);
        var allInStore = true;
        $tbodyCb.each(function () {
            if (!stored.has(String($(this).val()))) {
                allInStore = false;
                return false; // break
            }
        });

        $grid.find('thead input[type="checkbox"]').prop('checked', allInStore);
    }

    /**
     * Enables / disables the massive-action button for a grid.
     * Falls back to the generic switchStatusOfListActions behaviour when no
     * grid-specific button can be found.
     * @param {string} gridId
     */
    function _syncMassiveActionButton(gridId) {
        // Look for the listActions widget that belongs to this grid
        var $btn = $('[data-grid-id="' + gridId + '"]').find('.massiveAction');
        if (!$btn.length) {
            // Fallback: single massive-action button on the page
            $btn = $('.massiveAction');
        }
        if (_set(gridId).size > 0 || _selectAllMode.has(gridId)) {
            $btn.removeClass('disabled').removeAttr('disabled');
        } else {
            $btn.addClass('disabled').attr('disabled', 'disabled');
        }
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------
    return {
        /**
         * Restores checkboxes on the newly rendered page after an AJAX update.
         * Called by CLSGridView's afterAjaxUpdate for every grid automatically.
         *
         * @param {string} gridId  – the HTML id of the CLSGridView container
         */
        restoreCheckboxes: function (gridId) {
            // In select-all mode every row of the new page is selected by definition.
            if (_selectAllMode.has(gridId)) {
                $('#' + gridId + ' tbody .massiveActionsCheckbox').each(function () {
                    if (!$(this).is(':disabled')) {
                        $(this).prop('checked', true);
                        _set(gridId).add(String($(this).val()));
                    }
                });
                $('#' + gridId + ' thead input[type="checkbox"]').prop('checked', true);
                _syncMassiveActionButton(gridId);
                _syncSelectionBar(gridId);
                return;
            }

            var stored = _set(gridId);

            // Nothing selected: skip reconcile and restore entirely.
            if (stored.size === 0) { return; }

            // Remove confirmed deleted rows from the store (registered via markRowDeleted()).
            var deleted = _deletedRows.get(gridId);
            if (deleted && deleted.size > 0) {
                deleted.forEach(function (pkValue) {
                    stored.delete(pkValue);
                });
                _deletedRows.delete(gridId);
            }

            if (stored.size === 0) {
                _syncSelectionBar(gridId);
                _syncMassiveActionButton(gridId);
                return;
            }

            $('#' + gridId + ' tbody .massiveActionsCheckbox').each(function () {
                if (stored.has(String($(this).val())) && !$(this).is(':disabled')) {
                    $(this).prop('checked', true);
                }
            });

            _syncHeaderCheckbox(gridId);
            _syncMassiveActionButton(gridId);
            _syncSelectionBar(gridId);
        },

        /**
         * Marks a single row as deleted so that restoreCheckboxes() will remove
         * it from the selection store on the next grid refresh.
         * Call this from the delete-action success callback, before or after
         * yiiGridView.update() — both orderings are safe since the deleted set
         * is only consumed when the AJAX response arrives.
         *
         * @param {string} gridId   The HTML id of the CLSGridView container.
         * @param {string} pkValue  The PK value of the deleted row (checkbox value).
         */
        markRowDeleted: function (gridId, pkValue) {
            if (!_deletedRows.has(gridId)) {
                _deletedRows.set(gridId, new Set());
            }
            _deletedRows.get(gridId).add(String(pkValue));
        },

        /**
         * Returns all selected PKs for a grid as an array (all pages).
         * Use this instead of yiiGridView('getChecked') in listActions.js.
         *
         * @param  {string} gridId
         * @return {string[]}
         */
        getAll: function (gridId) {
            // Select-all mode: no ids are sent, the action posts a selectAll flag instead
            if (_selectAllMode.has(gridId)) {
                return [];
            }
            return Array.from(_set(gridId));
        },

        /**
         * Whether the grid is in "select all" mode (whole result set selected).
         *
         * @param  {string} gridId
         * @return {boolean}
         */
        isSelectAll: function (gridId) {
            return _selectAllMode.has(gridId);
        },

        /**
         * Serialized filter inputs of the grid, posted along with "select all"
         * massive actions so the backend can apply the same filters.
         *
         * @param  {string} gridId
         * @return {string}
         */
        getFilterQuery: function (gridId) {
            return $('#' + gridId + ' .filters :input').serialize();
        },

        /**
         * Clears the selection for a grid.
         * Call this after a massive action has been executed.
         *
         * @param {string} gridId
         */
        clear: function (gridId) {
            _store.set(gridId, new Set());
            _selectAllMode.delete(gridId);
            _syncHeaderCheckbox(gridId);
            _syncMassiveActionButton(gridId);
            _syncSelectionBar(gridId);
        },

        /**
         * Returns the number of selected items for a grid.
         *
         * @param  {string} gridId
         * @return {number}
         */
        count: function (gridId) {
            return _set(gridId).size;
        }
    };
}());

