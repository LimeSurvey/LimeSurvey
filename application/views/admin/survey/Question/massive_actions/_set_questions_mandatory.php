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
    <div id='MandatorySelection' class="form-group">
        <label class="col-sm-4 control-label"><?php eT("Mandatory:"); ?></label>
        <div class="col-sm-8">
        <?php
            $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'mandatory',
                'value' => 'N',
                'selectOptions'=>array(
                    "Y"=>gT("Yes",'unescaped'),
                    "S"=>gT("Soft",'unescaped'),
                    "N"=>gT("No",'unescaped')
                ),
                'htmlOptions'=>array(
                    'class'=>'custom-data',
                ),
            ));
        ?>
            <input type="hidden" name="sid" value="<?php echo (int) $surveyid; ?>" class="custom-data"/>
        </div>
    </div>
</form>
