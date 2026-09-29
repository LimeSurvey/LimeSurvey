/**
 * @file Script for timer
 * @copyright LimeSurvey <http://www.limesurvey.org>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

export default class TimerConstructor {

    /* ##### private methods ##### */
    /**
     * Parses the options to default values if not set
     * @param Object options 
     * @return Object 
     */
    _parseOptions(option) {
        return {
            questionid: option.questionid || null,
            surveyid: option.surveyid || null,
            timer: option.timer || 0,
            action: option.action || 1,
            warning: option.warning || 0,
            warning2: option.warning2 || 0,
            warninghide: option.warninghide || 0,
            warning2hide: option.warning2hide || 0,
            disabledElement: option.disabledElement || null,
        }
    }

    /**
     * Takes a duration in seconds and creates an object containing the duration in hours, minutes and seconds
     * @param int seconds The duration in seconds
     * @return Object Contains hours, minutes and seconds
     */
    _parseTimeToObject(secLeft, asStrings) {
        asStrings = asStrings || false;

        const oDuration = moment.duration(secLeft, 'seconds');
        let sHours = String(oDuration.hours()),
            sMinutes = String(oDuration.minutes()),
            sSeconds = String(oDuration.seconds());

        return {
            hours: asStrings ? (sHours.length == 1 ? '0' + sHours : sHours) : parseInt(sHours),
            minutes: asStrings ? (sMinutes.length == 1 ? '0' + sMinutes : sMinutes) : parseInt(sMinutes),
            seconds: asStrings ? (sSeconds.length == 1 ? '0' + sSeconds : sSeconds) : parseInt(sSecond)
        };
    }

    /**
     * The actions done on each step and the trigger to the finishing action
     */
    _intervalStep() {
        let currentTimeLeft = this._getTimerFromLocalStorage();
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
    _setInterval() {
        if (this._existsDisplayElement()) {
            this._setTimer(this.option.timer);
            this.intervalObject = setInterval(() => this._intervalStep.apply(this), 1000);
        }
    }

    /**
     * Unset the timer;
     */
    _unsetInterval() {
        clearInterval(this.intervalObject);
        this.intervalObject = null;
    }

    _existsDisplayElement() {
        if (!this.$timerDisplayElement().length > 0) {
            this._unsetInterval();
            return false;
        }
        return true;
    }

    /**
     * Sets the timer to the display element
     */
    _setTimer(currentTimeLeft) {
        const timeObject = this._parseTimeToObject(currentTimeLeft, true);
        if (this._existsDisplayElement()) {
            this.$timerDisplayElement()
                .css({
                    display: 'flex'
                })
                .html(this.$countDownMessageElement.html() + "&nbsp;&nbsp;<div class='ls-timer-time'>" + timeObject.hours + ':' + timeObject.minutes + ':' + timeObject.seconds + "</div>");
        }
    }

    /**
     * Checks if a warning should be shown relative to the interval
     * @param int currentTime The current amount of seconds gone
     */
    _checkForWarning(currentTime) {
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
    _showWarning() {
        if (this.option.warning !== 0) {
            this.timerLogger.log('Warning called!');
            const timeObject = this._parseTimeToObject(this.option.warning, true);
            this.$warningTimeDisplayElement.html(timeObject.hours + ':' + timeObject.minutes + ':' + timeObject.seconds);
            this.$warningDisplayElement.removeClass('hidden d-none').css({
                opacity: 0
            }).animate({
                'opacity': 1
            }, 200);
            setTimeout(() => {
                this.timerLogger.log('Warning ended!');
                this.$warningDisplayElement.animate({
                    opacity: 0
                }, 200, () => {
                    this.$warningDisplayElement.addClass('hidden d-none');
                })
            }, 1000 * this.option.warninghide);
        }
    }

    /**
     * Shows the warning2 and fades it out after the set amount of time
     */
    _showWarning2() {
        if (this.option.warning2 !== 0) {
            this.timerLogger.log('Warning2 called!');
            const timeObject = this._parseTimeToObject(this.option.warning, true);
            this.$warning2TimeDisplayElement.html(timeObject.hours + ':' + timeObject.minutes + ':' + timeObject.seconds);
            this.$warning2DisplayElement.removeClass('hidden d-none').css({
                opacity: 0
            }).animate({
                'opacity': 1
            }, 200);
            setTimeout(() => {
                this.timerLogger.log('Warning2 ended!');
                this.$warning2DisplayElement.animate({
                    opacity: 0
                }, 200, () => {
                    this.$warning2DisplayElement.addClass('hidden d-none');
                })
            }, 1000 * this.option.warning2hide);
        }
    }

    /**
     * Disables the navigation buttons if necessary
     */
    _disableNavigation() {
        this.timerLogger.log('Disabling navigation');
        $('.ls-move-previous-btn').each((i, item) => {
            $(item).prop('disabled', (this.disable_prev == 1));
        });
        $('.ls-move-next-btn,.ls-move-submit-btn').each((i, item) => {
            $(item).prop('disabled', (this.disable_next == 1));
        });
    }

    /**
     * Enables the navigation buttons
     */
    _enableNavigation() {
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
    _getTimerFromLocalStorage() {
        if(!window.localStorage) {
            return null;
        }
        const timeLeft = window.localStorage.getItem('limesurvey_timers_' + this.timersessionname);
        return (!isNaN(parseInt(timeLeft)) ? timeLeft : 0);
    }

    /**
     * Sets the current timer to localStorage
     */
    _setTimerToLocalStorage(timerValue) {
        if(!window.localStorage) {
            return;
        }
        window.localStorage.setItem('limesurvey_timers_' + this.timersessionname, timerValue);
    }
    
    /**
     * Appends the current timer's qid to the list of timers for the survey
     */
    _appendTimerToSurveyTimersList() {
        if(!window.localStorage) {
            return;
        }
        var timers = JSON.parse(window.localStorage.getItem(this.surveyTimersItemName) || "[]");
        if (!timers.includes(this.timersessionname)) timers.push(this.timersessionname);
        window.localStorage.setItem(this.surveyTimersItemName, JSON.stringify(timers));
    }
    
    /**
     * Unsets the timer in localStorage
     */
    _unsetTimerInLocalStorage() {
        if(!window.localStorage) {
            return;
        }
        window.localStorage.removeItem('limesurvey_timers_' + this.timersessionname);
    }

    /**
     * Finalize Method to show a warning and then redirect
     */
    _warnBeforeRedirection() {
        this._disableInput();
        setTimeout(this._redirectOut, this.redirectWarnTime);
    }

    /**
     * Finalize method to just diable the input
     */
    _disableInput() {
        this.$toBeDisabledElement.prop('readonly', true);
        $('#question' + this.option.questionid).find('.answer-container').children('div').not('.timer_header').fadeOut();
    }

    /**
     * Show the notice that the time is up and the input is expired
     */
    _showExpiredNotice() {
        this.$timerExpiredElement.removeClass('hidden d-none');
    }

    /**
     * redirect to the next page
     */
    _redirectOut() {
        $('#ls-button-submit').trigger('click');
    }
    /**
     * Binds the reset of the localStorage as soon as the participant has submitted the form
     */
    _bindUnsetToSubmit() {
        $('#limesurvey').on('submit', () => {
            this._unsetTimerInLocalStorage();
        });
    }

    /* ##### public methods ##### */

    /**
     * Finishing action
     * Unsets all timers and intervals and then triggers the defined action.
     * Either redirect, invalidate or warn before redirect
     */
    finishTimer() {

        this.timerLogger.log('Timer has ended or was ended');
        this._unsetInterval();
        this._enableNavigation();
        this._bindUnsetToSubmit();
        this._disableInput();

        switch (this.option.action) {
            case 3: //Just warn, don't move on
                this._showExpiredNotice();
                break;
            case 2: //Just move on, no warning
                this._redirectOut();
                break;
            case 1: //fallthrough
            default: //Warn and move on
                this._showExpiredNotice();
                this._warnBeforeRedirection();
                break;

        }
    }

    /** 
     * Starts the timer
     * Sts the interval to visualize the timer and the timeouts for the warnings.
     */
    startTimer() {
        if (this.timeLeft == 0) {
            this.finishTimer();
            return;
        }
        this._appendTimerToSurveyTimersList();
        this._setTimerToLocalStorage(this.timeLeft);
        this._disableNavigation();
        this._setInterval();
    }

    constructor(options) {
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
        this.$timerDisplayElement = () => $('#LS_question' + this.option.questionid + '_Timer');
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
            startTimer: () => this.startTimer.apply(this),
            finishTimer: () => this.finishTimer.apply(this)
        };
    }
};
