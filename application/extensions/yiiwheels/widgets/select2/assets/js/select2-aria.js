/**
 * Accessibility enhancement for Select2 widgets.
 *
 * Uses a MutationObserver to detect when Select2 containers are added to the DOM.
 * When a new container appears next to a <select> with aria-labelledby, that label
 * reference is prepended to the combobox aria-labelledby so screen readers
 * announce the correct label in all states (before and after opening).
 *
 * On select2:open, the same label reference is also copied to the search field
 * inside the dropdown.
 */
(function ($) {
    function patchCombobox($select) {
        var ariaLabelledBy = $select.attr('aria-labelledby');
        if (!ariaLabelledBy) {
            return;
        }
        var $combobox = $select.next('.select2-container').find('[role="combobox"]');
        var existing = $combobox.attr('aria-labelledby') || '';
        var existingTokens = existing.split(/\s+/).filter(Boolean);
        if (existingTokens.indexOf(ariaLabelledBy) === -1) {
            $combobox.attr('aria-labelledby', ariaLabelledBy + ' ' + existing);
        }
    }

    // Patch combobox aria-labelledby whenever a Select2 container is inserted into the DOM
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1 && $(node).hasClass('select2-container')) {
                    patchCombobox($(node).prev('select'));
                }
            });
        });
    });
    observer.observe(document.body, {childList: true, subtree: true});

    // On open, also patch the search field inside the dropdown
    $(document).on('select2:open', function (e) {
        var $select = $(e.target);
        var ariaLabelledBy = $select.attr('aria-labelledby');
        if (ariaLabelledBy) {
            $select.next('.select2-container').find('.select2-search__field').attr('aria-labelledby', ariaLabelledBy);
        }
    });
}(jQuery));