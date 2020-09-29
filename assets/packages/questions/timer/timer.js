/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/main.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/main.js":
/*!*********************!*\
  !*** ./src/main.js ***!
  \*********************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _timeclass = __webpack_require__(/*! ./timeclass */ "./src/timeclass.js");

var _timeclass2 = _interopRequireDefault(_timeclass);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

window.countdown = function countdown(questionid, surveyid, timer, action, warning, warning2, warninghide, warning2hide, disable) {
    window.timerObjectSpace = window.timerObjectSpace || {};
    if (!window.timerObjectSpace[questionid]) {
        window.timerObjectSpace[questionid] = new _timeclass2.default({
            questionid: questionid,
            surveyid: surveyid,
            timer: timer,
            action: action,
            warning: warning,
            warning2: warning2,
            warninghide: warninghide,
            warning2hide: warning2hide,
            disabledElement: disable
        });
        window.timerObjectSpace[questionid].startTimer();
    }
}; /**
    * @file Script for timer
    * @copyright LimeSurvey <http://www.limesurvey.org>
    * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
    */

/***/ }),

/***/ "./src/timeclass.js":
/*!**************************!*\
  !*** ./src/timeclass.js ***!
  \**************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * @file Script for timer
 * @copyright LimeSurvey <http://www.limesurvey.org>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

var TimerConstructor = function () {
    _createClass(TimerConstructor, [{
        key: '_parseOptions',


        /* ##### private methods ##### */
        /**
         * Parses the options to default values if not set
         * @param Object options 
         * @return Object 
         */
        value: function _parseOptions(option) {
            return {
                questionid: option.questionid || null,
                surveyid: option.surveyid || null,
                timer: option.timer || 0,
                action: option.action || 1,
                warning: option.warning || 0,
                warning2: option.warning2 || 0,
                warninghide: option.warninghide || 0,
                warning2hide: option.warning2hide || 0,
                disabledElement: option.disabledElement || null
            };
        }

        /**
         * Takes a duration in seconds and creates an object containing the duration in hours, minutes and seconds
         * @param int seconds The duration in seconds
         * @return Object Contains hours, minutes and seconds
         */

    }, {
        key: '_parseTimeToObject',
        value: function _parseTimeToObject(secLeft, asStrings) {
            asStrings = asStrings || false;

            var oDuration = moment.duration(secLeft, 'seconds');
            var sHours = String(oDuration.hours()),
                sMinutes = String(oDuration.minutes()),
                sSeconds = String(oDuration.seconds());

            return {
                hours: asStrings ? sHours.length == 1 ? '0' + sHours : sHours : parseInt(sHours),
                minutes: asStrings ? sMinutes.length == 1 ? '0' + sMinutes : sMinutes : parseInt(sMinutes),
                seconds: asStrings ? sSeconds.length == 1 ? '0' + sSeconds : sSeconds : parseInt(sSecond)
            };
        }

        /**
         * The actions done on each step and the trigger to the finishing action
         */

    }, {
        key: '_intervalStep',
        value: function _intervalStep() {
            var currentTimeLeft = this._getTimerFromLocalStorage();
            currentTimeLeft = parseInt(currentTimeLeft) - 1;
            this.timerLogger.log('Interval emitted | seconds left:', currentTimeLeft);
            if (currentTimeLeft <= 0) {
                this.finishTimer();
            }
            this._checkForWarning(currentTimeLeft);
            this._setTimerToLocalStorage(currentTimeLeft);
            this._setTimer(currentTimeLeft);
        }

        /**
         * Set the interval to update the timer visuals
         */

    }, {
        key: '_setInterval',
        value: function _setInterval() {
            var _this = this;

            if (this._existsDisplayElement()) {
                this._setTimer(this.option.timer);
                this.intervalObject = setInterval(function () {
                    return _this._intervalStep.apply(_this);
                }, 1000);
            }
        }

        /**
         * Unset the timer;
         */

    }, {
        key: '_unsetInterval',
        value: function _unsetInterval() {
            clearInterval(this.intervalObject);
            this.intervalObject = null;
        }
    }, {
        key: '_existsDisplayElement',
        value: function _existsDisplayElement() {
            if (!this.$timerDisplayElement().length > 0) {
                this._unsetInterval();
                return false;
            }
            return true;
        }

        /**
         * Sets the timer to the display element
         */

    }, {
        key: '_setTimer',
        value: function _setTimer(currentTimeLeft) {
            var timeObject = this._parseTimeToObject(currentTimeLeft, true);
            if (this._existsDisplayElement()) {
                this.$timerDisplayElement().css({
                    display: 'flex'
                }).html(this.$countDownMessageElement.html() + "&nbsp;&nbsp;<div class='ls-timer-time'>" + timeObject.hours + ':' + timeObject.minutes + ':' + timeObject.seconds + "</div>");
            }
        }

        /**
         * Checks if a warning should be shown relative to the interval
         * @param int currentTime The current amount of seconds gone
         */

    }, {
        key: '_checkForWarning',
        value: function _checkForWarning(currentTime) {
            if (currentTime == this.option.warning) {
                this._showWarning();
            }
            if (currentTime == this.option.warning2) {
                this._showWarning2();
            }
        }
        /**
         * Shows the warning and fades it out after the set amount of time
         */

    }, {
        key: '_showWarning',
        value: function _showWarning() {
            var _this2 = this;

            if (this.option.warning !== 0) {
                this.timerLogger.log('Warning called!');
                this.$warningDisplayElement.removeClass('hidden').css({
                    opacity: 0
                }).animate({
                    'opacity': 1
                }, 200);
                setTimeout(function () {
                    _this2.timerLogger.log('Warning ended!');
                    _this2.$warningDisplayElement.animate({
                        opacity: 0
                    }, 200, function () {
                        _this2.$warningDisplayElement.addClass('hidden');
                    });
                }, 1000 * this.option.warninghide);
            }
        }

        /**
         * Shows the warning2 and fades it out after the set amount of time
         */

    }, {
        key: '_showWarning2',
        value: function _showWarning2() {
            var _this3 = this;

            if (this.option.warning2 !== 0) {
                this.timerLogger.log('Warning2 called!');
                this.$warning2DisplayElement.removeClass('hidden').css({
                    opacity: 0
                }).animate({
                    'opacity': 1
                }, 200);
                setTimeout(function () {
                    _this3.timerLogger.log('Warning2 ended!');
                    _this3.$warning2DisplayElement.animate({
                        opacity: 0
                    }, 200, function () {
                        _this3.$warning2DisplayElement.addClass('hidden');
                    });
                }, 1000 * this.option.warning2hide);
            }
        }

        /**
         * Disables the navigation buttons if necessary
         */

    }, {
        key: '_disableNavigation',
        value: function _disableNavigation() {
            var _this4 = this;

            this.timerLogger.log('Disabling navigation');
            $('.ls-move-previous-btn').each(function (i, item) {
                $(item).prop('disabled', _this4.disable_prev == 1);
            });
            $('.ls-move-next-btn,.ls-move-submit-btn').each(function (i, item) {
                $(item).prop('disabled', _this4.disable_next == 1);
            });
        }

        /**
         * Enables the navigation buttons
         */

    }, {
        key: '_enableNavigation',
        value: function _enableNavigation() {
            $('.ls-move-previous-btn').each(function () {
                $(this).prop('disabled', false);
            });
            $('.ls-move-next-btn,.ls-move-submit-btn').each(function () {
                $(this).prop('disabled', false);
            });
        }

        /**
         * Gets the current timer from the localStorage
         */

    }, {
        key: '_getTimerFromLocalStorage',
        value: function _getTimerFromLocalStorage() {
            var timeLeft = window.localStorage.getItem('limesurvey_timers_' + this.timersessionname);
            return !isNaN(parseInt(timeLeft)) ? timeLeft : 0;
        }

        /**
         * Sets the current timer to localStorage
         */

    }, {
        key: '_setTimerToLocalStorage',
        value: function _setTimerToLocalStorage(timerValue) {
            window.localStorage.setItem('limesurvey_timers_' + this.timersessionname, timerValue);
        }

        /**
         * Appends the current timer's qid to the list of timers for the survey
         */

    }, {
        key: '_appendTimerToSurveyTimersList',
        value: function _appendTimerToSurveyTimersList() {
            var timers = JSON.parse(window.localStorage.getItem(this.surveyTimersItemName) || "[]");
            if (!timers.includes(this.timersessionname)) timers.push(this.timersessionname);
            window.localStorage.setItem(this.surveyTimersItemName, JSON.stringify(timers));
        }

        /**
         * Unsets the timer in localStorage
         */

    }, {
        key: '_unsetTimerInLocalStorage',
        value: function _unsetTimerInLocalStorage() {
            window.localStorage.removeItem('limesurvey_timers_' + this.timersessionname);
        }

        /**
         * Finalize Method to show a warning and then redirect
         */

    }, {
        key: '_warnBeforeRedirection',
        value: function _warnBeforeRedirection() {
            this._disableInput();
            setTimeout(this._redirectOut, this.redirectWarnTime);
        }

        /**
         * Finalize method to just diable the input
         */

    }, {
        key: '_disableInput',
        value: function _disableInput() {
            this.$toBeDisabledElement.prop('readonly', true);
            $('#question' + this.option.questionid).find('.answer-container').children('div').not('.timer_header').fadeOut();
        }

        /**
         * Show the notice that the time is up and the input is expired
         */

    }, {
        key: '_showExpiredNotice',
        value: function _showExpiredNotice() {
            this.$timerExpiredElement.removeClass('hidden');
        }

        /**
         * redirect to the next page
         */

    }, {
        key: '_redirectOut',
        value: function _redirectOut() {
            $('#ls-button-submit').trigger('click');
        }
        /**
         * Binds the reset of the localStorage as soon as the participant has submitted the form
         */

    }, {
        key: '_bindUnsetToSubmit',
        value: function _bindUnsetToSubmit() {
            var _this5 = this;

            $('#limesurvey').on('submit', function () {
                _this5._unsetTimerInLocalStorage();
            });
        }

        /* ##### public methods ##### */

        /**
         * Finishing action
         * Unsets all timers and intervals and then triggers the defined action.
         * Either redirect, invalidate or warn before redirect
         */

    }, {
        key: 'finishTimer',
        value: function finishTimer() {

            this.timerLogger.log('Timer has ended or was ended');
            this._unsetInterval();
            this._enableNavigation();
            this._bindUnsetToSubmit();
            this._disableInput();

            switch (this.option.action) {
                case 3:
                    //Just warn, don't move on
                    this._showExpiredNotice();
                    break;
                case 2:
                    //Just move on, no warning
                    this._redirectOut();
                    break;
                case 1: //fallthrough
                default:
                    //Warn and move on
                    this._showExpiredNotice();
                    this._warnBeforeRedirection();
                    break;

            }
        }

        /** 
         * Starts the timer
         * Sts the interval to visualize the timer and the timeouts for the warnings.
         */

    }, {
        key: 'startTimer',
        value: function startTimer() {
            if (this.timeLeft == 0) {
                this.finishTimer();
                return;
            }
            this._appendTimerToSurveyTimersList();
            this._setTimerToLocalStorage(this.timeLeft);
            this._disableNavigation();
            this._setInterval();
        }
    }]);

    function TimerConstructor(options) {
        var _this6 = this;

        _classCallCheck(this, TimerConstructor);

        /* ##### define state and closure vars ##### */
        this.option = this._parseOptions(options);

        this.timerWarning = null;
        this.timerWarning2 = null;
        this.timerLogger = new ConsoleShim('TIMER#' + options.questionid, !window.debugState.frontend);
        this.intervalObject = null;
        this.warning = 0;
        this.timersessionname = 'timer_question_' + this.option.questionid;
        this.surveyTimersItemName = 'limesurvey_timers_by_sid_' + this.option.surveyid;

        // Unset timer in local storage if the reset timers flag is set
        if (LSvar.bResetQuestionTimers) this._unsetTimerInLocalStorage();

        this.timeLeft = this._getTimerFromLocalStorage() || this.option.timer;
        this.disable_next = $("#disablenext-" + this.timersessionname).val();
        this.disable_prev = $("#disableprev-" + this.timersessionname).val();

        //jQuery Elements
        this.$timerDisplayElement = function () {
            return $('#LS_question' + _this6.option.questionid + '_Timer');
        };
        this.$timerExpiredElement = $('#question' + this.option.questionid + '_timer');
        this.$warningTimeDisplayElement = $('#LS_question' + this.option.questionid + '_Warning');
        this.$warningDisplayElement = $('#LS_question' + this.option.questionid + '_warning');
        this.$warning2TimeDisplayElement = $('#LS_question' + this.option.questionid + '_Warning_2');
        this.$warning2DisplayElement = $('#LS_question' + this.option.questionid + '_warning_2');
        this.$countDownMessageElement = $("#countdown-message-" + this.timersessionname);
        this.redirectWarnTime = $('#message-delay-' + this.timersessionname).val();
        this.$toBeDisabledElement = $('#' + this.option.disabledElement);

        this.timerLogger.log('Options set:', this.option);

        return {
            startTimer: function startTimer() {
                return _this6.startTimer.apply(_this6);
            },
            finishTimer: function finishTimer() {
                return _this6.finishTimer.apply(_this6);
            }
        };
    }

    return TimerConstructor;
}();

