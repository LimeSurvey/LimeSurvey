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
?>

