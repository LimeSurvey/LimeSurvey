<?php
/**
 * Copy survey
 */
?>
<div class="ls-flex-row wrap align-content-center align-items-center">
<div class="container-fluid col-sm-10 col-md-8">
<!-- tab copy survey -->
    <!-- copy survey form -->
    <?php echo CHtml::form(array('admin/survey/sa/copy'), 'post', array('id'=>'copysurveyform', 'name'=>'copysurveyform', 'class'=>'form30 ')); ?>
        <div class="ls-flex-column col-md-6">
        <!-- Select survey -->
        <div class="form-group">
            <label for='copysurveylist' class=" control-label"><?php  eT("Select survey to copy:"); ?> </label>
            <div class="">
                <select id='copysurveylist' name='copysurveylist' required="required" class="form-control">
                    <?php echo getSurveyList(false); ?>
                </select>
            </div>
            <div class="">
              <p class="form-control-static">
                <span class='annotation text-warning'><?php echo  gT("Required"); ?> </span>
              </p>
            </div>
        </div>

        <!-- New survey title -->
        <div class="form-group">
            <label for='copysurveyname' class=" control-label"><?php echo  eT("New survey title:"); ?> </label>
            <div class="">
                <input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' required="required" class="form-control" />
            </div>
            <div class="">
              <p class="form-control-static">
                <span class='annotation text-warning'><?php echo  gT("Required"); ?> </span>
              </p>
            </div>
        </div>

        <!-- New survey id -->
        <div class="form-group">
            <label class=" control-label" for='copysurveyid'><?php echo  eT("New survey id:"); ?> </label>
            <div class="">
                <input type='number' step="1" min="1" max="999999" id='copysurveyid' size='82' name='copysurveyid' value='' class="form-control" />
            </div>
            <div class="">
              <p class="form-control-static">
                <span class='annotation text-info'><?php echo  gT("Optional"); ?> </span>
              </p>
            </div>
        </div>
        </div>
         <div class="ls-flex-column col-md-6">
        <!-- Convert resource links -->
        <div class="form-group">
            <label class=" control-label" for='copysurveytranslinksfields'><?php echo  eT("Convert resource links and expression fields?"); ?> </label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'copysurveytranslinksfields',
                    'value'=> "1",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
            </div>
        </div>

        <!-- Exclude quotas -->
        <div class="form-group">
            <label class=" control-label" for='copysurveyexcludequotas'><?php echo  eT("Exclude quotas?"); ?> </label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'copysurveyexcludequotas',
                    'value'=> "0",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
            </div>
        </div>

        <!-- Exclude survey permissions -->
        <div class="form-group">
            <label class=" control-label" for='copysurveyexcludepermissions'><?php echo  eT("Exclude survey permissions?"); ?> </label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'copysurveyexcludepermissions',
                    'value'=> "0",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
            </div>
        </div>

        <!-- Exclude answers -->
        <div class="form-group">
            <label class=" control-label" for='copysurveyexcludeanswers'><?php echo  eT("Exclude answers?"); ?> </label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'copysurveyexcludeanswers',
                    'value'=> "0",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
            </div>
        </div>

        <!-- Reset conditions/relevance -->
        <div class="form-group">
            <label class=" control-label" for='copysurveyresetconditions'><?php echo  eT("Reset conditions/relevance?"); ?> </label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'copysurveyresetconditions',
                    'value'=> "0",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
            </div>
        </div>

        <!-- Reset start/end date/time -->
        <div class="form-group">
            <label class=" control-label" for='copysurveyresetstartenddate'><?php echo  eT("Reset start/end date/time?"); ?> </label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'copysurveyresetstartenddate',
                    'value'=> "0",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <label class=" control-label" for='copysurveyresetresponsestartid'><?php echo  eT("Reset response start ID?"); ?> </label>
            <div class="">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'copysurveyresetresponsestartid',
                    'value'=> "0",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
            </div>
        </div>
        </div>

        <!-- Submit -->
        <div class="text-center">
            <input type='submit' class='btn btn-primary col-6' value='<?php  eT("Copy survey"); ?>' />
            <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="' . $surveyid . '" />'; ?>
            <input type='hidden' name='action' value='copysurvey' />
        </div>
    </form>
</div>
</div>
