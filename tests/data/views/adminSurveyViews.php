<?php

/**
 * This contains a list of survey-related admin views that we can loop for testing
 * // TODO not complete views list
 */

return [
    // Survey general stuff -------------------------------------
    // --------------------------------------------------

    // NB the import_id is needed only for the first item OR if you need to change the survey you want to work with
    ['surveySummary', ['route' => 'surveyAdministration/view/surveyid/{SID}','import_id' => '454287', 'noAdminInFront' => true]],

    // Survey main menu
    ['surveyGeneralSettings', ['route' => 'surveyAdministration/rendersidemenulink/subaction/generalsettings/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyTexts', ['route' => 'surveyAdministration/rendersidemenulink/subaction/surveytexts/surveyid/{SID}', 'noAdminInFront' => true ]],
    ['surveyTemplateOptionsUpdate', ['route' => 'themeOptions/updateSurvey/sid/{SID}/gsid/1', 'noAdminInFront' => true]],
    ['surveyPresentationOptions', ['route' => 'surveyAdministration/rendersidemenulink/subaction/presentation/surveyid/{SID}', 'noAdminInFront' => true]],

    ['surveyResources', ['route' => 'surveyAdministration/rendersidemenulink/subaction/resources/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyPermissions', ['route' => 'surveyPermissions/index/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyParticipantTokenOptions', ['route' => 'surveyAdministration/rendersidemenulink/subaction/tokens/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyQuotas', ['route' => 'quotas/index/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyAssessments', ['route' => 'assessment/index/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyNotificationOptions', ['route' => 'surveyAdministration/rendersidemenulink/subaction/notification/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyPublicationOptions', ['route' => 'surveyAdministration/rendersidemenulink/subaction/publication/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyEmailTemplates', ['route' => 'emailtemplates/sa/index/surveyid/{SID}']],
    ['surveyFailedEmail', ['route' => 'failedEmail/index/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyPanelIntegration', ['route' => 'surveyAdministration/rendersidemenulink/subaction/panelintegration/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyPlugins', ['route' => 'surveyAdministration/rendersidemenulink/subaction/plugins/surveyid/{SID}', 'noAdminInFront' => true]],
    ['surveyListQuestions', ['route' => 'questionAdministration/listquestions/surveyid/{SID}', 'noAdminInFront' => true]],

    // going deeper -------------------------------------
    // --------------------------------------------------

    // adding elements to survey
    ['addQuestion', ['route' => 'questionAdministration/create/surveyid/{SID}', 'noAdminInFront' => true]],
    ['addQuestionGroup', ['route' => 'questionGroupsAdministration/add/surveyid/{SID}', 'noAdminInFront' => true]],
    ['importQuestionGroup', ['route' => 'questionGroupsAdministration/importview/surveyid/{SID}', 'noAdminInFront' => true]],
    ['addQuota', ['route' => 'quotas/addNewQuota/surveyid/{SID}', 'noAdminInFront' => true]],

    ['surveyLogicFile', ['route' => 'expressions/sa/survey_logic_file/sid/{SID}']],

    // open surveysummary again with new survey (triggers some needed session variables duh)
    ['surveySummary', ['route' => 'surveyAdministration/view/surveyid/{SID}','import_id' => '496242', 'noAdminInFront' => true]],
    ['printableSurvey', ['route' => 'printablesurvey/sa/index/surveyid/{SID}']],

    // Below are views need an activated survey
    ['surveySummary', ['route' => 'surveyAdministration/view/surveyid/{SID}','import_id' => '454287', 'activate' => true, 'noAdminInFront' => true]],
    ['dataEntryView', ['route' => 'dataentry/sa/view/surveyid/{SID}']],
    ['statisticsIndex', ['route' => 'statistics/sa/index/surveyid/{SID}']],
    ['exportResults', ['route' => 'export/sa/exportresults/surveyid/{SID}']],
    ['exportSpss', ['route' => 'export/sa/exportspss/sid/{SID}']],
    ['surveyResponsesBrowse', ['route' => 'responses/browse/surveyId/{SID}', 'noAdminInFront' => true]],
    ['surveyParticipantsIndex', ['route' => 'tokens/sa/index/surveyid/{SID}']],
];
