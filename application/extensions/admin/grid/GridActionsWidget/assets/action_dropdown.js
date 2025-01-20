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

LS.actionDropdown.create();