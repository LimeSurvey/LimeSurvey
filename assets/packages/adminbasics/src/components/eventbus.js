'use strict'
import Vue from 'vue';
class EventBus extends Vue {

    $getEventsBound() {
        return this.eventsBound;
    }

    // Override Vue's $emit to call a logger for any event emitted.
    $emit(event, ...args) {
        console.ls.log("Emitting -> ", event, ...args);
        if(this.eventsBound != undefined && this.eventsBound[event] != undefined) {
            this.eventsBound[event].forEach(element => {
                element[0](...args);
            });
        }
        return super.$emit(event, ...args);
    }
    // Override Vue's $emit to call a logger for any event bound.
    $on(event, ...args) {
        this.eventsBound = this.eventsBound || {};
        this.eventsBound[event] = this.eventsBound[event] || [];
        this.eventsBound[event].push(args);
        console.ls.log("Binding -> ", event, ...args);
        return super.$emit(event, ...args);
    }
}

window.EventBus = window.EventBus || (new EventBus({
    name: "EventBus"
}));

export default window.EventBus;
