/**
 * Accessibility enhancement for Select2 widgets.
 *
 * When a Select2 dropdown opens, copies the aria-labelledby attribute from the
 * original <select> element to the generated search input so that screen readers
 * announce the correct label when the search field receives focus.
 */
(function ($) {
    $(document).on('select2:open', function (e) {
            var $select = $(e.target);
            var ariaLabelledBy = $select.attr('aria-labelledby');
            if (ariaLabelledBy) {
                var $container = $select.next('.select2-container');
                $container.find('.select2-search__field').attr('aria-labelledby', ariaLabelledBy);
            }
        }
    );
}(jQuery));
