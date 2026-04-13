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
