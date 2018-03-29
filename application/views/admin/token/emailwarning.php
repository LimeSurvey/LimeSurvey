<div class='side-body  <?php echo getSideBodyClass(false); ?>'>
<div class="jumbotron message-box message-box-warning">
    <h2><?php eT("Warning"); ?></h2>
    <?php echo CHtml::form(array("admin/tokens/sa/email/action/{$sSubAction}/surveyid/{$surveyid}"), 'post', ['id' => 'tokenSubmitInviteForm']); ?>
        <?php echo sprintf(gT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below, or wait %s seconds."), '<span id="tokensendcounter">20</span>'); ?>
        <br />
        <br />
        <?php echo str_replace("{EMAILCOUNT}", (string) $lefttosend, gT("There are {EMAILCOUNT} emails still to be sent.")); ?>
        <br />
        <br />
        <input type='submit' class="btn btn-default" id="sendTokenInvitationsNow" value='<?php eT("Continue"); ?>' />
        <span>&nbsp;&nbsp;&nbsp;</span><button id="cancelAutomaticSubmission" class="btn btn-danger"><?php eT("Cancel automatic sending"); ?></button>
        <input type='hidden' name='ok' value="absolutely" />
        <input type='hidden' name='action' value="tokens" />
        <input type='hidden' name='bypassbademails' value="<?php echo Yii::app()->request->getPost('bypassbademails'); ?>" />
        <?php
        //Include values for constraints minreminderdelay and maxremindercount if they exist
        if (!$bEmail)
        {
            if (intval(Yii::app()->request->getPost('minreminderdelay')) != 0)
            { ?>
                    <input type='hidden' name='minreminderdelay' value="<?php echo Yii::app()->request->getPost('minreminderdelay'); ?>" />
                    <?php }
            if (intval(Yii::app()->request->getPost('maxremindercount')) != 0)
            { ?>
                    <input type='hidden' name='maxremindercount' value="<?php echo Yii::app()->request->getPost('maxremindercount'); ?>" />
                    <?php }
            if (Yii::app()->request->getPost('bypassdatecontrol')=='1')
            { ?>
                        <input type='hidden' name='bypassdatecontrol' value="<?php echo Yii::app()->request->getPost('bypassdatecontrol'); ?>" />
                        <?php }
        }
        ?>
        <?php if (!empty($tids)) { ?>
            <input type='hidden' name='tokenids' value="<?php echo $tids; ?>" />
        <?php } ?>
        <?php
            foreach ($aSurveyLangs as $language)
            {
                echo CHtml::hiddenField('from_'.$language, Yii::app()->request->getPost('from_' . $language));
                echo CHtml::hiddenField('subject_'.$language, Yii::app()->request->getPost('subject_' . $language));
                echo CHtml::hiddenField('message_'.$language, Yii::app()->request->getPost('message_' . $language));
            } 
        ?>
    </form>
    <br/>
    <div class="progress">
        <div class="progress-bar progress-bar-striped active" id="countdown-progress" role="progressbar" aria-valuenow="70"
        aria-valuemin="0" aria-valuemax="100" style="width:100%">
            <span class="sr-only">20 seconds to go</span>
        </div>
    </div>
</div>
<?php
App()->getClientScript()->registerScript('TokenInviteLooper', "
    $('#countdown-progress').css('-webkit-animation-duration', '1s');
    $('#countdown-progress').css('-moz-animation-duration', '1s');
    $('#countdown-progress').css('animation-duration', '1s');
    window.countdownTimerTokenSend = 20;
    var intervaltoRenew = window.setInterval(function(){
        if(window.countdownTimerTokenSend === 0){
            $('body').append('<div class=\"overlay\"></div>');
            $('#sendTokenInvitationsNow').after('<p class=\"text-center\"><i class=\"fa fa-cog fa-spin\"></i></p>');
            $('#cancelAutomaticSubmission').remove();
            $('#sendTokenInvitationsNow').remove();
            $('#tokenSubmitInviteForm').trigger('submit');
            clearInterval(intervaltoRenew);
            return;
        }
        window.countdownTimerTokenSend--;
        $('#countdown-progress').css('width', (window.countdownTimerTokenSend*5)+'%');
        $('#tokensendcounter').text(window.countdownTimerTokenSend);
    },1000);

    $('#cancelAutomaticSubmission').on('click', function(evt){
        evt.preventDefault();
        clearInterval(intervaltoRenew);
        $('#countdown-progress').css('width', '0%');
        $('#tokensendcounter').text('X');
    });

", LSYii_ClientScript::POS_POSTSCRIPT);

