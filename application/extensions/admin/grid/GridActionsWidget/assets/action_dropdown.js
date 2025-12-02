LS.actionDropdown = {
    DropdownClass: class extends bootstrap.Dropdown {
        _getMenuElement()
        {
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
                new LS.actionDropdown.DropdownClass(dropdownToggleEl, {
                    lsMenuElement: dropdownMenu,
                    boundary: body,
                    popperConfig: {
                        strategy: 'fixed',
                    },
                });
                body.append(dropdownMenu);
            }
        });
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
                menu.remove();
            }
        });
    },
};

/**
* Submenu positioning:
* Opens submenus on the right by default; if there is not enough space
* on the right, they are switched to open on the left (.dropdown-submenu-left).
*/
(function() {
    'use strict';
    function adjustSubmenuPosition(dropdownElement) {
        if (!dropdownElement) {
            return;
        }

        const submenuItems = dropdownElement.querySelectorAll('.has-submenu');
        submenuItems.forEach(function(item) {
            const submenu = item.querySelector('.dropdown-submenu');
            const trigger = item.querySelector('[data-bs-toggle="dropdown-submenu"]');

            if (!submenu || !trigger) {
                return;
            }

            // Remove any existing listener to avoid duplicates
            trigger.removeEventListener('mouseenter', trigger._handleMouseEnter);

            // Define and store the handler
            trigger._handleMouseEnter = function() {
                // Reset before measuring so we use the default (right) position first
                submenu.classList.remove('dropdown-submenu-left');

                // Force a reflow to ensure the submenu is positioned
                void submenu.offsetHeight;

                // Small delay to ensure submenu is in the layout and can be measured
                setTimeout(function() {
                    const rect = submenu.getBoundingClientRect();
                    const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                    // If the submenu overflows the right edge, switch it to the left side
                    if (rect.right > viewportWidth) {
                        submenu.classList.add('dropdown-submenu-left');
                    }
                }, 10);
            };

            trigger.addEventListener('mouseenter', trigger._handleMouseEnter);
        });
    }


    function initSubmenuPositioningForAll() {
        const actionDropdowns = document.querySelectorAll('.ls-action_dropdown');

        actionDropdowns.forEach(function(wrapper) {
            const toggle = wrapper.querySelector('[data-bs-toggle="dropdown"]');
            if (!toggle) {
                return;
            }

            // When the main dropdown is shown, configure submenus
            wrapper.addEventListener('shown.bs.dropdown', function() {
                // Get the toggle ID to find the corresponding menu
                const toggleId = toggle.getAttribute('data-ls-dropdown-toggle-id');
                if (!toggleId) {
                    return;
                }

                // Find the dropdown menu that was moved to body
                const dropdownMenu = document.querySelector(`.dropdown-menu[data-for-ls-dropdown-toggle-id="${toggleId}"]`);

                if (dropdownMenu) {
                    adjustSubmenuPosition(dropdownMenu);
                }
            });

            // On window resize, if the dropdown is open, recalculate submenu positions
            window.addEventListener('resize', function() {
                const toggleId = toggle.getAttribute('data-ls-dropdown-toggle-id');
                if (!toggleId) {
                    return;
                }

                const dropdownMenu = document.querySelector(`.dropdown-menu[data-for-ls-dropdown-toggle-id="${toggleId}"].show`);
                if (dropdownMenu) {
                    adjustSubmenuPosition(dropdownMenu);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSubmenuPositioningForAll);
    } else {
        initSubmenuPositioningForAll();
    }
})();

LS.actionDropdown.create();