<?php if($hasSurveyContentUpdatePermission):?>
    <!-- Conditions -->
    <li>
        <a class="dropdown-item" id="conditions_button" href="<?php echo Yii::App()->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
            <span class="ri-git-branch-fill icon"></span>
            <?php eT("Condition designer"); ?>
        </a>
    </li>
<?php endif;?>

<?php if($hasSurveyContentUpdatePermission && $hasdefaultvalues > 0):?>
    <!-- Default Values -->
    <li>
        <a class="dropdown-item" id="default_value_button" href="<?php echo Yii::App()->createUrl("questionAdministration/editdefaultvalues/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
            <span class="ri-grid-line"></span>
            <?php eT("Edit default answers"); ?>
        </a>
    </li>
<?php endif;?>

<?php if($hasSurveyContentExportPermission):?>
    <!-- Export -->
    <li>
        <a class="dropdown-item" href="<?php echo Yii::App()->createUrl("admin/export/sa/question/surveyid/$surveyid/gid/$gid/qid/{$qid}");?>">
            <span class="ri-download-fill"></span>
            <?php eT("Export"); ?>
        </a>
    </li>
<?php endif; ?>

<?php if($hasSurveyContentCreatePermission && ($oSurvey->active!='Y')):?>
    <!-- Copy -->
    <li>
        <a class="dropdown-item" id="copy_button" href='<?php echo Yii::App()->createUrl("questionAdministration/copyQuestion/surveyId/{$oQuestion->sid}/questionGroupId/{$oQuestion->gid}/questionId/{$oQuestion->qid}");?>'>
            <span class="ri-file-copy-line icon"></span>
            <?php eT("Copy"); ?>
        </a>
    </li>
<?php endif; ?>

<?php if($hasSurveyContentReadPermission): ?>
    <?php if (count($surveyLanguages) > 1): ?>
        <!-- Check survey logic multilanguage -->
        <li role="separator" class="dropdown-divider"></li>
        <li class="dropdown-header"><?php eT("Check logic"); ?></li>
        <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
            <li>
                <a class="dropdown-item" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/lang/" . $languageCode); ?>" >
                    <span class="ri-checkbox-fill"></span>
                    <?php echo $languageName; ?>
                </a>
            </li>
        <?php endforeach; ?>
    <?php else:?>
        <!-- Check survey logic -->
        <li>
            <a class="pjax dropdown-item" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}"); ?>">
                <span class="ri-checkbox-fill"></span>
                <?php eT("Check logic"); ?>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($showDeleteButton)) : ?>
    <!-- Delete -->
    <?php if ($oSurvey->active !== 'Y') : ?>
        <li>
            <a href="#" onclick="return false;"
                class="dropdown-item"
                data-bs-toggle="modal"
                data-bs-target="#confirmation-modal"
                data-btnclass="btn-danger"
                data-title="<?= gt('Delete this question') ?>"
                data-btntext="<?= gt('Delete') ?>"
                data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("questionAdministration/delete/", ["qid" => $qid, "redirectTo" => "groupoverview"])); ?>})'
                data-message="<?php eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?", "js"); ?>"
            >
                <span class="ri-delete-bin-fill text-danger"></span>
                <?php eT("Delete question"); ?>
            </a>
        </li>
    <?php else : ?>
        <li class="disabled">
            <a class="btntooltip dropdown-item" disabled data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("You can't delete a question if the survey is active."); ?>">
                <span class="ri-delete-bin-fill text-danger"></span>
                <?php eT("Delete question"); ?>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
