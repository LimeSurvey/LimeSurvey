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
<style>
    input[type=text]:disabled,
    input[type=email]:disabled{
        color: transparent;
    }
</style>

<form class="custom-modal-datas form form-horizontal">
    <div id='updateTokens' >
            <!-- Tabs -->
            <?php if( count($attrfieldnames) > 0 ):?>
                <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">

                    <!-- Common  -->
                    <li role="presentation" class="active">
                        <a data-toggle="tab" href="#general" aria-expanded="true">
                            <?php eT('General'); ?>
                        </a>
                        </li>

                        <!-- Custom attibutes -->
                        <li role="presentation" class="">
                            <a data-toggle="tab" href="#custom" aria-expanded="false">
                                <?php eT('Additional attributes'); ?>
                            </a>
                        </li>
                    </ul>
            <?php endif; ?>

            <!-- Tabs content-->
            <div class="tab-content">
            <div id="general" class="tab-pane active fade in">

                <!-- Completed -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='completed'><?php eT("Completed?"); ?></label>
                    <div class="col-sm-8 <?php echo ($oSurvey->anonymized != 'Y' ? 'yes-no-date-container' : 'yes-no-container' );?>" id="completed-yes-no-date-container" data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>">
                        <div class="row">

                            <?php if ($oSurvey->anonymized != 'Y'):?>

                                <div class="col-sm-4">
                                    <?php
                                    $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                        'name' => "completed-switch",
                                        'id'=>"completed-switch",
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
                                        'id'=>"completed-switch",
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
                                    <div id="sent-date-container" class="date-container selector_datechange"  style="display: none;">
                                        <div id="completed-date_datetimepicker" class="input-group date">
                                            <input
                                                class="YesNoDatePicker form-control"
                                                id="completed-date"
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
                        <input class='form-control custom-data selector_submitField hidden YesNoDateHidden' type='text' size='20' id='completed' name='completed' value="lskeep" />
                    </div>
                </div>

                <!-- First name -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label" for='firstname'><?php eT("First name:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='30' id='firstname' name='firstname' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Last name -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='lastname'><?php eT("Last name:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='text' size='30'  id='lastname' name='lastname' value="lskeep" disabled />
                    </div>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='email'><?php eT("Email:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='email' maxlength='320' size='50' id='email' name='email' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Email Status -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='emailstatus'><?php eT("Email status:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='text' maxlength='320' size='50' id='emailstatus' name='emailstatus' placeholder='OK' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Invitation sent -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='sent'><?php eT("Invitation sent?"); ?></label>
                    <div class="col-sm-8 <?php echo $sCointainerClass;?>" id="sent-yes-no-date-container" data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>">
                        <div class="row">
                            <div class="col-sm-4">

                                <?php if ($oSurvey->anonymized != 'Y'):?>

                                    <?php
                                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => "sent-switch",
                                            'id'=>"sent-switch",
                                            'htmlOptions'=>array('class'=>"YesNoSwitch YesNoDateSwitch bootstrap-switch-integer", 'disabled' => true),
                                            'value' => 0,
                                            'onLabel'=>gT('Yes'),
                                            'offLabel' => gT('No')));
                                    ?>
                                <?php else:?>

                                        <?php
                                            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                                'name' => "sent-switch",
                                                'id'=>"sent-switch",
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
                                            id="sent-date"
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
                        <input class='form-control hidden custom-data selector_submitField YesNoDateHidden' type='text' size='20' id='sent' name='sent' value="<?php if (isset($sentDBValue)){echo $sentDBValue;}else{echo "N";}?>" />
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <!-- Reminder sent -->
                    <label class="col-sm-3 control-label"  for='remindersent'><?php eT("Reminder sent?"); ?></label>
                    <div class="col-sm-8 <?php echo $sCointainerClass;?>" id="remind-yes-no-date-container" data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>">

                        <div class="row">
                            <div class="col-sm-4">
                                <?php if ($oSurvey->anonymized != 'Y'):?>
                                    <?php
                                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => "remind-switch",
                                            'id'=>"remind-switch",
                                            'htmlOptions'=>array('class'=>"YesNoSwitch YesNoDateSwitch bootstrap-switch-integer", 'disabled' => true),
                                            'value' => 0,
                                            'onLabel'=>gT('Yes'),
                                            'offLabel' => gT('No')));
                                    ?>
                                <?php else:?>
                                    <?php
                                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => "sent-switch",
                                            'id'=>"sent-switch",
                                            'htmlOptions'=>array('class'=>"YesNoSwitch bootstrap-switch-integer", 'disabled' => true),
                                            'value' => 0,
                                            'onLabel'=>gT('Yes'),
                                            'offLabel' => gT('No')));
                                    ?>
                                <?php endif; ?>
                            </div>

                            <div class="col-sm-8">

                                <div id="remind-date-container" class="date-container selector_datechange" style="display: none;">

                                    <div id="remind-date_datetimepicker" class="input-group date">
                                        <input
                                            class="YesNoDatePicker form-control"
                                            id="remind-date"
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
                        <input class='form-control custom-data hidden selector_submitField YesNoDateHidden' type='text' size='20' id='remindersent' name='remindersent' value="lskeep" />
                    </div>
                </div>
                
                
                <!-- Reminder count -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='remindercount'><?php eT("Reminder count:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='number' size='6' id='remindercount' name='remindercount' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Uses left -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='usesleft'><?php eT("Uses left:"); ?></label>
                    <div class="col-sm-8">
                        <input class='form-control custom-data selector_submitField' type='number' size='20' id='usesleft' name='usesleft' value="lskeep" disabled />
                    </div>
                </div>

                <!-- Valid from -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>

                    <label class="col-sm-3 control-label"  for='validfrom'><?php eT("Valid from"); ?>:</label>
                    <div class="col-sm-8 has-feedback">
                        <div id="validfrom_datetimepicker" class="input-group date">
                            <input
                                class="YesNoDatePicker form-control custom-data selector_submitField"
                                id="validfrom"
                                type="text"
                                value="lskeep"
                                name="validfrom"
                                data-date-format="<?php echo $dateformatdetails['jsdate'];?> HH:mm"
                                data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>"
                                disabled
                            >
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                </div>

                <!-- Valid to -->
                <div class="form-group">
                    <div class="col-sm-1">
                        <label class="" >
                            <?php eT("Change"); ?>
                            <input type="checkbox" class="action_check_to_keep_old_value"></input>
                        </label>
                    </div>
                    <label class="col-sm-3 control-label"  for='validuntil'><?php eT('Until:'); ?></label>
                    <div class="col-sm-8 has-feedback">
                        <div id="validuntil_datetimepicker" class="input-group date">
                            <input
                                class="YesNoDatePicker form-control custom-data selector_submitField"
                                id="validuntil"
                                type="text"
                                value="lskeep"
                                name="validuntil"
                                data-date-format="<?php echo $dateformatdetails['jsdate'];?> HH:mm"
                                data-locale="<?php echo convertLStoDateTimePickerLocale(Yii::app()->session['adminlang']);?>"
                                disabled
                            >
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
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
            <div id="custom" class="tab-pane fade in">
                <!-- Attributes -->
                <?php foreach ($attrfieldnames as $attr_name => $attr_description): ?>
                    <div class="form-group">
                        <div class="col-sm-1">
                            <label class="" >
                                <?php eT("Change"); ?>
                                <input type="checkbox" class="action_check_to_keep_old_value"></input>
                            </label>
                        </div>
                        <label class="col-sm-3 control-label"  for='<?php echo $attr_name; ?>'><?php echo $attr_description['description'] . ($attr_description['mandatory'] == 'Y' ? '*' : '') ?>:</label>
                        <div class="col-sm-8">
                            <input type='text' class="form-control custom-data selector_submitField" size='55' id='<?php echo $attr_name; ?>' disabled name='<?php echo $attr_name; ?>' value='lskeep' />
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <input type="hidden" id="sid" name="sid" class="custom-data" value="<?php echo $_GET['surveyid']; ?>" />
    </div>
</form>


<script>
   var bindBSSwitch = function(formGroup){
       console.log("bindBSSwitch run on:",formGroup);
    //Script to update the completed settings
    formGroup.find('.YesNoSwitch').on('switchChange.bootstrapSwitch', function(e, state){
        
        formGroup.find('.selector_datechange').css('display', (state ? '' : 'none'));
        formGroup.find('.selector_submitField').val(state ? 'Y' : 'N');

    });
   };

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
        
        $(this).closest('.form-group').find('input:not(.action_check_to_keep_old_value)').prop('disabled', currentValue)
        if($(this).closest('.form-group').find('.bootstrap-switch-container').length > 0){
            $(this).closest('.form-group').find('.bootstrap-switch-container input[type=checkbox]').bootstrapSwitch('disabled', currentValue);

        }

        if(currentValue){
            $(this).closest('.form-group').find('.selector_submitField').val('lskeep');
        } else {
            bindBSSwitch(myFormGroup);
        }

    })
</script>