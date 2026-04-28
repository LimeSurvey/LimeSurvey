<?php

/**
 * This file render the list of groups
 */

/** @var QuestionAdministrationController $this */
/** @var Survey $oSurvey */
/** @var Question $model */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyListQuestions');
$baseLanguage = $oSurvey->language;
?>

<div class='side-body'>
    <h1><?php eT("In this survey"); ?></h1>
    <ul class="nav nav-tabs  mt-4" role="tablist">
        <li id='overviewTab' class="nav-item"><a class="nav-link active" href="#questions" aria-controls="questions" role="tab" data-bs-toggle="tab"><?php eT('Questions'); ?></a></li>
        <li id='overviewTab' class="nav-item"><a class="nav-link" href="#groups" aria-controls="groups" role="tab" data-bs-toggle="tab"><?php eT('Groups'); ?></a></li>
        <li id='overviewTab' class='nav-item'><a class="nav-link" href="#reorder" aria-controls="reorder" role="tab" data-bs-toggle="tab"><?php eT('Reorder'); ?></a></li>
    </ul>
    <div class="tab-content p-4 h-100">
        <div id="questions" class="tab-pane show fade active row">
            <?php $this->renderPartial('partial/questionView', [
                'oSurvey' => $oSurvey,
                'surveybar' => $surveybar,
                'questionModel' => $questionModel,
                'hasSurveyContentCreatePermission' => $hasSurveyContentCreatePermission,
            ]) ?>
        </div>
        <div id="groups" class="tab-pane row">
            <?php $this->renderPartial('partial/groupView',
            [
                'oSurvey' => $oSurvey,
                'groupModel' => $groupModel,
                'surveybar' => $surveybar,
                'hasSurveyContentCreatePermission' => $hasSurveyContentCreatePermission,
            ]
            ) ?>
        </div>
        <div id="reorder" class="tab-pane row">
            <?php $this->renderPartial('/admin/survey/organizeGroupsAndQuestions_view',[
                'surveyid' => $surveyid,
                'surveyActivated' => $surveyActivated,
                'aGroupsAndQuestions' => $aGroupsAndQuestions,
            ]) ?>
        </div>
    </div>
</div>

<div class="modal fade" id="question-preview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel"><?php eT("Question preview"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="frame-question-preview" src="" style="zoom:0.60" width="99.6%" height="600" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php eT("Close"); ?></button>
            </div>
        </div>
    </div>
</div>
