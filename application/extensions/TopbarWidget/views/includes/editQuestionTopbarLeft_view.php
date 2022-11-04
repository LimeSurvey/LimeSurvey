<?php if ($oQuestion->qid !== 0): ?>
<?php ?>
    <!-- test/execute survey -->
    <?php
    $this->render(
    'includes/previewOrRunButton_view',
    [
    'survey' => $oSurvey,
    'surveyLanguages' => $surveyLanguages,
    ]
    );
    ?>

    <?php if($hasSurveyContentUpdatePermission): ?>
        <?php
        $this->render('includes/previewGroupButton_view', get_defined_vars());
        $this->render('includes/previewQuestionButton_view', get_defined_vars());
        ?>
    <?php endif; ?>
<?php else: ?>
    <!-- Import -->
    <?php if($hasSurveyContentCreatePermission):?>
        <?php if($oSurvey->active!='Y'): ?>
            <a class="btn btn-outline-secondary" id="import-button" href="<?php echo Yii::App()->createUrl("questionAdministration/importView", ["surveyid" => $surveyid, "groupid" => $gid]); ?>" role="button">
                <span class="ri-upload-fill icon"></span>
                <?php eT("Import question"); ?>
            </a>
        <?php else: ?>
            <a role="button" class="btn btn-outline-secondary btntooltip" disabled data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php eT("You can not import questions because the survey is currently active."); ?>">
                <span class="ri-upload-fill icon"></span>
                <?php eT("Import question"); ?>
            </a>
        <?php endif; ?>
    <?php endif;?>
<?php endif; ?>

