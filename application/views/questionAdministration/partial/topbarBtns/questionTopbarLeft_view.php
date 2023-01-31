<?php
    // Tools dropdown button
    $toolsDropdownItems = $this->renderPartial(
        '/questionAdministration/partial/topbarBtns/questionToolsDropdownItems',
        get_defined_vars(),
        true
    );
 if (!empty(trim($toolsDropdownItems))): ?>
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
$this->renderPartial(
    '/surveyAdministration/partial/topbar/previewOrRunButton_view',
    [
        'survey' => $oSurvey,
        'surveyLanguages' => $surveyLanguages,
    ]
);
$this->renderPartial('/questionGroupsAdministration/partial/topbarBtns/previewGroupButton_view', get_defined_vars());
$this->renderPartial('partial/topbarBtns/previewQuestionButton_view', get_defined_vars());
?>

