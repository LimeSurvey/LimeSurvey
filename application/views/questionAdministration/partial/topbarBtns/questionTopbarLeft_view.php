<?php if ($showToolsMenu) : ?>
    <?php $toolsDropDownItems = $this->renderPartial(
        '/questionAdministration/partial/topbarBtns/questionToolsDropdownMenu',
        get_defined_vars(),
        true
    ); ?>
    <!-- Tools  -->
    <!-- Main button dropdown -->
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-question-view-button',
        'id' => 'ls-question-view-button',
        'text' => gT('Tools'),
        'isDropDown' => true,
        'dropDownContent' => $toolsDropDownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]); ?>
<?php endif;?>

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

