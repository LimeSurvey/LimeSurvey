/**
 * StateManager - Vanilla JS replacement for Vuex store
 * Manages sidebar state with sessionStorage persistence
 *
 * Unified implementation used across admin and global sidepanels
 */
const StateManager = (function() {
    'use strict';

    let state = {};
    let storageKey = '';
    let listeners = [];
    let mutations = {};
    let getters = {};

    /**
     * Initialize state with default values
     * @param {Object} config - Configuration object
     * @param {string} config.storagePrefix - Storage key prefix (e.g., 'limesurveyadminsidepanel')
     * @param {string|number} [config.userid] - User ID
     * @param {string|number} [config.surveyid] - Survey ID (optional)
     * @param {Object} config.defaultState - Default state object
     * @param {Object} [config.mutations] - Mutations object (optional)
     * @param {Object} [config.getters] - Getters object (optional)
     */
    function init(config) {
        if (!config || !config.storagePrefix || !config.defaultState) {
            console.error('StateManager.init requires storagePrefix and defaultState');
            return state;
        }

        // Build storage key
        storageKey = config.storagePrefix;
        if (config.userid) {
            storageKey += '_' + config.userid;
        }
        if (config.surveyid) {
            storageKey += '_' + config.surveyid;
        }

        // Set mutations and getters
        mutations = config.mutations || {};
        getters = config.getters || {};

        // Try to load from sessionStorage
        const savedState = loadFromStorage();
        state = Object.assign({}, config.defaultState, savedState);

        return state;
    }

    /**
     * Load state from sessionStorage
     */
    function loadFromStorage() {
        try {
            const saved = sessionStorage.getItem(storageKey);
            if (saved) {
                return JSON.parse(saved);
            }
        } catch (e) {
            console.warn('Failed to load state from sessionStorage:', e);
        }
        return {};
    }

    /**
     * Save state to sessionStorage
     */
    function saveToStorage() {
        try {
            sessionStorage.setItem(storageKey, JSON.stringify(state));
        } catch (e) {
            console.warn('Failed to save state to sessionStorage:', e);
        }
    }

    /**
     * Get current state value
     * @param {string} [key] - State key to retrieve (omit to get entire state)
     * @returns {*} State value or entire state object
     */
    function get(key) {
        if (key) {
            return state[key];
        }
        return state;
    }

    /**
     * Set state value and persist
     * @param {string} key - State key
     * @param {*} value - New value
     */
    function set(key, value) {
        const oldValue = state[key];
        state[key] = value;
        saveToStorage();
        notifyListeners(key, value, oldValue);
    }

    /**
     * Subscribe to state changes
     * @param {Function} callback - Callback function (key, newValue, oldValue)
     * @returns {Function} Unsubscribe function
     */
    function subscribe(callback) {
        listeners.push(callback);
        return function unsubscribe() {
            listeners = listeners.filter(l => l !== callback);
        };
    }

    /**
     * Notify listeners of state change
     * @param {string} key - Changed state key
     * @param {*} newValue - New value
     * @param {*} oldValue - Old value
     */
    function notifyListeners(key, newValue, oldValue) {
        listeners.forEach(function(listener) {
            listener(key, newValue, oldValue);
        });
    }

    /**
     * Commit a mutation
     * @param {string} mutation - Mutation name
     * @param {*} payload - Mutation payload
     */
    function commit(mutation, payload) {
        if (mutations[mutation]) {
            mutations[mutation](payload);
        } else {
            console.warn('Unknown mutation:', mutation);
        }
    }

    /**
     * Get a computed value from getters
     * @param {string} getter - Getter name
     * @returns {*} Computed value
     */
    function getComputed(getter) {
        if (getters[getter]) {
            return getters[getter]();
        }
        console.warn('Unknown getter:', getter);
        return undefined;
    }

    /**
     * Register mutations (can be called after init to add more mutations)
     * @param {Object} newMutations - Mutations to register
     */
    function registerMutations(newMutations) {
        Object.assign(mutations, newMutations);
    }

    /**
     * Register getters (can be called after init to add more getters)
     * @param {Object} newGetters - Getters to register
     */
    function registerGetters(newGetters) {
        Object.assign(getters, newGetters);
    }

    return {
        init: init,
        get: get,
        set: set,
        commit: commit,
        getComputed: getComputed,
        subscribe: subscribe,
        registerMutations: registerMutations,
        registerGetters: registerGetters,
        getState: function() { return state; }
    };
})();

export default StateManager;
