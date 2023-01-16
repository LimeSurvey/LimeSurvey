<?php
    // Tools dropdown button
    $toolsDropdownItems = $this->render('includes/questionToolsDropdownItems', get_defined_vars(), true);
?>

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

