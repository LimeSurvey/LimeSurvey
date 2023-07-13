LS.actionDropdown = {
    DropdownClass: class extends bootstrap.Dropdown {
        _getMenuElement()
        {
            return this._config.lsMenuElement;
        }
    },
    create: function () {
        'use strict';
        let dropdownElementList = [].slice.call(
            document.querySelectorAll('.ls-dropdown-toggle')
        );
        let body = document.querySelector('body');
        dropdownElementList.map(function (dropdownToggleEl) {
            let dropdownMenu = dropdownToggleEl.nextElementSibling;
            if (dropdownMenu !== null) {
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
    }
};

LS.actionDropdown.create();