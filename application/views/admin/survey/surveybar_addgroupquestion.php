<?php

/**
 * Subview of surveybar_view.
 * @param $surveybar
 * @param $oSurvey
 * @param $surveyHasGroup
 */

?>

<!-- Add a new group -->
<?php if (isset($surveybar['buttons']['newgroup'])):?>
    <?php if ($oSurvey->isActive): ?>
        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
            <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                <span class="icon-add"></span>
                <?php eT("Add new group"); ?>
            </button>
        </span>
    <?php elseif (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'create')): ?>
        <a class="btn btn-default" href="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/$oSurvey->sid"); ?>" role="button">
            <span class="icon-add"></span>
            <?php eT("Add new group"); ?>
        </a>
        <a class="btn btn-default" href="<?php echo $this->createUrl("admin/questiongroups/sa/importview/surveyid/$oSurvey->sid"); ?>" role="button">

            <span class="icon-import"></span>
            <?php eT("Import a group"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<!-- Add a new question -->
<?php if (isset($surveybar['buttons']['newquestion'])):?>
    <?php if ($oSurvey->isActive): ?>
        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
            <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                <span class="icon-add"></span>
                <?php eT("Add new question"); ?>
            </button>
        </span>
    <?php elseif (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'create')): ?>
        <?php if (!$surveyHasGroup): ?>
            <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
                <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                    <span class="icon-add"></span>
                    <?php eT("Add new question"); ?>
                </button>
            </span>
        <?php else :?>
            <a class="btn btn-default" href='<?php echo $this->createUrl("admin/questions/sa/newquestion/surveyid/".$oSurvey->sid);
    ?>' role="button">
                <span class="icon-add"></span>
                <?php eT("Add new question"); ?>
            </a>
            <a class="btn btn-default" href='<?php echo $this->createUrl("admin/questions/sa/importview/surveyid/".$oSurvey->sid); ?>' role="button">
                <span class="icon-import"></span>
                <?php eT("Import a question"); ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
