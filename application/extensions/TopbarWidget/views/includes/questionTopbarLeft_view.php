<?php
/**
 * Include the Survey Preview and Group Preview buttons
 */
$this->render('includes/previewSurveyAndGroupButtons_view', get_defined_vars());
?>

<?php if($hasSurveyContentUpdatePermission): ?>
    <?php if (count($surveyLanguages) > 1): ?>
        <!-- Preview question multilanguage -->
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="icon-do"></span>
            <?php eT("Preview question"); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                    <li>
                        <a target="_blank" href="<?php echo Yii::App()->createUrl("survey/index/action/previewquestion/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/lang/{$languageCode}"); ?>" >
                            <?php echo $languageName; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else:?>
        <!-- Preview question single language -->
        <a class="btn btn-default" href="<?php echo Yii::App()->createUrl("survey/index/action/previewquestion/sid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button" target="_blank">
            <span class="icon-do"></span>
            <?php eT("Preview question");?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<!-- Edit button -->
<?php if($hasSurveyContentUpdatePermission): ?>
    <a id="questionEditorButton" class="btn btn-success pjax" href="#" role="button" onclick="LS.questionEditor.showEditor(); return false;">
        <span class="icon-edit"></span>
        <?php eT("Edit");?>
    </a>
<?php endif; ?>

<!-- Tools  -->
<div class="btn-group hidden-xs">

    <!-- Main button dropdown -->
    <button id="ls-question-tools-button" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="icon-tools" ></span>
        <?php eT('Tools'); ?>&nbsp;<span class="caret"></span>
    </button>

    <!-- dropdown -->
    <ul class="dropdown-menu">
        <!-- conditions -->
        <?php if($hasSurveyContentUpdatePermission):?>
            <li>
                <a id="conditions_button" href="<?php echo Yii::App()->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
                    <span class="icon-conditions icon"></span>
                    <?php eT("Condition designer"); ?>
                </a>
            </li>
        <?php endif;?>

        <!-- Default Values -->
        <?php if($hasSurveyContentUpdatePermission && $hasdefaultvalues > 0):?>
            <li>
                <a id="default_value_button" href="<?php echo Yii::App()->createUrl("questionAdministration/editdefaultvalues/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
                    <span class="icon-defaultanswers icon"></span>
                    <?php eT("Edit default answers"); ?>
                </a>
            </li>
        <?php endif;?>

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

    </ul>
</div>

<!-- Export -->
<?php if($hasSurveyContentExportPermission):?>
    <a class="btn btn-default " href="<?php echo Yii::App()->createUrl("admin/export/sa/question/surveyid/$surveyid/gid/$gid/qid/{$qid}");?>" role="button">
        <span class="icon-export"></span>
        <?php eT("Export"); ?>
    </a>
<?php endif; ?>

<!-- Copy -->
<?php if($hasSurveyContentCreatePermission && ($oSurvey->active!='Y')):?>
    <a class="btn btn-default" id="copy_button" href='<?php echo Yii::App()->createUrl("questionAdministration/copyQuestion/surveyId/{$oQuestion->sid}/questionGroupId/{$oQuestion->gid}/questionId/{$oQuestion->qid}");?>'>
        <span class="icon-copy icon"></span>
        <?php eT("Copy"); ?>
    </a>
<?php endif; ?>

<!-- Delete -->
<?php if($hasSurveyContentDeletePermission):?>
    <?php if($oSurvey->active!='Y'): ?>
        <button class="btn btn-danger"
                data-toggle="modal"
                data-target="#confirmation-modal"
                data-btnclass="btn-danger"
                data-btntext="<?= gt('Delete')?>"
                data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("questionAdministration/delete/", ["qid" => $qid, "redirectTo" => "groupoverview"])); ?>})'
                data-message="<?php eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js"); ?>"
        >
            <span class="fa fa-trash text-danger"></span>
            <?php eT("Delete"); ?>
        </button>
    <?php else: ?>
        <button class="btn btn-danger btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can't delete a question if the survey is active."); ?>">
            <span class="fa fa-trash text-danger"></span>
            <?php eT("Delete"); ?>
        </button>
    <?php endif; ?>
<?php endif; ?>
