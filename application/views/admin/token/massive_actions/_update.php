<?php

/**
 * Edit multiple tokens
 */
$iSurveyId = (int) Yii::app()->request->getParam('surveyid');
$attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
$aCoreTokenFields = array('validfrom', 'validuntil', 'firstname', 'lastname', 'emailstatus', 'token', 'language', 'sent', 'remindersent', 'completed', 'usesleft');
$oSurvey = Survey::model()->findByPk($iSurveyId);
$sCointainerClass = ($oSurvey->anonymized != 'Y' ?  'yes-no-date-container' : 'yes-no-container');
$locale = convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);
?>

<form class="custom-modal-datas form form-horizontal makeDisabledInputsTransparent">
    <div id='updateTokens'>
        <!-- Tabs -->
        <?php if (count($attrfieldnames) > 0) : ?>
            <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
                <!-- Common  -->
                <li role="presentation" class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#massive-general" aria-expanded="true">
                        <?php eT('General'); ?>
                    </a>
                </li>

                <!-- Custom attibutes -->
                <li role="presentation" class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#massive-custom" aria-expanded="false">
                        <?php eT('Additional attributes'); ?>
                    </a>
                </li>
            </ul>
        <?php endif; ?>

        <!-- Tabs content-->
        <div class="tab-content">
            <div id="massive-general" class="tab-pane fade show active">
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <?php eT("Modify"); ?>
                        </label>
                    </div>
                    <div class="col-md-11"></div>
                </div>
                <!-- Completed -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-md-3 form-label" for='massedit_completed'><?php eT("Completed?"); ?></label>
                    <div class="col-md-8 <?php echo ($oSurvey->anonymized != 'Y' ? 'yes-no-date-container' : 'yes-no-container'); ?>" id="massedit_completed-yes-no-date-container" data-locale="<?php echo $locale ?>">
                        <div class="row">

                            <?php if ($oSurvey->anonymized != 'Y') : ?>

                                <div class="col-md-4">
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => "completed-switch",
                                        'id'            => "massedit_completed-switch",
                                        'htmlOptions'   => ['class' => "YesNoSwitch YesNoDateSwitch", 'disabled' => true],
                                        'checkedOption' => 0,
                                        'selectOptions' => [
                                            '1' => gT('Yes'),
                                            '0' => gT('No'),
                                        ],
                                    ]); ?>
                                </div>

                            <?php else : ?>

                                <div class="col-md-4">
                                    <?php
                                    $this->widget(
                                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                                        [
                                            'name'        => "completed-switch",
                                            'id'          => "massedit_completed-switch",
                                            'htmlOptions' => ['class' => "YesNoSwitch", 'disabled' => true],
                                            'checkedOption'       => 0,
                                            'selectOptions' => [
                                                '1' => gT('Yes'),
                                                '0' => gT('No'),
                                            ],
                                        ]
                                    );
                                    ?>
                                </div>

                            <?php endif; ?>

                            <div class="col-md-8">
                                <?php if ($oSurvey->anonymized != 'Y') : ?>
                                    <div id="massedit_completed-date-container" class="date-container selector_datechange d-none">
                                        <div id="massedit_completed-date_datetimepicker" class="input-group date">
                                            <input class="YesNoDatePicker form-control" id="massedit_completed-date" type="text" value="<?php echo date($dateformatdetails['phpdate']); ?>" name="completed-date" data-locale="<?php echo $locale ?>" data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm">
                                            <span class="input-group-text"><span class="ri-calendar-2-fill"></span></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <input class='form-control custom-data selector_submitField d-none YesNoDateHidden' type='text' size='20' id='massedit_completed' name='completed' value="lskeep" />
                    </div>
                </div>

                <!-- First name -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-md-3 form-label" for='massedit_firstname'><?php eT("First name:"); ?></label>
                    <div class="col-md-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='30' id='massedit_firstname' name='firstname' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Last name -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-md-3 form-label" for='massedit_lastname'><?php eT("Last name:"); ?></label>
                    <div class="col-md-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='30' id='massedit_lastname' name='lastname' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Language -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-md-3 form-label" for='massedit_language'><?php eT("Language:"); ?></label>
                    <div class="col-md-8">
                        <?php echo CHtml::dropDownList('language', '', array_merge(['lskeep' => ''], $aLanguages), ['id' => 'massedit_language', 'class' => 'form-select custom-data selector_submitField', 'disabled' => 'disabled']); ?>
                    </div>
                </div>

                <!-- Email -->
                <div class="ex-form-group mb-3">
                    <div class="row mb-2">
                        <div class="col-md-1">
                            <label class="">
                                <input type="checkbox" class="action_check_to_keep_old_value"></input>
                            </label>
                        </div>
                        <label class="col-md-3 form-label" for='massedit_email'><?php eT("Email:"); ?></label>
                        <div class="col-md-8">
                            <input class='form-control custom-data selector_submitField action_validate_email' data-targetfield="#massedit_emailstatus" type='text' maxlength='320' size='50' id='massedit_email' name='email' value="lskeep" disabled />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-1">
                        </div>
                        <label class="col-md-3 form-label" for='massedit_emailstatus'><?php eT("Email status:"); ?></label>
                        <div class="col-md-8">
                            <input class='form-control custom-data selector_submitField' type='text' maxlength='320' size='50' id='massedit_emailstatus' name='emailstatus' placeholder='OK' value="lskeep" disabled />
                        </div>
                    </div>
                </div>

                <!-- Invitation sent -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-md-3 form-label" for='sent'><?php eT("Invitation sent?"); ?></label>
                    <div class="col-md-8 <?php echo $sCointainerClass; ?>" id="massedit_sent-yes-no-date-container" data-locale="<?php echo $locale ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <?php if ($oSurvey->anonymized !== 'Y') : ?>
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => "sent-switch",
                                        'id'            => "massedit_sent-switch",
                                        'htmlOptions'   => ['class' => "YesNoSwitch YesNoDateSwitch", 'disabled' => true],
                                        'checkedOption' => 0,
                                        'selectOptions' => [
                                            '1' => gT('Yes'),
                                            '0' => gT('No'),
                                        ],
                                    ]); ?>
                                <?php else : ?>
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => "sent-switch",
                                        'id'            => "massedit_sent-switch",
                                        'htmlOptions'   => ['class' => "YesNoSwitch", 'disabled' => true],
                                        'checkedOption' => 0,
                                        'selectOptions' => [
                                            '1' => gT('Yes'),
                                            '0' => gT('No'),
                                        ],
                                    ]); ?>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-8">
                                <div id="massedit_sent-date-container" class="date-container selector_datechange d-none">
                                    <!-- Sent Date -->
                                    <div id="massedit_sent-date_datetimepicker" class="input-group date">
                                        <input class="YesNoDatePicker form-control" id="massedit_sent-date" type="text" value="<?php echo date($dateformatdetails['phpdate']); ?>" name="sent-date" data-locale="<?php echo $locale ?>" data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm">
                                        <span class="input-group-text"><span class="ri-calendar-2-fill"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input class='form-control d-none custom-data selector_submitField YesNoDateHidden' type='text' size='20' id='massedit_sent' name='sent' value="lskeep" />
                    </div>
                </div>

                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <!-- Reminder sent -->
                    <label class="col-md-3 form-label" for='massedit_remindersent'><?php eT("Reminder sent?"); ?></label>
                    <div class="col-md-8 <?php echo $sCointainerClass; ?>" id="massedit_remind-yes-no-date-container" data-locale="<?php echo $locale ?>">

                        <div class="row">
                            <div class="col-md-4">
                                <?php if ($oSurvey->anonymized !== 'Y') : ?>
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => "remind-switch",
                                        'id'            => "massedit_remind-switch",
                                        'htmlOptions'   => ['class' => "YesNoSwitch YesNoDateSwitch", 'disabled' => true],
                                        'checkedOption' => 0,
                                        'selectOptions' => [
                                            '1' => gT('Yes'),
                                            '0' => gT('No'),
                                        ],
                                    ]); ?>
                                <?php else : ?>
                                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                        'name'          => "sent-switch",
                                        'id'            => "massedit_sent-switch",
                                        'htmlOptions'   => ['class' => "YesNoSwitch", 'disabled' => true],
                                        'checkedOption' => 0,
                                        'selectOptions' => [
                                            '1' => gT('Yes'),
                                            '0' => gT('No'),
                                        ],
                                    ]); ?>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-8">

                                <div id="massedit_remind-date-container" class="date-container selector_datechange d-none">

                                    <div id="massedit_remind-date_datetimepicker" class="input-group date">
                                        <input class="YesNoDatePicker form-control" id="massedit_remind-date" type="text" value="<?php echo date($dateformatdetails['phpdate']); ?>" name="remind-date" data-locale="<?php echo $locale ?>" data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm">
                                        <span class="input-group-text"><span class="ri-calendar-2-fill"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input class='form-control custom-data d-none selector_submitField YesNoDateHidden' type='text' size='20' id='massedit_remindersent' name='remindersent' value="lskeep" />
                    </div>
                </div>

                <!-- Reminder count -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">

                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-md-3 form-label" for='massedit_remindercount'><?php eT("Reminder count:"); ?></label>
                    <div class="col-md-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='6' id='massedit_remindercount' name='remindercount' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Uses left -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">

                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-md-3 form-label" for='massedit_usesleft'><?php eT("Uses left:"); ?></label>
                    <div class="col-md-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='20' id='massedit_usesleft' name='usesleft' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Valid from -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>

                    <label class="col-md-3 form-label" for='massedit_validfrom'><?php eT("Valid from"); ?>:</label>
                    <div class="col-md-8 has-feedback date-picker-container">
                        <div id="massedit_validfrom_datetimepicker" class="input-group date">
                            <input class="DatePicker form-control action_datepickerUpdateHiddenField" id="massedit_validfrom" type="text" value="" name="validfrom_date" data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm" data-locale="<?php echo $locale ?>" disabled>
                            <span class="input-group-text"><span class="ri-calendar-2-fill"></span></span>
                        </div>
                        <input id="sbmtvalidfrom" type="hidden" name="validfrom" value="lskeep" class="custom-data selector_submitField" />
                    </div>
                </div>

                <!-- Valid to -->
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-md-3 form-label" for='massedit_validuntil'><?php eT('Until:'); ?></label>
                    <div class="col-md-8 has-feedback date-picker-container">
                        <div id="massedit_validuntil_datetimepicker" class="input-group date">
                            <input class="DatePicker form-control action_datepickerUpdateHiddenField" id="massedit_validuntil" type="text" value="" name="validuntil_date" data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm" data-locale="<?php echo $locale ?>" disabled>
                            <span class="input-group-text"><span class="ri-calendar-2-fill"></span></span>
                        </div>
                        <input id="sbmtvaliduntil" type="hidden" name="validuntil" value="lskeep" class="custom-data selector_submitField" />
                    </div>
                </div>

                <?php /*                            
                <?php foreach($aCoreTokenFields as $sCoreTokenField): ?>
                    <div class="row">
                        <div class="ex-form-group mb-3 row">
                            <label class="col-md-2 form-label"  for='<?php echo $sCoreTokenField; ?>'><?php echo $sCoreTokenField;  ?>:</label>
                            <div class="col-md-8">
                                <input type="text" class="custom-data" name="<?php echo $sCoreTokenField;?>" id="<?php echo $sCoreTokenField;?>" value="lskeep" />
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
                */ ?>

            </div>

            <!-- Custom attibutes -->
            <div id="massive-custom" class="tab-pane fade">
                <div class="ex-form-group mb-3 row">
                    <div class="col-md-1">
                        <label class="">
                            <?php eT("Modify"); ?>
                        </label>
                    </div>
                    <div class="col-md-11"></div>
                </div>
                <!-- Attributes -->
                <?php foreach ($attrfieldnames as $attr_name => $attr_description) : ?>
                    <div class="ex-form-group mb-3 row">
                        <div class="col-md-1">
                            <label class="">

                                <input type="checkbox" class="action_check_to_keep_old_value"></input>
                            </label>
                        </div>
                        <label class="col-md-3 form-label" for='massedit_<?php echo $attr_name; ?>'><?php echo $attr_description['description'] . ($attr_description['mandatory'] == 'Y' ? '*' : '') ?>:</label>
                        <div class="col-md-8">
                            <input type='text' class="form-control custom-data selector_submitField" size='55' id='massedit_<?php echo $attr_name; ?>' disabled name='<?php echo $attr_name; ?>' value='lskeep' />
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <input type="hidden" id="sid" name="sid" class="custom-data" value="<?php echo $iSurveyId; ?>" />
    </div>
