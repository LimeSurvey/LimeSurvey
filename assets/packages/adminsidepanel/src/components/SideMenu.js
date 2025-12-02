/**
 * SideMenu - Side menu component (vanilla JS)
 * Replaces _sidemenu.vue and _submenu.vue
 */
import StateManager from '../StateManager.js';
import UIHelpers from '../UIHelpers.js';

const SideMenu = (function() {
    'use strict';

    let container = null;
    let isLoading = true;

    /**
     * Render the side menu
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

        const sidemenus = StateManager.get('sidemenus') || [];

        // Sort menus by ordering
        const sortedMenus = LS.ld.orderBy(
            sidemenus,
            function(a) { return parseInt(a.ordering || 999999); },
            ['asc']
        );

        let html = '<div class="ls-flex-column menu-pane overflow-enabled ls-space all-0 py-4 bg-white">';

        if (isLoading) {
            html += UIHelpers.createLoaderWidget('sidemenuLoaderWidget', '');
        } else if (sortedMenus.length >= 2) {
            // First menu (usually main settings)
            html += '<div title="' + UIHelpers.escapeHtml(sortedMenus[0].title) + '" id="' + sortedMenus[0].id + '" class="ls-flex-row wrap ls-space padding all-0">';
            html += renderSubmenu(sortedMenus[0]);
            html += '</div>';

            // Second menu (with label)
            html += '<div title="' + UIHelpers.escapeHtml(sortedMenus[1].title) + '" id="' + sortedMenus[1].id + '" class="ls-flex-row wrap ls-space padding all-0">';
            html += '<label class="menu-label mt-3 p-2 ls-survey-menu-item">' + UIHelpers.escapeHtml(sortedMenus[1].title) + '</label>';
            html += renderSubmenu(sortedMenus[1]);
            html += '</div>';
        }

        html += '</div>';

        container.innerHTML = html;
        bindEvents();
        UIHelpers.redoTooltips();
    }

    /**
     * Render a submenu
     * @param {Object} menu
     * @returns {string}
     */
    function renderSubmenu(menu) {
        if (!menu || !menu.entries) return '';

        const sortedEntries = LS.ld.orderBy(
            menu.entries,
            function(a) { return parseInt(a.ordering || 999999); },
            ['asc']
        );

        let html = '<ul class="list-group subpanel col-12 level-' + (menu.level || 0) + '">';

        sortedEntries.forEach(function(menuItem) {
            const linkClass = getLinkClass(menuItem);
            const href = menuItem.disabled ? '#' : menuItem.link;
            const target = menuItem.link_external === true ? '_blank' : '';
            const tooltip = menuItem.disabled ? menuItem.disabled_tooltip : UIHelpers.reConvertHTML(menuItem.menu_description);

            html += '<a href="' + href + '"' +
                (target ? ' target="' + target + '"' : '') +
                ' id="sidemenu_' + menuItem.name + '"' +
                ' class="list-group-item w-100 ' + linkClass + '"' +
                ' data-menu-item-id="' + menuItem.id + '"' +
                ' data-menu-id="' + menuItem.menu_id + '">';

            html += '<div class="d-flex ' + (menuItem.menu_class || '') + '"' +
                ' title="' + UIHelpers.escapeHtml(tooltip) + '"' +
                ' data-bs-toggle="tooltip">';

            html += '<div class="ls-space padding all-0 me-auto wrapper">';
            html += UIHelpers.renderMenuIcon(menuItem.menu_icon_type, menuItem.menu_icon);
            html += '<span class="title">' + (menuItem.menu_title || '') + '</span>';
            if (menuItem.link_external === true) {
                html += '<i class="ri-external-link-fill">&nbsp;</i>';
            }
            html += '</div>';

            html += '</div>';
            html += '</a>';
        });

        html += '</ul>';

        return html;
    }

    /**
     * Get CSS classes for a menu link
     * @param {Object} menuItem
     * @returns {string}
     */
    function getLinkClass(menuItem) {
        let classes = 'nowrap ';
        classes += menuItem.pjax ? 'pjax ' : ' ';
        classes += StateManager.get('lastMenuItemOpen') === menuItem.id ? 'selected ' : ' ';
        classes += menuItem.menu_icon ? '' : 'ls-survey-menu-item';
        if (menuItem.disabled) {
            classes += ' disabled';
        }
        return classes;
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        if (!container) return;

        // Menu item click
        $(container).off('click', '.list-group-item').on('click', '.list-group-item', function(e) {
            const $this = $(this);
            const menuItemId = $this.data('menu-item-id');
            const menuId = $this.data('menu-id');

            if ($this.hasClass('disabled')) {
                e.preventDefault();
                return false;
            }

            // Update state
            StateManager.commit('lastMenuItemOpen', {
                id: menuItemId,
                menu_id: menuId
            });

            // Re-render to update selected state
            renderMenu();

            // Allow default link behavior (pjax will handle it)
        });
    }

    return {
        render: render
    };
})();

export default SideMenu;
