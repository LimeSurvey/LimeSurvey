<?php
/**
 * Set question group and position modal body (parsed to massive action widget)
 */
?>
<form class="custom-modal-datas form-horizontal" data-trigger-validation="true">
    <div  class="form-group" id="OtherSelection">
        <label class="col-sm-4 control-label"><?php eT("Option 'Other':"); ?></label>
        <div class="col-sm-8">
            <select class="form-control custom-data attributes-to-update" id="other" name="other" required>
                <option value="" selected="selected"><?php eT('Please select and option');?></option>
                <option value="false"><?php eT('Off');?></option>
                <option value="true"><?php eT('On');?></option>
            </select>
        </div>
        <input type="hidden" name="sid" value="<?php echo (int) Yii::app()->request->getParam('surveyid',0); ?>" class="custom-data"/>
    </div>
</form>