</form>


<?php App()->getClientScript()->registerScript("Tokens:MassActionUpdateView_Scripts", "
   var bindDatepicker = function(myFormGroup){
    myFormGroup.find('.action_datepickerUpdateHiddenField').on('change dp.change', function(){
        myFormGroup.find('.selector_submitField').val($(this).val());
    })
   }
   var bindClicksInModal = function(){
    $('#email').on('keyup', function(){
        //Don't change emailstatus when it is still disabled
        if($('#emailstatus').prop('disabled')) return;

        //This is the official w3c regex to check for valid email addresses
        var regexemail = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        if(regexemail.test($(this).val())){
            $('#emailstatus').val('OK');
        }
    });

    $('.action_check_to_keep_old_value').on('click', function(){
        var currentValue = !$(this).prop('checked');
        var myFormGroup = $(this).closest('.ex-form-group');
        
        myFormGroup.find('input:not(.action_check_to_keep_old_value),select:not(.action_check_to_keep_old_value)').prop('disabled', currentValue)

        if(currentValue){
            myFormGroup.find('.selector_submitField').val('lskeep');
        } else {
            myFormGroup.find('.selector_submitField').val('');
            bindDatepicker(myFormGroup);
        }
        
    });
};
bindClicksInModal(); 
$(document).on('actions-updated', function(){
    bindClicksInModal(); 
});

", LSYii_ClientScript::POS_END); ?>
