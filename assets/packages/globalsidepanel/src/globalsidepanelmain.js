/**
 * Global Sidebar Panel
 * Vanilla JavaScript implementation
 */

import StateManager, { initStore } from './store';
import Actions from './actions';
import GlobalSidemenu from './components/GlobalSidemenu';
import Sidemenu from './components/Sidemenu';

// Initialize the application
const init = () => {
    // Get container element
    const container = document.getElementById('global-sidebar-container');

    if (!container) {
        console.error('Global sidebar container not found');
        return null;
    }

    // Check if required global data exists
    if (!window.GlobalSideMenuData) {
        console.error('GlobalSideMenuData not found');
        return null;
    }

    // Validate LS global
    if (!window.LS || typeof window.LS.globalUserId === 'undefined') {
        console.error('LS.globalUserId not found');
        return null;
    }

    // Create user ID for store
    let userid = window.GlobalSideMenuData.sgid
        ? LS.globalUserId + '-' + window.GlobalSideMenuData.sgid
        : LS.globalUserId;

    // Initialize store with unified API
    const store = initStore(userid);

    // Initialize actions
    const actions = new Actions(store);

    // Initialize main component
    const globalSidePanel = new GlobalSidemenu(container, store, actions, {
        Sidemenu
    });

    return {
        store,
        actions,
        component: globalSidePanel
    };
};

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const app = init();
        // Export to window for external access
        window.GlobalSidePanel = app;
    });
} else {
    const app = init();
    // Export to window for external access
    window.GlobalSidePanel = app;
}

// Export for module usage
export default {
    init,
    StateManager,
    Actions,
    GlobalSidemenu,
    Sidemenu
};
