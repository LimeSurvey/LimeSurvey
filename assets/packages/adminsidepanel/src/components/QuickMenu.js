/**
 * QuickMenu - Collapsed menu component (vanilla JS)
 * Replaces _quickmenu.vue
 */
import StateManager from '../StateManager.js';
import UIHelpers from '../UIHelpers.js';

const QuickMenu = (function() {
    'use strict';

    let container = null;
    let isLoading = true;

    /**
     * Render the quick menu
     * @param {HTMLElement} containerEl
     * @param {boolean} loading
     */
    function render(containerEl, loading) {
        container = containerEl;

        if (!container) return;

        // Menus are loaded from SideMenuData.basemenus in Sidebar.init()
        // Don't make extra AJAX calls - just render what's in state
        isLoading = false;
        renderMenu();
    }

    /**
     * Render the menu content
     */
    function renderMenu() {
        if (!container) return;

        const collapsedmenus = StateManager.get('collapsedmenus') || [];

        // Sort menus by ordering
        const sortedMenus = LS.ld.orderBy(
            collapsedmenus,
            function(a) { return parseInt(a.ordering || 999999); },
            ['asc']
        );

        let html = '<div class="ls-flex-column fill">';

        if (isLoading) {
            html += UIHelpers.createLoaderWidget('quickmenuLoadingIcon', 'loader-quickmenu');
        } else {
            sortedMenus.forEach(function(menu) {
                html += '<div class="ls-space margin top-10" title="' + UIHelpers.escapeHtml(menu.title) + '">';
                html += '<div class="btn-group-vertical ls-space padding right-10">';

                const sortedEntries = sortMenuEntries(menu.entries);
                sortedEntries.forEach(function(menuItem) {
                    html += renderMenuItem(menuItem);
                });

                html += '</div>';
                html += '</div>';
            });
        }

        html += '</div>';

        container.innerHTML = html;
        bindEvents();
        UIHelpers.redoTooltips();
    }

    /**
     * Sort menu entries by ordering
     * @param {Array} entries
     * @returns {Array}
     */
    function sortMenuEntries(entries) {
        return LS.ld.orderBy(
            entries,
            function(a) { return parseInt(a.ordering || 999999); },
            ['asc']
        );
    }

    /**
     * Render a single menu item
     * @param {Object} menuItem
     * @returns {string}
     */
    function renderMenuItem(menuItem) {
        const classes = compileEntryClasses(menuItem);
        const tooltip = UIHelpers.reConvertHTML(menuItem.menu_description);
        const target = menuItem.link_external ? '_blank' : '_self';

        let html = '<a href="' + menuItem.link + '"' +
            ' title="' + UIHelpers.escapeHtml(tooltip) + '"' +
            ' target="' + target + '"' +
            ' data-bs-toggle="tooltip"' +
            ' class="btn ' + classes + '"' +
            ' data-menu-item-id="' + menuItem.id + '">';

        // Render icon based on type
        html += renderIcon(menuItem);

        html += '</a>';

        return html;
    }

    /**
     * Render icon based on type
     * @param {Object} menuItem
     * @returns {string}
     */
    function renderIcon(menuItem) {
        const iconType = menuItem.menu_icon_type;
        const icon = menuItem.menu_icon;

        switch (iconType) {
            case 'fontawesome':
                return '<i class="quickmenuIcon fa fa-' + icon + '"></i>';
            case 'image':
                return '<img width="32px" src="' + icon + '" />';
            case 'iconclass':
            case 'remix':
                return '<i class="quickmenuIcon ' + icon + '"></i>';
            default:
                return '';
        }
    }

    /**
     * Compile CSS classes for menu entry
     * @param {Object} menuItem
     * @returns {string}
     */
    function compileEntryClasses(menuItem) {
        let classes = '';

        if (StateManager.get('lastMenuItemOpen') === menuItem.id) {
            classes += ' btn-primary ';
        } else {
            classes += ' btn-outline-secondary ';
        }

        if (!menuItem.link_external) {
            classes += ' pjax ';
        }

        return classes;
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        if (!container) return;

        // Menu item click
        $(container).off('click', '.btn').on('click', '.btn', function() {
            const menuItemId = $(this).data('menu-item-id');

            // Update state
            StateManager.commit('lastMenuItemOpen', {
                id: menuItemId,
                menu_id: null
            });

            // Re-render to update selected state
            renderMenu();
        });
    }

    return {
        render: render
    };
})();

export default QuickMenu;
