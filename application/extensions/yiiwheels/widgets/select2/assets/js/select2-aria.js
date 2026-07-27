/**
 * Accessibility enhancement for Select2 widgets.
 *
 * - Copies aria-labelledby from the hidden <select> onto the combobox so the
 *   visible label is announced (required because Select2 replaces the native select).
 * - Keeps aria-expanded and aria-controls on the combobox in sync when the
 *   dropdown opens/closes so screen readers announce the correct state.
 * - Replaces the generic "Search" aria-label on inline search fields when a
 *   visible label reference is available.
 */
(function ($) {
    function getContainer($select) {
        return $select.next('.select2-container');
    }

    function getCombobox($select) {
        return getContainer($select).find('[role="combobox"]');
    }

    function getResultsId($select) {
        var selectId = $select.attr('id');
        if (!selectId) {
            return null;
        }

        return 'select2-' + selectId + '-results';
    }

    function patchComboboxLabel($select) {
        var ariaLabelledBy = $select.attr('aria-labelledby');
        if (!ariaLabelledBy) {
            return;
        }

        var $combobox = getCombobox($select);
        if (!$combobox.length) {
            return;
        }

        var existing = $combobox.attr('aria-labelledby') || '';
        var existingTokens = existing.split(/\s+/).filter(Boolean);
        if (existingTokens.indexOf(ariaLabelledBy) === -1) {
            $combobox.attr('aria-labelledby', (ariaLabelledBy + ' ' + existing).trim());
        }
    }

    function patchSearchFieldLabel($select) {
        var ariaLabelledBy = $select.attr('aria-labelledby');
        if (!ariaLabelledBy) {
            return;
        }

        var $searchField = getContainer($select).find('.select2-search__field');
        if (!$searchField.length) {
            return;
        }

        $searchField.attr('aria-labelledby', ariaLabelledBy);
        $searchField.removeAttr('aria-label');
    }

    function syncComboboxExpandedState($select, isOpen) {
        var $combobox = getCombobox($select);
        if (!$combobox.length) {
            return;
        }

        $combobox.attr('aria-expanded', isOpen ? 'true' : 'false');

        var resultsId = getResultsId($select);
        if (!resultsId) {
            return;
        }

        if (isOpen) {
            $combobox.attr('aria-controls', resultsId);
        } else {
            $combobox.removeAttr('aria-controls');
        }
    }

    function patchSelect2Accessibility($select) {
        patchComboboxLabel($select);
        patchSearchFieldLabel($select);
        syncComboboxExpandedState(
            $select,
            getContainer($select).hasClass('select2-container--open')
        );
    }

    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1 && $(node).hasClass('select2-container')) {
                    patchSelect2Accessibility($(node).prev('select'));
                }
            });
        });
    });
    observer.observe(document.body, {childList: true, subtree: true});

    $(document).on('select2:open', function (e) {
        var $select = $(e.target);

        patchComboboxLabel($select);
        patchSearchFieldLabel($select);
        syncComboboxExpandedState($select, true);

        // Re-assert after Select2 moves focus to the inline search field.
        window.setTimeout(function () {
            syncComboboxExpandedState($select, true);
        }, 0);
    });

    $(document).on('select2:close', function (e) {
        syncComboboxExpandedState($(e.target), false);
    });
}(jQuery));
