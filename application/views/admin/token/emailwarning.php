<div class='side-body'>
<div class="jumbotron message-box message-box-warning">
    <h2><?php eT("Warning"); ?></h2>
    <?php echo CHtml::form(array("admin/tokens/sa/email/action/{$sSubAction}/surveyid/{$surveyid}"), 'post', ['id' => 'tokenSubmitInviteForm']); ?>
        <span id="tokenSendNotice"><?php printf(ngT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below, or wait %s{n}%s second.|There are more emails pending than can be sent in one batch. Continue sending emails by clicking below, or wait %s{n}%s seconds.", Yii::app()->getConfig('sendingrate')), '<span id="tokenSendCounter">', '</span>'); ?></span>
        <br />
        <br />
        <?php echo str_replace("{EMAILCOUNT}", (string) $lefttosend, (string) gT("There are {EMAILCOUNT} emails still to be sent.")); ?>
        <br />
        <br />
        <input 
            type='button' 
            class="btn btn-outline-secondary" 
            id="sendTokenInvitationsNow" 
            value='<?php eT("Continue"); ?>' />
        <span>&nbsp;&nbsp;&nbsp;</span>
        <button 
            id="cancelAutomaticSubmission" 
            class="btn btn-danger"
            type="button">
            <?php eT("Cancel automatic sending"); ?>
        </button>
        <input type='hidden' name='ok' value="absolutely" />
        <input type='hidden' name='action' value="tokens" />
        <input type='hidden' name='bypassbademails' value="<?php echo (int) Yii::app()->request->getPost('bypassbademails'); ?>" />
        <?php
        //Include values for constraints minreminderdelay and maxremindercount if they exist
        if (!$bEmail)
        {
            if (intval(Yii::app()->request->getPost('minreminderdelay')) != 0)
            { ?>
                <input 
                    type='hidden' 
                    name='minreminderdelay' 
                    value="<?php echo (int) Yii::app()->request->getPost('minreminderdelay'); ?>" />
                <?php }
            if (intval(Yii::app()->request->getPost('maxremindercount')) != 0)
            { ?>
                <input 
                    type='hidden' 
                    name='maxremindercount' 
                    value="<?php echo (int) Yii::app()->request->getPost('maxremindercount'); ?>" />
                <?php }
            if (Yii::app()->request->getPost('bypassdatecontrol')=='1')
            { ?>
                <input 
                    type='hidden' 
                    name='bypassdatecontrol' 
                    value="<?php echo (int) Yii::app()->request->getPost('bypassdatecontrol'); ?>" />
                <?php }
        }
        ?>
        <?php if (!empty($tids)) { ?>
            <input 
                type='hidden'
                name='tokenids' 
                value="<?php echo $tids; ?>" />
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
        <div 
            class="progress-bar progress-bar-striped active" 
            id="countdown-progress" 
            role="progressbar" 
            aria-valuenow="70"
            aria-valuemin="0" 
            aria-valuemax="100" 
            style="width:100%">
            <span class="visually-hidden"><?php neT("{n} second to go|{n} seconds to go", Yii::app()->getConfig('sendingrate')); ?></span>
        </div>
    </div>
</div>
<?php
App()->getClientScript()->registerScript('TokenInviteLooper', "
    $('#countdown-progress').css('-webkit-animation-duration', '1s');
    $('#countdown-progress').css('-moz-animation-duration', '1s');
    $('#countdown-progress').css('animation-duration', '1s');
    window.countdownTimerTokenSend = " . Yii::app()->getConfig('sendingrate') . ";

    var submitInviteForm = function() {
        $('body').append('<div class=\"overlay\"></div>');
        $('#sendTokenInvitationsNow').after('<p class=\"text-center\"><i class=\"ri-settings-5-fill remix-spin\"></i></p>');
        $('#cancelAutomaticSubmission').remove();
        $('#sendTokenInvitationsNow').remove();
        $('#tokenSubmitInviteForm').trigger('submit');
    };

    var intervaltoRenew = window.setInterval(function(){
        if(window.countdownTimerTokenSend === 0){
            submitInviteForm();
            clearInterval(intervaltoRenew);
            return;
        }
        window.countdownTimerTokenSend--;
        $('#countdown-progress').css('width', (window.countdownTimerTokenSend*100/" . Yii::app()->getConfig('sendingrate') . ")+'%');
        $('#tokenSendCounter').text(window.countdownTimerTokenSend);
    },1000);

    $('#sendTokenInvitationsNow').on('click', function(evt){
        clearInterval(intervaltoRenew);
        $('#countdown-progress').css('-webkit-animation-duration', '500ms');
        $('#countdown-progress').css('-moz-animation-duration', '500ms');
        $('#countdown-progress').css('animation-duration', '500ms');
        $('#countdown-progress').css('width', '0');
        $('#tokensendcounter').text('0');
        submitInviteForm();
    });        

    $('#cancelAutomaticSubmission').on('click', function(evt){
        evt.preventDefault();
        clearInterval(intervaltoRenew);
        $('#countdown-progress').css('width', '0%');
        $('#tokenSendNotice').text('" . gT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below.") . "');
    });

", LSYii_ClientScript::POS_POSTSCRIPT);

