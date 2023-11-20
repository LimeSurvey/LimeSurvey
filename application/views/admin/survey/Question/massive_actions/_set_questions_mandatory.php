<?php
/**
 * Set question group and position modal body (parsed to massive action widget)
 */
$surveyid = App()->request->getParam('surveyid', 0);
/** @var AdminController $this */
/** @var Question $model */
/** @var Survey $oSurvey */

?>
<form class="custom-modal-datas form-horizontal">
    <div id='MandatorySelection' class="mb-3">
        <label class="col-md-4 form-label"><?php eT("Mandatory:"); ?></label>
        <div class="col-md-8">
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'mandatory',
                'checkedOption' => 'N',
                'selectOptions' => [
                    "Y" => gT("Yes", 'unescaped'),
                    "S" => gT("Soft", 'unescaped'),
                    "N" => gT("No", 'unescaped')
                ],
                'htmlOptions'   => [
                    'class' => 'custom-data',
                ],
            ]); ?>
            <input type="hidden" name="sid" value="<?php echo (int)$surveyid; ?>" class="custom-data"/>
        </div>
    </div>
</form>
