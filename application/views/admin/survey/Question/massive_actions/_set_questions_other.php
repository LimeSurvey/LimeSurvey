<?php
/**
 * Set question group and position modal body (parsed to massive action widget)
 */
?>
<form class="custom-modal-datas form-horizontal">
    <div  class="form-group" id="OtherSelection">
        <label class="col-sm-4 control-label"><?php eT("Option 'Other':"); ?></label>
        <div class="col-sm-8">
            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'other', 'value'=> '', 'htmlOptions'=>array('class'=>'custom-data  bootstrap-switch-boolean', 'data-gridid'=>'question-grid'), 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
        </div>
        <input type="hidden" name="sid" value="<?php echo (int) Yii::app()->request->getParam('surveyid',0); ?>" class="custom-data"/>
    </div>
</form>
