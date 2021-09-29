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
                var timeObject = this._parseTimeToObject(this.option.warning, true);
                this.$warningTimeDisplayElement.html(timeObject.hours + ':' + timeObject.minutes + ':' + timeObject.seconds);
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
                var timeObject = this._parseTimeToObject(this.option.warning, true);
                this.$warning2TimeDisplayElement.html(timeObject.hours + ':' + timeObject.minutes + ':' + timeObject.seconds);
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

        // Unser timer in local storage if the reset timers flag is set
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vc3JjL21haW4uanMiLCJ3ZWJwYWNrOi8vLy4vc3JjL3RpbWVjbGFzcy5qcyJdLCJuYW1lcyI6WyJ3aW5kb3ciLCJjb3VudGRvd24iLCJxdWVzdGlvbmlkIiwic3VydmV5aWQiLCJ0aW1lciIsImFjdGlvbiIsIndhcm5pbmciLCJ3YXJuaW5nMiIsIndhcm5pbmdoaWRlIiwid2FybmluZzJoaWRlIiwiZGlzYWJsZSIsInRpbWVyT2JqZWN0U3BhY2UiLCJUaW1lckNvbnN0cnVjdG9yIiwiZGlzYWJsZWRFbGVtZW50Iiwic3RhcnRUaW1lciIsIm9wdGlvbiIsInNlY0xlZnQiLCJhc1N0cmluZ3MiLCJvRHVyYXRpb24iLCJtb21lbnQiLCJkdXJhdGlvbiIsInNIb3VycyIsIlN0cmluZyIsImhvdXJzIiwic01pbnV0ZXMiLCJtaW51dGVzIiwic1NlY29uZHMiLCJzZWNvbmRzIiwibGVuZ3RoIiwicGFyc2VJbnQiLCJzU2Vjb25kIiwiY3VycmVudFRpbWVMZWZ0IiwiX2dldFRpbWVyRnJvbUxvY2FsU3RvcmFnZSIsInRpbWVyTG9nZ2VyIiwibG9nIiwiZmluaXNoVGltZXIiLCJfY2hlY2tGb3JXYXJuaW5nIiwiX3NldFRpbWVyVG9Mb2NhbFN0b3JhZ2UiLCJfc2V0VGltZXIiLCJfZXhpc3RzRGlzcGxheUVsZW1lbnQiLCJpbnRlcnZhbE9iamVjdCIsInNldEludGVydmFsIiwiX2ludGVydmFsU3RlcCIsImFwcGx5IiwiY2xlYXJJbnRlcnZhbCIsIiR0aW1lckRpc3BsYXlFbGVtZW50IiwiX3Vuc2V0SW50ZXJ2YWwiLCJ0aW1lT2JqZWN0IiwiX3BhcnNlVGltZVRvT2JqZWN0IiwiY3NzIiwiZGlzcGxheSIsImh0bWwiLCIkY291bnREb3duTWVzc2FnZUVsZW1lbnQiLCJjdXJyZW50VGltZSIsIl9zaG93V2FybmluZyIsIl9zaG93V2FybmluZzIiLCIkd2FybmluZ1RpbWVEaXNwbGF5RWxlbWVudCIsIiR3YXJuaW5nRGlzcGxheUVsZW1lbnQiLCJyZW1vdmVDbGFzcyIsIm9wYWNpdHkiLCJhbmltYXRlIiwic2V0VGltZW91dCIsImFkZENsYXNzIiwiJHdhcm5pbmcyVGltZURpc3BsYXlFbGVtZW50IiwiJHdhcm5pbmcyRGlzcGxheUVsZW1lbnQiLCIkIiwiZWFjaCIsImkiLCJpdGVtIiwicHJvcCIsImRpc2FibGVfcHJldiIsImRpc2FibGVfbmV4dCIsInRpbWVMZWZ0IiwibG9jYWxTdG9yYWdlIiwiZ2V0SXRlbSIsInRpbWVyc2Vzc2lvbm5hbWUiLCJpc05hTiIsInRpbWVyVmFsdWUiLCJzZXRJdGVtIiwidGltZXJzIiwiSlNPTiIsInBhcnNlIiwic3VydmV5VGltZXJzSXRlbU5hbWUiLCJpbmNsdWRlcyIsInB1c2giLCJzdHJpbmdpZnkiLCJyZW1vdmVJdGVtIiwiX2Rpc2FibGVJbnB1dCIsIl9yZWRpcmVjdE91dCIsInJlZGlyZWN0V2FyblRpbWUiLCIkdG9CZURpc2FibGVkRWxlbWVudCIsImZpbmQiLCJjaGlsZHJlbiIsIm5vdCIsImZhZGVPdXQiLCIkdGltZXJFeHBpcmVkRWxlbWVudCIsInRyaWdnZXIiLCJvbiIsIl91bnNldFRpbWVySW5Mb2NhbFN0b3JhZ2UiLCJfZW5hYmxlTmF2aWdhdGlvbiIsIl9iaW5kVW5zZXRUb1N1Ym1pdCIsIl9zaG93RXhwaXJlZE5vdGljZSIsIl93YXJuQmVmb3JlUmVkaXJlY3Rpb24iLCJfYXBwZW5kVGltZXJUb1N1cnZleVRpbWVyc0xpc3QiLCJfZGlzYWJsZU5hdmlnYXRpb24iLCJfc2V0SW50ZXJ2YWwiLCJvcHRpb25zIiwiX3BhcnNlT3B0aW9ucyIsInRpbWVyV2FybmluZyIsInRpbWVyV2FybmluZzIiLCJDb25zb2xlU2hpbSIsImRlYnVnU3RhdGUiLCJmcm9udGVuZCIsIkxTdmFyIiwiYlJlc2V0UXVlc3Rpb25UaW1lcnMiLCJ2YWwiXSwibWFwcGluZ3MiOiI7QUFBQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFD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bkM7QUFDQSxhQUFLOEQsdUJBQUwsR0FBK0JDLEVBQUUsaUJBQWlCLEtBQUtsRCxNQUFMLENBQVliLFVBQTdCLEdBQTBDLFlBQTVDLENBQS9CO0FBQ0EsYUFBS2tELHdCQUFMLEdBQWdDYSxFQUFFLHdCQUF3QixLQUFLVSxnQkFBL0IsQ0FBaEM7QUFDQSxhQUFLYyxnQkFBTCxHQUF3QnhCLEVBQUUsb0JBQW9CLEtBQUtVLGdCQUEzQixFQUE2Q3dDLEdBQTdDLEVBQXhCO0FBQ0EsYUFBS3pCLG9CQUFMLEdBQTRCekIsRUFBRSxNQUFNLEtBQUtsRCxNQUFMLENBQVlGLGVBQXBCLENBQTVCOztBQUVBLGFBQUtvQixXQUFMLENBQWlCQyxHQUFqQixDQUFxQixjQUFyQixFQUFxQyxLQUFLbkIsTUFBMUM7O0FBRUEsZUFBTztBQUNIRCx3QkFBWTtBQUFBLHVCQUFNLE9BQUtBLFVBQUwsQ0FBZ0I2QixLQUFoQixDQUFzQixNQUF0QixDQUFOO0FBQUEsYUFEVDtBQUVIUix5QkFBYTtBQUFBLHVCQUFNLE9BQUtBLFdBQUwsQ0FBaUJRLEtBQWpCLENBQXVCLE1BQXZCLENBQU47QUFBQTtBQUZWLFNBQVA7QUFJSDs7Ozs7a0JBN1VnQi9CLGdCO0FBOFVwQixDIiwiZmlsZSI6InRpbWVyLmpzIiwic291cmNlc0NvbnRlbnQiOlsiIFx0Ly8gVGhlIG1vZHVsZSBjYWNoZVxuIFx0dmFyIGluc3RhbGxlZE1vZHVsZXMgPSB7fTtcblxuIFx0Ly8gVGhlIHJlcXVpcmUgZnVuY3Rpb25cbiBcdGZ1bmN0aW9uIF9fd2VicGFja19yZXF1aXJlX18obW9kdWxlSWQpIHtcblxuIFx0XHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcbiBcdFx0aWYoaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0pIHtcbiBcdFx0XHRyZXR1cm4gaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0uZXhwb3J0cztcbiBcdFx0fVxuIFx0XHQvLyBDcmVhdGUgYSBuZXcgbW9kdWxlIChhbmQgcHV0IGl0IGludG8gdGhlIGNhY2hlKVxuIFx0XHR2YXIgbW9kdWxlID0gaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0gPSB7XG4gXHRcdFx0aTogbW9kdWxlSWQsXG4gXHRcdFx0bDogZmFsc2UsXG4gXHRcdFx0ZXhwb3J0czoge31cbiBcdFx0fTtcblxuIFx0XHQvLyBFeGVjdXRlIHRoZSBtb2R1bGUgZnVuY3Rpb25cbiBcdFx0bW9kdWxlc1ttb2R1bGVJZF0uY2FsbChtb2R1bGUuZXhwb3J0cywgbW9kdWxlLCBtb2R1bGUuZXhwb3J0cywgX193ZWJwYWNrX3JlcXVpcmVfXyk7XG5cbiBcdFx0Ly8gRmxhZyB0aGUgbW9kdWxlIGFzIGxvYWRlZFxuIFx0XHRtb2R1bGUubCA9IHRydWU7XG5cbiBcdFx0Ly8gUmV0dXJuIHRoZSBleHBvcnRzIG9mIHRoZSBtb2R1bGVcbiBcdFx0cmV0dXJuIG1vZHVsZS5leHBvcnRzO1xuIFx0fVxuXG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlcyBvYmplY3QgKF9fd2VicGFja19tb2R1bGVzX18pXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm0gPSBtb2R1bGVzO1xuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZSBjYWNoZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5jID0gaW5zdGFsbGVkTW9kdWxlcztcblxuIFx0Ly8gZGVmaW5lIGdldHRlciBmdW5jdGlvbiBmb3IgaGFybW9ueSBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQgPSBmdW5jdGlvbihleHBvcnRzLCBuYW1lLCBnZXR0ZXIpIHtcbiBcdFx0aWYoIV9fd2VicGFja19yZXF1aXJlX18ubyhleHBvcnRzLCBuYW1lKSkge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBuYW1lLCB7IGVudW1lcmFibGU6IHRydWUsIGdldDogZ2V0dGVyIH0pO1xuIFx0XHR9XG4gXHR9O1xuXG4gXHQvLyBkZWZpbmUgX19lc01vZHVsZSBvbiBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnIgPSBmdW5jdGlvbihleHBvcnRzKSB7XG4gXHRcdGlmKHR5cGVvZiBTeW1ib2wgIT09ICd1bmRlZmluZWQnICYmIFN5bWJvbC50b1N0cmluZ1RhZykge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBTeW1ib2wudG9TdHJpbmdUYWcsIHsgdmFsdWU6ICdNb2R1bGUnIH0pO1xuIFx0XHR9XG4gXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCAnX19lc01vZHVsZScsIHsgdmFsdWU6IHRydWUgfSk7XG4gXHR9O1xuXG4gXHQvLyBjcmVhdGUgYSBmYWtlIG5hbWVzcGFjZSBvYmplY3RcbiBcdC8vIG1vZGUgJiAxOiB2YWx1ZSBpcyBhIG1vZHVsZSBpZCwgcmVxdWlyZSBpdFxuIFx0Ly8gbW9kZSAmIDI6IG1lcmdlIGFsbCBwcm9wZXJ0aWVzIG9mIHZhbHVlIGludG8gdGhlIG5zXG4gXHQvLyBtb2RlICYgNDogcmV0dXJuIHZhbHVlIHdoZW4gYWxyZWFkeSBucyBvYmplY3RcbiBcdC8vIG1vZGUgJiA4fDE6IGJlaGF2ZSBsaWtlIHJlcXVpcmVcbiBcdF9fd2VicGFja19yZXF1aXJlX18udCA9IGZ1bmN0aW9uKHZhbHVlLCBtb2RlKSB7XG4gXHRcdGlmKG1vZGUgJiAxKSB2YWx1ZSA9IF9fd2VicGFja19yZXF1aXJlX18odmFsdWUpO1xuIFx0XHRpZihtb2RlICYgOCkgcmV0dXJuIHZhbHVlO1xuIFx0XHRpZigobW9kZSAmIDQpICYmIHR5cGVvZiB2YWx1ZSA9PT0gJ29iamVjdCcgJiYgdmFsdWUgJiYgdmFsdWUuX19lc01vZHVsZSkgcmV0dXJuIHZhbHVlO1xuIFx0XHR2YXIgbnMgPSBPYmplY3QuY3JlYXRlKG51bGwpO1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLnIobnMpO1xuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkobnMsICdkZWZhdWx0JywgeyBlbnVtZXJhYmxlOiB0cnVlLCB2YWx1ZTogdmFsdWUgfSk7XG4gXHRcdGlmKG1vZGUgJiAyICYmIHR5cGVvZiB2YWx1ZSAhPSAnc3RyaW5nJykgZm9yKHZhciBrZXkgaW4gdmFsdWUpIF9fd2VicGFja19yZXF1aXJlX18uZChucywga2V5LCBmdW5jdGlvbihrZXkpIHsgcmV0dXJuIHZhbHVlW2tleV07IH0uYmluZChudWxsLCBrZXkpKTtcbiBcdFx0cmV0dXJuIG5zO1xuIFx0fTtcblxuIFx0Ly8gZ2V0RGVmYXVsdEV4cG9ydCBmdW5jdGlvbiBmb3IgY29tcGF0aWJpbGl0eSB3aXRoIG5vbi1oYXJtb255IG1vZHVsZXNcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubiA9IGZ1bmN0aW9uKG1vZHVsZSkge1xuIFx0XHR2YXIgZ2V0dGVyID0gbW9kdWxlICYmIG1vZHVsZS5fX2VzTW9kdWxlID9cbiBcdFx0XHRmdW5jdGlvbiBnZXREZWZhdWx0KCkgeyByZXR1cm4gbW9kdWxlWydkZWZhdWx0J107IH0gOlxuIFx0XHRcdGZ1bmN0aW9uIGdldE1vZHVsZUV4cG9ydHMoKSB7IHJldHVybiBtb2R1bGU7IH07XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18uZChnZXR0ZXIsICdhJywgZ2V0dGVyKTtcbiBcdFx0cmV0dXJuIGdldHRlcjtcbiBcdH07XG5cbiBcdC8vIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbFxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5vID0gZnVuY3Rpb24ob2JqZWN0LCBwcm9wZXJ0eSkgeyByZXR1cm4gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG9iamVjdCwgcHJvcGVydHkpOyB9O1xuXG4gXHQvLyBfX3dlYnBhY2tfcHVibGljX3BhdGhfX1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5wID0gXCJcIjtcblxuXG4gXHQvLyBMb2FkIGVudHJ5IG1vZHVsZSBhbmQgcmV0dXJuIGV4cG9ydHNcbiBcdHJldHVybiBfX3dlYnBhY2tfcmVxdWlyZV9fKF9fd2VicGFja19yZXF1aXJlX18ucyA9IFwiLi9zcmMvbWFpbi5qc1wiKTtcbiIsIi8qKlxuICogQGZpbGUgU2NyaXB0IGZvciB0aW1lclxuICogQGNvcHlyaWdodCBMaW1lU3VydmV5IDxodHRwOi8vd3d3LmxpbWVzdXJ2ZXkub3JnPlxuICogQGxpY2Vuc2UgbWFnbmV0Oj94dD11cm46YnRpaDoxZjczOWQ5MzU2NzYxMTFjZmZmNGI0NjkzZTM4MTZlNjY0Nzk3MDUwJmRuPWdwbC0zLjAudHh0IEdQTC12My1vci1MYXRlclxuICovXG5cbmltcG9ydCBUaW1lckNvbnN0cnVjdG9yIGZyb20gJy4vdGltZWNsYXNzJztcblxud2luZG93LmNvdW50ZG93biA9IGZ1bmN0aW9uIGNvdW50ZG93bihxdWVzdGlvbmlkLCBzdXJ2ZXlpZCwgdGltZXIsIGFjdGlvbiwgd2FybmluZywgd2FybmluZzIsIHdhcm5pbmdoaWRlLCB3YXJuaW5nMmhpZGUsIGRpc2FibGUpIHtcbiAgICB3aW5kb3cudGltZXJPYmplY3RTcGFjZSA9IHdpbmRvdy50aW1lck9iamVjdFNwYWNlIHx8IHt9O1xuICAgIGlmICghd2luZG93LnRpbWVyT2JqZWN0U3BhY2VbcXVlc3Rpb25pZF0pIHtcbiAgICAgICAgd2luZG93LnRpbWVyT2JqZWN0U3BhY2VbcXVlc3Rpb25pZF0gPSBuZXcgVGltZXJDb25zdHJ1Y3Rvcih7XG4gICAgICAgICAgICBxdWVzdGlvbmlkOiBxdWVzdGlvbmlkLFxuICAgICAgICAgICAgc3VydmV5aWQ6IHN1cnZleWlkLFxuICAgICAgICAgICAgdGltZXI6IHRpbWVyLFxuICAgICAgICAgICAgYWN0aW9uOiBhY3Rpb24sXG4gICAgICAgICAgICB3YXJuaW5nOiB3YXJuaW5nLFxuICAgICAgICAgICAgd2FybmluZzI6IHdhcm5pbmcyLFxuICAgICAgICAgICAgd2FybmluZ2hpZGU6IHdhcm5pbmdoaWRlLFxuICAgICAgICAgICAgd2FybmluZzJoaWRlOiB3YXJuaW5nMmhpZGUsXG4gICAgICAgICAgICBkaXNhYmxlZEVsZW1lbnQ6IGRpc2FibGVcbiAgICAgICAgfSk7XG4gICAgICAgIHdpbmRvdy50aW1lck9iamVjdFNwYWNlW3F1ZXN0aW9uaWRdLnN0YXJ0VGltZXIoKTtcbiAgICB9XG59XG4iLCIvKipcbiAqIEBmaWxlIFNjcmlwdCBmb3IgdGltZXJcbiAqIEBjb3B5cmlnaHQgTGltZVN1cnZleSA8aHR0cDovL3d3dy5saW1lc3VydmV5Lm9yZz5cbiAqIEBsaWNlbnNlIG1hZ25ldDo/eHQ9dXJuOmJ0aWg6MWY3MzlkOTM1Njc2MTExY2ZmZjRiNDY5M2UzODE2ZTY2NDc5NzA1MCZkbj1ncGwtMy4wLnR4dCBHUEwtdjMtb3ItTGF0ZXJcbiAqL1xuXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBUaW1lckNvbnN0cnVjdG9yIHtcblxuICAgIC8qICMjIyMjIHByaXZhdGUgbWV0aG9kcyAjIyMjIyAqL1xuICAgIC8qKlxuICAgICAqIFBhcnNlcyB0aGUgb3B0aW9ucyB0byBkZWZhdWx0IHZhbHVlcyBpZiBub3Qgc2V0XG4gICAgICogQHBhcmFtIE9iamVjdCBvcHRpb25zIFxuICAgICAqIEByZXR1cm4gT2JqZWN0IFxuICAgICAqL1xuICAgIF9wYXJzZU9wdGlvbnMob3B0aW9uKSB7XG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICBxdWVzdGlvbmlkOiBvcHRpb24ucXVlc3Rpb25pZCB8fCBudWxsLFxuICAgICAgICAgICAgc3VydmV5aWQ6IG9wdGlvbi5zdXJ2ZXlpZCB8fCBudWxsLFxuICAgICAgICAgICAgdGltZXI6IG9wdGlvbi50aW1lciB8fCAwLFxuICAgICAgICAgICAgYWN0aW9uOiBvcHRpb24uYWN0aW9uIHx8IDEsXG4gICAgICAgICAgICB3YXJuaW5nOiBvcHRpb24ud2FybmluZyB8fCAwLFxuICAgICAgICAgICAgd2FybmluZzI6IG9wdGlvbi53YXJuaW5nMiB8fCAwLFxuICAgICAgICAgICAgd2FybmluZ2hpZGU6IG9wdGlvbi53YXJuaW5naGlkZSB8fCAwLFxuICAgICAgICAgICAgd2FybmluZzJoaWRlOiBvcHRpb24ud2FybmluZzJoaWRlIHx8IDAsXG4gICAgICAgICAgICBkaXNhYmxlZEVsZW1lbnQ6IG9wdGlvbi5kaXNhYmxlZEVsZW1lbnQgfHwgbnVsbCxcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFRha2VzIGEgZHVyYXRpb24gaW4gc2Vjb25kcyBhbmQgY3JlYXRlcyBhbiBvYmplY3QgY29udGFpbmluZyB0aGUgZHVyYXRpb24gaW4gaG91cnMsIG1pbnV0ZXMgYW5kIHNlY29uZHNcbiAgICAgKiBAcGFyYW0gaW50IHNlY29uZHMgVGhlIGR1cmF0aW9uIGluIHNlY29uZHNcbiAgICAgKiBAcmV0dXJuIE9iamVjdCBDb250YWlucyBob3VycywgbWludXRlcyBhbmQgc2Vjb25kc1xuICAgICAqL1xuICAgIF9wYXJzZVRpbWVUb09iamVjdChzZWNMZWZ0LCBhc1N0cmluZ3MpIHtcbiAgICAgICAgYXNTdHJpbmdzID0gYXNTdHJpbmdzIHx8IGZhbHNlO1xuXG4gICAgICAgIGNvbnN0IG9EdXJhdGlvbiA9IG1vbWVudC5kdXJhdGlvbihzZWNMZWZ0LCAnc2Vjb25kcycpO1xuICAgICAgICBsZXQgc0hvdXJzID0gU3RyaW5nKG9EdXJhdGlvbi5ob3VycygpKSxcbiAgICAgICAgICAgIHNNaW51dGVzID0gU3RyaW5nKG9EdXJhdGlvbi5taW51dGVzKCkpLFxuICAgICAgICAgICAgc1NlY29uZHMgPSBTdHJpbmcob0R1cmF0aW9uLnNlY29uZHMoKSk7XG5cbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgIGhvdXJzOiBhc1N0cmluZ3MgPyAoc0hvdXJzLmxlbmd0aCA9PSAxID8gJzAnICsgc0hvdXJzIDogc0hvdXJzKSA6IHBhcnNlSW50KHNIb3VycyksXG4gICAgICAgICAgICBtaW51dGVzOiBhc1N0cmluZ3MgPyAoc01pbnV0ZXMubGVuZ3RoID09IDEgPyAnMCcgKyBzTWludXRlcyA6IHNNaW51dGVzKSA6IHBhcnNlSW50KHNNaW51dGVzKSxcbiAgICAgICAgICAgIHNlY29uZHM6IGFzU3RyaW5ncyA/IChzU2Vjb25kcy5sZW5ndGggPT0gMSA/ICcwJyArIHNTZWNvbmRzIDogc1NlY29uZHMpIDogcGFyc2VJbnQoc1NlY29uZClcbiAgICAgICAgfTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBUaGUgYWN0aW9ucyBkb25lIG9uIGVhY2ggc3RlcCBhbmQgdGhlIHRyaWdnZXIgdG8gdGhlIGZpbmlzaGluZyBhY3Rpb25cbiAgICAgKi9cbiAgICBfaW50ZXJ2YWxTdGVwKCkge1xuICAgICAgICBsZXQgY3VycmVudFRpbWVMZWZ0ID0gdGhpcy5fZ2V0VGltZXJGcm9tTG9jYWxTdG9yYWdlKCk7XG4gICAgICAgIGN1cnJlbnRUaW1lTGVmdCA9IHBhcnNlSW50KGN1cnJlbnRUaW1lTGVmdCkgLSAxO1xuICAgICAgICB0aGlzLnRpbWVyTG9nZ2VyLmxvZygnSW50ZXJ2YWwgZW1pdHRlZCB8IHNlY29uZHMgbGVmdDonLCBjdXJyZW50VGltZUxlZnQpO1xuICAgICAgICBpZiAoY3VycmVudFRpbWVMZWZ0IDw9IDApIHtcbiAgICAgICAgICAgIHRoaXMuZmluaXNoVGltZXIoKTtcbiAgICAgICAgfVxuICAgICAgICB0aGlzLl9jaGVja0Zvcldhcm5pbmcoY3VycmVudFRpbWVMZWZ0KTtcbiAgICAgICAgdGhpcy5fc2V0VGltZXJUb0xvY2FsU3RvcmFnZShjdXJyZW50VGltZUxlZnQpO1xuICAgICAgICB0aGlzLl9zZXRUaW1lcihjdXJyZW50VGltZUxlZnQpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFNldCB0aGUgaW50ZXJ2YWwgdG8gdXBkYXRlIHRoZSB0aW1lciB2aXN1YWxzXG4gICAgICovXG4gICAgX3NldEludGVydmFsKCkge1xuICAgICAgICBpZiAodGhpcy5fZXhpc3RzRGlzcGxheUVsZW1lbnQoKSkge1xuICAgICAgICAgICAgdGhpcy5fc2V0VGltZXIodGhpcy5vcHRpb24udGltZXIpO1xuICAgICAgICAgICAgdGhpcy5pbnRlcnZhbE9iamVjdCA9IHNldEludGVydmFsKCgpID0+IHRoaXMuX2ludGVydmFsU3RlcC5hcHBseSh0aGlzKSwgMTAwMCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBVbnNldCB0aGUgdGltZXI7XG4gICAgICovXG4gICAgX3Vuc2V0SW50ZXJ2YWwoKSB7XG4gICAgICAgIGNsZWFySW50ZXJ2YWwodGhpcy5pbnRlcnZhbE9iamVjdCk7XG4gICAgICAgIHRoaXMuaW50ZXJ2YWxPYmplY3QgPSBudWxsO1xuICAgIH1cblxuICAgIF9leGlzdHNEaXNwbGF5RWxlbWVudCgpIHtcbiAgICAgICAgaWYgKCF0aGlzLiR0aW1lckRpc3BsYXlFbGVtZW50KCkubGVuZ3RoID4gMCkge1xuICAgICAgICAgICAgdGhpcy5fdW5zZXRJbnRlcnZhbCgpO1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiB0cnVlO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFNldHMgdGhlIHRpbWVyIHRvIHRoZSBkaXNwbGF5IGVsZW1lbnRcbiAgICAgKi9cbiAgICBfc2V0VGltZXIoY3VycmVudFRpbWVMZWZ0KSB7XG4gICAgICAgIGNvbnN0IHRpbWVPYmplY3QgPSB0aGlzLl9wYXJzZVRpbWVUb09iamVjdChjdXJyZW50VGltZUxlZnQsIHRydWUpO1xuICAgICAgICBpZiAodGhpcy5fZXhpc3RzRGlzcGxheUVsZW1lbnQoKSkge1xuICAgICAgICAgICAgdGhpcy4kdGltZXJEaXNwbGF5RWxlbWVudCgpXG4gICAgICAgICAgICAgICAgLmNzcyh7XG4gICAgICAgICAgICAgICAgICAgIGRpc3BsYXk6ICdmbGV4J1xuICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAgICAgLmh0bWwodGhpcy4kY291bnREb3duTWVzc2FnZUVsZW1lbnQuaHRtbCgpICsgXCImbmJzcDsmbmJzcDs8ZGl2IGNsYXNzPSdscy10aW1lci10aW1lJz5cIiArIHRpbWVPYmplY3QuaG91cnMgKyAnOicgKyB0aW1lT2JqZWN0Lm1pbnV0ZXMgKyAnOicgKyB0aW1lT2JqZWN0LnNlY29uZHMgKyBcIjwvZGl2PlwiKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENoZWNrcyBpZiBhIHdhcm5pbmcgc2hvdWxkIGJlIHNob3duIHJlbGF0aXZlIHRvIHRoZSBpbnRlcnZhbFxuICAgICAqIEBwYXJhbSBpbnQgY3VycmVudFRpbWUgVGhlIGN1cnJlbnQgYW1vdW50IG9mIHNlY29uZHMgZ29uZVxuICAgICAqL1xuICAgIF9jaGVja0Zvcldhcm5pbmcoY3VycmVudFRpbWUpIHtcbiAgICAgICAgaWYgKGN1cnJlbnRUaW1lID09IHRoaXMub3B0aW9uLndhcm5pbmcpIHtcbiAgICAgICAgICAgIHRoaXMuX3Nob3dXYXJuaW5nKCk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKGN1cnJlbnRUaW1lID09IHRoaXMub3B0aW9uLndhcm5pbmcyKSB7XG4gICAgICAgICAgICB0aGlzLl9zaG93V2FybmluZzIoKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICAvKipcbiAgICAgKiBTaG93cyB0aGUgd2FybmluZyBhbmQgZmFkZXMgaXQgb3V0IGFmdGVyIHRoZSBzZXQgYW1vdW50IG9mIHRpbWVcbiAgICAgKi9cbiAgICBfc2hvd1dhcm5pbmcoKSB7XG4gICAgICAgIGlmICh0aGlzLm9wdGlvbi53YXJuaW5nICE9PSAwKSB7XG4gICAgICAgICAgICB0aGlzLnRpbWVyTG9nZ2VyLmxvZygnV2FybmluZyBjYWxsZWQhJyk7XG4gICAgICAgICAgICBjb25zdCB0aW1lT2JqZWN0ID0gdGhpcy5fcGFyc2VUaW1lVG9PYmplY3QodGhpcy5vcHRpb24ud2FybmluZywgdHJ1ZSk7XG4gICAgICAgICAgICB0aGlzLiR3YXJuaW5nVGltZURpc3BsYXlFbGVtZW50Lmh0bWwodGltZU9iamVjdC5ob3VycyArICc6JyArIHRpbWVPYmplY3QubWludXRlcyArICc6JyArIHRpbWVPYmplY3Quc2Vjb25kcyk7XG4gICAgICAgICAgICB0aGlzLiR3YXJuaW5nRGlzcGxheUVsZW1lbnQucmVtb3ZlQ2xhc3MoJ2hpZGRlbicpLmNzcyh7XG4gICAgICAgICAgICAgICAgb3BhY2l0eTogMFxuICAgICAgICAgICAgfSkuYW5pbWF0ZSh7XG4gICAgICAgICAgICAgICAgJ29wYWNpdHknOiAxXG4gICAgICAgICAgICB9LCAyMDApO1xuICAgICAgICAgICAgc2V0VGltZW91dCgoKSA9PiB7XG4gICAgICAgICAgICAgICAgdGhpcy50aW1lckxvZ2dlci5sb2coJ1dhcm5pbmcgZW5kZWQhJyk7XG4gICAgICAgICAgICAgICAgdGhpcy4kd2FybmluZ0Rpc3BsYXlFbGVtZW50LmFuaW1hdGUoe1xuICAgICAgICAgICAgICAgICAgICBvcGFjaXR5OiAwXG4gICAgICAgICAgICAgICAgfSwgMjAwLCAoKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuJHdhcm5pbmdEaXNwbGF5RWxlbWVudC5hZGRDbGFzcygnaGlkZGVuJyk7XG4gICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgIH0sIDEwMDAgKiB0aGlzLm9wdGlvbi53YXJuaW5naGlkZSk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTaG93cyB0aGUgd2FybmluZzIgYW5kIGZhZGVzIGl0IG91dCBhZnRlciB0aGUgc2V0IGFtb3VudCBvZiB0aW1lXG4gICAgICovXG4gICAgX3Nob3dXYXJuaW5nMigpIHtcbiAgICAgICAgaWYgKHRoaXMub3B0aW9uLndhcm5pbmcyICE9PSAwKSB7XG4gICAgICAgICAgICB0aGlzLnRpbWVyTG9nZ2VyLmxvZygnV2FybmluZzIgY2FsbGVkIScpO1xuICAgICAgICAgICAgY29uc3QgdGltZU9iamVjdCA9IHRoaXMuX3BhcnNlVGltZVRvT2JqZWN0KHRoaXMub3B0aW9uLndhcm5pbmcsIHRydWUpO1xuICAgICAgICAgICAgdGhpcy4kd2FybmluZzJUaW1lRGlzcGxheUVsZW1lbnQuaHRtbCh0aW1lT2JqZWN0LmhvdXJzICsgJzonICsgdGltZU9iamVjdC5taW51dGVzICsgJzonICsgdGltZU9iamVjdC5zZWNvbmRzKTtcbiAgICAgICAgICAgIHRoaXMuJHdhcm5pbmcyRGlzcGxheUVsZW1lbnQucmVtb3ZlQ2xhc3MoJ2hpZGRlbicpLmNzcyh7XG4gICAgICAgICAgICAgICAgb3BhY2l0eTogMFxuICAgICAgICAgICAgfSkuYW5pbWF0ZSh7XG4gICAgICAgICAgICAgICAgJ29wYWNpdHknOiAxXG4gICAgICAgICAgICB9LCAyMDApO1xuICAgICAgICAgICAgc2V0VGltZW91dCgoKSA9PiB7XG4gICAgICAgICAgICAgICAgdGhpcy50aW1lckxvZ2dlci5sb2coJ1dhcm5pbmcyIGVuZGVkIScpO1xuICAgICAgICAgICAgICAgIHRoaXMuJHdhcm5pbmcyRGlzcGxheUVsZW1lbnQuYW5pbWF0ZSh7XG4gICAgICAgICAgICAgICAgICAgIG9wYWNpdHk6IDBcbiAgICAgICAgICAgICAgICB9LCAyMDAsICgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy4kd2FybmluZzJEaXNwbGF5RWxlbWVudC5hZGRDbGFzcygnaGlkZGVuJyk7XG4gICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgIH0sIDEwMDAgKiB0aGlzLm9wdGlvbi53YXJuaW5nMmhpZGUpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogRGlzYWJsZXMgdGhlIG5hdmlnYXRpb24gYnV0dG9ucyBpZiBuZWNlc3NhcnlcbiAgICAgKi9cbiAgICBfZGlzYWJsZU5hdmlnYXRpb24oKSB7XG4gICAgICAgIHRoaXMudGltZXJMb2dnZXIubG9nKCdEaXNhYmxpbmcgbmF2aWdhdGlvbicpO1xuICAgICAgICAkKCcubHMtbW92ZS1wcmV2aW91cy1idG4nKS5lYWNoKChpLCBpdGVtKSA9PiB7XG4gICAgICAgICAgICAkKGl0ZW0pLnByb3AoJ2Rpc2FibGVkJywgKHRoaXMuZGlzYWJsZV9wcmV2ID09IDEpKTtcbiAgICAgICAgfSk7XG4gICAgICAgICQoJy5scy1tb3ZlLW5leHQtYnRuLC5scy1tb3ZlLXN1Ym1pdC1idG4nKS5lYWNoKChpLCBpdGVtKSA9PiB7XG4gICAgICAgICAgICAkKGl0ZW0pLnByb3AoJ2Rpc2FibGVkJywgKHRoaXMuZGlzYWJsZV9uZXh0ID09IDEpKTtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogRW5hYmxlcyB0aGUgbmF2aWdhdGlvbiBidXR0b25zXG4gICAgICovXG4gICAgX2VuYWJsZU5hdmlnYXRpb24oKSB7XG4gICAgICAgICQoJy5scy1tb3ZlLXByZXZpb3VzLWJ0bicpLmVhY2goZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgJCh0aGlzKS5wcm9wKCdkaXNhYmxlZCcsIGZhbHNlKTtcbiAgICAgICAgfSk7XG4gICAgICAgICQoJy5scy1tb3ZlLW5leHQtYnRuLC5scy1tb3ZlLXN1Ym1pdC1idG4nKS5lYWNoKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICQodGhpcykucHJvcCgnZGlzYWJsZWQnLCBmYWxzZSk7XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldHMgdGhlIGN1cnJlbnQgdGltZXIgZnJvbSB0aGUgbG9jYWxTdG9yYWdlXG4gICAgICovXG4gICAgX2dldFRpbWVyRnJvbUxvY2FsU3RvcmFnZSgpIHtcbiAgICAgICAgY29uc3QgdGltZUxlZnQgPSB3aW5kb3cubG9jYWxTdG9yYWdlLmdldEl0ZW0oJ2xpbWVzdXJ2ZXlfdGltZXJzXycgKyB0aGlzLnRpbWVyc2Vzc2lvbm5hbWUpO1xuICAgICAgICByZXR1cm4gKCFpc05hTihwYXJzZUludCh0aW1lTGVmdCkpID8gdGltZUxlZnQgOiAwKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXRzIHRoZSBjdXJyZW50IHRpbWVyIHRvIGxvY2FsU3RvcmFnZVxuICAgICAqL1xuICAgIF9zZXRUaW1lclRvTG9jYWxTdG9yYWdlKHRpbWVyVmFsdWUpIHtcbiAgICAgICAgd2luZG93LmxvY2FsU3RvcmFnZS5zZXRJdGVtKCdsaW1lc3VydmV5X3RpbWVyc18nICsgdGhpcy50aW1lcnNlc3Npb25uYW1lLCB0aW1lclZhbHVlKTtcbiAgICB9XG4gICAgXG4gICAgLyoqXG4gICAgICogQXBwZW5kcyB0aGUgY3VycmVudCB0aW1lcidzIHFpZCB0byB0aGUgbGlzdCBvZiB0aW1lcnMgZm9yIHRoZSBzdXJ2ZXlcbiAgICAgKi9cbiAgICBfYXBwZW5kVGltZXJUb1N1cnZleVRpbWVyc0xpc3QoKSB7XG4gICAgICAgIHZhciB0aW1lcnMgPSBKU09OLnBhcnNlKHdpbmRvdy5sb2NhbFN0b3JhZ2UuZ2V0SXRlbSh0aGlzLnN1cnZleVRpbWVyc0l0ZW1OYW1lKSB8fCBcIltdXCIpO1xuICAgICAgICBpZiAoIXRpbWVycy5pbmNsdWRlcyh0aGlzLnRpbWVyc2Vzc2lvbm5hbWUpKSB0aW1lcnMucHVzaCh0aGlzLnRpbWVyc2Vzc2lvbm5hbWUpO1xuICAgICAgICB3aW5kb3cubG9jYWxTdG9yYWdlLnNldEl0ZW0odGhpcy5zdXJ2ZXlUaW1lcnNJdGVtTmFtZSwgSlNPTi5zdHJpbmdpZnkodGltZXJzKSk7XG4gICAgfVxuICAgIFxuICAgIC8qKlxuICAgICAqIFVuc2V0cyB0aGUgdGltZXIgaW4gbG9jYWxTdG9yYWdlXG4gICAgICovXG4gICAgX3Vuc2V0VGltZXJJbkxvY2FsU3RvcmFnZSgpIHtcbiAgICAgICAgd2luZG93LmxvY2FsU3RvcmFnZS5yZW1vdmVJdGVtKCdsaW1lc3VydmV5X3RpbWVyc18nICsgdGhpcy50aW1lcnNlc3Npb25uYW1lKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBGaW5hbGl6ZSBNZXRob2QgdG8gc2hvdyBhIHdhcm5pbmcgYW5kIHRoZW4gcmVkaXJlY3RcbiAgICAgKi9cbiAgICBfd2FybkJlZm9yZVJlZGlyZWN0aW9uKCkge1xuICAgICAgICB0aGlzLl9kaXNhYmxlSW5wdXQoKTtcbiAgICAgICAgc2V0VGltZW91dCh0aGlzLl9yZWRpcmVjdE91dCwgdGhpcy5yZWRpcmVjdFdhcm5UaW1lKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBGaW5hbGl6ZSBtZXRob2QgdG8ganVzdCBkaWFibGUgdGhlIGlucHV0XG4gICAgICovXG4gICAgX2Rpc2FibGVJbnB1dCgpIHtcbiAgICAgICAgdGhpcy4kdG9CZURpc2FibGVkRWxlbWVudC5wcm9wKCdyZWFkb25seScsIHRydWUpO1xuICAgICAgICAkKCcjcXVlc3Rpb24nICsgdGhpcy5vcHRpb24ucXVlc3Rpb25pZCkuZmluZCgnLmFuc3dlci1jb250YWluZXInKS5jaGlsZHJlbignZGl2Jykubm90KCcudGltZXJfaGVhZGVyJykuZmFkZU91dCgpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFNob3cgdGhlIG5vdGljZSB0aGF0IHRoZSB0aW1lIGlzIHVwIGFuZCB0aGUgaW5wdXQgaXMgZXhwaXJlZFxuICAgICAqL1xuICAgIF9zaG93RXhwaXJlZE5vdGljZSgpIHtcbiAgICAgICAgdGhpcy4kdGltZXJFeHBpcmVkRWxlbWVudC5yZW1vdmVDbGFzcygnaGlkZGVuJyk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogcmVkaXJlY3QgdG8gdGhlIG5leHQgcGFnZVxuICAgICAqL1xuICAgIF9yZWRpcmVjdE91dCgpIHtcbiAgICAgICAgJCgnI2xzLWJ1dHRvbi1zdWJtaXQnKS50cmlnZ2VyKCdjbGljaycpO1xuICAgIH1cbiAgICAvKipcbiAgICAgKiBCaW5kcyB0aGUgcmVzZXQgb2YgdGhlIGxvY2FsU3RvcmFnZSBhcyBzb29uIGFzIHRoZSBwYXJ0aWNpcGFudCBoYXMgc3VibWl0dGVkIHRoZSBmb3JtXG4gICAgICovXG4gICAgX2JpbmRVbnNldFRvU3VibWl0KCkge1xuICAgICAgICAkKCcjbGltZXN1cnZleScpLm9uKCdzdWJtaXQnLCAoKSA9PiB7XG4gICAgICAgICAgICB0aGlzLl91bnNldFRpbWVySW5Mb2NhbFN0b3JhZ2UoKTtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgLyogIyMjIyMgcHVibGljIG1ldGhvZHMgIyMjIyMgKi9cblxuICAgIC8qKlxuICAgICAqIEZpbmlzaGluZyBhY3Rpb25cbiAgICAgKiBVbnNldHMgYWxsIHRpbWVycyBhbmQgaW50ZXJ2YWxzIGFuZCB0aGVuIHRyaWdnZXJzIHRoZSBkZWZpbmVkIGFjdGlvbi5cbiAgICAgKiBFaXRoZXIgcmVkaXJlY3QsIGludmFsaWRhdGUgb3Igd2FybiBiZWZvcmUgcmVkaXJlY3RcbiAgICAgKi9cbiAgICBmaW5pc2hUaW1lcigpIHtcblxuICAgICAgICB0aGlzLnRpbWVyTG9nZ2VyLmxvZygnVGltZXIgaGFzIGVuZGVkIG9yIHdhcyBlbmRlZCcpO1xuICAgICAgICB0aGlzLl91bnNldEludGVydmFsKCk7XG4gICAgICAgIHRoaXMuX2VuYWJsZU5hdmlnYXRpb24oKTtcbiAgICAgICAgdGhpcy5fYmluZFVuc2V0VG9TdWJtaXQoKTtcbiAgICAgICAgdGhpcy5fZGlzYWJsZUlucHV0KCk7XG5cbiAgICAgICAgc3dpdGNoICh0aGlzLm9wdGlvbi5hY3Rpb24pIHtcbiAgICAgICAgICAgIGNhc2UgMzogLy9KdXN0IHdhcm4sIGRvbid0IG1vdmUgb25cbiAgICAgICAgICAgICAgICB0aGlzLl9zaG93RXhwaXJlZE5vdGljZSgpO1xuICAgICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgY2FzZSAyOiAvL0p1c3QgbW92ZSBvbiwgbm8gd2FybmluZ1xuICAgICAgICAgICAgICAgIHRoaXMuX3JlZGlyZWN0T3V0KCk7XG4gICAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICBjYXNlIDE6IC8vZmFsbHRocm91Z2hcbiAgICAgICAgICAgIGRlZmF1bHQ6IC8vV2FybiBhbmQgbW92ZSBvblxuICAgICAgICAgICAgICAgIHRoaXMuX3Nob3dFeHBpcmVkTm90aWNlKCk7XG4gICAgICAgICAgICAgICAgdGhpcy5fd2FybkJlZm9yZVJlZGlyZWN0aW9uKCk7XG4gICAgICAgICAgICAgICAgYnJlYWs7XG5cbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKiBcbiAgICAgKiBTdGFydHMgdGhlIHRpbWVyXG4gICAgICogU3RzIHRoZSBpbnRlcnZhbCB0byB2aXN1YWxpemUgdGhlIHRpbWVyIGFuZCB0aGUgdGltZW91dHMgZm9yIHRoZSB3YXJuaW5ncy5cbiAgICAgKi9cbiAgICBzdGFydFRpbWVyKCkge1xuICAgICAgICBpZiAodGhpcy50aW1lTGVmdCA9PSAwKSB7XG4gICAgICAgICAgICB0aGlzLmZpbmlzaFRpbWVyKCk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cbiAgICAgICAgdGhpcy5fYXBwZW5kVGltZXJUb1N1cnZleVRpbWVyc0xpc3QoKTtcbiAgICAgICAgdGhpcy5fc2V0VGltZXJUb0xvY2FsU3RvcmFnZSh0aGlzLnRpbWVMZWZ0KTtcbiAgICAgICAgdGhpcy5fZGlzYWJsZU5hdmlnYXRpb24oKTtcbiAgICAgICAgdGhpcy5fc2V0SW50ZXJ2YWwoKTtcbiAgICB9XG5cbiAgICBjb25zdHJ1Y3RvcihvcHRpb25zKSB7XG4gICAgICAgIC8qICMjIyMjIGRlZmluZSBzdGF0ZSBhbmQgY2xvc3VyZSB2YXJzICMjIyMjICovXG4gICAgICAgIHRoaXMub3B0aW9uID0gdGhpcy5fcGFyc2VPcHRpb25zKG9wdGlvbnMpO1xuXG4gICAgICAgIHRoaXMudGltZXJXYXJuaW5nID0gbnVsbDtcbiAgICAgICAgdGhpcy50aW1lcldhcm5pbmcyID0gbnVsbDtcbiAgICAgICAgdGhpcy50aW1lckxvZ2dlciA9IG5ldyBDb25zb2xlU2hpbSgnVElNRVIjJyArIG9wdGlvbnMucXVlc3Rpb25pZCwgIXdpbmRvdy5kZWJ1Z1N0YXRlLmZyb250ZW5kKTtcbiAgICAgICAgdGhpcy5pbnRlcnZhbE9iamVjdCA9IG51bGw7XG4gICAgICAgIHRoaXMud2FybmluZyA9IDA7XG4gICAgICAgIHRoaXMudGltZXJzZXNzaW9ubmFtZSA9ICd0aW1lcl9xdWVzdGlvbl8nICsgdGhpcy5vcHRpb24ucXVlc3Rpb25pZDtcbiAgICAgICAgdGhpcy5zdXJ2ZXlUaW1lcnNJdGVtTmFtZSA9ICdsaW1lc3VydmV5X3RpbWVyc19ieV9zaWRfJyArIHRoaXMub3B0aW9uLnN1cnZleWlkO1xuXG4gICAgICAgIC8vIFVuc2VyIHRpbWVyIGluIGxvY2FsIHN0b3JhZ2UgaWYgdGhlIHJlc2V0IHRpbWVycyBmbGFnIGlzIHNldFxuICAgICAgICBpZiAoTFN2YXIuYlJlc2V0UXVlc3Rpb25UaW1lcnMpIHRoaXMuX3Vuc2V0VGltZXJJbkxvY2FsU3RvcmFnZSgpO1xuICAgICAgICBcbiAgICAgICAgdGhpcy50aW1lTGVmdCA9IHRoaXMuX2dldFRpbWVyRnJvbUxvY2FsU3RvcmFnZSgpIHx8IHRoaXMub3B0aW9uLnRpbWVyO1xuICAgICAgICB0aGlzLmRpc2FibGVfbmV4dCA9ICQoXCIjZGlzYWJsZW5leHQtXCIgKyB0aGlzLnRpbWVyc2Vzc2lvbm5hbWUpLnZhbCgpO1xuICAgICAgICB0aGlzLmRpc2FibGVfcHJldiA9ICQoXCIjZGlzYWJsZXByZXYtXCIgKyB0aGlzLnRpbWVyc2Vzc2lvbm5hbWUpLnZhbCgpO1xuXG4gICAgICAgIC8valF1ZXJ5IEVsZW1lbnRzXG4gICAgICAgIHRoaXMuJHRpbWVyRGlzcGxheUVsZW1lbnQgPSAoKSA9PiAkKCcjTFNfcXVlc3Rpb24nICsgdGhpcy5vcHRpb24ucXVlc3Rpb25pZCArICdfVGltZXInKTtcbiAgICAgICAgdGhpcy4kdGltZXJFeHBpcmVkRWxlbWVudCA9ICQoJyNxdWVzdGlvbicgKyB0aGlzLm9wdGlvbi5xdWVzdGlvbmlkICsgJ190aW1lcicpO1xuICAgICAgICB0aGlzLiR3YXJuaW5nVGltZURpc3BsYXlFbGVtZW50ID0gJCgnI0xTX3F1ZXN0aW9uJyArIHRoaXMub3B0aW9uLnF1ZXN0aW9uaWQgKyAnX1dhcm5pbmcnKTtcbiAgICAgICAgdGhpcy4kd2FybmluZ0Rpc3BsYXlFbGVtZW50ID0gJCgnI0xTX3F1ZXN0aW9uJyArIHRoaXMub3B0aW9uLnF1ZXN0aW9uaWQgKyAnX3dhcm5pbmcnKTtcbiAgICAgICAgdGhpcy4kd2FybmluZzJUaW1lRGlzcGxheUVsZW1lbnQgPSAkKCcjTFNfcXVlc3Rpb24nICsgdGhpcy5vcHRpb24ucXVlc3Rpb25pZCArICdfV2FybmluZ18yJyk7XG4gICAgICAgIHRoaXMuJHdhcm5pbmcyRGlzcGxheUVsZW1lbnQgPSAkKCcjTFNfcXVlc3Rpb24nICsgdGhpcy5vcHRpb24ucXVlc3Rpb25pZCArICdfd2FybmluZ18yJyk7XG4gICAgICAgIHRoaXMuJGNvdW50RG93bk1lc3NhZ2VFbGVtZW50ID0gJChcIiNjb3VudGRvd24tbWVzc2FnZS1cIiArIHRoaXMudGltZXJzZXNzaW9ubmFtZSk7XG4gICAgICAgIHRoaXMucmVkaXJlY3RXYXJuVGltZSA9ICQoJyNtZXNzYWdlLWRlbGF5LScgKyB0aGlzLnRpbWVyc2Vzc2lvbm5hbWUpLnZhbCgpO1xuICAgICAgICB0aGlzLiR0b0JlRGlzYWJsZWRFbGVtZW50ID0gJCgnIycgKyB0aGlzLm9wdGlvbi5kaXNhYmxlZEVsZW1lbnQpO1xuXG4gICAgICAgIHRoaXMudGltZXJMb2dnZXIubG9nKCdPcHRpb25zIHNldDonLCB0aGlzLm9wdGlvbik7XG5cbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgIHN0YXJ0VGltZXI6ICgpID0+IHRoaXMuc3RhcnRUaW1lci5hcHBseSh0aGlzKSxcbiAgICAgICAgICAgIGZpbmlzaFRpbWVyOiAoKSA9PiB0aGlzLmZpbmlzaFRpbWVyLmFwcGx5KHRoaXMpXG4gICAgICAgIH07XG4gICAgfVxufTtcbiJdLCJzb3VyY2VSb290IjoiIn0=