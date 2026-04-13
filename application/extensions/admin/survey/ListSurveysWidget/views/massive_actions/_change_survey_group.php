<?php
/** @var AdminController $this */
?>

<?php $form = $this->beginWidget('CActiveForm', array('id'=>'survey-group',)); ?>

<div id='change-surveygroup-modal' >
    <label class="form-label" for='surveygroupid'><?php  eT("Survey group:"); ?></label>
    <div class="mb-3">
        <select id='surveygroupid' class="form-select custom-data"  name='surveygroupid' >
            <?php
                $aSurveyGroupList = SurveysGroups::model()->findAll();
                foreach ($aSurveyGroupList as $oSurveyGroup) { ?>
                    <option value='<?=$oSurveyGroup->gsid?>'>
                        <?php echo $oSurveyGroup->name; ?>
                    </option>
                <?php } ?>
        </select>
    </div>
    <?= gT('This will update the survey group for all selected surveys.').' '.gT('Continue?'); ?>
</div>
<?php $this->endWidget(); ?>
