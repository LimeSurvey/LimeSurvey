<?php
/**
 * Copy survey
 */
?>
<div class="ls-flex-row">
    <div class="grow-10 ls-space padding left-10 right-10">
        <!-- copy survey form -->
        <?php echo CHtml::form(array('surveyAdministration/copy'), 'post', array('id'=>'copysurveyform', 'name'=>'copysurveyform', 'class'=>'form30 row')); ?>
            <div class="col-4">
                <!-- Select survey -->
                <label for='copysurveylist' class=" form-label"><?php  eT("Select survey to copy:"); ?> </label>
                <select id='copysurveylist' name='copysurveylist' required="required" class="form-control">
                    <?php echo getSurveyList(false); ?>
                </select>
                <p class="form-control-static">
                    <span class='annotation text-warning'><?php echo  gT("Required"); ?> </span>
                </p>

                <!-- New survey title -->
                <label for='copysurveyname' class=" form-label"><?php echo  eT("New survey title:"); ?> </label>
                <input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' required="required" class="form-control" />
                <p class="form-control-static">
                    <span class='annotation text-warning'><?php echo  gT("Required"); ?> </span>
                </p>

                <!-- New survey id -->
                <label class=" form-label" for='copysurveyid'><?php echo  eT("New survey id:"); ?> </label>
                <input type='number' step="1" min="1" max="999999" id='copysurveyid' size='82' name='copysurveyid' value='' class="form-control" />
                <p class="form-control-static">
                    <span class='annotation text-info'><?php echo  gT("Optional"); ?> </span>
                </p>

                <!-- Submit -->
                <div class="text-center">
                    <input type='submit' class='btn btn-primary col-4' value='<?php  eT("Copy survey"); ?>' />
                    <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="' . $surveyid . '" />'; ?>
                    <input type='hidden' name='action' value='copysurvey' />
                </div>
            </div>

            <div class="col">
                <!-- Convert resource links -->
                <div class="form-check">
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'copysurveytranslinksfields',
                        'value'=> "1",
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                        ));
                    ?>
                    <label class=" form-label" for='copysurveytranslinksfields'><?php echo  eT("Copy survey resource files and adapt links"); ?> </label>
                </div>

                <!-- Exclude quotas -->
                <div class="form-check">
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'copysurveyexcludequotas',
                        'value'=> "0",
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                        ));
                    ?>
                    <label class=" form-label" for='copysurveyexcludequotas'><?php echo  eT("Exclude quotas"); ?> </label>
                </div>

                <!-- Exclude survey permissions -->
                <div class="form-check">
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'copysurveyexcludepermissions',
                        'value'=> "0",
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                        ));
                    ?>
                    <label class=" form-label" for='copysurveyexcludepermissions'><?php echo  eT("Exclude survey permissions"); ?> </label>
                </div>

                <!-- Exclude answers -->
                <div class="form-check">
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'copysurveyexcludeanswers',
                        'value'=> "0",
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                        ));
                    ?>
                    <label class=" form-label" for='copysurveyexcludeanswers'><?php echo  eT("Exclude answers"); ?> </label>
                </div>

                <!-- Reset conditions/relevance -->
                <div class="form-check">
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'copysurveyresetconditions',
                        'value'=> "0",
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                        ));
                    ?>
                    <label class=" form-label" for='copysurveyresetconditions'><?php echo  eT("Reset conditions"); ?> </label>
                </div>

                <!-- Reset start/end date/time -->
                <div class="form-check">
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'copysurveyresetstartenddate',
                        'value'=> "0",
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                        ));
                    ?>
                    <label class=" form-label" for='copysurveyresetstartenddate'><?php echo  eT("Reset start/end date/time"); ?> </label>
                </div>

                <div class="form-check">
                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'copysurveyresetresponsestartid',
                        'value'=> "0",
                        'onLabel'=>gT('On'),
                        'offLabel'=>gT('Off')
                        ));
                    ?>
                    <label class=" form-label" for='copysurveyresetresponsestartid'><?php echo  eT("Reset response start ID"); ?> </label>
                </div>
            </div>
        </form>
    </div>
</div>
