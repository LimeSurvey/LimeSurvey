<?php
    // Tools dropdown button
    $toolsDropdownItems = $this->render('includes/questionToolsDropdownItems', get_defined_vars(), true);
?>
<?php if (!empty(trim($toolsDropdownItems))): ?>
    <!-- Tools  -->
    <div class="d-inline-flex ">
        <!-- Main button dropdown -->
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => 'ls-question-tools-button',
            'id' => 'ls-question-tools-button',
            'text' => gT('Tools'),
            'isDropDown' => true,
            'dropDownContent' => '<ul class="dropdown-menu">' . $toolsDropdownItems . '</ul>',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]); ?>
    </div>
<?php endif; ?>

<?php
/**
 * Include the Survey Preview and Group Preview buttons
 */
$this->render(
    'includes/previewOrRunButton_view',
    [
        'survey' => $oSurvey,
        'surveyLanguages' => $surveyLanguages,
    ]
);
$this->render('includes/previewGroupButton_view', get_defined_vars());
$this->render('includes/previewQuestionButton_view', get_defined_vars());
?>

<<<<<<< HEAD
<?php if($hasSurveyContentUpdatePermission): ?>
    <?php if (count($surveyLanguages) > 1): ?>
        <!-- Preview question multilanguage -->
        <div class="btn-group">
            <button type="button" role="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="ri-eye-fill"></span>
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
        <a class="btn btn-outline-secondary" href="<?php echo Yii::App()->createUrl("survey/index/action/previewquestion/sid/$surveyid/gid/$gid/qid/$qid"); ?>" target="_blank">
            <span class="ri-eye-fill"></span>
            <?php eT("Preview question");?>
        </a>
    <?php endif; ?>
<?php endif; ?>
=======

>>>>>>> 53797b7d487856da3dd8485a5d5054fa268217d9
