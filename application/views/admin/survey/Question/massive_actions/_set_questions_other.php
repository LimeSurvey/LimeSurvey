<?php
/**
 * Set question group and position modal body (parsed to massive action widget)
 */

/** @var AdminController $this */
/** @var Question $model */

?>
<form class="custom-modal-datas form-horizontal" data-trigger-validation="true">
    <div  class="mb-3" id="OtherSelection">
        <label class="col-md-4 form-label"><?php eT("Option 'Other':"); ?></label>
        <div class="col-md-8">
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'other',
                'checkedOption' => '0',
                'selectOptions' => [
                    '1' => gT('On'),
                    '0' => gT('Off'),
                ],
                'htmlOptions'   => [
                    'class'       => 'custom-data'
                ],
            ]); ?>
        </div>
        <input type="hidden" name="sid" value="<?php echo (int) Yii::app()->request->getParam('surveyid',0); ?>" class="custom-data"/>
    </div>
</form>
