<?php

namespace LimeSurvey\Models\Services;

use App;
use CDbCriteria;
use Survey;
use SurveyLanguageSetting;
use TemplateConfiguration;

/**
 * Service class to fix integrity on a single survey
 * @version 0.1.0
 */
class SurveyIntegrity
{
    /** @var Survey */
    private $survey;

    /**
     * @param Survey $survey
     */
    public function __construct(
        Survey $survey
    ) {
        $this->survey = $survey;
    }
    /**
     * Add needed language if needed in related SurveyLanguageSetting
     * Remove uneeded language if needed in related SurveyLanguageSetting
     * @return void
     */
    public function fixSurveyLanguageSetting()
    {
        /* Check if SurveyLanguageSetting exist, create if not */
        foreach ($this->survey->additionalLanguages as $sLang) {
            if ($sLang) {
                $oLanguageSettings = SurveyLanguageSetting::model()->find(
                    'surveyls_survey_id=:surveyid AND surveyls_language=:langname',
                    array(':surveyid' => $this->survey->sid, ':langname' => $sLang)
                );
                if (!$oLanguageSettings) {
                    $oLanguageSettings = new SurveyLanguageSetting();
                    $languagedetails = getLanguageDetails($sLang);
                    $oLanguageSettings->surveyls_survey_id = $this->survey->sid;
                    $oLanguageSettings->surveyls_language = $sLang;
                    $oLanguageSettings->surveyls_title = ''; // Not in default model ?
                    $oLanguageSettings->surveyls_dateformat = $languagedetails['dateformat'];
                    if (!$oLanguageSettings->save()) {
                        App()->setFlashMessage(sprintf(gT("Survey language %s could not be created."), $sLang), "error");
                    }
                }
            }
        }
        /* Delete all unneeded language setting */
        $aAvailableLanguage = $this->survey->getAllLanguages();
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('surveyls_survey_id', $this->survey->sid);
        $oCriteria->addNotInCondition('surveyls_language', $aAvailableLanguage);
        SurveyLanguageSetting::model()->deleteAll($oCriteria);
    }

    /**
     * Function to find and fix potential issue inside current survey, mpore fix to be added
     * - fixes missing groups, questions, answers, quotas & assessments for languages on a survey
     * - Remove invalid question in this survey : exist in another la,guage but not in primary
     * @return void
     */
    public function fixSurveyIntegrity()
    {
        /* Fix current related language database */
        $this->fixSurveyLanguageSetting();
        /* Common helper function : missing groups, questions, answers, quotas & assessments for languages on a survey*/
        fixLanguageConsistency($this->survey->sid, $this->survey->additional_languages);
        /* Invalid question and subquestions */
        $this->survey->fixInvalidQuestions();
        /* Create an empty theme if needed */
        TemplateConfiguration::checkAndcreateSurveyConfig($this->survey->sid);
    }
}
