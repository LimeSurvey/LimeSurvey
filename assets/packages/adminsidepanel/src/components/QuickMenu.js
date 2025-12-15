/**
 * QuickMenu - Collapsed menu component (vanilla JS)
 * Replaces _quickmenu.vue
 */
import StateManager from '../StateManager.js';
import UIHelpers from '../UIHelpers.js';

class QuickMenu {
    constructor() {
        this.container = null;
        this.isLoading = true;

        // Bind methods
        this.handleMenuItemClick = this.handleMenuItemClick.bind(this);
    }

    /**
     * Render the quick menu
     * @param {HTMLElement} containerEl
     * @param {boolean} loading
     */
    render(containerEl, loading) {
        this.container = containerEl;

        if (!this.container) return;

        // Menus are loaded from SideMenuData.basemenus in Sidebar.init()
        // Don't make extra AJAX calls - just render what's in state
        this.isLoading = false;
        this.renderMenu();
    }

    /**
     * Render the menu content
     */
    renderMenu() {
        if (!this.container) return;

        const collapsedmenus = StateManager.get('collapsedmenus') || [];

        // Sort menus by ordering
        const sortedMenus = LS.ld.orderBy(
            collapsedmenus,
            function(a) { return parseInt(a.ordering || 999999); },
            ['asc']
        );

        let html = '<div class="ls-flex-column fill">';

        if (this.isLoading) {
            html += UIHelpers.createLoaderWidget('quickmenuLoadingIcon', 'loader-quickmenu');
        } else {
            sortedMenus.forEach((menu) => {
                html += '<div class="ls-space margin top-10" title="' + UIHelpers.escapeHtml(menu.title) + '">';
                html += '<div class="btn-group-vertical ls-space padding right-10">';

                const sortedEntries = this.sortMenuEntries(menu.entries);
                sortedEntries.forEach((menuItem) => {
                    html += this.renderMenuItem(menuItem);
                });

                html += '</div>';
                html += '</div>';
            });
        }

        html += '</div>';

        this.container.innerHTML = html;
        this.bindEvents();
        UIHelpers.redoTooltips();
    }

    /**
     * Sort menu entries by ordering
     * @param {Array} entries
     * @returns {Array}
     */
    sortMenuEntries(entries) {
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
    renderMenuItem(menuItem) {
        const classes = this.compileEntryClasses(menuItem);
        const tooltip = UIHelpers.reConvertHTML(menuItem.menu_description);
        const target = menuItem.link_external ? '_blank' : '_self';

        let html = '<a href="' + menuItem.link + '"' +
            ' title="' + UIHelpers.escapeHtml(tooltip) + '"' +
            ' target="' + target + '"' +
            ' data-bs-toggle="tooltip"' +
            ' class="btn ' + classes + '"' +
            ' data-menu-item-id="' + menuItem.id + '">';

        // Render icon based on type
        html += this.renderIcon(menuItem);

        html += '</a>';

        return html;
    }

    /**
     * Render icon based on type
     * @param {Object} menuItem
     * @returns {string}
     */
    renderIcon(menuItem) {
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
    compileEntryClasses(menuItem) {
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
    bindEvents() {
        if (!this.container) return;

        // Menu item click
        $(this.container).off('click', '.btn').on('click', '.btn', this.handleMenuItemClick);
    }

    /**
     * Handle menu item click
     */
    handleMenuItemClick(e) {
        const menuItemId = $(e.currentTarget).data('menu-item-id');

        // Update state
        StateManager.commit('lastMenuItemOpen', {
            id: menuItemId,
            menu_id: null
        });

        // Re-render to update selected state
        this.renderMenu();
    }
}

export default QuickMenu;
