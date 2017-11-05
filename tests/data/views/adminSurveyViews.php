<?php
/**
 * This contains a list of survey-related admin views that we can loop for testing
 * // TODO not complete views list
 */

return [
    // NB the import_id is needed only for the first item OR if you need to change the survey you want to work with
    'surveySummary' =>['route'=>'survey/sa/view/surveyid/{SID}','import_id'=>'454287'],
    'surveyGeneralSettings' =>['route'=>'survey/sa/rendersidemenulink/subaction/generalsettings/surveyid/{SID}'],
];