/**
 * State configuration for globalsidepanel
 * Defines default state, mutations, and getters
 */

/**
 * Create default state
 * @param {string|number} userid
 * @returns {Object}
 */
export function createDefaultState(userid) {
    return {
        currentUser: userid,
        language: '',
        sidebarwidth: 380,
        menu: null,
        lastMenuItemOpen: '',
        isCollapsed: false
    };
}

/**
 * Create mutations for StateManager
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
export function createMutations(StateManager) {
    return {
        setMenu: function(menu) {
            StateManager.set('menu', menu);
        },
        setLastMenuItemOpen: function(menuItem) {
            StateManager.set('lastMenuItemOpen', menuItem);
        },
        setSidebarwidth: function(width) {
            StateManager.set('sidebarwidth', width);
        },
        setLanguage: function(language) {
            StateManager.set('language', language);
        },
        setCurrentUser: function(user) {
            StateManager.set('currentUser', user);
        },
        setIsCollapsed: function(collapsed) {
            StateManager.set('isCollapsed', collapsed);
        }
    };
}

/**
 * Create getters for StateManager (optional, can be expanded as needed)
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
export function createGetters(StateManager) {
    return {
        // Add computed properties here if needed
    };
}
