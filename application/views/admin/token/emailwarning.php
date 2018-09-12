<div class='side-body  <?php echo getSideBodyClass(false); ?>'>
    <div class="jumbotron message-box message-box-warning">
            <h2><?php eT("Warning"); ?></h2>

            <?php echo CHtml::form(array("admin/tokens/sa/email/action/{$sSubAction}/surveyid/{$surveyid}"), 'post'); ?>

                <?php eT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below."); ?><br /><br />
                <?php echo str_replace("{EMAILCOUNT}", (string) $lefttosend, gT("There are {EMAILCOUNT} emails still to be sent.")); ?>
                <br /><br />
                <input type='submit' value='<?php eT("Continue"); ?>' />
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
                } ?>
            </form>
    </div>
</div>
