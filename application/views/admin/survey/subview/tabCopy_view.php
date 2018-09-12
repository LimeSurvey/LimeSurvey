<?php
/**
 * Copy survey
 */
?>
<!-- tab copy survey -->
    <!-- copy survey form -->
    <?php echo CHtml::form(array('admin/survey/sa/copy'), 'post', array('id'=>'copysurveyform', 'name'=>'copysurveyform', 'class'=>'form30 form-horizontal')); ?>

        <!-- Select survey -->
        <div class="form-group">
            <label for='copysurveylist' class="col-sm-3 control-label"><?php  eT("Select survey to copy:"); ?> </label>
            <div class="col-sm-5">
                <select id='copysurveylist' name='copysurveylist' required="required" class="form-control">
                    <?php echo getSurveyList(false); ?>
                </select>
            </div>
            <div class="col-sm-2">
              <p class="form-control-static">
                <span class='annotation text-warning'><?php echo  gT("Required"); ?> </span>
              </p>
            </div>
        </div>

        <!-- New survey title -->
        <div class="form-group">
            <label for='copysurveyname' class="col-sm-3 control-label"><?php echo  eT("New survey title:"); ?> </label>
            <div class="col-sm-5">
                <input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' required="required" class="form-control" />
            </div>
            <div class="col-sm-2">
              <p class="form-control-static">
                <span class='annotation text-warning'><?php echo  gT("Required"); ?> </span>
              </p>
            </div>
        </div>

        <!-- New survey id -->
        <div class="form-group">
            <label class="col-sm-3 control-label" for='copysurveyid'><?php echo  eT("New survey id:"); ?> </label>
            <div class="col-sm-1">
                <input  type='text' id='copysurveyid' size='82' maxlength='6' name='copysurveyid' value='' class="form-control" />
            </div>
            <div class="col-sm-2">
              <p class="form-control-static">
                <span class='annotation text-info'><?php echo  gT("Optional"); ?> </span>
              </p>
            </div>
        </div>

        <!-- Convert resource links -->
        <div class="form-group">
            <label class="col-sm-3 control-label" for='copysurveytranslinksfields'><?php echo  eT("Convert resource links and expression fields?"); ?> </label>
            <div class="col-sm-5">
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
            <label class="col-sm-3 control-label" for='copysurveyexcludequotas'><?php echo  eT("Exclude quotas?"); ?> </label>
            <div class="col-sm-5">
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
            <label class="col-sm-3 control-label" for='copysurveyexcludepermissions'><?php echo  eT("Exclude survey permissions?"); ?> </label>
            <div class="col-sm-5">
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
            <label class="col-sm-3 control-label" for='copysurveyexcludeanswers'><?php echo  eT("Exclude answers?"); ?> </label>
            <div class="col-sm-5">
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
            <label class="col-sm-3 control-label" for='copysurveyresetconditions'><?php echo  eT("Reset conditions/relevance?"); ?> </label>
            <div class="col-sm-5">
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
            <label class="col-sm-3 control-label" for='copysurveyresetstartenddate'><?php echo  eT("Reset start/end date/time?"); ?> </label>
            <div class="col-sm-5">
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'copysurveyresetstartenddate',
                    'value'=> "0",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
            </div>
        </div>

        <!-- Submit -->
        <div class="text-center">
                <input type='submit' class='btn btn-default' value='<?php  eT("Copy survey"); ?>' />
                <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="' . $surveyid . '" />'; ?>
                <input type='hidden' name='action' value='copysurvey' />
        </div>
    </form>
