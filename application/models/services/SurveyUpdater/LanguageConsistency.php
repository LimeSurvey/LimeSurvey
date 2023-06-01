<?php

namespace LimeSurvey\Models\Services\SurveyUpdater;

use Survey;
use LSYii_Application;
use SurveyLanguageSetting;
use CDbCriteria;

/**
 * Survey Updater Service language consistency
 *
 * Dependencies are injected to enable mocking.
 */
class LanguageConsistency
{
    private ?SurveyLanguageSetting $modelSurveyLanguageSetting = null;
    private ?LSYii_Application $yiiApp = null;

    /**
     * @param SurveyLanguageSetting $modelSurveyLanguageSetting
     * @param LSYii_Application $yiiApp
     */
    public function __construct(
        SurveyLanguageSetting $modelSurveyLanguageSetting,
        LSYii_Application $yiiApp
    )
    {
        $this->modelSurveyLanguageSetting = $modelSurveyLanguageSetting;
        $this->yiiApp = $yiiApp;
    }

    /**
     * Update
     *
     * @param Survey $survey
     * @param string $initBaseLanguage
     * @return void
     */
    public function update(Survey $survey, $initBaseLanguage)
    {
        if ($survey->language == $initBaseLanguage) {
            // language has not changed - nothing to do
            return null;
        }

        // If the base language is changing, we may need the current title to avoid the survey
        // being listed with an empty title.
        $surveyTitle = $survey
            ->languagesettings[$initBaseLanguage]
            ->surveyls_title;

        // Add new language fixLanguageConsistency do it
        $aAvailableLanguage = $survey->getAllLanguages();
        foreach ($aAvailableLanguage as $sLang) {
            if ($sLang) {
                $oLanguageSettings = $this->modelSurveyLanguageSetting->find(
                    'surveyls_survey_id=:surveyid AND surveyls_language=:langname',
                    array(':surveyid' => $survey->id, ':langname' => $sLang)
                );
                if (!$oLanguageSettings) {
                    $oLanguageSettings = new SurveyLanguageSetting();
                    $languagedetails = getLanguageDetails($sLang);
                    $oLanguageSettings->surveyls_survey_id = $survey->id;
                    $oLanguageSettings->surveyls_language = $sLang;
                    if ($sLang == $survey->language) {
                        $oLanguageSettings->surveyls_title = $surveyTitle;
                    } else {
                        $oLanguageSettings->surveyls_title = ''; // Not in default model ?
                    }
                    $oLanguageSettings->surveyls_dateformat = $languagedetails['dateformat'];
                    if (!$oLanguageSettings->save()) {
                        $this->yiiApp->setFlashMessage(
                            gT("Survey language could not be created."),
                            "error"
                        );
                        tracevar($oLanguageSettings->getErrors());
                    }
                }
            }
        }
        fixLanguageConsistency(
            $survey->id,
            implode(' ', $aAvailableLanguage),
            $initBaseLanguage
        );

        // Delete removed language cleanLanguagesFromSurvey do it
        // already why redo it (cleanLanguagesFromSurvey must be moved to model)
        $oCriteria = new CDbCriteria();
        $oCriteria->compare(
            'surveyls_survey_id',
            $survey->id
        );
        $oCriteria->addNotInCondition(
            'surveyls_language',
            $aAvailableLanguage
        );
        $this->modelSurveyLanguageSetting
            ->deleteAll($oCriteria);

        // Language fix : remove and add question/group
        cleanLanguagesFromSurvey(
            $survey->id,
            implode(' ', $survey->additionalLanguages)
        );
    }
}
