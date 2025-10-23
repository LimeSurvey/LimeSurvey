'use strict';

const getInitialState = (userid) => {
    // Return your initial state structure here
    return {
        userid: userid,
        // Add other state properties from statePreset
    };
};

const getAppState = function (userid, surveyid) {
    const AppStateName = 'limesurveyadminsidepanel';
    const storageKey = `${AppStateName}_${userid}_${surveyid}`;

    // Load initial state from sessionStorage or use preset
    const loadState = () => {
        try {
            const serializedState = window.sessionStorage.getItem(storageKey);
            if (serializedState === null) {
                return getInitialState(userid);
            }
            return JSON.parse(serializedState);
        } catch (err) {
            return getInitialState(userid);
        }
    };

    // Save state to sessionStorage
    const saveState = (state) => {
        try {
            const serializedState = JSON.stringify(state);
            window.sessionStorage.setItem(storageKey, serializedState);
        } catch (err) {
            console.error('Error saving state:', err);
        }
    };

    // Initialize state
    const state = loadState();
    const listeners = [];

    // Store API
    return {
        getState: () => ({ ...state }),

        commit: (mutation, payload) => {
            // Apply mutation to state
            Object.assign(state, typeof mutation === 'function' ? mutation(state, payload) : mutation);
            saveState(state);
            listeners.forEach(listener => listener(state));
        },

        dispatch: (action, payload) => {
            return Promise.resolve(action(state, payload));
        },

        subscribe: (listener) => {
            listeners.push(listener);
            return () => {
                const index = listeners.indexOf(listener);
                if (index > -1) {
                    listeners.splice(index, 1);
                }
            };
        }
    };
};

export default getAppState;
