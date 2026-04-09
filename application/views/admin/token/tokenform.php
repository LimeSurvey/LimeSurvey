<?php
/**
 * Add token entry
 */

$locale = convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);
?>
<div class='<?= (!isset($ajax) || $ajax = false) ? 'col-12 side-body' : ''?>'>
    <?php if (!isset($ajax) || $ajax = false) { ?>
        <h3>
            <?php

            if ($token_subaction == "edit") {
                eT("Edit survey participant");
            } else {
                eT("Add survey participant");
            }
            ?>
        </h3>
    <?php } ?>
    <?php
    foreach ($tokendata as $Key => $Value) {
        $$Key = $Value;
    }
    ?>
    <div id="edittoken-error-container" class="row" style="display: none;">
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => '<span class="alert-content"></span>',
            'type' => 'danger',
        ]);
        ?>
    </div>
    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(array("admin/tokens/sa/{$token_subaction}/surveyid/{$surveyid}/tokenid/{$tokenid}"),
                'post',
                array(
                    'id' => 'edittoken',
                    'class' => '',
                    'data-additional-attributes-validation-error' => gT("Some mandatory additional attributes were left blank. Please review them."),
                    'data-expiration-validation-error' => gT('Participant expiration date can\'t be lower than the "Valid from" date'),
                )
            ); ?>
            <!-- Tabs -->
            <?php if( count($attrfieldnames) > 0 ):?>
                <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
                    <!-- Common  -->
                    <li role="presentation" class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#general" aria-expanded="true">
                            <?php eT('General'); ?>
                        </a>
                    </li>

                    <!-- Custom attibutes -->
                    <li role="presentation" class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#custom" aria-expanded="false">
                            <?php eT('Additional attributes'); ?>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>

            <!-- Tabs content-->
            <div class="tab-content">
                <div id="general" class="tab-pane fade show active">
                <div class="row">
                        <!-- General -->
                        <div class="ex-form-group mb-3 col-6">
                            <!-- ID  -->
                            <label class="form-label">ID:</label>
                            <div class="">
                                <p class="form-control-static">
                                    <?php
                                    if ($token_subaction == "edit") {
                                        echo $tokenid;
                                    } else {
                                        eT("Auto");
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="ex-form-group mb-3 col-6">
                            <!--
                            TODO:
                            To take in account the anonomyzed survey case (completed field contain no date, but a {Y,N}), the code become more complexe
                            It will need a refactorisation .
                            maybe a widget? At least, a lot of variable should be set in the controller (classes etc)
                            -->
                            <?php $sCointainerClass = ($oSurvey->anonymized != 'Y') ? 'yes-no-date-container' : 'yes-no-container'; ?>
                            <!-- Completed -->
                            <div id="completed-container" class="">
                                <label class="form-label" for='completed'>
                                    <?php eT("Completed?"); ?>
                                </label>
                                <div class="selector__yesNoContainer <?php echo $sCointainerClass; ?>"
                                     id="completed-yes-no-date-container"
                                     data-locale="<?php echo $locale ?>">
                                    <div class="row">
                                        <?php if ($oSurvey->anonymized != 'Y'): ?>
                                            <?php $bCompletedValue = "0";
                                            if (isset($completed) && $completed != 'N') {
                                                $completedDBFormat = $completed;
                                                $bCompletedValue   = "1";
                                                $completed         = convertToGlobalSettingFormat($completed, true);
                                            } ?>
                                            <div>
                                                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                    'name'          => "completed-switch",
                                                    'htmlOptions'   => ['class' => "YesNoDateSwitch action_toggle_bootstrap_switch mb-1"],
                                                    'checkedOption' => $bCompletedValue,
                                                    'selectOptions' => [
                                                        '1' => gT('Yes'),
                                                        '0' => gT('No'),
                                                    ],
                                                ]); ?>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <?php $completedDBFormat = $completed ?? 'N';
                                                $bCompletedValue = (isset($completed) && $completed != 'N') ? "1" : "0";
                                                $completed = (isset($completed) && $completed != 'N') ? 'Y' : 'N'; ?>
                                                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                    'name'          => "completed-switch",
                                                    'htmlOptions'   => ['class' => "YesNoSwitch action_toggle_bootstrap_switch mb-1"],
                                                    'checkedOption' => $bCompletedValue,
                                                    'selectOptions' => [
                                                        '1' => gT('Yes'),
                                                        '0' => gT('No'),
                                                    ],
                                                ]); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($oSurvey->anonymized !== 'Y'): ?>
                                            <div class="">
                                                <div id="sent-date-container" class="date-container <?= !$bCompletedValue ? "d-none" : "" ?>">
                                                    <div id="completed-date_datetimepicker" class="input-group date">
                                                        <input class="YesNoDatePicker form-control"
                                                               id="completed-date" type="text"
                                                               value="<?php echo isset($completed) ? $completed : '' ?>"
                                                               name="completed-date"
                                                               data-locale="<?php echo $locale ?>"
                                                               data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm">
                                                        <span class="input-group-text datepicker-icon"><span class="ri-calendar-2-fill"></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input class='form-control d-none YesNoDateHidden' type='text' size='20' id='completed' name='completed' value="<?php if (isset($completed)) {echo $completed; } else {echo "N"; }?>" />
                                </div>
                            </div>
                        </div>

                        <!-- First name, Last name -->
                        <div class="ex-form-group mb-3 col-6">
                            <label class="form-label" for='firstname'>
                                <?php eT("First name:"); ?>
                            </label>
                            <div class="">
                                <?= TbHtml::textField('firstname',
                                    $firstname,
                                    [
                                        'class' => 'form-control',
                                        'size' => '30',
                                    ]
                                ); ?>
                            </div>
                        </div>
                        <div class="ex-form-group mb-3 col-6">
                            <label class="form-label" for='lastname'>
                                <?php eT("Last name:"); ?>
                            </label>
                            <div class="">
                                <?= TbHtml::textField('lastname',
                                    $lastname,
                                    [
                                        'class' => 'form-control',
                                        'size' => '30',
                                    ]
                                ); ?>
                            </div>
                        </div>

                        <!-- Token, language -->
                        <div class="ex-form-group mb-3 col-6">
                            <label class="form-label" for='token'>
                                <?php eT("Access code:"); ?>
                            </label>
                            <div class="">
                                <?= TbHtml::textField('token',
                                    (isset($token) ? $token : ""),
                                    [
                                        'class' => 'form-control',
                                        'size' => '20',
                                        'maxlength' => $iTokenLength
                                    ]
                                ); ?>
                                <?php if ($token_subaction == "addnew") : ?>
                                    <?php
                                    $this->widget('ext.AlertWidget.AlertWidget', [
                                        'text'        => gT(
                                            "You can leave this blank, and automatically generate access codes using 'Generate access codes'"
                                        ),
                                        'type'        => 'info',
                                        'htmlOptions' => ['class' => 'mt-1'],
                                    ]);
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ex-form-group mb-3 col-6">
                            <label class="form-label" for='language'>
                                <?php eT("Language:"); ?>
                            </label>
                            <div class="">
                                <?php if (isset($language)) {
                                    echo languageDropdownClean($surveyid, $language);
                                } else {
                                    echo languageDropdownClean($surveyid, Survey::model()->findByPk($surveyid)->language);
                                } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <!-- Email, Email Status  -->
                        <div class="ex-form-group mb-3 col-6">
                            <label class="form-label" for='email'>
                                <?php eT("Email:"); ?>
                            </label>
                            <div class="">
                                <?= TbHtml::emailField('email',
                                    $email,
                                    [
                                        'class' => 'form-control',
                                        'size' => '50',
                                        'maxlength' => '320',
                                        'multiple' => true
                                    ]
                                ); ?>
                            </div>
                        </div>

                        <!-- Email Status -->
                        <div class="ex-form-group mb-3 col-6">
                            <label class="form-label" for='emailstatus'>
                                <?php eT("Email status:"); ?>
                            </label>
                            <div class="">
                                <?= TbHtml::textField('emailstatus',
                                    $emailstatus,
                                    [
                                        'class' => 'form-control',
                                        'size' => '50',
                                        'maxlength' => '320',
                                        'placeholder' => 'OK'
                                    ]
                                ); ?>
                            </div>
                        </div>

                        <!-- Invitation sent, Reminder sent -->
                        <div class="ex-form-group mb-3 col-6">
                            <!-- Invitation sent -->
                            <label class="form-label" for='sent'>
                                <?php eT("Invitation sent?"); ?>
                            </label>
                            <div class="selector__yesNoContainer <?php echo $sCointainerClass; ?>" id="sent-yes-no-date-container"
                                 data-locale="<?php echo $locale ?>">
                                <div class="row">
                                    <div class="">
                                        <?php if ($oSurvey->anonymized != 'Y'): ?>
                                            <?php
                                            // TODO: move to controller
                                            $bSwitchValue       = (isset($sent) && $sent != 'N') ? "1" : "0";
                                            $bRemindSwitchValue = (isset($remindersent) && $remindersent != 'N') ? "1" : "0";

                                            $bSwitchValue = "0";
                                            if (isset($sent) && $sent != 'N') {
                                                $bSwitchValue = "1";
                                                $sentDBValue  = $sent;
                                                $sent         = convertToGlobalSettingFormat($sent, true);
                                            }

                                            $bRemindSwitchValue = "0";
                                            if (isset($remindersent) && $remindersent != 'N') {
                                                $bRemindSwitchValue  = "1";
                                                $remindersentDBValue = $remindersent;
                                                $remindersent        = convertToGlobalSettingFormat($remindersent, true);
                                            } ?>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "sent-switch",
                                                'htmlOptions'   => ['class' => "YesNoDateSwitch action_toggle_bootstrap_switch mb-1"],
                                                'checkedOption' => $bSwitchValue,
                                                'selectOptions' => [
                                                    '1' => gT('Yes'),
                                                    '0' => gT('No'),
                                                ],
                                            ]); ?>
                                        <?php else: ?>
                                            <?php
                                            $sentDBValue         = $sent ?? 'N';
                                            $remindersentDBValue = $remindersent ?? 'N';
                                            $bSwitchValue        = (isset($sent) && $sent != 'N') ? "1" : "0";
                                            $bRemindSwitchValue  = (isset($remindersent) && $remindersent != 'N') ? "1" : "0";
                                            ?>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "sent-switch",
                                                'htmlOptions'   => ['class' => "YesNoSwitch action_toggle_bootstrap_switch mb-1"],
                                                'checkedOption' => $bSwitchValue,
                                                'selectOptions' => [
                                                    '1' => gT('Yes'),
                                                    '0' => gT('No'),
                                                ],
                                            ]); ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="">
                                        <div id="sent-date-container" data-parent="#sent-switch" class="selector__date-container_hidden date-container <?= !$bSwitchValue ? "d-none" : "" ?>">
                                            <!-- Sent Date -->
                                            <div id="sent-date_datetimepicker" class="input-group date">
                                                <input class="YesNoDatePicker form-control"
                                                       id="sent-date"
                                                       type="text"
                                                       value="<?php echo isset($sent) && $sent != 'N' ? $sent : '' ?>"
                                                       name="sent-date"
                                                       data-locale="<?php echo $locale ?>"
                                                       data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm">
                                                <span class="input-group-text datepicker-icon"><span class="ri-calendar-2-fill"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input class='form-control d-none YesNoDateHidden' type='text' size='20' id='sent' name='sent' value="<?php if (isset($sent)) {echo $sent; } else {echo "N"; }?>" />
                            </div>
                        </div>

                        <div class="ex-form-group mb-3 col-6">
                            <!-- Reminder sent -->
                            <label class="form-label" for='remindersent'>
                                <?php eT("Reminder sent?"); ?>
                            </label>
                            <div class="selector__yesNoContainer <?php echo $sCointainerClass; ?>" id="remind-yes-no-date-container"
                                 data-locale="<?php echo $locale ?>">

                                <div class="row">
                                    <div>
                                        <?php if ($oSurvey->anonymized !== 'Y'): ?>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "remind-switch",
                                                'htmlOptions'   => ['class' => "YesNoDateSwitch action_toggle_bootstrap_switch mb-1"],
                                                'checkedOption' => $bRemindSwitchValue,
                                                'selectOptions' => [
                                                    '1' => gT('Yes'),
                                                    '0' => gT('No'),
                                                ],
                                            ]); ?>
                                        <?php else: ?>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "remind-switch",
                                                'htmlOptions'   => ['class' => "YesNoSwitch action_toggle_bootstrap_switch mb-1"],
                                                'checkedOption' => $bRemindSwitchValue,
                                                'selectOptions' => [
                                                    '1' => gT('Yes'),
                                                    '0' => gT('No'),
                                                ],
                                            ]); ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="">
                                        <div id="remind-date-container" data-parent="#remind-switch" class="selector__date-container_hidden date-container <?= !$bRemindSwitchValue ? "d-none" : "" ?>">
                                            <div id="remind-date_datetimepicker" class="input-group date">
                                                <input class="YesNoDatePicker form-control"
                                                       id="remind-date"
                                                       type="text"
                                                       value="<?php echo isset($remindersent) && $remindersent != 'N' ? $remindersent : '' ?>"
                                                       name="remind-date"
                                                       data-locale="<?php echo $locale ?>"
                                                       data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm">
                                                <span class="input-group-text datepicker-icon"><span class="ri-calendar-2-fill"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input class='form-control d-none YesNoDateHidden' type='text' size='20' id='remindersent' name='remindersent' value="<?php if (isset($remindersent) && $remindersent!='N') {echo $remindersent; } else {echo "N"; }?>" />
                            </div>
                        </div>

                            <!-- Reminder count -->
                            <?php if ($token_subaction == "edit"): ?>
                                <!-- Reminder count, Uses left -->
                                <div class="ex-form-group mb-3 col-6">
                                        <label class="form-label" for='remindercount'>
                                            <?php eT("Reminder count:"); ?>
                                        </label>
                                        <input class='form-control' type='number' size='6' id='remindercount' name='remindercount' value="<?php echo $remindercount; ?>"/>
                                </div>
                            <?php endif; ?>

                            <!-- Uses left -->
                            <div class="ex-form-group mb-3 col-6">
                                <label class="form-label" for='usesleft'>
                                    <?php eT("Uses left:"); ?>
                                </label>
                                <input class='form-control' type='number' size='20' id='usesleft' name='usesleft' value="<?php if (isset($usesleft)) {
                                    echo $usesleft;
                                } else {
                                    echo " 1 ";
                                } ?>"/>
                            </div>
                        </div>

                    <div class="row">
                        <div class="col-6">
                            <!-- Valid from to  -->
                            <div class="ex-form-group mb-3">
                                <?php
                                if (isset($validfrom) && $validfrom != 'N') {
                                    $validfrom = convertToGlobalSettingFormat($validfrom, true);
                                }

                                if (isset($validuntil) && $validuntil != 'N') {
                                    $validuntil = convertToGlobalSettingFormat($validuntil, true);
                                }
                                ?>

                                <!-- From -->
                                <label class="form-label" for='validfrom'>
                                    <?php eT("Valid from"); ?>:</label>
                                <div class=" has-feedback">
                                    <div id="validfrom_datetimepicker" class="input-group date">
                                    <input class="YesNoDatePicker form-control" id="validfrom" type="text" value="<?php echo isset($validfrom) ? $validfrom : '' ?>" name="validfrom"
                                           data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm"
                                           data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']); ?>">
                                    <span class="input-group-text datepicker-icon"><span class="ri-calendar-2-fill"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="ex-form-group mb-3">
                                <!-- To -->
                                <label class="form-label" for='validuntil'>
                                    <?php eT('Until:'); ?>
                                </label>
                                <div class="has-feedback">
                                    <div id="validuntil_datetimepicker" class="input-group date">
                                    <input class="YesNoDatePicker form-control" id="validuntil" type="text" value="<?php echo isset($validuntil) ? $validuntil : '' ?>" name="validuntil"
                                           data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm"
                                           data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']); ?>">
                                    <span class="input-group-text datepicker-icon"><span class="ri-calendar-2-fill"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom attibutes -->
                <div id="custom" class="tab-pane fade">
                    <!-- Attributes -->
                    <?php foreach ($attrfieldnames as $attr_name => $attr_description): ?>
                        <div class="ex-form-group mb-3 col-6">
                            <label class="form-label" for='<?php echo $attr_name; ?>'>
                                <?php echo $attr_description['description'] . ($attr_description['mandatory'] == 'Y' ? '*' : '') ?>:
                            </label>
                            <div class="">
                                <input
                                    class='form-control<?= $attr_description['mandatory'] == 'Y' ? ' mandatory-attribute' : '' ?>'
                                    type='text'
                                    size='55'
                                    id='<?php echo $attr_name; ?>'
                                    name='<?php echo $attr_name; ?>'
                                    value='<?php if (isset($$attr_name)) {
                                        echo htmlspecialchars((string) $$attr_name, ENT_QUOTES, 'utf-8');
                                    } ?>'
                                />
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Buttons -->
            <p>
                <?php
                switch ($token_subaction) {
                    case "edit":
                        ?>
                        <input type='submit' class="d-none" value='<?php eT("Update participant entry"); ?>'/>
                        <input type='hidden' name='subaction' value='updatetoken'/>
                        <input type='hidden' name='tid' value='<?php echo $tokenid; ?>'/>
                        <?php break;
                    case "addnew": ?>
                        <input type='submit' class="d-none" value='<?php eT("Add participant entry"); ?>'/>
                        <input type='hidden' name='subaction' value='inserttoken'/>
                        <?php break;
                } ?>
                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>'/>
            </p>
            <?php echo CHtml::endForm() ?>
        </div>
    </div>
</div>

<?php if ($token_subaction == "addnew"): ?>
    <!-- Empty Token Confirmation Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="emptyTokenConfirmationModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= gT('Create empty participant') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= gT("You are about to create a participant without the basic details. Are you sure you want to proceed?") ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                    	<?php eT("Cancel");?>
                    </button>
                    <button role="button" type="button" class="btn btn-primary" id="save-empty-token">
                        <?php eT("Save");?>
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
<?php endif; ?>
