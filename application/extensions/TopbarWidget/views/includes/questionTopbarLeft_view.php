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
                        <a target="_blank" href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/lang/{$languageCode}"); ?>" >
                            <?php echo $languageName; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else:?>
        <!-- Preview question single language -->
        <a class="btn btn-default" href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button" target="_blank">
            <span class="icon-do"></span>
            <?php eT("Preview question");?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<?php if($hasSurveyContentReadPermission): ?>
    <?php if (count($surveyLanguages) > 1): ?>
        <!-- Check survey logic multilanguage -->
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="icon-expressionmanagercheck"></span>
            <?php eT("Check logic"); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                    <li>
                        <a href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/lang/" . $languageCode); ?>" >
                            <?php echo $languageName; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else:?>
        <!-- Check survey logic -->
        <a class="btn btn-default pjax" href="<?php echo Yii::App()->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}"); ?>" role="button">
            <span class="icon-expressionmanagercheck"></span>
            <?php eT("Check logic"); ?>
        </a>
    <?php endif; ?>
<?php endif; ?>

<!-- Delete -->
<?php if($hasSurveyContentDeletePermission):?>
    <?php if($oSurvey->active!='Y'): ?>
        <button class="btn btn-danger"
            data-toggle="modal"
            data-target="#confirmation-modal"
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

<!-- Export -->
<?php if($hasSurveyContentExportPermission):?>
    <a class="btn btn-default " href="<?php echo Yii::App()->createUrl("admin/export/sa/question/surveyid/$surveyid/gid/$gid/qid/{$qid}");?>" role="button">
        <span class="icon-export"></span>
        <?php eT("Export"); ?>
    </a>
<?php endif; ?>

<!-- Copy -->
<?php if($hasSurveyContentCreatePermission):?>
    <a class="btn btn-default" id="copy_button" href='<?php echo Yii::App()->createUrl("questionAdministration/copyQuestion/surveyId/{$oQuestion->sid}/questionGroupId/{$oQuestion->gid}/questionId/{$oQuestion->qid}");?>'>
        <span class="icon-copy icon"></span>
        <?php eT("Copy"); ?>
    </a>
<?php endif; ?>

<!-- conditions -->
<?php if($hasSurveyContentUpdatePermission):?>
    <a class="btn btn-default" id="conditions_button" href="<?php echo Yii::App()->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button">
        <span class="icon-conditions icon"></span>
        <?php eT("Condition designer"); ?>
    </a>
<?php endif;?>

<!-- Default Values -->
<?php if($hasSurveyContentUpdatePermission && $hasdefaultvalues > 0):?>
    <a class="btn btn-default" id="default_value_button" href="<?php echo Yii::App()->createUrl("questionAdministration/editdefaultvalues/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button">
        <span class="icon-defaultanswers icon"></span>
        <?php eT("Edit default answers"); ?>
    </a>
<?php endif;?>

<!-- Import -->
<?php if($hasSurveyContentCreatePermission):?>
    <?php if($oSurvey->active!='Y'): ?>
        <a class="btn btn-default" id="import-button" href="<?php echo Yii::App()->createUrl("questionAdministration/importView/surveyid/$surveyid"); ?>" role="button">
            <span class="icon-import icon"></span>
            <?php eT("Import question"); ?>
        </a>
    <?php else: ?>
        <button class="btn btn-default btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can not import questions because the survey is currently active."); ?>">
            <span class="icon-import icon"></span>
            <?php eT("Import question"); ?>
        </button>
    <?php endif; ?>
<?php endif;?>