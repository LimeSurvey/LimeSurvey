/**
 * Store initialization for Global Sidebar Panel
 * Uses unified StateManager implementation
 */
import StateManager from './StateManager.js';
import { createDefaultState, createMutations, createGetters } from './stateConfig.js';

/**
 * Initialize store with user ID
 * @param {string|number} userid
 * @returns {Object} StateManager instance
 */
export function initStore(userid = null) {
    StateManager.init({
        storagePrefix: 'lsglobalsidemenu',
        userid: userid,
        defaultState: createDefaultState(userid),
        mutations: createMutations(StateManager),
        getters: createGetters(StateManager)
    });

    return StateManager;
}

export default StateManager;
