/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */


class ConsoleShim {
    constructor(param='', silencer=false) {

        this.param = param;
        this.silencer = silencer;
        this.collector = [];
        this.currentGroupDescription = '';
        this.activeGroups = 0;
        this.timeHolder = null;
        this.methods = [
            'group', 'groupEnd', 'log', 'trace', 'time', 'timeEnd', 'error', 'warn'
        ];

        this.silent = {
            group : ()=>{return;},
            groupEnd : ()=>{return;},
            log : ()=>{return;},
            trace : ()=>{return;},
            time : ()=>{return;},
            timeEnd : ()=>{return;},
            error : ()=>{return;},
            err : ()=>{return;},
            debug : ()=>{return;},
            warn : ()=>{return;}
        }
    }

    _generateError() {
        try {
            throw new Error();
        } catch (err) {
            return err;
        }
    }
    _insertParamToArguments(rawArgs){
        if(this.param !== ''){
            let args = [...rawArgs];
            args.unshift(this.param);
            return args;
        }
        return Array.from(arguments);
    }
    setSilent(newValue = null){
        this.silencer = newValue || !this.silencer;
    }
    //Start grouping logs
    group() {
        if(this.silencer) { return; }
        const args = this._insertParamToArguments(arguments);
        if (typeof console.group === 'function') {
            console.group.apply(console, args);
            return;
        }
        const description = args[0] || 'GROUP';
        this.currentGroupDescription = description;
        this.activeGroups++;
    }
    //Stop grouping logs
    groupEnd() {
        if(this.silencer) { return; }
        const args = this._insertParamToArguments(arguments);
        if (typeof console.groupEnd === 'function') {
            console.groupEnd.apply(console, args);
            return;
        }
        this.currentGroupDescription = '';
        this.activeGroups--;
        this.activeGroups = this.activeGroups === 0 ? 0 : this.activeGroups--;
    }
    //Simplest mechanism to log stuff
    // Aware of the group shim
    log() {
        if(this.silencer) { return; }
        const args = this._insertParamToArguments(arguments);
        if (typeof console.group === 'function') {
            console.log.apply(console, args);
            return;
        }
        args.shift();
        args.unshift(' '.repeat(this.activeGroups * 2));
        this.log.apply(this,args);
    }
    //Trace back the apply.
    //Uses either the inbuilt function console trace or opens a shim to trace by calling this._insertParamToArguments(arguments).callee
    trace() {
        if(this.silencer) { return; }
        const args = this._insertParamToArguments(arguments);        
        if (typeof console.trace === 'function') {
            console.trace.apply(console, args);
            return;
        }
        const artificialError = this._generateError();
        if (artificialError.stack) {
            this.log.apply(console, artificialError.stack);
            return;
        }

        this.log(args);
        if (arguments.callee != undefined) {
            this.trace.apply(console, arguments.callee);
        }
    }

    time() {
        if(this.silencer) { return; }
        const args = this._insertParamToArguments(arguments);    
        if (typeof console.time === 'function') {
            console.time.apply(console, args);
            return;
        }

        this.timeHolder = new Date();
    }

    timeEnd() {
        if(this.silencer) { return; }
        const args = this._insertParamToArguments(arguments);
        if (typeof console.timeEnd === 'function') {
            console.timeEnd.apply(console, args);
            return;
        }
        const diff = (new Date()) - this.timeHolder;
        this.log(`Took ${Math.floor(diff/(1000*60*60))} hours, ${Math.floor(diff/(1000*60))} minutes and ${Math.floor(diff/(1000))} seconds ( ${diff} ms)`);
        this.time = new Date();
    }

    error() {
        const args = this._insertParamToArguments(arguments);
        if (typeof console.error === 'function') {
            console.error.apply(console,args);
            return;
        }

        this.log('--- ERROR ---');
        this.log(args);
    }


    warn() {
        const args = this._insertParamToArguments(arguments);
        if (typeof console.warn === 'function') {
            console.warn.apply(console,args);
            return;
        }

        this.log('--- WARN ---');
        this.log(args);
    }
}

const adminCoreLSConsole = new ConsoleShim('AdminCore');

export default adminCoreLSConsole;
