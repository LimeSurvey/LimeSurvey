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

        if (count > 0) {
            var label = $bar.data('label') || 'rows selected';
            $bar.find('.grid-selection-count').text(count + ' ' + label);
            $bar.show();
        } else {
            $bar.hide();
        }
    }

    // ------------------------------------------------------------------
    // "Deselect all" button – delegated so it survives AJAX replacements
    // ------------------------------------------------------------------
    $(document).on('click', '.grid-selection-bar .grid-deselect-all', function () {
        var gridId = $(this).closest('.grid-selection-bar').data('grid-id');
        if (!gridId) { return; }

        // Clear internal store
        _store.set(gridId, new Set());

        // Uncheck all visible checkboxes in this grid (both header and body)
        $('#' + gridId + ' input[type="checkbox"]').prop('checked', false);

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

        $('#' + gridId + ' tbody input[type="checkbox"]').each(function () {
            var pk = String($(this).val());
            if (isChecked) {
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
    $(document).on('change', '.grid-view-ls tbody input[type="checkbox"]', function () {
        var gridId = _gridIdFromCheckbox(this);
        if (!gridId) { return; }

        var pk = String($(this).val());
        if ($(this).is(':checked')) {
            _set(gridId).add(pk);
        } else {
            _set(gridId).delete(pk);
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
        var $tbodyCb = $grid.find('tbody input[type="checkbox"]');
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
        if (_set(gridId).size > 0) {
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
            var stored = _set(gridId);
            if (stored.size === 0) { return; }

            $('#' + gridId + ' tbody input[type="checkbox"]').each(function () {
                if (stored.has(String($(this).val()))) {
                    $(this).prop('checked', true);
                }
            });

            _syncHeaderCheckbox(gridId);
            _syncMassiveActionButton(gridId);
            _syncSelectionBar(gridId);
        },

        /**
         * Returns all selected PKs for a grid as an array (all pages).
         * Use this instead of yiiGridView('getChecked') in listActions.js.
         *
         * @param  {string} gridId
         * @return {string[]}
         */
        getAll: function (gridId) {
            return Array.from(_set(gridId));
        },

        /**
         * Clears the selection for a grid.
         * Call this after a massive action has been executed.
         *
         * @param {string} gridId
         */
        clear: function (gridId) {
            _store.set(gridId, new Set());
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

