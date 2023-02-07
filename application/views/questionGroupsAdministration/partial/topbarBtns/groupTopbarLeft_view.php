<?php
    // Tools dropdown button
    $toolsDropdownItems = $this->renderPartial('partial/topbarBtns/groupToolsDropdownItems', get_defined_vars(), true);
?>
<?php if (!empty(trim($toolsDropdownItems))): ?>
    <!-- Tools  groupTopbarLeft-->

    <!-- Main button dropdown -->
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-tools-button',
        'id' => 'ls-tools-button',
        'text' => gT('Tools'),
        'isDropDown' => true,
        'dropDownContent' => '<ul class="dropdown-menu">' . $toolsDropdownItems . '</ul>',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]); ?>
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
$this->renderPartial('partial/topbarBtns/previewGroupButton_view', get_defined_vars());
?>
