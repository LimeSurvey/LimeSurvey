<?php if ($showToolsMenu) {
    $toolsDropDownItems = $this->renderPartial(
        '/surveyAdministration/partial/topbar/surveyToolsDropdownItems',
        get_defined_vars(),
        true
    ); ?>
    <!-- Tools  -->
    <!-- Main button dropdown -->
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-tools-button',
        'id' => 'ls-tools-button',
        'text' => gT('Tools'),
        'isDropDown' => true,
        'dropDownContent' => $toolsDropDownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]); ?>
<?php } ?>

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
