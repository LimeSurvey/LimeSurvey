<?php
/**
 * Send email reminder
 */
?>

<div class='side-body'>
    <h3 aria-level="1"><?php eT("Send email reminder"); ?></h3>
    <div class="row">
        <div class="col-12 content-right">
            <?php echo PrepareEditorScript(true, $this); ?>

            <?php if ($thissurvey['active'] != 'Y'): ?>
                <?php if ($thissurvey[$baselang]['active'] != 'Y'): ?>
                    <div class="jumbotron message-box message-box-error">
                        <h2 class='text-danger'><?php eT('Warning!'); ?></h2>
                        <p class="lead text-danger">
                            <?php eT("This survey is not yet activated and so your participants won't be able to fill out the survey."); ?>
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php echo CHtml::form(array("admin/tokens/sa/email/action/remind/surveyid/{$surveyid}"), 'post', array('id' => 'sendreminder', 'class' => '')); ?>
            <div class="row">
                <div class="col-md-6">
                    <?php if (count($tokenids) > 0): ?>
                        <div class='mb-3'>
                            <label class='form-label '><?php eT("Send reminder to participant ID(s):"); ?></label>
                            <div class=''>
                                <?php echo short_implode(", ", "-", (array)$tokenids); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class='mb-3'>
                        <label class='form-label '
                               for='bypassbademails'><?php eT("Bypass participants with failing email addresses:"); ?></label>
                        <div>
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name'          => 'bypassbademails',
                                'ariaLabel'    => gT("Bypass participants with failing email addresses"),
                                'checkedOption' => '1',
                                'selectOptions' => [
                                    '1' => gT('On'),
                                    '0' => gT('Off'),
                                ]
                            ]); ?>
                        </div>
                    </div>

                    <div class='mb-3'>
                        <?php
                        echo CHtml::label(gT("Bypass date control before sending email:"), 'bypassdatecontrol', [
                            'title' => gT("If some participants have a 'valid from' date set which is in the future, they will not be able to access the survey before that 'valid from' date."),
                            'unescaped' => 'unescaped',
                            'class' => 'form-label '
                        ]);
                        ?>
                        <div>
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name'          => 'bypassdatecontrol',
                                'ariaLabel'    => gT("Bypass date control before sending email"),
                                'checkedOption' => '0',
                                'selectOptions' => [
                                    '1' => gT('On'),
                                    '0' => gT('Off'),
                                ]
                            ]); ?>
                        </div>
                        <div class=''></div>
                    </div>

                    <!-- Only partial responses -->
                    <div class='mb-3'>
                        <label class='form-label '
                               for='partialonly'><?php eT("Send email only to participants with partial responses:"); ?></label>
                        <div>
                            <?php
                                $disabledTip = gT('Not supported for anonymous surveys.');
                            ?>
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name' => 'partialonly',
                                'ariaLabel'    => gT('Send email only to participants with partial responses'),
                                'checkedOption' => '0',
                                'htmlOptions' => [
                                    'title' => $oSurvey->anonymized == 'Y' ? $disabledTip : '',
                                    'disabled' => $oSurvey->anonymized == 'Y' ? '1' : '0',
                                ],
                                'selectOptions' => [
                                    '1' => gT('On'),
                                    '0' => gT('Off'),
                                ]
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Max reminders -->
                    <div class='mb-3'>
                        <label class='form-label ' for='maxremindercount'><?php eT("Maximum reminders:"); ?></label>
                        <div class=''>
                            <input type="text" id="maxremindercount" class="form-control" size="25" value=""
                                   name="maxremindercount" style="width: 50%;"/>
                        </div>
                    </div>

                    <!-- Min days between reminders -->
                    <div class='mb-3'>
                        <label class='form-label '
                               for='minreminderdelay'><?php eT("Minimum days between reminders:"); ?></label>
                        <div class=''>
                            <input type="text" id="minreminderdelay" class="form-control" size="25" value=""
                                   name="minreminderdelay" style="width: 50%;">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <ul class="nav nav-tabs">
                    <?php $c = true ?>
                    <?php foreach ($oSurvey->allLanguages as $language): ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $c ? "active" : "" ?>" data-bs-toggle="tab" href="#<?= $language ?>">
                                <?php if ($c) {
                                    $c = false;
                                } ?>
                                <?= getLanguageNameFromCode($language, false) . " " . (($language == $oSurvey->language) ? "(" . gT("Base language") . ")" : "") ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php
                    $c = true;
                    foreach ($surveylangs as $language) {
                        $fieldsarray["{ADMINNAME}"] = $thissurvey['adminname'];
                        $fieldsarray["{ADMINEMAIL}"] = $thissurvey['adminemail'];
                        $fieldsarray["{SURVEYNAME}"] = $thissurvey[$language]['name'];
                        $fieldsarray["{SURVEYDESCRIPTION}"] = $thissurvey[$language]['description'];
                        $fieldsarray["{EXPIRY}"] = strval($thissurvey["expiry"]);

                        $subject = Replacefields($thissurvey[$language]['email_remind_subj'], $fieldsarray, false);
                        $textarea = Replacefields($thissurvey[$language]['email_remind'], $fieldsarray, false);
                        if ($ishtml !== true) {
                            $textarea = str_replace(array('<x>', '</x>'), array(''), (string) $textarea); // ?????
                        }
                        ?>

                        <div id="<?php echo $language; ?>" class="tab-pane fade <?php if ($c) {
                            $c = false;
                            echo 'show active';
                        } ?>">

                            <div class='mb-3'>
                                <label class='form-label '
                                       for='from_<?php echo $language; ?>'><?php eT("From:"); ?></label>
                                <div class=''>
                                    <?php echo CHtml::textField("from_{$language}", $thissurvey[$baselang]['adminname'] . " <" . $thissurvey[$baselang]['adminemail'] . ">", array('class' => 'form-control')); ?>
                                </div>
                            </div>

                            <div class='mb-3'>
                                <label class='form-label '
                                       for='subject_<?php echo $language; ?>'><?php eT("Subject:"); ?></label>
                                <div class=''>
                                    <?php echo CHtml::textField("subject_{$language}", $subject, array('class' => 'form-control')); ?>
                                </div>
                            </div>

                            <div class='mb-3'>
                                <label class='form-label '
                                       for='message_<?php echo $language; ?>'><?php eT("Message:"); ?></label>
                                <div class="input-group htmleditor ">
                                    <?php echo CHtml::textArea("message_{$language}", $textarea, array('cols' => 80, 'rows' => 20, 'class' => 'form-control')); ?>
                                    <?php echo getEditor("email-reminder", "message_$language", "[" . gT("Reminder Email:", "js") . "](" . $language . ")", $surveyid, '', '', "tokens"); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="row">
                    <div class='mb-3'>
                        <div class=''></div>
                        <div class=''>
                            <?php echo CHtml::submitButton(gT("Send Reminders", 'unescaped'), array('class' => 'btn btn-outline-secondary')); ?>
                        </div>

                        <?php
                        echo CHtml::hiddenField('ok', 'absolutely');
                        echo CHtml::hiddenField('subaction', 'remind');
                        if (!empty($tokenids)) {
                            echo CHtml::hiddenField('tokenids', implode('|', (array)$tokenids));
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php echo CHtml::endForm() ?>
        </div>

        <?php
        App()->getClientScript()->registerScript("Tokens:BindReminderView", "
        $('#send-reminders-button').on('click', function(){
            $('#sendreminder').submit();
        })
", LSYii_ClientScript::POS_POSTSCRIPT);
        ?>
