'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */

var ConsoleShim = function () {
    function ConsoleShim() {
        _classCallCheck(this, ConsoleShim);

        this.collector = [];
        this.currentGroupDescription = '';
        this.activeGroups = 0;
        this.timeHolder = null;
        this.methods = ['group', 'groupEnd', 'log', 'trace', 'time', 'timeEnd', 'error', 'warn'];
    }

    _createClass(ConsoleShim, [{
        key: '_generateError',
        value: function _generateError() {
            try {
                throw new Error();
            } catch (err) {
                return err;
            }
        }
        //Start grouping logs

    }, {
        key: 'group',
        value: function group() {
            if (typeof console.group === 'function') {
                console.group.apply(this, arguments);
                return;
            }
            var description = arguments[0] || 'GROUP';
            this.currentGroupDescription = description;
            this.activeGroups++;
        }
        //Stop grouping logs

    }, {
        key: 'groupEnd',
        value: function groupEnd() {
            if (typeof console.groupEnd === 'function') {
                console.groupEnd.apply(this, arguments);
                return;
            }
            this.currentGroupDescription = '';
            this.activeGroups--;
            this.activeGroups = this.activeGroups === 0 ? 0 : this.activeGroups--;
        }
        //Simplest mechanism to log stuff
        // Aware of the group shim

    }, {
        key: 'log',
        value: function log() {
            if (typeof console.group === 'function') {
                console.log.apply(this, arguments);
                return;
            }

            console.log(' '.repeat(this.activeGroups * 2), arguments);
        }
        //Trace back the apply.
        //Uses either the inbuilt function console trace or opens a shim to trace by calling arguments.callee

    }, {
        key: 'trace',
        value: function trace() {
            if (typeof console.trace === 'function') {
                console.trace.apply(this, arguments);
                return;
            }
            var artificialError = this._generateError();
            if (artificialError.stack) {
                this.log.apply(this, artificialError.stack);
                return;
            }

            this.log(arguments);
            if (arguments.callee != undefined) {
                this.trace.apply(this, arguments.callee);
            }
        }
    }, {
        key: 'time',
        value: function time() {
            if (typeof console.time === 'function') {
                console.time.apply(this, arguments);
                return;
            }

            this.timeHolder = new Date();
        }
    }, {
        key: 'timeEnd',
        value: function timeEnd() {
            if (typeof console.timeEnd === 'function') {
                console.timeEnd.apply(this, arguments);
                return;
            }
            var diff = new Date() - this.timeHolder;
            this.log('Took ' + Math.floor(diff / (1000 * 60 * 60)) + ' hours, ' + Math.floor(diff / (1000 * 60)) + ' minutes and ' + Math.floor(diff / 1000) + ' seconds ( ' + diff + ' ms)');
            this.time = new Date();
        }
    }, {
        key: 'error',
        value: function error() {
            if (typeof console.error === 'function') {
                console.error.apply(arguments);
                return;
            }

            this.log('--- ERROR ---');
            this.log(arguments);
        }
    }, {
        key: 'warn',
        value: function warn() {
            if (typeof console.warn === 'function') {
                console.warn.apply(arguments);
                return;
            }

            this.log('--- WARN ---');
            this.log(arguments);
        }
    }]);

    return ConsoleShim;
}();

var globalLSConsole = new ConsoleShim();

window.console.ls = globalLSConsole;