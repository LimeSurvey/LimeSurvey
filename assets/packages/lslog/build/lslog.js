'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */

var ConsoleShim = function () {
    function ConsoleShim() {
        var param = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
        var silencer = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

        _classCallCheck(this, ConsoleShim);

        this.param = param;
        this.silencer = silencer;
        this.collector = [];
        this.currentGroupDescription = '';
        this.activeGroups = 0;
        this.timeHolder = null;
        this.methods = ['group', 'groupEnd', 'log', 'trace', 'time', 'timeEnd', 'error', 'warn'];

        this.silent = {
            group: function group() {
                return;
            },
            groupEnd: function groupEnd() {
                return;
            },
            log: function log() {
                return;
            },
            trace: function trace() {
                return;
            },
            time: function time() {
                return;
            },
            timeEnd: function timeEnd() {
                return;
            },
            error: function error() {
                return;
            },
            err: function err() {
                return;
            },
            debug: function debug() {
                return;
            },
            warn: function warn() {
                return;
            }
        };
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
    }, {
        key: '_insertParamToArguments',
        value: function _insertParamToArguments(rawArgs) {
            if (this.param !== '') {
                var args = [].concat(_toConsumableArray(rawArgs));
                args.unshift(this.param);
                return args;
            }
            return Array.from(arguments);
        }
    }, {
        key: 'setSilent',
        value: function setSilent() {
            var newValue = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

            this.silencer = newValue || !this.silencer;
        }
        //Start grouping logs

    }, {
        key: 'group',
        value: function group() {
            if (this.silencer) {
                return;
            }
            var args = this._insertParamToArguments(arguments);
            if (typeof console.group === 'function') {
                console.group.apply(console, args);
                return;
            }
            var description = args[0] || 'GROUP';
            this.currentGroupDescription = description;
            this.activeGroups++;
        }
        //Stop grouping logs

    }, {
        key: 'groupEnd',
        value: function groupEnd() {
            if (this.silencer) {
                return;
            }
            var args = this._insertParamToArguments(arguments);
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

    }, {
        key: 'log',
        value: function log() {
            if (this.silencer) {
                return;
            }
            var args = this._insertParamToArguments(arguments);
            if (typeof console.group === 'function') {
                console.log.apply(console, args);
                return;
            }
            args.shift();
            args.unshift(' '.repeat(this.activeGroups * 2));
            this.log.apply(this, args);
        }
        //Trace back the apply.
        //Uses either the inbuilt function console trace or opens a shim to trace by calling this._insertParamToArguments(arguments).callee

    }, {
        key: 'trace',
        value: function trace() {
            if (this.silencer) {
                return;
            }
            var args = this._insertParamToArguments(arguments);
            if (typeof console.trace === 'function') {
                console.trace.apply(console, args);
                return;
            }
            var artificialError = this._generateError();
            if (artificialError.stack) {
                this.log.apply(console, artificialError.stack);
                return;
            }

            this.log(args);
            if (arguments.callee != undefined) {
                this.trace.apply(console, arguments.callee);
            }
        }
    }, {
        key: 'time',
        value: function time() {
            if (this.silencer) {
                return;
            }
            var args = this._insertParamToArguments(arguments);
            if (typeof console.time === 'function') {
                console.time.apply(console, args);
                return;
            }

            this.timeHolder = new Date();
        }
    }, {
        key: 'timeEnd',
        value: function timeEnd() {
            if (this.silencer) {
                return;
            }
            var args = this._insertParamToArguments(arguments);
            if (typeof console.timeEnd === 'function') {
                console.timeEnd.apply(console, args);
                return;
            }
            var diff = new Date() - this.timeHolder;
            this.log('Took ' + Math.floor(diff / (1000 * 60 * 60)) + ' hours, ' + Math.floor(diff / (1000 * 60)) + ' minutes and ' + Math.floor(diff / 1000) + ' seconds ( ' + diff + ' ms)');
            this.time = new Date();
        }
    }, {
        key: 'error',
        value: function error() {
            var args = this._insertParamToArguments(arguments);
            if (typeof console.error === 'function') {
                console.error.apply(console, args);
                return;
            }

            this.log('--- ERROR ---');
            this.log(args);
        }
    }, {
        key: 'warn',
        value: function warn() {
            var args = this._insertParamToArguments(arguments);
            if (typeof console.warn === 'function') {
                console.warn.apply(console, args);
                return;
            }

            this.log('--- WARN ---');
            this.log(args);
        }
    }]);

    return ConsoleShim;
}();

if (window.debugState.backend || window.debugState.frontend) {
    var globalLSConsole = new ConsoleShim('LSLOG');
    window.console.ls = globalLSConsole;
} else {
    var globalLSConsole = new ConsoleShim('LSLOG', true);
    window.console.ls = globalLSConsole;
}