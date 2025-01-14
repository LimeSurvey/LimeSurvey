/**
 * @file Script for timer
 * @copyright GititSurvey <http://www.gitit-tech.com>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

import TimerConstructor from './timeclass';

window.countdown = function countdown(questionid, surveyid, timer, action, warning, warning2, warninghide, warning2hide, disable) {
    window.timerObjectSpace = window.timerObjectSpace || {};
    if (!window.timerObjectSpace[questionid]) {
        window.timerObjectSpace[questionid] = new TimerConstructor({
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
}
