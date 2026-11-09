/**
 * Keep the keyboard focus on a grid filter field after an AJAX grid update.
 *
 * The update replaces the filter row, so without this the focus is lost after each change.
 * With a closed dropdown filter, each arrow key changes the value (and updates the grid),
 * so the user could not continue to choose an option with the keyboard.
 *
 * The last focused filter field is tracked per grid. After the update, the focus is only
 * restored when it got lost (the focused element was removed with the old filter row), so a
 * user who moved on (e.g. tabbed from a text filter to the next field) is not pulled back.
 * Called from LS.gridView.afterAjaxUpdate (see afterAjaxUpdate.js).
 */
(function ($) {
    'use strict';

    // The filter field that last had the focus: {gridId, cellIndex, name}, or null
    var lastFilterFocus = null;

    $(document).on('focusin', function (e) {
        var $cell = $(e.target).closest('.grid-view-ls tr.filters > *');
        if (!$cell.length) {
            lastFilterFocus = null;
            return;
        }
        lastFilterFocus = {
            gridId: $cell.closest('.grid-view-ls').attr('id'),
            cellIndex: $cell.index(),
            name: e.target.name || ''
        };
    });

    // A click elsewhere in the grid (e.g. the summary, or a link that does not take the focus in Safari) means
    // the user left the filter, even if no other element got the focus
    $(document).on('mousedown', '.grid-view-ls', function (e) {
        if (!$(e.target).closest('tr.filters').length) {
            lastFilterFocus = null;
        }
    });

    /**
     * Restore the focus to the filter field that had it before the grid update replaced the filter row.
     *
     * @param {string} gridId The DOM id of the updated grid
     * @return {boolean} True if the focus was moved to a filter field
     */
    function restoreFilterFocus(gridId) {
        var active = document.activeElement;
        if (!lastFilterFocus || lastFilterFocus.gridId !== gridId) {
            return false;
        }
        // The focus is still on an element on the page: The user moved on, or another restore already ran
        if (active && active !== document.body && document.contains(active)) {
            return false;
        }
        var $cell = $('#' + gridId + ' tr.filters').first().children().eq(lastFilterFocus.cellIndex);
        // Skip hidden elements of widgets (e.g. the native select behind select2), so the visible control gets the focus
        var $candidates = $cell.find('input, select, textarea, button, [tabindex]').filter(function () {
            return this.type !== 'hidden'
                && this.getAttribute('tabindex') !== '-1'
                && this.getAttribute('aria-hidden') !== 'true'
                && $(this).is(':visible');
        });
        var name = lastFilterFocus.name;
        var $field = name ? $candidates.filter(function () {
            return this.name === name;
        }) : $();
        if (!$field.length) {
            $field = $candidates;
        }
        if (!$field.length) {
            return false;
        }
        $field[0].focus({preventScroll: true});
        return document.activeElement === $field[0];
    }

    window.LS = window.LS || {};
    LS.gridView = LS.gridView || {};
    LS.gridView.restoreFilterFocus = restoreFilterFocus;
})(jQuery);
