<?php
/**
 * Send email invitations

 * @var AdminController $this
 * @var Survey $oSurvey
 */
?>

<div class='side-body'>
    <h3><?php eT("Send email invitations"); ?></h3>
    <div class="row">
        <div class="col-12 content-right">
            <?php echo PrepareEditorScript(true, $this); ?>
            <div>
                <?php if ($oSurvey->active != 'Y'): ?>
                    <div class="jumbotron message-box message-box-error">
                        <h2 class='text-danger'><?php eT('Warning!'); ?></h2>
                        <p class="lead text-danger">
                            <?php eT("This survey is not yet activated and so your participants won't be able to fill out the survey."); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div>
                    <?php echo CHtml::form(array("admin/tokens/sa/email/surveyid/{$oSurvey->sid}"), 'post', array('id'=>'sendinvitation', 'name'=>'sendinvitation', 'class'=>'')); ?>
                    <div class="row">
                        <div class="col-md-4">
                            <?php if (count($tokenids)>0): ?>
                                <div class='mb-3'>
                                    <label class='form-label '><?php eT("Send invitation email to participant ID(s):"); ?></label>
                                    <div class=''>
                                        <?php echo short_implode(", ", "-", (array) $tokenids); ?>
                                    </div>
                                </div>

                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <div class='mb-3'>

                                <label class='form-label ' for='bypassbademails'><?php eT("Bypass participants with failing email addresses:"); ?></label>
                                <div>
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => "bypassbademails",
                                        'checkedOption' => '1',
                                        'selectOptions' => [
                                            '1' => gT('On'),
                                            '0' => gT('Off'),
                                        ],
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class='mb-3'>
                                <?php echo CHtml::label(
                                    gT("Bypass date control before sending email:"),
                                    'bypassdatecontrol', 
                                    array(
                                        'title'=>gt("If some participants have a 'valid from' date set which is in the future, they will not be able to access the survey before that 'valid from' date."),
                                        'unescaped' => 'unescaped', 
                                        'class' => 'form-label ')
                                    ); ?>
                                <div>
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => "bypassdatecontrol",
                                        'checkedOption' => '0',
                                        'selectOptions' => [
                                            '1' => gT('On'),
                                            '0' => gT('Off'),
                                        ],
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="nav nav-tabs">
                        <?php $c = true ?>
                        <?php foreach ($oSurvey->allLanguages as $language): ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $c ? "active" : "" ?>" data-bs-toggle="tab" href="#<?= $language ?>">
                                    <?php if ($c) {
                                        $c = false;
                                    } ?>
                                    <?= getLanguageNameFromCode($language, false) . " " . (($language == $oSurvey->language) ? "(" . gT("Base language") . ")" : "")  ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content">
                        <?php
                        $c = true;
                        foreach ($oSurvey->allLanguages as $language) {
                                $admin_name = (empty($oSurvey->oOptions->admin))?(Yii::app()->getConfig("siteadminname")):($oSurvey->oOptions->admin);
                                $admin_email  = (empty($oSurvey->oOptions->adminemail))?(Yii::app()->getConfig("siteadminemail")):($oSurvey->oOptions->adminemail);
                                $fieldsarray["{ADMINNAME}"] = $admin_name;
                                $fieldsarray["{ADMINEMAIL}"] = $admin_email;
                                $fieldsarray["{SURVEYNAME}"] = $oSurvey->languagesettings[$language]->surveyls_title;
                                $fieldsarray["{SURVEYDESCRIPTION}"] = $oSurvey->languagesettings[$language]->surveyls_description;
                                $fieldsarray["{EXPIRY}"] = strval($oSurvey->expires);

                                $subject = Replacefields($oSurvey->languagesettings[$language]->surveyls_email_invite_subj, $fieldsarray, false);
                                $textarea = Replacefields($oSurvey->languagesettings[$language]->surveyls_email_invite, $fieldsarray, false);
                                if ($ishtml !== true) {
                                    $textarea = str_replace(array('<x>', '</x>'), array(''), (string) $textarea);
                                }
                            ?>
                            <div id="<?php echo $language; ?>" class="tab-pane fade <?php if ($c){$c=false;echo 'show active';}?>">

                                <div class='mb-3'>
                                    <label class='form-label ' for='from_<?php echo $language; ?>'><?php eT("From:"); ?></label>
                                    <div class=''>
                                        <?php echo CHtml::textField("from_{$language}",$admin_name." <".$admin_email.">",array('class' => 'form-control')); ?>
                                    </div>
                                </div>

                                <div class='mb-3'>
                                    <label class='form-label ' for='subject_<?php echo $language; ?>'><?php eT("Subject:"); ?></label>
                                    <div class=''>
                                        <?php echo CHtml::textField("subject_{$language}",$subject,array('class' => 'form-control')); ?>
                                    </div>
                                </div>

                                <div class='mb-3'>

                                    <label class='form-label ' for='message_<?php echo $language; ?>'><?php eT("Message:"); ?></label>
                                    <div class=''>
                                        <div class="input-group htmleditor">
                                            <?php echo CHtml::textArea("message_{$language}",$textarea,array('cols'=>80,'rows'=>20, 'class' => 'form-control')); ?>
                                            <?php echo getEditor("email-invitation", "message_$language", "[" . gT("Invitation email:", "js") . "](" . $language . ")", $surveyid, '', '', "tokens"); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="row">
                        <div class='mb-3'>
                            <div class=''></div>
                            <div class=''>
                                <?php $this->widget(
                                    'ext.ButtonWidget.ButtonWidget',
                                    [
                                        'name' => 'send-invitations-button',
                                        'text' => gT('Send invitations', 'unescaped'),
                                        'icon' => 'ri-mail-send-fill',
                                        'htmlOptions' => [
                                            'class' => 'btn btn-primary',
                                            'type' => 'submit',
                                        ],
                                    ]
                                ); ?>
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
        $('#send-invitation-button').on('click', function(){
            $('#sendinvitation').submit();
        })
", LSYii_ClientScript::POS_POSTSCRIPT ); 
?>
