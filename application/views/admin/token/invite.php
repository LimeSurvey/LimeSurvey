<?php
/**
 * Send email invitations

 * @var AdminController $this
 * @var Survey $oSurvey
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Send email invitations"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo PrepareEditorScript(true, $this); ?>
            <div>
                <?php if ($oSurvey->active != 'Y'): ?>
                    <div class="jumbotron message-box message-box-error">
                        <h2 class='text-warning'><?php eT('Warning!'); ?></h2>
                        <p class="lead text-warning">
                            <?php eT("This survey is not yet activated and so your participants won't be able to fill out the survey."); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div>
                    <?php echo CHtml::form(array("admin/tokens/sa/email/surveyid/{$oSurvey->sid}"), 'post', array('id'=>'sendinvitation', 'name'=>'sendinvitation', 'class'=>'')); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <?php if (count($tokenids)>0): ?>
                                <div class='form-group'>
                                    <label class='control-label '><?php eT("Send invitation email to token ID(s):"); ?></label>
                                    <div class=''>
                                        <?php echo short_implode(", ", "-", (array) $tokenids); ?>
                                    </div>
                                </div>

                            <?php endif; ?>
                        </div>

                        <div class="col-sm-4">
                            <div class='form-group'>

                                <label class='control-label ' for='bypassbademails'><?php eT("Bypass token with failing email addresses:"); ?></label>
                                <div class=''>
                                    <?php
                                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                        'name' => "bypassbademails",
                                        'id'=>"bypassbademails",
                                        'value' => '1',
                                        'onLabel'=>gT('On'),
                                        'offLabel' => gT('Off')));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class='form-group'>
                                <?php echo CHtml::label(
                                    gT("Bypass date control before sending email:"),
                                    'bypassdatecontrol', 
                                    array(
                                        'title'=>gt("If some tokens have a 'valid from' date set which is in the future, they will not be able to access the survey before that 'valid from' date."),
                                        'unescaped' => 'unescaped', 
                                        'class' => 'control-label ')
                                    ); ?>
                                <div class=''>
                                <?php
                                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                        'name' => "bypassdatecontrol",
                                        'id'=>"bypassdatecontrol",
                                        'value' => '0',
                                        'onLabel'=>gT('On'),
                                        'offLabel' => gT('Off')));
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="nav nav-tabs">
                        <?php
                        $c = true;
                            foreach ($oSurvey->allLanguages as $language) {
                                echo '<li role="presentation"';

                                if ($c) {
                                    $c=false;
                                    echo ' class="active"';
                                }

                                echo '><a  data-toggle="tab" href="#'.$language.'">' . getLanguageNameFromCode($language, false);
                                if ($language == $oSurvey->language) {
                                    echo " (" . gT("Base language") . ")";
                                }
                                echo "</a></li>";
                            }
                        ?>
                    </ul>

                    <div class="tab-content">
                        <?php
                        $c = true;
                        foreach ($oSurvey->allLanguages as $language) {
                                $admin_name = (empty($oSurvey->admin))?(Yii::app()->getConfig("siteadminname")):($oSurvey->admin);
                                $admin_email  = (empty($oSurvey->adminemail))?(Yii::app()->getConfig("siteadminemail")):($oSurvey->adminemail);
                                $fieldsarray["{ADMINNAME}"] = $admin_name;
                                $fieldsarray["{ADMINEMAIL}"] = $admin_email;
                                $fieldsarray["{SURVEYNAME}"] = $oSurvey->languagesettings[$language]->surveyls_title;
                                $fieldsarray["{SURVEYDESCRIPTION}"] = $oSurvey->languagesettings[$language]->surveyls_description;
                                $fieldsarray["{EXPIRY}"] = $oSurvey->expires;

                                $subject = Replacefields($oSurvey->languagesettings[$language]->surveyls_email_invite_subj, $fieldsarray, false);
                                $textarea = Replacefields($oSurvey->languagesettings[$language]->surveyls_email_invite, $fieldsarray, false);
                                if ($ishtml !== true) {
                                    $textarea = str_replace(array('<x>', '</x>'), array(''), $textarea);
                                }
                            ?>
                            <div id="<?php echo $language; ?>" class="tab-pane fade in <?php if ($c){$c=false;echo ' active';}?>">

                                <div class='form-group'>
                                    <label class='control-label ' for='from_<?php echo $language; ?>'><?php eT("From:"); ?></label>
                                    <div class=''>
                                        <?php echo CHtml::textField("from_{$language}",$admin_name." <".$admin_email.">",array('class' => 'form-control')); ?>
                                    </div>
                                </div>

                                <div class='form-group'>
                                    <label class='control-label ' for='subject_<?php echo $language; ?>'><?php eT("Subject:"); ?></label>
                                    <div class=''>
                                        <?php echo CHtml::textField("subject_{$language}",$subject,array('class' => 'form-control')); ?>
                                    </div>
                                </div>

                                <div class='form-group'>

                                    <label class='control-label ' for='message_<?php echo $language; ?>'><?php eT("Message:"); ?></label>
                                    <div class=''>
                                        <div class="htmleditor">
                                            <?php echo CHtml::textArea("message_{$language}",$textarea,array('cols'=>80,'rows'=>20, 'class' => 'form-control')); ?>
                                            <?php echo getEditor("email-invitation", "message_$language", "[" . gT("Invitation email:", "js") . "](" . $language . ")", $surveyid, '', '', "tokens"); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="row">
                        <div class='form-group'>
                            <div class=''></div>
                            <div class=''>
                                <?php echo CHtml::submitButton(gT("Send Invitations",'unescaped'), array('class'=>'btn btn-default')); ?>
                            </div>

                            <?php
                                echo CHtml::hiddenField('ok','absolutely');
                                echo CHtml::hiddenField('subaction','invite');
                                if (!empty($tokenids)) {
                                    echo CHtml::hiddenField('tokenids',implode('|', (array) $tokenids));
                                }
                            ?>
                        </div>
                    </div>
                <?php echo CHtml::endForm() ?>
            </div>
        <?php echo CHtml::endForm() ?>
    </div>
</div>

<?php

App()->getClientScript()->registerScript("Tokens:BindInviteView", "
        LS.renderBootstrapSwitch();
        $('#send-invitation-button').on('click', function(){
            $('#sendinvitation').submit();
        })
", LSYii_ClientScript::POS_POSTSCRIPT ); 
?>
