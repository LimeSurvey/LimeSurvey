<?php
/**
 * This file render the javascript for the timer
 * TODO : replace all php statement by JavaScript on hidden html input
 * @var $iAction
 */
?>

<script type='text/javascript'>
    <!--
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
            <?php if($iAction != ''):?>
                action = <?php echo $iAction;?>;
            <?php endif; ?>


            var timerdisplay='LS_question'+questionid+'_Timer';
            var warningtimedisplay='LS_question'+questionid+'_Warning';
            var warningdisplay='LS_question'+questionid+'_warning';
            var warning2timedisplay='LS_question'+questionid+'_Warning_2';
            var warning2display='LS_question'+questionid+'_warning_2';
            var expireddisplay='question'+questionid+'_timer';
            var timersessionname='timer_question_'+questionid;
            var disable_next = <?php echo $disable_next; ?>;
            var disable_prev = <?php echo $disable_prev; ?>;

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

            <?php if($disable_next > 0): ?>// $disable_next can be 1 or 0 (it's a select).

                if(timeleft > disable_next)
                {
                    $('#movenextbtn').prop('disabled',true);$('#movenextbtn.ui-button').button( 'option', 'disabled', true );
                }
                else if (disable_next >= 1 && timeleft <= disable_next)
                {
                    $('#movenextbtn').prop('disabled',false);$('#movenextbtn.ui-button').button( 'option', 'disabled', false );
                }
            <?php endif; ?>

            <?php if($disable_prev > 0): ?>
                if(timeleft > disable_prev)
                {
                    $('#moveprevbtn').prop('disabled',true);$('#moveprevbtn.ui-button').button( 'option', 'disabled', true );
                }
                else if (disable_prev >= 1 && timeleft <= disable_prev)
                {
                    $('#moveprevbtn').prop('disabled',false);$('#moveprevbtn.ui-button').button( 'option', 'disabled', false );
                }
            <?php endif;?>

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
                if (whours > 0) dhours = whours + ' <?php echo gT('hours'); ?>, ';
                if (wmins > 0) dmins = wmins + ' <?php echo gT('mins'); ?>, ';
                if (wsecs > 0) dsecs = wsecs + ' <?php echo gT('seconds'); ?>';
                $('#'+warningtimedisplay).html(dhours+dmins+dsecs);
                $('#'+warningdisplay).show();
                if(warninghide > 0 ) {
                    setTimeout(function(){ $('#'+warningdisplay).hide(); },warninghide*1000);
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
                if (w2hours > 0) d2hours = w2hours + ' <?php echo gT('hours'); ?>, ';
                if (w2mins > 0) d2mins = w2mins + ' <?php echo gT('mins'); ?>, ';
                if (w2secs > 0) d2secs = w2secs + ' <?php echo gT('seconds'); ?>';
                $('#'+warning2timedisplay).html(dhours+dmins+dsecs);
                $('#'+warning2display).show();
                if(warning2hide > 0 )
                {
                    setTimeout(function(){ $('#'+warning2display).hide(); },warning2hide*1000);
                }
                warning2=0;
            }

            var secs = timeleft % 60;
            if (secs < 10) secs = '0'+secs;
            var T1 = (timeleft - secs) / 60;
            var mins = T1 % 60; if (mins < 10) mins = '0'+mins;
            var hours = (T1 - mins) / 60;
            if (hours < 10) hours = '0'+hours;
            var d2hours='';
            var d2mins='';
            var d2secs='';
            if (hours > 0) d2hours = hours+' <?php echo gT('hours'); ?>: ';
            if (mins > 0) d2mins = mins+' <?php echo gT('mins'); ?>: ';
            if (secs > 0) d2secs = secs+' <?php echo gT('seconds'); ?>';
            if (secs < 1) d2secs = '0 <?php echo gT('seconds'); ?>';
            $('#'+timerdisplay).html('<?php echo $time_limit_countdown_message; ?><br />'+d2hours + d2mins + d2secs);


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
                        $('#movenextbtn').prop('disabled',false);$('#movenextbtn.ui-button').button( 'option', 'disabled', false );
                        $('#moveprevbtn').prop('disabled',false);$('#moveprevbtn.ui-button').button( 'option', 'disabled', false );
                        freezeFrame(disable);
                        subcookiejar.crumble('limesurvey_timers', timersessionname);
                        $('#defaultbtn').click();
                        break;
                    case 3: //Just warn, don't move on
                        $('#'+expireddisplay).show();
                        $('#movenextbtn').prop('disabled',false);$('#movenextbtn.ui-button').button( 'option', 'disabled', false );
                        $('#moveprevbtn').prop('disabled',false);$('#moveprevbtn.ui-button').button( 'option', 'disabled', false );
                        freezeFrame(disable);
                        $('#limesurvey').submit(function(){ subcookiejar.crumble('limesurvey_timers', timersessionname); });
                        break;
                    default: //Warn and move on
                        $('#'+expireddisplay).show();
                        $('#movenextbtn').prop('disabled',false);$('#movenextbtn.ui-button').button( 'option', 'disabled', false );
                        $('#moveprevbtn').prop('disabled',false);$('#moveprevbtn.ui-button').button( 'option', 'disabled', false );
                        freezeFrame(disable);
                        subcookiejar.crumble('limesurvey_timers', timersessionname);
                        setTimeout($('#defaultbtn').click(), ".$time_limit_message_delay.");
                        break;
                }
            }
        }
    //-->
</script>
