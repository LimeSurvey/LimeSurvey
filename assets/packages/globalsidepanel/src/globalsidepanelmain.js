"use strict"

// Simple state management to replace Vuex with localStorage persistence
const createStateManager = (storageKey) => {
    // Initial state structure
    const defaultState = {
        currentUser: null,
        language: '',
        sidebarwidth: 380,
        menu: null,
        lastMenuItemOpen: ''
    };

    // Load state from localStorage or use default
    const loadState = () => {
        try {
            const serializedState = window.localStorage.getItem(storageKey);
            if (serializedState === null) {
                return { ...defaultState };
            }
            return { ...defaultState, ...JSON.parse(serializedState) };
        } catch (err) {
            console.error('Error loading state:', err);
            return { ...defaultState };
        }
    };

    // Save state to localStorage
    const saveState = (state) => {
        try {
            const serializedState = JSON.stringify(state);
            window.localStorage.setItem(storageKey, serializedState);
        } catch (err) {
            console.error('Error saving state:', err);
        }
    };

    const state = loadState();
    const listeners = [];

    return {
        state: state,

        commit(mutation, payload) {
            switch(mutation) {
                case 'setCurrentUser':
                    state.currentUser = payload;
                    break;
                case 'setLanguage':
                    state.language = payload;
                    break;
                case 'setSidebarwidth':
                    state.sidebarwidth = payload;
                    break;
                case 'setMenu':
                    state.menu = payload;
                    break;
                case 'setLastMenuItemOpen':
                    state.lastMenuItemOpen = payload;
                    break;
            }
            saveState(state);
            this.notify();
        },

        dispatch(action, payload) {
            switch(action) {
                case 'updatePjax':
                    $(document).trigger('pjax:refresh');
                    return Promise.resolve();

                case 'getMenus':
                    return new Promise((resolve, reject) => {
                        if (!window.GlobalSideMenuData?.getUrl) {
                            reject(new Error('No getUrl configured'));
                            return;
                        }

                        $.ajax({
                            url: window.GlobalSideMenuData.getUrl,
                            method: 'GET',
                            dataType: 'json'
                        })
                        .done((result) => {
                            if (console.ls?.log) {
                                console.ls.log("menues", result);
                            }
                            this.commit('setMenu', result.data || result);
                            this.dispatch('updatePjax');
                            resolve(result);
                        })
                        .fail((xhr, status, error) => {
                            console.error('Error fetching menus:', error);
                            reject(error);
                        });
                    });

                default:
                    return Promise.resolve();
            }
        },

        notify() {
            listeners.forEach(listener => listener(state));
        },

        subscribe(listener) {
            listeners.push(listener);
            return () => {
                const index = listeners.indexOf(listener);
                if (index > -1) listeners.splice(index, 1);
            };
        }
    };
};

// Helper functions
const translate = (string) => {
    return window.GlobalSideMenuData?.i10n?.[string] || string;
};

const redoTooltips = () => {
    if (window.LS?.doToolTip) {
        window.LS.doToolTip();
    }
};

const updatePjaxLinks = () => {
    // Force update - trigger any necessary re-renders
    $(document).trigger('vue-redraw');
};

// Initialize the global side panel
const initGlobalSidePanel = () => {
    const $container = $("#global-sidebar-container");

    if (!$container.length) {
        console.warn('Global sidebar container not found');
        return null;
    }

    // Determine storage key
    const storeName = window.GlobalSideMenuData?.sgid
        ? LS.globalUserId + '-' + window.GlobalSideMenuData.sgid
        : LS.globalUserId;

    const storageKey = storeName ? 'lsglobalsidemenu_' + storeName : 'lsglobalsidemenu';

    // Create state manager
    const AppState = createStateManager(storageKey);

    // Setup event handlers
    $(document).on("vue-redraw", () => {
        updatePjaxLinks();
    });

    // Trigger initial remote reload
    $(document).trigger("vue-reload-remote");

    // Return API for external access
    return {
        store: AppState,
        updatePjaxLinks: updatePjaxLinks,
        redoTooltips: redoTooltips,
        translate: translate
    };
};

// Initialize on document ready
$(document).ready(() => {
    window.GlobalSidePanel = initGlobalSidePanel();
});
