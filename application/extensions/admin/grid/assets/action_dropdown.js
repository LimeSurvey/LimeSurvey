(function () {
    'use strict';
    let dropdownElementList = [].slice.call(document.querySelectorAll('.ls-dropdown-toggle'));
    dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            boundary: document.querySelector('body'),
            popperConfig: function (defaultBsPopperConfig) {
                return {
                    defaultBsPopperConfig, strategy: 'fixed'
                };
            }
        });
    });
})();
