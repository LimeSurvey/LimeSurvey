<?php
/**
 * Publication Panel
 */
?>
<!-- Publication panel -->
<div id='publication' class="tab-pane fade in">

    <!-- List survey publicly -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='public'><?php  eT("List survey publicly:");?></label>
        <div class="col-sm-6">
            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                'name' => 'listpublic',
                'value'=> $esrow['listpublic'] == "Y",
                'onLabel'=>gT('On'),
                'offLabel'=>gT('Off')
                ));
            ?>
        </div>
    </div>

    <!-- Start date/time -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='startdate'><?php  eT("Start date/time:"); ?></label>
        <div class="col-sm-6 has-feedback">
            <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                    'name' => "startdate",
                    'id' => 'startdate',
                    'value' => $startdate,
                    'pluginOptions' => array(
                        'format' => $dateformatdetails['jsdate'] . " HH:mm",
                        'allowInputToggle' =>true,
                        'showClear' => true,
                        'tooltips' => array(
                            'clear'=> gT('Clear selection'),
                            'prevMonth'=> gT('Previous month'),
                            'nextMonth'=> gT('Next month'),
                            'selectYear'=> gT('Select year'),
                            'prevYear'=> gT('Previous year'),
                            'nextYear'=> gT('Next year'),
                            'selectDecade'=> gT('Select decade'),
                            'prevDecade'=> gT('Previous decade'),
                            'nextDecade'=> gT('Next decade'),
                            'prevCentury'=> gT('Previous century'),
                            'nextCentury'=> gT('Next century'),
                            'selectTime'=> gT('Select time')
                        ),
                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                    )
                ));
            ?>
        </div>
    </div>

    <!-- Expiry date/time -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='expires'><?php  eT("Expiry date/time:"); ?></label>
        <div class="col-sm-6 has-feedback">
            <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                    'name' => "expires",
                    'id' => 'expires',
                    'value' => $expires,
                    'pluginOptions' => array(
                        'format' => $dateformatdetails['jsdate'] . " HH:mm",
                        'allowInputToggle' =>true,
                        'showClear' => true,
                        'tooltips' => array(
                            'clear'=> gT('Clear selection'),
                            'prevMonth'=> gT('Previous month'),
                            'nextMonth'=> gT('Next month'),
                            'selectYear'=> gT('Select year'),
                            'prevYear'=> gT('Previous year'),
                            'nextYear'=> gT('Next year'),
                            'selectDecade'=> gT('Select decade'),
                            'prevDecade'=> gT('Previous decade'),
                            'nextDecade'=> gT('Next decade'),
                            'prevCentury'=> gT('Previous century'),
                            'nextCentury'=> gT('Next century'),
                                'selectTime'=> gT('Select time')
                        ),
                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                    )
                ));
            ?>
        </div>
    </div>

    <!-- Set cookie to prevent repeated participation -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='usecookie'><?php  eT("Set cookie to prevent repeated participation:"); ?></label>
        <div class="col-sm-6">
            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                'name' => 'usecookie',
                'value'=> $esrow['usecookie'] == "Y",
                'onLabel'=>gT('On'),
                'offLabel'=>gT('Off')
                ));
            ?>
        </div>
    </div>

    <!-- Use CAPTCHA for survey access -->
    <?php $usecap = $esrow['usecaptcha']; // Just a short-hand ?>
    <div class="form-group">
        <label class="col-sm-6 control-label" for='usecaptcha_surveyaccess'><?php  eT("Use CAPTCHA for survey access:"); ?></label>
        <div class="col-sm-6">
            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                'name' => 'usecaptcha_surveyaccess',
                'value'=> $usecap === 'A' || $usecap === 'B' || $usecap === 'C' || $usecap === 'X',
                'onLabel'=>gT('On'),'offLabel'=>gT('Off')));
            ?>
        </div>
    </div>

    <!-- Use CAPTCHA for registration -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='usecaptcha_registration'><?php  eT("Use CAPTCHA for registration:"); ?></label>
        <div class="col-sm-6">
            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                'name' => 'usecaptcha_registration',
                'value'=> $usecap === 'A' || $usecap === 'B' || $usecap === 'D' || $usecap === 'R',
                'onLabel'=>gT('On'),
                'offLabel'=>gT('Off')));
            ?>
        </div>
    </div>

    <!-- Use CAPTCHA for save and load -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='usecaptcha_saveandload'><?php  eT("Use CAPTCHA for save and load:"); ?></label>
        <div class="col-sm-6">
            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                'name' => 'usecaptcha_saveandload',
                'value'=> $usecap === 'A' || $usecap === 'C' || $usecap === 'D' || $usecap === 'S',
                'onLabel'=>gT('On'),
                'offLabel'=>gT('Off')));
            ?>
        </div>
    </div>

</div>
