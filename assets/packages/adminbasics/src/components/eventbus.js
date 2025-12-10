'use strict'

/**
 * Vanilla JavaScript EventBus implementation
 * Replaces Vue-based EventBus with similar API
 */
class EventBus {
    constructor() {
        this.events = {};
        this.eventsBound = {};
    }

    /**
     * Get all bound events
     * @return {object} Events bound map
     */
    $getEventsBound() {
        return this.eventsBound;
    }

    /**
     * Emit an event with arguments
     * @param {string} event - Event name
     * @param {...*} args - Arguments to pass to listeners
     */
    $emit(event, ...args) {
        console.ls.log("Emitting -> ", event, ...args);

        if (this.eventsBound[event] !== undefined) {
            this.eventsBound[event].forEach(element => {
                // Placeholder for tracking - kept from original
            });
        }

        if (this.events[event]) {
            this.events[event].forEach(callback => {
                try {
                    callback(...args);
                } catch (error) {
                    console.error(`Error in event handler for ${event}:`, error);
                }
            });
        }

        return this;
    }

    /**
     * Register an event listener
     * @param {string} event - Event name
     * @param {function} callback - Callback function
     */
    $on(event, callback) {
        if (!this.events[event]) {
            this.events[event] = [];
        }

        this.events[event].push(callback);

        // Track bound events
        this.eventsBound[event] = this.eventsBound[event] || [];
        this.eventsBound[event].push([callback]);

        console.ls.log("Binding -> ", event, callback);

        return this;
    }

    /**
     * Unregister an event listener
     * @param {string} event - Event name
     * @param {function} callback - Callback function to remove (optional)
     */
    $off(event, callback) {
        if (!this.events[event]) {
            return this;
        }

        if (callback) {
            // Remove specific callback
            this.events[event] = this.events[event].filter(cb => cb !== callback);

            // Update eventsBound tracking
            if (this.eventsBound[event] !== undefined) {
                this.eventsBound[event] = this.eventsBound[event].filter((arg) => {
                    return arg[0] !== callback;
                });
            }
        } else {
            // Remove all callbacks for this event
            delete this.events[event];
            delete this.eventsBound[event];
        }

        console.ls.log("Remove Binding -> ", event, callback);

        return this;
    }
}

window.EventBus = window.EventBus || (new EventBus({
    name: "EventBus"
}));

export default window.EventBus;
