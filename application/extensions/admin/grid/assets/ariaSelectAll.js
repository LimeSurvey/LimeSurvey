(function ($) {
    'use strict';

    /**
     * Apply the translated "Select all" aria-label to every header checkbox in
     * all .grid-view-ls grids on the page.
     *
     * The label is read from the grid container's data-select-all-label attribute,
     * which is set server-side via CLSGridView::init() so the translation is
     * applied correctly for each locale.
     */
    function applySelectAllLabels() {
        $('.grid-view-ls').each(function () {
            var label = $(this).data('select-all-label');
            if (label) {
                $(this).find('input[type="checkbox"][id$="_all"]').attr('aria-label', label);
            }
        });
    }

    $(document).ready(applySelectAllLabels);
    $(document).ajaxComplete(applySelectAllLabels);
})(jQuery);
