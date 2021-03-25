<?php if ($oSurvey->isActive): ?>
    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block">
        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
            <span class="icon-add"></span>
            <?php eT("Add new question"); ?>
        </button>
        <button type="button" class="btn btn-default btntooltip" disabled="disabled">
            <span class="icon-import"></span>
            <?php eT("Import a question"); ?>
        </button>
    </span>
<?php elseif ($hasSurveyContentCreatePermission): ?>
    <?php if (!$oSurvey->groups): ?>
        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block">
            <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                <span class="icon-add"></span>
                <?php eT("Add new question"); ?>
            </button>
            <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                <span class="icon-import"></span>
                <?php eT("Import a question"); ?>
            </button>
        </span>
    <?php else :?>
        <a class="btn btn-default" href='<?php echo Yii::App()->createUrl("questionAdministration/create/surveyid/".$oSurvey->sid);
?>' role="button">
            <span class="icon-add"></span>
            <?php eT("Add new question"); ?>
        </a>
        <a class="btn btn-default" href='<?php echo Yii::App()->createUrl("questionAdministration/importView/surveyid/".$oSurvey->sid); ?>' role="button">
            <span class="icon-import"></span>
            <?php eT("Import a question"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>