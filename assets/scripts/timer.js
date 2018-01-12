/**
 * @file Script for timer
 * @copyright LimeSurvey <http://www.limesurvey.org>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

/* disble an element after time expiry */
function freezeFrame(elementid)
{
    $('#'+elementid).prop('readonly',true);
};

function countdown(questionid,timer,action,warning,warning2,warninghide,warning2hide,disable)
{
    if(!timeleft) { var timeleft=timer;}
    if(!warning) { var warning=0;}
    if(!warning2) { var warning2=0;}
    if(!warninghide) { var warninghide=0;}
    if(!warning2hide) { var warning2hide=0;}

    var timerdisplay='LS_question'+questionid+'_Timer';
    var warningtimedisplay='LS_question'+questionid+'_Warning';
    var warningdisplay='LS_question'+questionid+'_warning';
    var warning2timedisplay='LS_question'+questionid+'_Warning_2';
    var warning2display='LS_question'+questionid+'_warning_2';
    var expireddisplay='question'+questionid+'_timer';
    var timersessionname='timer_question_'+questionid;
    //~ var action = $("#action-"+timersessionname).val();
    var disable_next = $("#disablenext-"+timersessionname).val();
    var disable_prev = $("#disableprev-"+timersessionname).val();

    $('#'+timersessionname).val(timeleft);
    timeleft--;
    cookietimer=subcookiejar.fetch('limesurvey_timers',timersessionname);
    if(cookietimer && cookietimer <= timeleft)
    {
        timeleft=cookietimer;
    }
    var timeleftobject=new Object();
    subcookiejar.crumble('limesurvey_timers', timersessionname);
    timeleftobject[timersessionname]=timeleft;
    subcookiejar.bake('limesurvey_timers', timeleftobject, 7);

    if(disable_next > 0){// $disable_next can be 1 or 0 (it's a select).

        if(timeleft > disable_next)
        {
            $('.ls-move-previous-btn').each(function(){
                $(this).prop('disabled',true);
            });
        }
        else if (disable_next >= 1 && timeleft <= disable_next)
        {
            $('.ls-move-next-btn,.ls-move-submit-btn').each(function(){
                $(this).prop('disabled',false);
            });
        }
    }

    if(disable_prev > 0){
        if(timeleft > disable_prev)
        {
            $('.ls-move-next-btn,.ls-move-submit-btn').each(function(){
                $(this).prop('disabled',true);
            });
        }
        else if (disable_prev >= 1 && timeleft <= disable_prev)
        {
            $('.ls-move-previous-btn').each(function(){
                $(this).prop('disabled',false);
            });
        }
    }

    if(warning > 0 && timeleft<=warning) {
        var wsecs=warning%60;
        if(wsecs<10) wsecs='0' + wsecs;
        var WT1 = (warning - wsecs) / 60;
        var wmins = WT1 % 60; if (wmins < 10) wmins = '0' + wmins;
        var whours = (WT1 - wmins) / 60;
        var dmins='';
        var dhours='';
        var dsecs='';
        if (whours < 10) whours = '0' + whours;
        if (whours > 0) dhours = whours + ' '+LSvar.lang.timer.hours+' ';
        if (wmins > 0) dmins = wmins + ' '+LSvar.lang.timer.mins+' ';
        if (wsecs > 0) dsecs = wsecs + ' '+LSvar.lang.timer.secs+' ';
        $('#'+warningtimedisplay).html(dhours+dmins+dsecs);
        $('#'+warningdisplay).removeClass("hidden");
        if(warninghide > 0 ) {
            setTimeout(function(){ $('#'+warningdisplay).addClass("hidden"); },warninghide*1000);
        }
        warning=0;
    }

    if(warning2 > 0 && timeleft<=warning2)
    {
        var w2secs=warning2%60;
        if(wsecs<10) w2secs='0' + wsecs;
        var W2T1 = (warning2 - w2secs) / 60;
        var w2mins = W2T1 % 60; if (w2mins < 10) w2mins = '0' + w2mins;
        var w2hours = (W2T1 - w2mins) / 60;
        var d2mins='';
        var d2hours='';
        var d2secs='';
        if (w2hours < 10) w2hours = '0' + w2hours;
        if (w2hours > 0) d2hours = w2hours + ' '+LSvar.lang.timer.hours+' ';
        if (w2mins > 0) d2mins = w2mins + ' '+LSvar.lang.timer.mins+' ';
        if (w2secs > 0) d2secs = w2secs + ' '+LSvar.lang.timer.seconds+' ';
        $('#'+warning2timedisplay).html(dhours+dmins+dsecs);
        $('#'+warning2display).removeClass("hidden");
        if(warning2hide > 0 )
        {
            setTimeout(function(){ $('#'+warning2display).addClass("hidden"); },warning2hide*1000);
        }
        warning2=0;
    }
    var secs = timeleft % 60;
    if (secs < 10) secs = '0'+secs;
    var T1 = (timeleft - secs) / 60;
    var mins = T1 % 60; if (mins < 10) mins = '0'+mins;
    var hours = (T1 - mins) / 60;
    var d2hours='';
    var d2mins='';
    var d2secs='';
    if (hours > 0) d2hours = hours + ' '+LSvar.lang.timer.hours;
    if (mins > 0) d2mins = mins + ' '+LSvar.lang.timer.mins;
    if (secs > 0) d2secs = secs + ' '+LSvar.lang.timer.seconds;
    if (secs < 1) d2secs = '0' +  ' '+LSvar.lang.timer.seconds;
    $('#'+timerdisplay).html($("#countdown-message-"+timersessionname).html()+"<div class='ls-timer-time'>"+ d2hours + d2mins + d2secs+"</div>");


    if (timeleft>0)
    {
        var text='countdown('+questionid+', '+timeleft+', '+action+', '+warning+', '+warning2+', '+warninghide+', '+warning2hide+', \"'+disable+'\")';
        setTimeout(text,1000);
    }
    else
    {
        //Countdown is finished, now do action
        switch(action)
        {

            case 2: //Just move on, no warning
                $('.ls-move-previous-btn').each(function(){
                    $(this).prop('disabled',false);
                });
                $('.ls-move-next-btn,.ls-move-submit-btn').each(function(){
                    $(this).prop('disabled',false);
                });
                freezeFrame(disable);
                subcookiejar.crumble('limesurvey_timers', timersessionname);
                $('#defaultbtn').click();
                break;
            case 3: //Just warn, don't move on
                $('#'+expireddisplay).removeClass("hidden");
                $('.ls-move-previous-btn').each(function(){
                    $(this).prop('disabled',false);
                });
                $('.ls-move-next-btn,.ls-move-submit-btn').each(function(){
                    $(this).prop('disabled',false);
                });
                freezeFrame(disable);
                $('#limesurvey').submit(function(){ subcookiejar.crumble('limesurvey_timers', timersessionname); });
                break;
            default: //Warn and move on
                $('#'+expireddisplay).removeClass("hidden");
                $('.ls-move-previous-btn').each(function(){
                    $(this).prop('disabled',false);
                });
                $('.ls-move-next-btn,.ls-move-submit-btn').each(function(){
                    $(this).prop('disabled',false);
                });
                freezeFrame(disable);
                subcookiejar.crumble('limesurvey_timers', timersessionname);
                setTimeout($('#defaultbtn').click(), $("#message-delay-"+timersessionname).val());
                break;
        }
    }
}
