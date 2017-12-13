/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */


class ConsoleShim {
    constructor() {
        this.collector = [];
        this.currentGroupDescription = '';
        this.activeGroups = 0;
        this.timeHolder = null;
        this.methods = [
            'group', 'groupEnd', 'log', 'trace', 'time', 'timeEnd', 'error', 'warn'
        ];
    }

    _generateError() {
        try {
            throw new Error();
        } catch (err) {
            return err;
        }
    }
    //Start grouping logs
    group() {
        if (typeof console.group === 'function') {
            console.group.apply(console, arguments);
            return;
        }
        const description = arguments[0] || 'GROUP';
        this.currentGroupDescription = description;
        this.activeGroups++;
    }
    //Stop grouping logs
    groupEnd() {
        if (typeof console.groupEnd === 'function') {
            console.groupEnd.apply(console, arguments);
            return;
        }
        this.currentGroupDescription = '';
        this.activeGroups--;
        this.activeGroups = this.activeGroups === 0 ? 0 : this.activeGroups--;
    }
    //Simplest mechanism to log stuff
    // Aware of the group shim
    log() {
        if (typeof console.group === 'function') {
            console.log.apply(console, arguments);
            return;
        }

        console.log(' '.repeat(this.activeGroups * 2), arguments);
    }
    //Trace back the apply.
    //Uses either the inbuilt function console trace or opens a shim to trace by calling arguments.callee
    trace() {
        if (typeof console.trace === 'function') {
            console.trace.apply(console, arguments);
            return;
        }
        const artificialError = this._generateError();
        if (artificialError.stack) {
            this.log.apply(console, artificialError.stack);
            return;
        }

        this.log(arguments);
        if (arguments.callee != undefined) {
            this.trace.apply(console, arguments.callee);
        }
    }

    time() {
        if (typeof console.time === 'function') {
            console.time.apply(console, arguments);
            return;
        }

        this.timeHolder = new Date();
    }

    timeEnd() {
        if (typeof console.timeEnd === 'function') {
            console.timeEnd.apply(console, arguments);
            return;
        }
        const diff = (new Date()) - this.timeHolder;
        this.log(`Took ${Math.floor(diff/(1000*60*60))} hours, ${Math.floor(diff/(1000*60))} minutes and ${Math.floor(diff/(1000))} seconds ( ${diff} ms)`);
        this.time = new Date();
    }

    error() {
        if (typeof console.error === 'function') {
            console.error.apply(arguments);
            return;
        }

        this.log('--- ERROR ---');
        this.log(arguments);
    }

    warn() {
        if (typeof console.warn === 'function') {
            console.warn.apply(arguments);
            return;
        }

        this.log('--- WARN ---');
        this.log(arguments);
    }

}
var globalLSConsole = new ConsoleShim();

window.console.ls = globalLSConsole;
