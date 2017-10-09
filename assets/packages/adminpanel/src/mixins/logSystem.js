/* eslint-disable no-alert, no-console */

/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */


class ConsoleShim {
  constructor(){
    this.collector = [];
    this.currentGroupDescription = '';
    this.activeGroups = 0;
    this.timeHolder = null;
    this.methods = [
      'group', 'groupEnd', 'log', 'trace', 'time', 'timeEnd', 'error', 'warn'
    ];
  }

  _generateError () {
    try { throw new Error(); } catch (err) { return err; }
  }
  //Start grouping logs
  group (){
    if (typeof console.group === 'function') { 
      console.group(arguments);
      return;
    }
    const description = arguments[0] || 'GROUP';
    this.currentGroupDescription = description;
    this.activeGroups++;
  }
  //Stop grouping logs
  groupEnd (){
    if (typeof console.groupEnd === 'function') { 
      console.groupEnd(arguments);
      return;
    }
    this.currentGroupDescription = '';
    this.activeGroups--;
    this.activeGroups = this.activeGroups === 0 ? 0 : this.activeGroups--;
  }
  //Simplest mechanism to log stuff
  // Aware of the group shim
  log () {
    if (typeof console.group === 'function') { 
      console.log(arguments);
      return;
    }

    console.log( ' '.repeat(this.activeGroups*2), arguments);
  }
  //Trace back the call.
  //Uses either the inbuilt function console trace or opens a shim to trace by calling arguments.callee
  trace(){
    if (typeof console.trace === 'function') { 
      console.trace(arguments);
      return;
    }
    const artificialError = this._generateError();
    if(artificialError.stack){
      this.log(artificialError.stack);
      return;
    }

    this.log(arguments);
    if(arguments.callee != undefined){
      this.trace(arguments.callee);
    }
  }

  time() {
    if (typeof console.time === 'function') { 
      console.time(arguments);
      return;
    }

    this.timeHolder = new Date();
  }

  timeEnd() {
    if (typeof console.timeEnd === 'function') { 
      console.timeEnd(arguments);
      return;
    }
    const diff = (new Date()) - this.timeHolder;
    this.log(`Took ${Math.floor(diff/(1000*60*60))} hours, ${Math.floor(diff/(1000*60))} minutes and ${Math.floor(diff/(1000))} seconds ( ${diff} ms)`);
    this.time = new Date();
  }

  error(){
    if (typeof console.error === 'function') { 
      console.error(arguments);
      return;
    }

    this.log('--- ERROR ---');
    this.log(arguments);
  }

  warn(){
    if (typeof console.warn === 'function') { 
      console.warn(arguments);
      return;
    }

    this.log('--- WARN ---');
    this.log(arguments);
  }

}

const env = process.env.NODE_ENV;
const debugConsole = new ConsoleShim();

exports.install = function (Vue) {
  console.log(`The systen is currently in ${process.env.NODE_ENV} mode.`);

  const debugmode = (env=='developement');

  Vue.prototype.$log = {
    debug : function(){
      if(debugmode)
        debugConsole.trace.apply(Vue,['LoggingSystem DEBUG:', arguments]);
    },
    warn : function(){
      if(debugmode)
        debugConsole.log.apply(Vue,['LoggingSystem WARN:\n', arguments]);
    },
    error : function(){
      if(debugmode)
        debugConsole.error.apply(Vue,['LoggingSystem ERROR:\n', arguments]);
    },
    log : function(){
      debugConsole.log.apply(Vue,['LoggingSystem ERROR:\n', arguments]);
    }
  };
};
