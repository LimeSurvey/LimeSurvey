<?php if ($oSurvey->isActive): ?>
    <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block">
        <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
            <span class="icon-add"></span>
            <?php eT("Add new group"); ?>
        </button>
        <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
            <span class="icon-import"></span>
            <?php eT("Import group"); ?>
        </button>
    </span>
<?php elseif ($hasSurveyContentCreatePermission): ?>
    <!-- Add group -->
    <a class="btn btn-outline-secondary" href="<?php echo Yii::App()->createUrl("questionGroupsAdministration/add/surveyid/".$surveyid); ?>">
        <span class="icon-add"></span>
        <?php eT('Add new group'); ?>
    </a>
    <!-- Import -->
    <a class="btn btn-outline-secondary" href="<?php echo Yii::App()->createUrl("questionGroupsAdministration/importview/surveyid/".$surveyid); ?>">
        <span class="icon-import"></span>
        <?php eT('Import group'); ?>
    </a>
<?php endif; ?>
