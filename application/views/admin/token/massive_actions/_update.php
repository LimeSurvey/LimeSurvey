<?php
/**
 * Edit multiple tokens
 */
 $iSurveyId = Yii::app()->request->getParam('surveyid');
 $attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
 $aCoreTokenFields = array('validfrom', 'validuntil', 'firstname', 'lastname', 'emailstatus', 'token', 'language', 'sent', 'remindersent', 'completed', 'usesleft' );
 $oSurvey = Survey::model()->findByPk($iSurveyId);
 $sCointainerClass = ($oSurvey->anonymized != 'Y' ?  'yes-no-date-container' : 'yes-no-container');

?>

<form class="custom-modal-datas form form-horizontal makeDisabledInputsTransparent">
    <div id='updateTokens' >
            <!-- Tabs -->
            <?php if( count($attrfieldnames) > 0 ):?>
                <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">

                    <!-- Common  -->
                    <li role="presentation" class="active">
                        <a data-toggle="tab" href="#massive-general" aria-expanded="true">
                            <?php eT('General'); ?>
                        </a>
                        </li>

                        <!-- Custom attibutes -->
                        <li role="presentation" class="">
                            <a data-toggle="tab" href="#massive-custom" aria-expanded="false">
                                <?php eT('Additional attributes'); ?>
                            </a>
                        </li>
                    </ul>
            <?php endif; ?>

            <!-- Tabs content-->
            <div class="tab-content">
            <div id="massive-general" class="tab-pane active fade in">
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                        <?php eT("Modify"); ?>
                        </label>
                    </div>
                    <div class="col-sm-11"></div>
                </div>
                <!-- Completed -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='massedit_completed'><?php eT("Completed?"); ?></label>
                    <div class="col-sm-8 <?php echo ($oSurvey->anonymized != 'Y' ? 'yes-no-date-container' : 'yes-no-container' );?>" id="massedit_completed-yes-no-date-container" data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>">
                        <div class="row">

                            <?php if ($oSurvey->anonymized != 'Y'):?>

                                <div class="col-sm-4">
                                    <?php
                                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                        'name' => "completed-switch",
                                        'id'=>"massedit_completed-switch",
                                        'htmlOptions'=>array('class'=>"YesNoSwitch YesNoDateSwitch bootstrap-switch-integer", 'disabled' => true),
                                        'value' => 0,
                                        'onLabel'=>gT('Yes'),
                                        'offLabel' => gT('No')
                                        )
                                    );
                                        ?>
                                </div>

                            <?php else:?>

                                <div class="col-sm-4">
                                    <?php
                                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                        'name' => "completed-switch",
                                        'id'=>"massedit_completed-switch",
                                        'htmlOptions'=>array('class'=>"YesNoSwitch bootstrap-switch-integer", 'disabled' => true),
                                        'value' => 0,
                                        'onLabel'=>gT('Yes'),
                                        'offLabel' => gT('No')
                                        )
                                    );
                                        ?>
                                </div>

                            <?php endif;?>

                            <div class="col-sm-7 col-sm-offset-1">
                                <?php if ($oSurvey->anonymized != 'Y'):?>
                                    <div id="massedit_sent-date-container" class="date-container selector_datechange"  style="display: none;">
                                        <div id="completed-date_datetimepicker" class="input-group date">
                                            <input
                                                class="YesNoDatePicker form-control"
                                                id="massedit_completed-date"
                                                type="text"
                                                value="<?php echo date($dateformatdetails['phpdate']); ?>"
                                                name="completed-date"
                                                data-date-format="<?php echo $dateformatdetails['jsdate'];?> HH:mm"
                                                >
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                        </div>
                                    </div>
                                <?php endif;?>
                            </div>
                        </div>
                        <input class='form-control custom-data selector_submitField hidden YesNoDateHidden' type='text' size='20' id='massedit_completed' name='completed' value="lskeep" />
                    </div>
                </div>

                <!-- First name -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label" for='massedit_firstname'><?php eT("First name:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='30' id='massedit_firstname' name='firstname' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Last name -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='massedit_lastname'><?php eT("Last name:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='30'  id='massedit_lastname' name='lastname' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Language -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='massedit_language'><?php eT("Language:"); ?></label>
                    <div class="col-sm-8">
                        <?php echo CHtml::dropDownList('language', '', array_merge(array('lskeep'=>''), $aLanguages), array('id'=>'massedit_language', 'class'=>'form-control custom-data selector_submitField', 'disabled'=>'disabled')); ?>
                    </div>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-1">
                            <label class="" >
                                <input type="checkbox" class="action_check_to_keep_old_value"></input>
                            </label>
                        </div>
                        <label class="col-sm-3 control-label"  for='massedit_email'><?php eT("Email:"); ?></label>
                        <div class="col-sm-8">
                            <input class='form-control custom-data selector_submitField action_validate_email' data-targetfield="#massedit_emailstatus" type='text' maxlength='320' size='50' id='massedit_email' name='email' value="lskeep" disabled />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1">
                        </div>
                        <label class="col-sm-3 control-label"  for='massedit_emailstatus'><?php eT("Email status:"); ?></label>
                        <div class="col-sm-8">
                            <input class='form-control custom-data selector_submitField' type='text' maxlength='320' size='50' id='massedit_emailstatus' name='emailstatus' placeholder='OK' value="lskeep" disabled />
                        </div>
                    </div>
                </div>

                <!-- Invitation sent -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='sent'><?php eT("Invitation sent?"); ?></label>
                    <div class="col-sm-8 <?php echo $sCointainerClass;?>" id="massedit_sent-yes-no-date-container" data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>">
                        <div class="row">
                            <div class="col-sm-4">

                                <?php if ($oSurvey->anonymized != 'Y'):?>

                                    <?php
                                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => "sent-switch",
                                            'id'=>"massedit_sent-switch",
                                            'htmlOptions'=>array('class'=>"YesNoSwitch YesNoDateSwitch bootstrap-switch-integer", 'disabled' => true),
                                            'value' => 0,
                                            'onLabel'=>gT('Yes'),
                                            'offLabel' => gT('No')));
                                    ?>
                                <?php else:?>

                                        <?php
                                            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                                'name' => "sent-switch",
                                                'id'=>"massedit_sent-switch",
                                                'htmlOptions'=>array('class'=>"YesNoSwitch bootstrap-switch-integer", 'disabled' => true),
                                                'value' => 0,
                                                'onLabel'=>gT('Yes'),
                                                'offLabel' => gT('No')));
                                        ?>
                                <?php endif; ?>
                            </div>

                            <div class="col-sm-8">
                                <div id="sent-date-container" class="date-container selector_datechange" style="display: none;">
                                    <!-- Sent Date -->
                                    <div id="sent-date_datetimepicker" class="input-group date">
                                        <input
                                            class="YesNoDatePicker form-control"
                                            id="massedit_sent-date"
                                            type="text"
                                            value="<?php echo date($dateformatdetails['phpdate']); ?>"
                                            name="sent-date"
                                            data-date-format="<?php echo $dateformatdetails['jsdate'];?> HH:mm"
                                        >
                                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input class='form-control hidden custom-data selector_submitField YesNoDateHidden' type='text' size='20' id='massedit_sent' name='sent' value="lskeep" />
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <!-- Reminder sent -->
                    <label class="col-sm-3 control-label"  for='massedit_remindersent'><?php eT("Reminder sent?"); ?></label>
                    <div class="col-sm-8 <?php echo $sCointainerClass;?>" id="massedit_remind-yes-no-date-container" data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>">

                        <div class="row">
                            <div class="col-sm-4">
                                <?php if ($oSurvey->anonymized != 'Y'):?>
                                    <?php
                                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => "remind-switch",
                                            'id'=>"massedit_remind-switch",
                                            'htmlOptions'=>array('class'=>"YesNoSwitch YesNoDateSwitch bootstrap-switch-integer", 'disabled' => true),
                                            'value' => 0,
                                            'onLabel'=>gT('Yes'),
                                            'offLabel' => gT('No')));
                                    ?>
                                <?php else:?>
                                    <?php
                                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => "sent-switch",
                                            'id'=>"massedit_sent-switch",
                                            'htmlOptions'=>array('class'=>"YesNoSwitch bootstrap-switch-integer", 'disabled' => true),
                                            'value' => 0,
                                            'onLabel'=>gT('Yes'),
                                            'offLabel' => gT('No')));
                                    ?>
                                <?php endif; ?>
                            </div>

                            <div class="col-sm-8">

                                <div id="massedit_remind-date-container" class="date-container selector_datechange" style="display: none;">

                                    <div id="massedit_remind-date_datetimepicker" class="input-group date">
                                        <input
                                            class="YesNoDatePicker form-control"
                                            id="massedit_remind-date"
                                            type="text"
                                            value="<?php echo date($dateformatdetails['phpdate']); ?>"
                                            name="remind-date"
                                            data-date-format="<?php echo $dateformatdetails['jsdate'];?> HH:mm"
                                        >
                                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input class='form-control custom-data hidden selector_submitField YesNoDateHidden' type='text' size='20' id='massedit_remindersent' name='remindersent' value="lskeep" />
                    </div>
                </div>
                
                
                <!-- Reminder count -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='massedit_remindercount'><?php eT("Reminder count:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='6' id='massedit_remindercount' name='remindercount' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Uses left -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='massedit_usesleft'><?php eT("Uses left:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='20' id='massedit_usesleft' name='usesleft' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Valid from -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>

                    <label class="col-sm-3 control-label"  for='massedit_validfrom'><?php eT("Valid from"); ?>:</label>
                    <div class="col-sm-8 has-feedback">
                        <div id="massedit_validfrom_datetimepicker" class="input-group date">
                            <input
                                class="YesNoDatePicker form-control action_datepickerUpdateHiddenField"
                                id="massedit_validfrom"
                                type="text"
                                value="lskeep"
                                name="validfrom_date"
                                data-date-format="<?php echo $dateformatdetails['jsdate'];?> HH:mm"
                                data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>"
                                disabled
                            >
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                        <input id="sbmtvalid" type="hidden" name="validfrom" value="lskeep" class="custom-data selector_submitField" />
                    </div>
                </div>

                <!-- Valid to -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='massedit_validuntil'><?php eT('Until:'); ?></label>
                    <div class="col-sm-8 has-feedback">
                        <div id="massedit_validuntil_datetimepicker" class="input-group date">
                            <input
                                class="YesNoDatePicker form-control action_datepickerUpdateHiddenField"
                                id="massedit_validuntil"
                                type="text"
                                value="lskeep"
                                name="validuntil_date"
                                data-date-format="<?php echo $dateformatdetails['jsdate'];?> HH:mm"
                                data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>"
                                disabled
                            >
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                        <input id="sbmtvalid" type="hidden" name="validuntil" value="lskeep" class="custom-data selector_submitField" />
                    </div>
                </div>

                <?php /*                            
                <?php foreach($aCoreTokenFields as $sCoreTokenField): ?>
                    <div class="row">
                        <div class="form-group">
                            <label class="col-sm-2 control-label"  for='<?php echo $sCoreTokenField; ?>'><?php echo $sCoreTokenField;  ?>:</label>
                            <div class="col-sm-8">
                                <input type="text" class="custom-data" name="<?php echo $sCoreTokenField;?>" id="<?php echo $sCoreTokenField;?>" value="lskeep" />
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
                */ ?>

            </div>

            <!-- Custom attibutes -->
            <div id="massive-custom" class="tab-pane fade in">
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                        <?php eT("Modify"); ?>
                        </label>
                    </div>
                    <div class="col-sm-11"></div>
                </div>
                <!-- Attributes -->
                <?php foreach ($attrfieldnames as $attr_name => $attr_description): ?>
                    <div class="form-group">
                        <div class="col-sm-1">
                            <label class="" >
                                
                                <input type="checkbox" class="action_check_to_keep_old_value"></input>
                            </label>
                        </div>
                        <label class="col-sm-3 control-label"  for='massedit_<?php echo $attr_name; ?>'><?php echo $attr_description['description'] . ($attr_description['mandatory'] == 'Y' ? '*' : '') ?>:</label>
                        <div class="col-sm-8">
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

   var bindBSSwitch = function(formGroup){
       console.log(\"bindBSSwitch run on:\",formGroup);
    //Script to update the completed settings
    formGroup.find('.YesNoSwitch').on('switchChange.bootstrapSwitch', function(e, state){
        
        formGroup.find('.selector_datechange').css('display', (state ? '' : 'none'));
        formGroup.find('.selector_submitField').val(state ? 'Y' : 'N');

    });
   };

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
        var myFormGroup = $(this).closest('.form-group');
        
        $(this).closest('.form-group').find('input:not(.action_check_to_keep_old_value),select:not(.action_check_to_keep_old_value)').prop('disabled', currentValue)

        if($(this).closest('.form-group').find('.bootstrap-switch-container').length > 0){
            $(this).closest('.form-group').find('.bootstrap-switch-container input[type=checkbox]').bootstrapSwitch('disabled', currentValue);

        }

        if(currentValue){
            $(this).closest('.form-group').find('.selector_submitField').val('lskeep');
        } else {
            $(this).closest('.form-group').find('.selector_submitField').val('');
            bindBSSwitch(myFormGroup);
            bindDatepicker(myFormGroup);
        }
        
    });
};
bindClicksInModal(); 
$(document).on('actions-updated', function(){
    bindClicksInModal(); 
});

", LSYii_ClientScript::POS_END); ?>
