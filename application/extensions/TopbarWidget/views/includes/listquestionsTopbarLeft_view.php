<?php if ($oSurvey->isActive): ?>
    <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block">
        <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
            <span class="icon-add"></span>
            <?php eT("Add new question"); ?>
        </button>
        <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
            <span class="icon-import"></span>
            <?php eT("Import a question"); ?>
        </button>
    </span>
<?php elseif ($hasSurveyContentCreatePermission): ?>
    <?php if (!$oSurvey->groups): ?>
        <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block">
            <button type="button" role="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                <span class="icon-add"></span>
                <?php eT("Add new question"); ?>
            </button>
            <button type="button" role="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                <span class="icon-import"></span>
                <?php eT("Import a question"); ?>
            </button>
        </span>
    <?php else :?>
        <button class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("questionAdministration/create/surveyid/".$oSurvey->sid);
?>' type="button" role="button">
            <span class="icon-add"></span>
            <?php eT("Add new question"); ?>
        </button>
        <button class="btn btn-outline-secondary" href='<?php echo Yii::App()->createUrl("questionAdministration/importView/surveyid/".$oSurvey->sid); ?>' role="button">
            <span class="icon-import"></span>
            <?php eT("Import a question"); ?>
        </button>
    <?php endif; ?>
<?php endif; ?>
