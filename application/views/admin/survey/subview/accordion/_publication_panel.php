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
            <select id='public' name='public'  class="form-control">
                <option value='Y'
                    <?php if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") { ?>
                  selected='selected'
                    <?php } ?>
                    ><?php  eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") { ?>
                  selected='selected'
                    <?php } ?>
                 ><?php  eT("No"); ?>
                </option>
            </select>
        </div>
    </div>

    <!-- Start date/time -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='startdate'><?php  eT("Start date/time:"); ?></label>
        <div class="col-sm-6">
            <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                    'name' => "startdate",
                    'id' => 'startdate',
                    'value' => $startdate,
                    'pluginOptions' => array(
                        'format' => reverseDateToFitDatePicker($dateformatdetails['dateformat']) . " HH:mm",
                        'singleDatePicker' => true,
                        'startDate' => date("Y-m-d hh:ii", time()),
                        'drops' => 'up',  // TODO: Does not work. Why?
                        'timePicker' => true,
                        'timePicker12Hour' => false,  // NB: timePicker24Hour = true does not work
                        'timePickerIncrement' => 1
                    )
                ));
            ?>
        </div>
    </div>

    <!-- Expiry date/time -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='expires'><?php  eT("Expiry date/time:"); ?></label>
        <div class="col-sm-6">
            <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                    'name' => "expires",
                    'id' => 'expires',
                    'value' => $expires,
                    'pluginOptions' => array(
                        'format' => reverseDateToFitDatePicker($dateformatdetails['dateformat']) . " HH:mm",
                        'singleDatePicker' => true,
                        'startDate' => date("Y-m-d", time()),
                        'drops' => 'up',  // TODO: Does not work. Why?
                        'timePicker' => true,
                        'timePicker12Hour' => false,  // NB: timePicker24Hour = true does not work
                        'timePickerIncrement' => 1
                    )
                ));
            ?>
        </div>
    </div>

    <!-- Set cookie to prevent repeated participation -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='usecookie'><?php  eT("Set cookie to prevent repeated participation:"); ?></label>
        <div class="col-sm-6">
            <select name='usecookie' id='usecookie'  class="form-control">
                <option value='Y'
                        <?php if ($esrow['usecookie'] == "Y") { ?>
                         selected='selected'
                        <?php } ?>
                        ><?php  eT("Yes"); ?>
                </option>
                <option value='N'
                        <?php if ($esrow['usecookie'] != "Y") { ?>
                         selected='selected'
                           <?php } ?>
                        ><?php  eT("No"); ?>
                </option>
            </select>

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
