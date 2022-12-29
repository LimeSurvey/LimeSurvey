<?php if ($oSurvey->isActive): ?>
    <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block">
        <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
            <i class="ri-add-circle-fill"></i>
            <?php eT("Add new question"); ?>
        </button>
        <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
            <span class="ri-upload-fill"></span>
            <?php eT("Import a question"); ?>
        </button>
    </span>
<?php elseif ($hasSurveyContentCreatePermission): ?>
    <?php if (!$oSurvey->groups): ?>
        <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block">
            <button type="button" role="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                <i class="ri-add-circle-fill"></i>
                <?php eT("Add new question"); ?>
            </button>
            <button type="button" role="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                <span class="ri-upload-fill"></span>
                <?php eT("Import a question"); ?>
            </button>
        </span>
    <?php else : ?>
        <a class="btn btn-outline-secondary"
           href='<?php echo Yii::App()->createUrl("questionAdministration/create/surveyid/" . $oSurvey->sid); ?>'>
            <i class="ri-add-circle-fill"></i>
            <?php eT("Add new question"); ?>
        </a>
        <a class="btn btn-outline-secondary"
           href='<?php echo Yii::App()->createUrl("questionAdministration/importView/surveyid/" . $oSurvey->sid); ?>'>
            <span class="ri-upload-fill"></span>
            <?php eT("Import a question"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>
