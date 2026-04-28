LS.actionDropdown = {
    DropdownClass: class extends bootstrap.Dropdown {
        _getMenuElement() {
            return this._config.lsMenuElement;
        }
    },
    create: function () {
        'use strict';
        this.removeOrphanedDropdowns();
        let dropdownElementList = [].slice.call(
            document.querySelectorAll('.ls-dropdown-toggle')
        );
        let body = document.querySelector('body');
        dropdownElementList.map(function (dropdownToggleEl) {
            // Don't process the element if it already has the 'data-ls-dropdown-toggle-id' attribute
            if (dropdownToggleEl.hasAttribute('data-ls-dropdown-toggle-id')) {
                return;
            }
            // Generate random ID to link toggle and menu
            let dropdownToggleId = Math.random().toString(36).substring(2, 12);
            dropdownToggleEl.setAttribute('data-ls-dropdown-toggle-id', dropdownToggleId);
            let dropdownMenu = dropdownToggleEl.nextElementSibling;
            if (dropdownMenu !== null) {
                dropdownMenu.setAttribute('data-for-ls-dropdown-toggle-id', dropdownToggleId);
                let dropdownInstance = new LS.actionDropdown.DropdownClass(dropdownToggleEl, {
                    lsMenuElement: dropdownMenu,
                    boundary: body,
                    popperConfig: {
                        strategy: 'fixed',
                    },
                });
                body.append(dropdownMenu);

                // ✅ Add focus trap logic
                dropdownToggleEl.addEventListener('shown.bs.dropdown', function () {
                    trapFocus(dropdownMenu, dropdownToggleEl, dropdownInstance);
                });

                dropdownToggleEl.addEventListener('hidden.bs.dropdown', function () {
                    LS.actionDropdown.releaseFocusTrap(dropdownMenu);
                });
            }
        });

        // ✅ Focus Trap Helpers
        function trapFocus(container, toggleButton, dropdownInstance) {
            let focusableSelectors = 'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
            let focusableEls = Array.from(container.querySelectorAll(focusableSelectors));
            if (focusableEls.length === 0) return;

            let firstEl = focusableEls[0];
            let lastEl = focusableEls[focusableEls.length - 1];

            // Tab trap on the container (bubble phase is fine for Tab)
            container._tabHandler = function (e) {
                if (e.key === 'Tab') {
                    if (e.shiftKey) {
                        if (document.activeElement === firstEl) {
                            e.preventDefault();
                            lastEl.focus();
                        }
                    } else {
                        if (document.activeElement === lastEl) {
                            e.preventDefault();
                            firstEl.focus();
                        }
                    }
                }
            };

            // Escape handler on document capture phase — fires before Bootstrap's delegated handler
            container._escHandler = function (e) {
                if (e.key === 'Escape' || e.key === 'Esc') {
                    // Only handle if the event target is inside this container
                    if (container.contains(e.target)) {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        if (dropdownInstance) {
                            dropdownInstance.hide();
                        }
                        if (toggleButton) {
                            toggleButton.focus();
                        }
                    }
                }
            };

            container.addEventListener('keydown', container._tabHandler);
            document.addEventListener('keydown', container._escHandler, true);
            firstEl.focus();
        }
    },

    /**
     * Cleans up focus-trap listeners (Tab on container, Escape on document) for a given menu element.
     */
    releaseFocusTrap: function (container) {
        if (container._tabHandler) {
            container.removeEventListener('keydown', container._tabHandler);
            delete container._tabHandler;
        }
        if (container._escHandler) {
            document.removeEventListener('keydown', container._escHandler, true);
            delete container._escHandler;
        }
    },

    /**
     * Removes dropdown menus that no longer have a toggle element (i.e. the toggle was in a table row
     * and the table was filtered or sorted).
     * This is limited to dropdown menus handled by LS.actionDropdown.create().
     */
    removeOrphanedDropdowns: function () {
        document.querySelectorAll('.dropdown-menu').forEach(function (menu) {
            // If the menu doesn't have a 'data-for-ls-dropdown-toggle-id' attribute, it's not
            // a dropdown menu created by LS.actionDropdown.create(), so we can remove it.
            if (!menu.hasAttribute('data-for-ls-dropdown-toggle-id')) {
                return;
            }
            const toggleId = menu.getAttribute('data-for-ls-dropdown-toggle-id');
            // If the toggle doesn't exist, remove the menu.
            if (!document.querySelector(`.ls-dropdown-toggle[data-ls-dropdown-toggle-id="${toggleId}"]`)) {
                LS.actionDropdown.releaseFocusTrap(menu);
                menu.remove();
            }
        });
    },
};

LS.actionDropdown.create();