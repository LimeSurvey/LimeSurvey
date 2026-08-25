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
 *
 * Keeps aria-expanded and aria-controls on the combobox in sync when the
 * dropdown opens/closes so screen readers announce the correct state.
 */
(function ($) {
    function getLabelledBy($select) {
        var ariaLabelledBy = $select.attr('aria-labelledby');
        if (ariaLabelledBy) {
            return ariaLabelledBy;
        }

        var selectId = $select.attr('id');
        if (!selectId) {
            return '';
        }

        var $label = $('label[for="' + selectId.replace(/"/g, '\\"') + '"]');
        if (!$label.length) {
            return '';
        }

        var labelId = $label.attr('id');
        if (!labelId) {
            labelId = selectId + '-label';
            $label.attr('id', labelId);
        }

        return labelId;
    }

    function getLabel($select) {
        return $select.attr('aria-label') || $select.attr('name') || '';
    }

    function mergeIdTokens(existing, additional) {
        var tokens = (existing || '').split(/\s+/).filter(Boolean);
        (additional || '').split(/\s+/).filter(Boolean).forEach(function (token) {
            if (tokens.indexOf(token) === -1) {
                tokens.push(token);
            }
        });
        return tokens.join(' ');
    }

    function getResultsId($select) {
        var selectId = $select.attr('id');
        if (!selectId) {
            return null;
        }

        return 'select2-' + selectId + '-results';
    }

    function patchCombobox($select) {
        var ariaLabelledBy = getLabelledBy($select);
        var ariaLabel = getLabel($select);
        if (!ariaLabelledBy && !ariaLabel) {
            return;
        }

        var $combobox = $select.next('.select2-container').find('[role="combobox"]');
        if (!$combobox.length) {
            return;
        }

        if (ariaLabelledBy) {
            var merged = mergeIdTokens($combobox.attr('aria-labelledby'), ariaLabelledBy);
            if (merged) {
                $combobox.attr('aria-labelledby', merged);
            }
        } else {
            $combobox.attr('aria-label', ariaLabel);
        }
    }

    function patchSearchField($select) {
        var ariaLabelledBy = getLabelledBy($select);
        var ariaLabel = getLabel($select);
        var $searchField = $('.select2-search--dropdown .select2-search__field');
        if (!$searchField.length) {
            return;
        }

        if (ariaLabelledBy) {
            $searchField.attr('aria-labelledby', ariaLabelledBy);
            $searchField.removeAttr('aria-label');
        } else if (ariaLabel) {
            $searchField.attr('aria-label', ariaLabel);
            $searchField.removeAttr('aria-labelledby');
        }
    }

    function syncComboboxExpandedState($select, isOpen) {
        var $combobox = $select.next('.select2-container').find('[role="combobox"]');
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

    function patchSelect2Container($container) {
        var $select = $container.prev('select');
        if ($select.length) {
            patchCombobox($select);
        }
    }

    // Patch combobox aria-labelledby whenever a Select2 container is inserted into the DOM
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            Array.prototype.forEach.call(mutation.addedNodes, function (node) {
                if (node.nodeType === 1 && $(node).hasClass('select2-container')) {
                    patchSelect2Container($(node));
                }
            });
        });
    });
    observer.observe(document.body, {childList: true, subtree: true});

    // Patch any select2 containers that already exist when the script loads
    $('.select2-container').each(function () {
        patchSelect2Container($(this));
    });

    // On open, patch the search field and sync expanded state
    $(document).on('select2:open', function (e) {
        var $select = $(e.target);
        patchSearchField($select);
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
