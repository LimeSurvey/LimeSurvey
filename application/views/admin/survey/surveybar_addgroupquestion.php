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
        <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
            <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                <i class="ri-add-circle-fill"></i>
                <?php eT("Add new group"); ?>
            </button>
        </span>
    <?php elseif (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'create')): ?>
        <a class="btn btn-outline-secondary" href="<?php echo $this->createUrl("questionGroupsAdministration/add/surveyid/$oSurvey->sid"); ?>" role="button">
            <i class="ri-add-circle-fill"></i>
            <?php eT("Add new group"); ?>
        </a>
        <a class="btn btn-outline-secondary" href="<?php echo $this->createUrl("questionGroupsAdministration/importview/surveyid/$oSurvey->sid"); ?>" role="button">

            <span class="ri-download-2-fill"></span>
            <?php eT("Import a group"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<!-- Add a new question -->
<?php if (isset($surveybar['buttons']['newquestion'])):?>
    <?php if ($oSurvey->isActive): ?>
        <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
            <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                <i class="ri-add-circle-fill"></i>
                <?php eT("Add new question"); ?>
            </button>
        </span>
    <?php elseif (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'create')): ?>
        <?php if (!$surveyHasGroup): ?>
            <span class="btntooltip" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
                <button type="button" class="btn btn-outline-secondary btntooltip" disabled="disabled">
                    <i class="ri-add-circle-fill"></i>
                    <?php eT("Add new question"); ?>
                </button>
            </span>
        <?php else :?>
            <a class="btn btn-outline-secondary" href='<?php echo $this->createUrl("questionAdministration/view/surveyid/".$oSurvey->sid);
    ?>' role="button">
                <i class="ri-add-circle-fill"></i>
                <?php eT("Add new question"); ?>
            </a>
            <a class="btn btn-outline-secondary" href='<?php echo $this->createUrl("questionAdministration/importView/surveyid/".$oSurvey->sid); ?>' role="button">
                <span class="ri-download-2-fill"></span>
                <?php eT("Import a question"); ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
