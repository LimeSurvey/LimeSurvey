/**
 * Keep the keyboard focus on a grid filter field after the AJAX grid update it triggered.
 *
 * The update replaces the filter row, so without this the focus is lost after each change.
 * With a closed dropdown filter, each arrow key changes the value (and updates the grid),
 * so the user could not continue to choose an option with the keyboard.
 * An empty result keeps the focus on the "no results" message (see afterAjaxUpdate.js).
 */
(function ($) {
    'use strict';

    // gridId → {name, element} of the filter field that triggered the update
    var pendingFocus = {};

    $(document).on('change keydown', '.grid-view-ls .filters input, .grid-view-ls .filters select', function (e) {
        if (e.type === 'keydown' && e.key !== 'Enter') {
            return;
        }
        var gridId = $(this).closest('.grid-view-ls').attr('id');
        if (gridId && this.name) {
            pendingFocus[gridId] = {name: this.name, element: this};
        }
    });

    $(document).ajaxComplete(function () {
        $.each(pendingFocus, function (gridId, pending) {
            var $field = $('#' + gridId + ' .filters').find('[name="' + pending.name + '"]');
            // Not updated yet: the filter row still holds the same field
            if (!$field.length || $field[0] === pending.element) {
                return;
            }
            delete pendingFocus[gridId];
            if (document.getElementById(gridId + '-empty-message')) {
                return;
            }
            $field[0].focus({preventScroll: true});
        });
    });
})(jQuery);
