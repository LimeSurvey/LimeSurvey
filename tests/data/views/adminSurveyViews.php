<?php
/**
 * This contains a list of survey-related admin views that we can loop for testing
 * // TODO not complete views list
 */
return [
    // Survey general stuff -------------------------------------
    // --------------------------------------------------

    // NB the import_id is needed only for the first item OR if you need to change the survey you want to work with
    ['surveySummary', ['route'=>'survey/sa/view/surveyid/{SID}','import_id'=>'454287']],

    // Survey main menu
    ['surveyGeneralSettings', ['route'=>'survey/sa/rendersidemenulink/subaction/generalsettings/surveyid/{SID}']],
    ['surveyTexts', ['route'=>'survey/sa/rendersidemenulink/subaction/surveytexts/surveyid/{SID}']],
    ['surveyTemplateOptionsUpdate', ['route'=>'themeoptions/sa/updatesurvey/surveyid/{SID}/gsid/1']],
    ['surveyPresentationOptions', ['route'=>'survey/sa/rendersidemenulink/subaction/presentation/surveyid/{SID}']],

    ['surveyResources', ['route'=>'filemanager/surveyid/{SID}']],
    ['surveyPermissions', ['route'=>'surveypermission/sa/view/surveyid/{SID}']],
    ['surveyParticipantTokenOptions', ['route'=>'survey/sa/rendersidemenulink/subaction/tokens/surveyid/{SID}']],
    ['surveyQuotas', ['route'=>'quotas/sa/index/surveyid/{SID}']],
    ['surveyAssessments', ['route'=>'assessments/sa/index/surveyid/{SID}']],
    ['surveyNotificationOptions', ['route'=>'survey/sa/rendersidemenulink/subaction/notification/surveyid/{SID}']],
    ['surveyPublicationOptions', ['route'=>'survey/sa/rendersidemenulink/subaction/publication/surveyid/{SID}']],
    ['surveyEmailTemplates', ['route'=>'emailtemplates/sa/index/surveyid/{SID}']],
    ['surveyPanelIntegration', ['route'=>'survey/sa/rendersidemenulink/subaction/panelintegration/surveyid/{SID}']],
    ['surveyPlugins', ['route'=>'survey/sa/rendersidemenulink/subaction/plugins/surveyid/{SID}']],
    ['surveyListQuestions', ['route'=>'survey/sa/listquestions/surveyid/{SID}']],

    // going deeper -------------------------------------
    // --------------------------------------------------

    // adding elements to survey
    ['addQuestion', ['route'=>'questions/sa/newquestion/surveyid/{SID}']],
    ['addQuestionGroup', ['route'=>'questiongroups/sa/add/surveyid/{SID}']],
    ['importQuestionGroup', ['route'=>'questiongroups/sa/importview/surveyid/{SID}']],
    ['addQuota', ['route'=>'quotas/sa/newquota/surveyid/{SID}']],

    ['surveyLogicFile', ['route'=>'expressions/sa/survey_logic_file/sid/{SID}']],

    // open surveysummary again with new survey (triggers some needed session variables duh)
    ['surveySummary', ['route'=>'survey/sa/view/surveyid/{SID}','import_id'=>'496242']],
    ['printableSurvey', ['route'=>'printablesurvey/sa/index/surveyid/{SID}']],

    // Below are views need an activated survey
    ['surveySummary', ['route'=>'survey/sa/view/surveyid/{SID}','import_id'=>'454287', 'activate'=>true]],
    ['dataEntryView', ['route'=>'dataentry/sa/view/surveyid/{SID}']],
    ['statisticsIndex', ['route'=>'statistics/sa/index/surveyid/{SID}']],
    ['exportResults', ['route'=>'export/sa/exportresults/surveyid/{SID}']],
    ['exportSpss', ['route'=>'export/sa/exportspss/sid/{SID}']],
    ['surveyResponsesIndex', ['route'=>'responses/sa/index/surveyid/{SID}']],
    ['surveyResponsesBrowse', ['route'=>'responses/sa/browse/surveyid/{SID}']],
    ['surveyParticipantsIndex', ['route'=>'tokens/sa/index/surveyid/{SID}']],
];
