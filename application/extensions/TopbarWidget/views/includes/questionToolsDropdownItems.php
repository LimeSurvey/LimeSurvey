<?php if($hasSurveyContentUpdatePermission):?>
    <!-- Conditions -->
    <li>
        <a id="conditions_button" href="<?php echo Yii::App()->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
            <span class="icon-conditions icon"></span>
            <?php eT("Condition designer"); ?>
        </a>
    </li>
<?php endif;?>

<?php if($hasSurveyContentUpdatePermission && $hasdefaultvalues > 0):?>
    <!-- Default Values -->
    <li>
        <a id="default_value_button" href="<?php echo Yii::App()->createUrl("questionAdministration/editdefaultvalues/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
            <span class="icon-defaultanswers icon"></span>
            <?php eT("Edit default answers"); ?>
        </a>
    </li>
<?php endif;?>

<?php if($hasSurveyContentExportPermission):?>
    <!-- Export -->
    <li>
        <a href="<?php echo Yii::App()->createUrl("admin/export/sa/question/surveyid/$surveyid/gid/$gid/qid/{$qid}");?>">
            <span class="icon-export"></span>
            <?php eT("Export"); ?>
        </a>
    </li>
<?php endif; ?>

<?php if($hasSurveyContentCreatePermission && ($oSurvey->active!='Y')):?>
    <!-- Copy -->
    <li>
        <a id="copy_button" href='<?php echo Yii::App()->createUrl("questionAdministration/copyQuestion/surveyId/{$oQuestion->sid}/questionGroupId/{$oQuestion->gid}/questionId/{$oQuestion->qid}");?>'>
            <span class="icon-copy icon"></span>
            <?php eT("Copy"); ?>
        </a>
    </li>
<?php endif; ?>

<?php if($hasSurveyContentReadPermission): ?>
    <?php if (count($surveyLanguages) > 1): ?>
        <!-- Check survey logic multilanguage -->
        <li role="separator" class="divider"></li>
        <li class="dropdown-header"><?php eT("Survey logic file"); ?></li>
        <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
            <li>
                <a href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/lang/" . $languageCode); ?>" >
                    <span class="icon-expressionmanagercheck"></span>
                    <?php echo $languageName; ?>
                </a>
            </li>
        <?php endforeach; ?>
    <?php else:?>
        <!-- Check survey logic -->
        <li>
            <a class="pjax" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}"); ?>">
                <span class="icon-expressionmanagercheck"></span>
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
                data-toggle="modal"
                data-target="#confirmation-modal"
                data-btnclass="btn-danger"
                data-title="<?= gt('Delete this question') ?>"
                data-btntext="<?= gt('Delete') ?>"
                data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("questionAdministration/delete/", ["qid" => $qid, "redirectTo" => "groupoverview"])); ?>})'
                data-message="<?php eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?", "js"); ?>"
            >
                <span class="fa fa-trash text-danger"></span>
                <?php eT("Delete question"); ?>
            </a>
        </li>
    <?php else : ?>
        <li class="disabled">
            <a class="btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can't delete a question if the survey is active."); ?>">
                <span class="fa fa-trash text-danger"></span>
                <?php eT("Delete question"); ?>
            </a>
        </li>
    <?php endif; ?>
<?php endif; ?>
