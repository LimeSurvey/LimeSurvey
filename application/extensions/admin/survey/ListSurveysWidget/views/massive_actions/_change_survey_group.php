<?php
/** @var AdminController $this */
?>

<?php $form = $this->beginWidget('CActiveForm', array('id' => 'survey-group',)); ?>

<div id='change-surveygroup-modal' >
    <label class="form-label" for='surveygroupid'><?php  eT("Survey group:"); ?></label>
    <div class="mb-3">
        <select id='surveygroupid' class="form-select custom-data"  name='surveygroupid' >
            <?php
                $aSurveyGroupList = SurveysGroups::getSurveyGroupsList();
            foreach ($aSurveyGroupList as $iGsid => $sGroupTitle) { ?>
                    <option value='<?=$iGsid?>'>
                        <?php echo CHtml::encode($sGroupTitle); ?>
                    </option>
            <?php } ?>
        </select>
    </div>
    <?= gT('This will update the survey group for all selected surveys.') . ' ' . gT('Continue?'); ?>
</div>
<?php $this->endWidget(); ?>