exports.default = TimerConstructor;
;

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vc3JjL21haW4uanMiLCJ3ZWJwYWNrOi8vLy4vc3JjL3RpbWVjbGFzcy5qcyJdLCJuYW1lcyI6WyJ3aW5kb3ciLCJjb3VudGRvd24iLCJxdWVzdGlvbmlkIiwic3VydmV5aWQiLCJ0aW1lciIsImFjdGlvbiIsIndhcm5pbmciLCJ3YXJuaW5nMiIsIndhcm5pbmdoaWRlIiwid2FybmluZzJoaWRlIiwiZGlzYWJsZSIsInRpbWVyT2JqZWN0U3BhY2UiLCJUaW1lckNvbnN0cnVjdG9yIiwiZGlzYWJsZWRFbGVtZW50Iiwic3RhcnRUaW1lciIsIm9wdGlvbiIsInNlY0xlZnQiLCJhc1N0cmluZ3MiLCJvRHVyYXRpb24iLCJtb21lbnQiLCJkdXJhdGlvbiIsInNIb3VycyIsIlN0cmluZyIsImhvdXJzIiwic01pbnV0ZXMiLCJtaW51dGVzIiwic1NlY29uZHMiLCJzZWNvbmRzIiwibGVuZ3RoIiwicGFyc2VJbnQiLCJzU2Vjb25kIiwiY3VycmVudFRpbWVMZWZ0IiwiX2dldFRpbWVyRnJvbUxvY2FsU3RvcmFnZSIsInRpbWVyTG9nZ2VyIiwibG9nIiwiZmluaXNoVGltZXIiLCJfY2hlY2tGb3JXYXJuaW5nIiwiX3NldFRpbWVyVG9Mb2NhbFN0b3JhZ2UiLCJfc2V0VGltZXIiLCJfZXhpc3RzRGlzcGxheUVsZW1lbnQiLCJpbnRlcnZhbE9iamVjdCIsInNldEludGVydmFsIiwiX2ludGVydmFsU3RlcCIsImFwcGx5IiwiY2xlYXJJbnRlcnZhbCIsIiR0aW1lckRpc3BsYXlFbGVtZW50IiwiX3Vuc2V0SW50ZXJ2YWwiLCJ0aW1lT2JqZWN0IiwiX3BhcnNlVGltZVRvT2JqZWN0IiwiY3NzIiwiZGlzcGxheSIsImh0bWwiLCIkY291bnREb3duTWVzc2FnZUVsZW1lbnQiLCJjdXJyZW50VGltZSIsIl9zaG93V2FybmluZyIsIl9zaG93V2FybmluZzIiLCIkd2FybmluZ0Rpc3BsYXlFbGVtZW50IiwicmVtb3ZlQ2xhc3MiLCJvcGFjaXR5IiwiYW5pbWF0ZSIsInNldFRpbWVvdXQiLCJhZGRDbGFzcyIsIiR3YXJuaW5nMkRpc3BsYXlFbGVtZW50IiwiJCIsImVhY2giLCJpIiwiaXRlbSIsInByb3AiLCJkaXNhYmxlX3ByZXYiLCJkaXNhYmxlX25leHQiLCJ0aW1lTGVmdCIsImxvY2FsU3RvcmFnZSIsImdldEl0ZW0iLCJ0aW1lcnNlc3Npb25uYW1lIiwiaXNOYU4iLCJ0aW1lclZhbHVlIiwic2V0SXRlbSIsInRpbWVycyIsIkpTT04iLCJwYXJzZSIsInN1cnZleVRpbWVyc0l0ZW1OYW1lIiwiaW5jbHVkZXMiLCJwdXNoIiwic3RyaW5naWZ5IiwicmVtb3ZlSXRlbSIsIl9kaXNhYmxlSW5wdXQiLCJfcmVkaXJlY3RPdXQiLCJyZWRpcmVjdFdhcm5UaW1lIiwiJHRvQmVEaXNhYmxlZEVsZW1lbnQiLCJmaW5kIiwiY2hpbGRyZW4iLCJub3QiLCJmYWRlT3V0IiwiJHRpbWVyRXhwaXJlZEVsZW1lbnQiLCJ0cmlnZ2VyIiwib24iLCJfdW5zZXRUaW1lckluTG9jYWxTdG9yYWdlIiwiX2VuYWJsZU5hdmlnYXRpb24iLCJfYmluZFVuc2V0VG9TdWJtaXQiLCJfc2hvd0V4cGlyZWROb3RpY2UiLCJfd2FybkJlZm9yZVJlZGlyZWN0aW9uIiwiX2FwcGVuZFRpbWVyVG9TdXJ2ZXlUaW1lcnNMaXN0IiwiX2Rpc2FibGVOYXZpZ2F0aW9uIiwiX3NldEludGVydmFsIiwib3B0aW9ucyIsIl9wYXJzZU9wdGlvbnMiLCJ0aW1lcldhcm5pbmciLCJ0aW1lcldhcm5pbmcyIiwiQ29uc29sZVNoaW0iLCJkZWJ1Z1N0YXRlIiwiZnJvbnRlbmQiLCJMU3ZhciIsImJSZXNldFF1ZXN0aW9uVGltZXJzIiwidmFsIiwiJHdhcm5pbmdUaW1lRGlzcGxheUVsZW1lbnQiLCIkd2FybmluZzJUaW1lRGlzcGxheUVsZW1lbnQiXSwibWFwcGluZ3MiOiI7QUFBQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFD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NkIsS0FBaEIsQ0FBc0IsTUFBdEIsQ0FBTjtBQUFBLGFBRFQ7QUFFSFIseUJBQWE7QUFBQSx1QkFBTSxPQUFLQSxXQUFMLENBQWlCUSxLQUFqQixDQUF1QixNQUF2QixDQUFOO0FBQUE7QUFGVixTQUFQO0FBSUg7Ozs7O2tCQXpVZ0IvQixnQjtBQTBVcEIsQyIsImZpbGUiOiJ0aW1lci5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGdldHRlciB9KTtcbiBcdFx0fVxuIFx0fTtcblxuIFx0Ly8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yID0gZnVuY3Rpb24oZXhwb3J0cykge1xuIFx0XHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcbiBcdFx0fVxuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xuIFx0fTtcblxuIFx0Ly8gY3JlYXRlIGEgZmFrZSBuYW1lc3BhY2Ugb2JqZWN0XG4gXHQvLyBtb2RlICYgMTogdmFsdWUgaXMgYSBtb2R1bGUgaWQsIHJlcXVpcmUgaXRcbiBcdC8vIG1vZGUgJiAyOiBtZXJnZSBhbGwgcHJvcGVydGllcyBvZiB2YWx1ZSBpbnRvIHRoZSBuc1xuIFx0Ly8gbW9kZSAmIDQ6IHJldHVybiB2YWx1ZSB3aGVuIGFscmVhZHkgbnMgb2JqZWN0XG4gXHQvLyBtb2RlICYgOHwxOiBiZWhhdmUgbGlrZSByZXF1aXJlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnQgPSBmdW5jdGlvbih2YWx1ZSwgbW9kZSkge1xuIFx0XHRpZihtb2RlICYgMSkgdmFsdWUgPSBfX3dlYnBhY2tfcmVxdWlyZV9fKHZhbHVlKTtcbiBcdFx0aWYobW9kZSAmIDgpIHJldHVybiB2YWx1ZTtcbiBcdFx0aWYoKG1vZGUgJiA0KSAmJiB0eXBlb2YgdmFsdWUgPT09ICdvYmplY3QnICYmIHZhbHVlICYmIHZhbHVlLl9fZXNNb2R1bGUpIHJldHVybiB2YWx1ZTtcbiBcdFx0dmFyIG5zID0gT2JqZWN0LmNyZWF0ZShudWxsKTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yKG5zKTtcbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KG5zLCAnZGVmYXVsdCcsIHsgZW51bWVyYWJsZTogdHJ1ZSwgdmFsdWU6IHZhbHVlIH0pO1xuIFx0XHRpZihtb2RlICYgMiAmJiB0eXBlb2YgdmFsdWUgIT0gJ3N0cmluZycpIGZvcih2YXIga2V5IGluIHZhbHVlKSBfX3dlYnBhY2tfcmVxdWlyZV9fLmQobnMsIGtleSwgZnVuY3Rpb24oa2V5KSB7IHJldHVybiB2YWx1ZVtrZXldOyB9LmJpbmQobnVsbCwga2V5KSk7XG4gXHRcdHJldHVybiBucztcbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiXCI7XG5cblxuIFx0Ly8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4gXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhfX3dlYnBhY2tfcmVxdWlyZV9fLnMgPSBcIi4vc3JjL21haW4uanNcIik7XG4iLCIvKipcbiAqIEBmaWxlIFNjcmlwdCBmb3IgdGltZXJcbiAqIEBjb3B5cmlnaHQgTGltZVN1cnZleSA8aHR0cDovL3d3dy5saW1lc3VydmV5Lm9yZz5cbiAqIEBsaWNlbnNlIG1hZ25ldDo/eHQ9dXJuOmJ0aWg6MWY3MzlkOTM1Njc2MTExY2ZmZjRiNDY5M2UzODE2ZTY2NDc5NzA1MCZkbj1ncGwtMy4wLnR4dCBHUEwtdjMtb3ItTGF0ZXJcbiAqL1xuXG5pbXBvcnQgVGltZXJDb25zdHJ1Y3RvciBmcm9tICcuL3RpbWVjbGFzcyc7XG5cbndpbmRvdy5jb3VudGRvd24gPSBmdW5jdGlvbiBjb3VudGRvd24ocXVlc3Rpb25pZCwgc3VydmV5aWQsIHRpbWVyLCBhY3Rpb24sIHdhcm5pbmcsIHdhcm5pbmcyLCB3YXJuaW5naGlkZSwgd2FybmluZzJoaWRlLCBkaXNhYmxlKSB7XG4gICAgd2luZG93LnRpbWVyT2JqZWN0U3BhY2UgPSB3aW5kb3cudGltZXJPYmplY3RTcGFjZSB8fCB7fTtcbiAgICBpZiAoIXdpbmRvdy50aW1lck9iamVjdFNwYWNlW3F1ZXN0aW9uaWRdKSB7XG4gICAgICAgIHdpbmRvdy50aW1lck9iamVjdFNwYWNlW3F1ZXN0aW9uaWRdID0gbmV3IFRpbWVyQ29uc3RydWN0b3Ioe1xuICAgICAgICAgICAgcXVlc3Rpb25pZDogcXVlc3Rpb25pZCxcbiAgICAgICAgICAgIHN1cnZleWlkOiBzdXJ2ZXlpZCxcbiAgICAgICAgICAgIHRpbWVyOiB0aW1lcixcbiAgICAgICAgICAgIGFjdGlvbjogYWN0aW9uLFxuICAgICAgICAgICAgd2FybmluZzogd2FybmluZyxcbiAgICAgICAgICAgIHdhcm5pbmcyOiB3YXJuaW5nMixcbiAgICAgICAgICAgIHdhcm5pbmdoaWRlOiB3YXJuaW5naGlkZSxcbiAgICAgICAgICAgIHdhcm5pbmcyaGlkZTogd2FybmluZzJoaWRlLFxuICAgICAgICAgICAgZGlzYWJsZWRFbGVtZW50OiBkaXNhYmxlXG4gICAgICAgIH0pO1xuICAgICAgICB3aW5kb3cudGltZXJPYmplY3RTcGFjZVtxdWVzdGlvbmlkXS5zdGFydFRpbWVyKCk7XG4gICAgfVxufVxuIiwiLyoqXG4gKiBAZmlsZSBTY3JpcHQgZm9yIHRpbWVyXG4gKiBAY29weXJpZ2h0IExpbWVTdXJ2ZXkgPGh0dHA6Ly93d3cubGltZXN1cnZleS5vcmc+XG4gKiBAbGljZW5zZSBtYWduZXQ6P3h0PXVybjpidGloOjFmNzM5ZDkzNTY3NjExMWNmZmY0YjQ2OTNlMzgxNmU2NjQ3OTcwNTAmZG49Z3BsLTMuMC50eHQgR1BMLXYzLW9yLUxhdGVyXG4gKi9cblxuZXhwb3J0IGRlZmF1bHQgY2xhc3MgVGltZXJDb25zdHJ1Y3RvciB7XG5cbiAgICAvKiAjIyMjIyBwcml2YXRlIG1ldGhvZHMgIyMjIyMgKi9cbiAgICAvKipcbiAgICAgKiBQYXJzZXMgdGhlIG9wdGlvbnMgdG8gZGVmYXVsdCB2YWx1ZXMgaWYgbm90IHNldFxuICAgICAqIEBwYXJhbSBPYmplY3Qgb3B0aW9ucyBcbiAgICAgKiBAcmV0dXJuIE9iamVjdCBcbiAgICAgKi9cbiAgICBfcGFyc2VPcHRpb25zKG9wdGlvbikge1xuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgcXVlc3Rpb25pZDogb3B0aW9uLnF1ZXN0aW9uaWQgfHwgbnVsbCxcbiAgICAgICAgICAgIHN1cnZleWlkOiBvcHRpb24uc3VydmV5aWQgfHwgbnVsbCxcbiAgICAgICAgICAgIHRpbWVyOiBvcHRpb24udGltZXIgfHwgMCxcbiAgICAgICAgICAgIGFjdGlvbjogb3B0aW9uLmFjdGlvbiB8fCAxLFxuICAgICAgICAgICAgd2FybmluZzogb3B0aW9uLndhcm5pbmcgfHwgMCxcbiAgICAgICAgICAgIHdhcm5pbmcyOiBvcHRpb24ud2FybmluZzIgfHwgMCxcbiAgICAgICAgICAgIHdhcm5pbmdoaWRlOiBvcHRpb24ud2FybmluZ2hpZGUgfHwgMCxcbiAgICAgICAgICAgIHdhcm5pbmcyaGlkZTogb3B0aW9uLndhcm5pbmcyaGlkZSB8fCAwLFxuICAgICAgICAgICAgZGlzYWJsZWRFbGVtZW50OiBvcHRpb24uZGlzYWJsZWRFbGVtZW50IHx8IG51bGwsXG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBUYWtlcyBhIGR1cmF0aW9uIGluIHNlY29uZHMgYW5kIGNyZWF0ZXMgYW4gb2JqZWN0IGNvbnRhaW5pbmcgdGhlIGR1cmF0aW9uIGluIGhvdXJzLCBtaW51dGVzIGFuZCBzZWNvbmRzXG4gICAgICogQHBhcmFtIGludCBzZWNvbmRzIFRoZSBkdXJhdGlvbiBpbiBzZWNvbmRzXG4gICAgICogQHJldHVybiBPYmplY3QgQ29udGFpbnMgaG91cnMsIG1pbnV0ZXMgYW5kIHNlY29uZHNcbiAgICAgKi9cbiAgICBfcGFyc2VUaW1lVG9PYmplY3Qoc2VjTGVmdCwgYXNTdHJpbmdzKSB7XG4gICAgICAgIGFzU3RyaW5ncyA9IGFzU3RyaW5ncyB8fCBmYWxzZTtcblxuICAgICAgICBjb25zdCBvRHVyYXRpb24gPSBtb21lbnQuZHVyYXRpb24oc2VjTGVmdCwgJ3NlY29uZHMnKTtcbiAgICAgICAgbGV0IHNIb3VycyA9IFN0cmluZyhvRHVyYXRpb24uaG91cnMoKSksXG4gICAgICAgICAgICBzTWludXRlcyA9IFN0cmluZyhvRHVyYXRpb24ubWludXRlcygpKSxcbiAgICAgICAgICAgIHNTZWNvbmRzID0gU3RyaW5nKG9EdXJhdGlvbi5zZWNvbmRzKCkpO1xuXG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICBob3VyczogYXNTdHJpbmdzID8gKHNIb3Vycy5sZW5ndGggPT0gMSA/ICcwJyArIHNIb3VycyA6IHNIb3VycykgOiBwYXJzZUludChzSG91cnMpLFxuICAgICAgICAgICAgbWludXRlczogYXNTdHJpbmdzID8gKHNNaW51dGVzLmxlbmd0aCA9PSAxID8gJzAnICsgc01pbnV0ZXMgOiBzTWludXRlcykgOiBwYXJzZUludChzTWludXRlcyksXG4gICAgICAgICAgICBzZWNvbmRzOiBhc1N0cmluZ3MgPyAoc1NlY29uZHMubGVuZ3RoID09IDEgPyAnMCcgKyBzU2Vjb25kcyA6IHNTZWNvbmRzKSA6IHBhcnNlSW50KHNTZWNvbmQpXG4gICAgICAgIH07XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogVGhlIGFjdGlvbnMgZG9uZSBvbiBlYWNoIHN0ZXAgYW5kIHRoZSB0cmlnZ2VyIHRvIHRoZSBmaW5pc2hpbmcgYWN0aW9uXG4gICAgICovXG4gICAgX2ludGVydmFsU3RlcCgpIHtcbiAgICAgICAgbGV0IGN1cnJlbnRUaW1lTGVmdCA9IHRoaXMuX2dldFRpbWVyRnJvbUxvY2FsU3RvcmFnZSgpO1xuICAgICAgICBjdXJyZW50VGltZUxlZnQgPSBwYXJzZUludChjdXJyZW50VGltZUxlZnQpIC0gMTtcbiAgICAgICAgdGhpcy50aW1lckxvZ2dlci5sb2coJ0ludGVydmFsIGVtaXR0ZWQgfCBzZWNvbmRzIGxlZnQ6JywgY3VycmVudFRpbWVMZWZ0KTtcbiAgICAgICAgaWYgKGN1cnJlbnRUaW1lTGVmdCA8PSAwKSB7XG4gICAgICAgICAgICB0aGlzLmZpbmlzaFRpbWVyKCk7XG4gICAgICAgIH1cbiAgICAgICAgdGhpcy5fY2hlY2tGb3JXYXJuaW5nKGN1cnJlbnRUaW1lTGVmdCk7XG4gICAgICAgIHRoaXMuX3NldFRpbWVyVG9Mb2NhbFN0b3JhZ2UoY3VycmVudFRpbWVMZWZ0KTtcbiAgICAgICAgdGhpcy5fc2V0VGltZXIoY3VycmVudFRpbWVMZWZ0KTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXQgdGhlIGludGVydmFsIHRvIHVwZGF0ZSB0aGUgdGltZXIgdmlzdWFsc1xuICAgICAqL1xuICAgIF9zZXRJbnRlcnZhbCgpIHtcbiAgICAgICAgaWYgKHRoaXMuX2V4aXN0c0Rpc3BsYXlFbGVtZW50KCkpIHtcbiAgICAgICAgICAgIHRoaXMuX3NldFRpbWVyKHRoaXMub3B0aW9uLnRpbWVyKTtcbiAgICAgICAgICAgIHRoaXMuaW50ZXJ2YWxPYmplY3QgPSBzZXRJbnRlcnZhbCgoKSA9PiB0aGlzLl9pbnRlcnZhbFN0ZXAuYXBwbHkodGhpcyksIDEwMDApO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogVW5zZXQgdGhlIHRpbWVyO1xuICAgICAqL1xuICAgIF91bnNldEludGVydmFsKCkge1xuICAgICAgICBjbGVhckludGVydmFsKHRoaXMuaW50ZXJ2YWxPYmplY3QpO1xuICAgICAgICB0aGlzLmludGVydmFsT2JqZWN0ID0gbnVsbDtcbiAgICB9XG5cbiAgICBfZXhpc3RzRGlzcGxheUVsZW1lbnQoKSB7XG4gICAgICAgIGlmICghdGhpcy4kdGltZXJEaXNwbGF5RWxlbWVudCgpLmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgIHRoaXMuX3Vuc2V0SW50ZXJ2YWwoKTtcbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXRzIHRoZSB0aW1lciB0byB0aGUgZGlzcGxheSBlbGVtZW50XG4gICAgICovXG4gICAgX3NldFRpbWVyKGN1cnJlbnRUaW1lTGVmdCkge1xuICAgICAgICBjb25zdCB0aW1lT2JqZWN0ID0gdGhpcy5fcGFyc2VUaW1lVG9PYmplY3QoY3VycmVudFRpbWVMZWZ0LCB0cnVlKTtcbiAgICAgICAgaWYgKHRoaXMuX2V4aXN0c0Rpc3BsYXlFbGVtZW50KCkpIHtcbiAgICAgICAgICAgIHRoaXMuJHRpbWVyRGlzcGxheUVsZW1lbnQoKVxuICAgICAgICAgICAgICAgIC5jc3Moe1xuICAgICAgICAgICAgICAgICAgICBkaXNwbGF5OiAnZmxleCdcbiAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgICAgIC5odG1sKHRoaXMuJGNvdW50RG93bk1lc3NhZ2VFbGVtZW50Lmh0bWwoKSArIFwiJm5ic3A7Jm5ic3A7PGRpdiBjbGFzcz0nbHMtdGltZXItdGltZSc+XCIgKyB0aW1lT2JqZWN0LmhvdXJzICsgJzonICsgdGltZU9iamVjdC5taW51dGVzICsgJzonICsgdGltZU9iamVjdC5zZWNvbmRzICsgXCI8L2Rpdj5cIik7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDaGVja3MgaWYgYSB3YXJuaW5nIHNob3VsZCBiZSBzaG93biByZWxhdGl2ZSB0byB0aGUgaW50ZXJ2YWxcbiAgICAgKiBAcGFyYW0gaW50IGN1cnJlbnRUaW1lIFRoZSBjdXJyZW50IGFtb3VudCBvZiBzZWNvbmRzIGdvbmVcbiAgICAgKi9cbiAgICBfY2hlY2tGb3JXYXJuaW5nKGN1cnJlbnRUaW1lKSB7XG4gICAgICAgIGlmIChjdXJyZW50VGltZSA9PSB0aGlzLm9wdGlvbi53YXJuaW5nKSB7XG4gICAgICAgICAgICB0aGlzLl9zaG93V2FybmluZygpO1xuICAgICAgICB9XG4gICAgICAgIGlmIChjdXJyZW50VGltZSA9PSB0aGlzLm9wdGlvbi53YXJuaW5nMikge1xuICAgICAgICAgICAgdGhpcy5fc2hvd1dhcm5pbmcyKCk7XG4gICAgICAgIH1cbiAgICB9XG4gICAgLyoqXG4gICAgICogU2hvd3MgdGhlIHdhcm5pbmcgYW5kIGZhZGVzIGl0IG91dCBhZnRlciB0aGUgc2V0IGFtb3VudCBvZiB0aW1lXG4gICAgICovXG4gICAgX3Nob3dXYXJuaW5nKCkge1xuICAgICAgICBpZiAodGhpcy5vcHRpb24ud2FybmluZyAhPT0gMCkge1xuICAgICAgICAgICAgdGhpcy50aW1lckxvZ2dlci5sb2coJ1dhcm5pbmcgY2FsbGVkIScpO1xuICAgICAgICAgICAgdGhpcy4kd2FybmluZ0Rpc3BsYXlFbGVtZW50LnJlbW92ZUNsYXNzKCdoaWRkZW4nKS5jc3Moe1xuICAgICAgICAgICAgICAgIG9wYWNpdHk6IDBcbiAgICAgICAgICAgIH0pLmFuaW1hdGUoe1xuICAgICAgICAgICAgICAgICdvcGFjaXR5JzogMVxuICAgICAgICAgICAgfSwgMjAwKTtcbiAgICAgICAgICAgIHNldFRpbWVvdXQoKCkgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMudGltZXJMb2dnZXIubG9nKCdXYXJuaW5nIGVuZGVkIScpO1xuICAgICAgICAgICAgICAgIHRoaXMuJHdhcm5pbmdEaXNwbGF5RWxlbWVudC5hbmltYXRlKHtcbiAgICAgICAgICAgICAgICAgICAgb3BhY2l0eTogMFxuICAgICAgICAgICAgICAgIH0sIDIwMCwgKCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICB0aGlzLiR3YXJuaW5nRGlzcGxheUVsZW1lbnQuYWRkQ2xhc3MoJ2hpZGRlbicpO1xuICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICB9LCAxMDAwICogdGhpcy5vcHRpb24ud2FybmluZ2hpZGUpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogU2hvd3MgdGhlIHdhcm5pbmcyIGFuZCBmYWRlcyBpdCBvdXQgYWZ0ZXIgdGhlIHNldCBhbW91bnQgb2YgdGltZVxuICAgICAqL1xuICAgIF9zaG93V2FybmluZzIoKSB7XG4gICAgICAgIGlmICh0aGlzLm9wdGlvbi53YXJuaW5nMiAhPT0gMCkge1xuICAgICAgICAgICAgdGhpcy50aW1lckxvZ2dlci5sb2coJ1dhcm5pbmcyIGNhbGxlZCEnKTtcbiAgICAgICAgICAgIHRoaXMuJHdhcm5pbmcyRGlzcGxheUVsZW1lbnQucmVtb3ZlQ2xhc3MoJ2hpZGRlbicpLmNzcyh7XG4gICAgICAgICAgICAgICAgb3BhY2l0eTogMFxuICAgICAgICAgICAgfSkuYW5pbWF0ZSh7XG4gICAgICAgICAgICAgICAgJ29wYWNpdHknOiAxXG4gICAgICAgICAgICB9LCAyMDApO1xuICAgICAgICAgICAgc2V0VGltZW91dCgoKSA9PiB7XG4gICAgICAgICAgICAgICAgdGhpcy50aW1lckxvZ2dlci5sb2coJ1dhcm5pbmcyIGVuZGVkIScpO1xuICAgICAgICAgICAgICAgIHRoaXMuJHdhcm5pbmcyRGlzcGxheUVsZW1lbnQuYW5pbWF0ZSh7XG4gICAgICAgICAgICAgICAgICAgIG9wYWNpdHk6IDBcbiAgICAgICAgICAgICAgICB9LCAyMDAsICgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy4kd2FybmluZzJEaXNwbGF5RWxlbWVudC5hZGRDbGFzcygnaGlkZGVuJyk7XG4gICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgIH0sIDEwMDAgKiB0aGlzLm9wdGlvbi53YXJuaW5nMmhpZGUpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogRGlzYWJsZXMgdGhlIG5hdmlnYXRpb24gYnV0dG9ucyBpZiBuZWNlc3NhcnlcbiAgICAgKi9cbiAgICBfZGlzYWJsZU5hdmlnYXRpb24oKSB7XG4gICAgICAgIHRoaXMudGltZXJMb2dnZXIubG9nKCdEaXNhYmxpbmcgbmF2aWdhdGlvbicpO1xuICAgICAgICAkKCcubHMtbW92ZS1wcmV2aW91cy1idG4nKS5lYWNoKChpLCBpdGVtKSA9PiB7XG4gICAgICAgICAgICAkKGl0ZW0pLnByb3AoJ2Rpc2FibGVkJywgKHRoaXMuZGlzYWJsZV9wcmV2ID09IDEpKTtcbiAgICAgICAgfSk7XG4gICAgICAgICQoJy5scy1tb3ZlLW5leHQtYnRuLC5scy1tb3ZlLXN1Ym1pdC1idG4nKS5lYWNoKChpLCBpdGVtKSA9PiB7XG4gICAgICAgICAgICAkKGl0ZW0pLnByb3AoJ2Rpc2FibGVkJywgKHRoaXMuZGlzYWJsZV9uZXh0ID09IDEpKTtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogRW5hYmxlcyB0aGUgbmF2aWdhdGlvbiBidXR0b25zXG4gICAgICovXG4gICAgX2VuYWJsZU5hdmlnYXRpb24oKSB7XG4gICAgICAgICQoJy5scy1tb3ZlLXByZXZpb3VzLWJ0bicpLmVhY2goZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgJCh0aGlzKS5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKTtcbiAgICAgICAgfSk7XG4gICAgICAgICQoJy5scy1tb3ZlLW5leHQtYnRuLC5scy1tb3ZlLXN1Ym1pdC1idG4nKS5lYWNoKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICQodGhpcykucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldHMgdGhlIGN1cnJlbnQgdGltZXIgZnJvbSB0aGUgbG9jYWxTdG9yYWdlXG4gICAgICovXG4gICAgX2dldFRpbWVyRnJvbUxvY2FsU3RvcmFnZSgpIHtcbiAgICAgICAgY29uc3QgdGltZUxlZnQgPSB3aW5kb3cubG9jYWxTdG9yYWdlLmdldEl0ZW0oJ2xpbWVzdXJ2ZXlfdGltZXJzXycgKyB0aGlzLnRpbWVyc2Vzc2lvbm5hbWUpO1xuICAgICAgICByZXR1cm4gKCFpc05hTihwYXJzZUludCh0aW1lTGVmdCkpID8gdGltZUxlZnQgOiAwKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXRzIHRoZSBjdXJyZW50IHRpbWVyIHRvIGxvY2FsU3RvcmFnZVxuICAgICAqL1xuICAgIF9zZXRUaW1lclRvTG9jYWxTdG9yYWdlKHRpbWVyVmFsdWUpIHtcbiAgICAgICAgd2luZG93LmxvY2FsU3RvcmFnZS5zZXRJdGVtKCdsaW1lc3VydmV5X3RpbWVyc18nICsgdGhpcy50aW1lcnNlc3Npb25uYW1lLCB0aW1lclZhbHVlKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBBcHBlbmRzIHRoZSBjdXJyZW50IHRpbWVyJ3MgcWlkIHRvIHRoZSBsaXN0IG9mIHRpbWVycyBmb3IgdGhlIHN1cnZleVxuICAgICAqL1xuICAgIF9hcHBlbmRUaW1lclRvU3VydmV5VGltZXJzTGlzdCgpIHtcbiAgICAgICAgdmFyIHRpbWVycyA9IEpTT04ucGFyc2Uod2luZG93LmxvY2FsU3RvcmFnZS5nZXRJdGVtKHRoaXMuc3VydmV5VGltZXJzSXRlbU5hbWUpIHx8IFwiW11cIik7XG4gICAgICAgIGlmICghdGltZXJzLmluY2x1ZGVzKHRoaXMudGltZXJzZXNzaW9ubmFtZSkpIHRpbWVycy5wdXNoKHRoaXMudGltZXJzZXNzaW9ubmFtZSk7XG4gICAgICAgIHdpbmRvdy5sb2NhbFN0b3JhZ2Uuc2V0SXRlbSh0aGlzLnN1cnZleVRpbWVyc0l0ZW1OYW1lLCBKU09OLnN0cmluZ2lmeSh0aW1lcnMpKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBVbnNldHMgdGhlIHRpbWVyIGluIGxvY2FsU3RvcmFnZVxuICAgICAqL1xuICAgIF91bnNldFRpbWVySW5Mb2NhbFN0b3JhZ2UoKSB7XG4gICAgICAgIHdpbmRvdy5sb2NhbFN0b3JhZ2UucmVtb3ZlSXRlbSgnbGltZXN1cnZleV90aW1lcnNfJyArIHRoaXMudGltZXJzZXNzaW9ubmFtZSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogRmluYWxpemUgTWV0aG9kIHRvIHNob3cgYSB3YXJuaW5nIGFuZCB0aGVuIHJlZGlyZWN0XG4gICAgICovXG4gICAgX3dhcm5CZWZvcmVSZWRpcmVjdGlvbigpIHtcbiAgICAgICAgdGhpcy5fZGlzYWJsZUlucHV0KCk7XG4gICAgICAgIHNldFRpbWVvdXQodGhpcy5fcmVkaXJlY3RPdXQsIHRoaXMucmVkaXJlY3RXYXJuVGltZSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogRmluYWxpemUgbWV0aG9kIHRvIGp1c3QgZGlhYmxlIHRoZSBpbnB1dFxuICAgICAqL1xuICAgIF9kaXNhYmxlSW5wdXQoKSB7XG4gICAgICAgIHRoaXMuJHRvQmVEaXNhYmxlZEVsZW1lbnQucHJvcCgncmVhZG9ubHknLCB0cnVlKTtcbiAgICAgICAgJCgnI3F1ZXN0aW9uJyArIHRoaXMub3B0aW9uLnF1ZXN0aW9uaWQpLmZpbmQoJy5hbnN3ZXItY29udGFpbmVyJykuY2hpbGRyZW4oJ2RpdicpLm5vdCgnLnRpbWVyX2hlYWRlcicpLmZhZGVPdXQoKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTaG93IHRoZSBub3RpY2UgdGhhdCB0aGUgdGltZSBpcyB1cCBhbmQgdGhlIGlucHV0IGlzIGV4cGlyZWRcbiAgICAgKi9cbiAgICBfc2hvd0V4cGlyZWROb3RpY2UoKSB7XG4gICAgICAgIHRoaXMuJHRpbWVyRXhwaXJlZEVsZW1lbnQucmVtb3ZlQ2xhc3MoJ2hpZGRlbicpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIHJlZGlyZWN0IHRvIHRoZSBuZXh0IHBhZ2VcbiAgICAgKi9cbiAgICBfcmVkaXJlY3RPdXQoKSB7XG4gICAgICAgICQoJyNscy1idXR0b24tc3VibWl0JykudHJpZ2dlcignY2xpY2snKTtcbiAgICB9XG4gICAgLyoqXG4gICAgICogQmluZHMgdGhlIHJlc2V0IG9mIHRoZSBsb2NhbFN0b3JhZ2UgYXMgc29vbiBhcyB0aGUgcGFydGljaXBhbnQgaGFzIHN1Ym1pdHRlZCB0aGUgZm9ybVxuICAgICAqL1xuICAgIF9iaW5kVW5zZXRUb1N1Ym1pdCgpIHtcbiAgICAgICAgJCgnI2xpbWVzdXJ2ZXknKS5vbignc3VibWl0JywgKCkgPT4ge1xuICAgICAgICAgICAgdGhpcy5fdW5zZXRUaW1lckluTG9jYWxTdG9yYWdlKCk7XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIC8qICMjIyMjIHB1YmxpYyBtZXRob2RzICMjIyMjICovXG5cbiAgICAvKipcbiAgICAgKiBGaW5pc2hpbmcgYWN0aW9uXG4gICAgICogVW5zZXRzIGFsbCB0aW1lcnMgYW5kIGludGVydmFscyBhbmQgdGhlbiB0cmlnZ2VycyB0aGUgZGVmaW5lZCBhY3Rpb24uXG4gICAgICogRWl0aGVyIHJlZGlyZWN0LCBpbnZhbGlkYXRlIG9yIHdhcm4gYmVmb3JlIHJlZGlyZWN0XG4gICAgICovXG4gICAgZmluaXNoVGltZXIoKSB7XG5cbiAgICAgICAgdGhpcy50aW1lckxvZ2dlci5sb2coJ1RpbWVyIGhhcyBlbmRlZCBvciB3YXMgZW5kZWQnKTtcbiAgICAgICAgdGhpcy5fdW5zZXRJbnRlcnZhbCgpO1xuICAgICAgICB0aGlzLl9lbmFibGVOYXZpZ2F0aW9uKCk7XG4gICAgICAgIHRoaXMuX2JpbmRVbnNldFRvU3VibWl0KCk7XG4gICAgICAgIHRoaXMuX2Rpc2FibGVJbnB1dCgpO1xuXG4gICAgICAgIHN3aXRjaCAodGhpcy5vcHRpb24uYWN0aW9uKSB7XG4gICAgICAgICAgICBjYXNlIDM6IC8vSnVzdCB3YXJuLCBkb24ndCBtb3ZlIG9uXG4gICAgICAgICAgICAgICAgdGhpcy5fc2hvd0V4cGlyZWROb3RpY2UoKTtcbiAgICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgIGNhc2UgMjogLy9KdXN0IG1vdmUgb24sIG5vIHdhcm5pbmdcbiAgICAgICAgICAgICAgICB0aGlzLl9yZWRpcmVjdE91dCgpO1xuICAgICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgY2FzZSAxOiAvL2ZhbGx0aHJvdWdoXG4gICAgICAgICAgICBkZWZhdWx0OiAvL1dhcm4gYW5kIG1vdmUgb25cbiAgICAgICAgICAgICAgICB0aGlzLl9zaG93RXhwaXJlZE5vdGljZSgpO1xuICAgICAgICAgICAgICAgIHRoaXMuX3dhcm5CZWZvcmVSZWRpcmVjdGlvbigpO1xuICAgICAgICAgICAgICAgIGJyZWFrO1xuXG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKiogXG4gICAgICogU3RhcnRzIHRoZSB0aW1lclxuICAgICAqIFN0cyB0aGUgaW50ZXJ2YWwgdG8gdmlzdWFsaXplIHRoZSB0aW1lciBhbmQgdGhlIHRpbWVvdXRzIGZvciB0aGUgd2FybmluZ3MuXG4gICAgICovXG4gICAgc3RhcnRUaW1lcigpIHtcbiAgICAgICAgaWYgKHRoaXMudGltZUxlZnQgPT0gMCkge1xuICAgICAgICAgICAgdGhpcy5maW5pc2hUaW1lcigpO1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICAgIHRoaXMuX2FwcGVuZFRpbWVyVG9TdXJ2ZXlUaW1lcnNMaXN0KCk7XG4gICAgICAgIHRoaXMuX3NldFRpbWVyVG9Mb2NhbFN0b3JhZ2UodGhpcy50aW1lTGVmdCk7XG4gICAgICAgIHRoaXMuX2Rpc2FibGVOYXZpZ2F0aW9uKCk7XG4gICAgICAgIHRoaXMuX3NldEludGVydmFsKCk7XG4gICAgfVxuXG4gICAgY29uc3RydWN0b3Iob3B0aW9ucykge1xuICAgICAgICAvKiAjIyMjIyBkZWZpbmUgc3RhdGUgYW5kIGNsb3N1cmUgdmFycyAjIyMjIyAqL1xuICAgICAgICB0aGlzLm9wdGlvbiA9IHRoaXMuX3BhcnNlT3B0aW9ucyhvcHRpb25zKTtcblxuICAgICAgICB0aGlzLnRpbWVyV2FybmluZyA9IG51bGw7XG4gICAgICAgIHRoaXMudGltZXJXYXJuaW5nMiA9IG51bGw7XG4gICAgICAgIHRoaXMudGltZXJMb2dnZXIgPSBuZXcgQ29uc29sZVNoaW0oJ1RJTUVSIycgKyBvcHRpb25zLnF1ZXN0aW9uaWQsICF3aW5kb3cuZGVidWdTdGF0ZS5mcm9udGVuZCk7XG4gICAgICAgIHRoaXMuaW50ZXJ2YWxPYmplY3QgPSBudWxsO1xuICAgICAgICB0aGlzLndhcm5pbmcgPSAwO1xuICAgICAgICB0aGlzLnRpbWVyc2Vzc2lvbm5hbWUgPSAndGltZXJfcXVlc3Rpb25fJyArIHRoaXMub3B0aW9uLnF1ZXN0aW9uaWQ7XG4gICAgICAgIHRoaXMuc3VydmV5VGltZXJzSXRlbU5hbWUgPSAnbGltZXN1cnZleV90aW1lcnNfYnlfc2lkXycgKyB0aGlzLm9wdGlvbi5zdXJ2ZXlpZDtcblxuICAgICAgICAvLyBVbnNldCB0aW1lciBpbiBsb2NhbCBzdG9yYWdlIGlmIHRoZSByZXNldCB0aW1lcnMgZmxhZyBpcyBzZXRcbiAgICAgICAgaWYgKExTdmFyLmJSZXNldFF1ZXN0aW9uVGltZXJzKSB0aGlzLl91bnNldFRpbWVySW5Mb2NhbFN0b3JhZ2UoKTtcbiAgICAgICAgXG4gICAgICAgIHRoaXMudGltZUxlZnQgPSB0aGlzLl9nZXRUaW1lckZyb21Mb2NhbFN0b3JhZ2UoKSB8fCB0aGlzLm9wdGlvbi50aW1lcjtcbiAgICAgICAgdGhpcy5kaXNhYmxlX25leHQgPSAkKFwiI2Rpc2FibGVuZXh0LVwiICsgdGhpcy50aW1lcnNlc3Npb25uYW1lKS52YWwoKTtcbiAgICAgICAgdGhpcy5kaXNhYmxlX3ByZXYgPSAkKFwiI2Rpc2FibGVwcmV2LVwiICsgdGhpcy50aW1lcnNlc3Npb25uYW1lKS52YWwoKTtcblxuICAgICAgICAvL2pRdWVyeSBFbGVtZW50c1xuICAgICAgICB0aGlzLiR0aW1lckRpc3BsYXlFbGVtZW50ID0gKCkgPT4gJCgnI0xTX3F1ZXN0aW9uJyArIHRoaXMub3B0aW9uLnF1ZXN0aW9uaWQgKyAnX1RpbWVyJyk7XG4gICAgICAgIHRoaXMuJHRpbWVyRXhwaXJlZEVsZW1lbnQgPSAkKCcjcXVlc3Rpb24nICsgdGhpcy5vcHRpb24ucXVlc3Rpb25pZCArICdfdGltZXInKTtcbiAgICAgICAgdGhpcy4kd2FybmluZ1RpbWVEaXNwbGF5RWxlbWVudCA9ICQoJyNMU19xdWVzdGlvbicgKyB0aGlzLm9wdGlvbi5xdWVzdGlvbmlkICsgJ19XYXJuaW5nJyk7XG4gICAgICAgIHRoaXMuJHdhcm5pbmdEaXNwbGF5RWxlbWVudCA9ICQoJyNMU19xdWVzdGlvbicgKyB0aGlzLm9wdGlvbi5xdWVzdGlvbmlkICsgJ193YXJuaW5nJyk7XG4gICAgICAgIHRoaXMuJHdhcm5pbmcyVGltZURpc3BsYXlFbGVtZW50ID0gJCgnI0xTX3F1ZXN0aW9uJyArIHRoaXMub3B0aW9uLnF1ZXN0aW9uaWQgKyAnX1dhcm5pbmdfMicpO1xuICAgICAgICB0aGlzLiR3YXJuaW5nMkRpc3BsYXlFbGVtZW50ID0gJCgnI0xTX3F1ZXN0aW9uJyArIHRoaXMub3B0aW9uLnF1ZXN0aW9uaWQgKyAnX3dhcm5pbmdfMicpO1xuICAgICAgICB0aGlzLiRjb3VudERvd25NZXNzYWdlRWxlbWVudCA9ICQoXCIjY291bnRkb3duLW1lc3NhZ2UtXCIgKyB0aGlzLnRpbWVyc2Vzc2lvbm5hbWUpO1xuICAgICAgICB0aGlzLnJlZGlyZWN0V2FyblRpbWUgPSAkKCcjbWVzc2FnZS1kZWxheS0nICsgdGhpcy50aW1lcnNlc3Npb25uYW1lKS52YWwoKTtcbiAgICAgICAgdGhpcy4kdG9CZURpc2FibGVkRWxlbWVudCA9ICQoJyMnICsgdGhpcy5vcHRpb24uZGlzYWJsZWRFbGVtZW50KTtcblxuICAgICAgICB0aGlzLnRpbWVyTG9nZ2VyLmxvZygnT3B0aW9ucyBzZXQ6JywgdGhpcy5vcHRpb24pO1xuXG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICBzdGFydFRpbWVyOiAoKSA9PiB0aGlzLnN0YXJ0VGltZXIuYXBwbHkodGhpcyksXG4gICAgICAgICAgICBmaW5pc2hUaW1lcjogKCkgPT4gdGhpcy5maW5pc2hUaW1lci5hcHBseSh0aGlzKVxuICAgICAgICB9O1xuICAgIH1cbn07XG4iXSwic291cmNlUm9vdCI6IiJ9