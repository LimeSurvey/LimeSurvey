/**
 * @file Script for timer
 * @copyright LimeSurvey <http://www.limesurvey.org>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

var TimerConstructor = function(options){
    "use strict";
    /* ##### public methods ##### */

    /** 
     * Starts the timer
     * Sts the interval to visualize the timer and the timeouts for the warnings.
     */
    var startTimer = function(){
        if(timeLeft == 0){
            finishTimer();
            return;
        }
        _setTimerToLocalStorage(timeLeft);
        _disableNavigation();
        _setInterval();
    },
    /**
     * Finishing action
     * Unsets all timers and intervals and then triggers the defined action.
     * Either redirect, invalidate or warn before redirect
     */
    finishTimer = function(){
        timerLogger.log('Timer has ended or was ended');
        _unsetInterval();
        _enableNavigation();
        _bindUnsetToSubmit();

        switch(option.action)
        {
            case 3: //Just warn, don't move on
                _showExpiredNotice();
                _disableInput();
                break;
            case 2: //Just move on, no warning
                _redirectOut();
                break;
            case 1: //fallthrough
            default: //Warn and move on
                _showExpiredNotice();
                _warnBeforeRedirection();
                break;
        }
    };

    /* ##### private methods ##### */

    /**
     * Parses the options to default values if not set
     * @param Object options 
     * @return Object 
     */
    var _parseOptions = function(option) {
        return {
            questionid      : option.questionid      ||  null,
            timer           : option.timer           ||  0,
            action          : option.action          ||  1,
            warning         : option.warning         ||  0,
            warning2        : option.warning2        ||  0,
            warninghide     : option.warninghide     ||  0,
            warning2hide    : option.warning2hide    ||  0,
            disabledElement : option.disabledElement ||  null,
        }
    },

    /**
     * Takes a duration in seconds and creates an object containing the duration in hours, minutes and seconds
     * @param int seconds The duration in seconds
     * @return Object Contains hours, minutes and seconds
     */
    _parseTimeToObject = function(secLeft, asStrings){
        asStrings = asStrings || false;

        var oDuration = moment.duration(secLeft, 'seconds');
        var sHours   = String(oDuration.hours()),
            sMinutes = String(oDuration.minutes()),
            sSeconds = String(oDuration.seconds());

        return {
            hours   : asStrings ? (sHours.length == 1   ? '0'+sHours   : sHours) : parseInt(sHours),
            minutes : asStrings ? (sMinutes.length == 1 ? '0'+sMinutes : sMinutes) : parseInt(sMinutes),
            seconds : asStrings ? (sSeconds.length == 1 ? '0'+sSeconds : sSeconds) : parseInt(sSecond)
        };
    },

    /**
     * The actions done on each step and the trigger to the finishing action
     */
    _intervalStep = function(){
        var currentTimeLeft = _getTimerFromLocalStorage();
        currentTimeLeft = currentTimeLeft-1 ;
        timerLogger.log('Interval emitted | seconds left:', currentTimeLeft);
        if(currentTimeLeft <= 0){
            finishTimer();
        }
        _checkForWarning(currentTimeLeft);
        _setTimerToLocalStorage(currentTimeLeft);
        _setTimer(currentTimeLeft);
    },

    /**
     * Set the interval to update the timer visuals
     */
    _setInterval = function(){
        _setTimer(option.timer);
        intervalObject = setInterval(_intervalStep, 1000);
    },

    /**
     * Unset the timer;
     */
    _unsetInterval = function(){
        clearInterval(intervalObject);
        intervalObject = null;
    },

    /**
     * Sets the timer to the display element
     */
    _setTimer = function(currentTimeLeft){
        var timeObject = _parseTimeToObject(currentTimeLeft, true);
        $timerDisplayElement
            .css({display: 'flex'})
            .html($countDownMessageElement.html()+"&nbsp;&nbsp;<div class='ls-timer-time'>"+ timeObject.hours +':'+ timeObject.minutes +':'+ timeObject.seconds +"</div>");
    },

    /**
     * Checks if a warning should be shown relative to the interval
     * @param int currentTime The current amount of seconds gone
     */
    _checkForWarning = function(currentTime){
        if(currentTime == option.warning){
            _showWarning();
        }
        if(currentTime == option.warning2){
            _showWarning2();
        }
    },
    /**
     * Shows the warning and fades it out after the set amount of time
     */
    _showWarning = function(){
        timerLogger.log('Warning called!');
        $warningDisplayElement.removeClass('d-none').css({opacity: 0}).animate({'opacity': 1}, 200);
        setTimeout( function(){
            timerLogger.log('Warning ended!');
            $warningDisplayElement.animate({opacity: 0}, 200, function(){
                $(this).addClass('d-none');
            }) 
        }, 1000*option.warninghide );
    },

    /**
     * Shows the warning2 and fades it out after the set amount of time
     */
    _showWarning2 = function(){
        timerLogger.log('Warning2 called!');
        $warning2DisplayElement.removeClass('d-none').css({opacity: 0}).animate({'opacity': 1}, 200);
        setTimeout(function(){
            timerLogger.log('Warning2 ended!');
            $warning2DisplayElement.animate({opacity: 0}, 200, function(){
                $(this).addClass('d-none');
            }) 
        }, 1000*option.warning2hide );
    },

    /**
     * Disables the navigation buttons if necessary
     */
    _disableNavigation = function(){
        timerLogger.log('Disabling navigation');
        $('.ls-move-previous-btn').each(function(){
            $(this).prop('disabled',(disable_prev==1));
        });
        $('.ls-move-next-btn,.ls-move-submit-btn').each(function(){
            $(this).prop('disabled',(disable_next==1));
        });
    },

    /**
     * Enables the navigation buttons
     */
    _enableNavigation = function(){
        $('.ls-move-previous-btn').each(function(){
            $(this).prop('disabled',false);
        });
        $('.ls-move-next-btn,.ls-move-submit-btn').each(function(){
            $(this).prop('disabled',false);
        });
    },

    /**
     * Gets the current timer from the localStorage
     */
    _getTimerFromLocalStorage = function(){
        if(!window.localStorage) {
            return null;
        }

        return window.localStorage.getItem('limesurvey_timers_'+timersessionname);
    },

    /**
     * Sets the current timer to localStorage
     */
    _setTimerToLocalStorage = function(timerValue){
        if(!window.localStorage) {
            return;
        }
        window.localStorage.setItem('limesurvey_timers_'+timersessionname, timerValue);
    },

    /**
     * Unsets the timer in localStorage
     */
    _unsetTimerInLocalStorage = function(){
        if(!window.localStorage) {
            return;
        }
        window.localStorage.removeItem('limesurvey_timers_'+timersessionname);
    },

    /**
     * Finalize Method to show a warning and then redirect
     */
    _warnBeforeRedirection = function(){
        _disableInput();
        setTimeout(_redirectOut, redirectWarnTime);
    },

    /**
     * Finalize method to just diable the input
     */
    _disableInput = function(){
        $toBeDisabledElement.prop('readonly',true);
    },

    /**
     * Show the notice that the time is up and the input is expired
     */
    _showExpiredNotice = function(){
        $timerExpiredElement.removeClass('d-none');
    },

    /**
     * redirect to the next page
     */
    _redirectOut = function(){
        $('.action--ls-button-submit').trigger('click');
    },
    /**
     * Binds the reset of the localStorage as soon as the participant has submitted the form
     */
    _bindUnsetToSubmit = function(){
        $('#limesurvey').on('submit', function(){
            _unsetTimerInLocalStorage();
        });
    };

    /* ##### define state and closure vars ##### */
    var option = _parseOptions(options),
        timerWarning  = null,
        timerWarning2  = null,
        timerLogger = new ConsoleShim('TIMER#'+options.questionid, !window.debugState.frontend),
        intervalObject  = null,
        warning = 0,
        timersessionname='timer_question_'+option.questionid,
        timeLeft = _getTimerFromLocalStorage() || option.timer,
        disable_next = $("#disablenext-"+timersessionname).val(),
        disable_prev = $("#disableprev-"+timersessionname).val(),
        //jQuery Elements
        $timerDisplayElement = $('#LS_question'+option.questionid+'_Timer'),
        $timerExpiredElement = $('#question'+option.questionid+'_timer'),
        $warningTimeDisplayElement = $('#LS_question'+option.questionid+'_Warning'),
        $warningDisplayElement = $('#LS_question'+option.questionid+'_warning'),
        $warning2TimeDisplayElement=$('#LS_question'+option.questionid+'_Warning_2'),
        $warning2DisplayElement=$('#LS_question'+option.questionid+'_warning_2'),
        $countDownMessageElement = $("#countdown-message-"+timersessionname),
        redirectWarnTime=$('#message-delay-'+timersessionname).val(),
        $toBeDisabledElement=$('#'+option.disabledElement);

    timerLogger.log('Options set:', option);

    return {
        startTimer : startTimer,
        finishTimer : finishTimer
    };
};


function countdown(questionid,timer,action,warning,warning2,warninghide,warning2hide,disable)
{
    window.timerObjectSpace = window.timerObjectSpace || {};
    window.timerObjectSpace[questionid] = new TimerConstructor(
        {
            questionid : questionid,
            timer : timer,
            action : action,
            warning : warning,
            warning2 : warning2,
            warninghide : warninghide,
            warning2hide : warning2hide,
            disabledElement : disable
        }
    );
    window.timerObjectSpace[questionid].startTimer();
}
