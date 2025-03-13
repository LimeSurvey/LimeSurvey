<?php

namespace LimeSurvey\Models\Services\SurveyAggregateService;

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
    private SurveyLanguageSetting $modelSurveyLanguageSetting;
    private LSYii_Application $yiiApp;

    /**
     * @param SurveyLanguageSetting $modelSurveyLanguageSetting
     * @param LSYii_Application $yiiApp
     */
    public function __construct(
        SurveyLanguageSetting $modelSurveyLanguageSetting,
        LSYii_Application $yiiApp
    ) {
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
        // If the base language is changing, we may need the current title to avoid the survey
        // being listed with an empty title.
        $surveyTitle = $survey
            ->languagesettings[$initBaseLanguage]
            ->surveyls_title;

        // Add new language fixLanguageConsistency do it
        $aAvailableLanguages = $survey->getAllLanguages();
        foreach ($aAvailableLanguages as $sLang) {
            if ($sLang) {
                $this->updateLanguage(
                    $survey,
                    $sLang,
                    $surveyTitle
                );
            }
        }

        $this->cleanup(
            $survey,
            $initBaseLanguage,
            $aAvailableLanguages
        );
    }

    public function updateQuestionsOnly(Survey $survey)
    {
        $languagesToCheck = $survey->getAdditionalLanguages();
        $surveyId = $survey->sid;
        $baseLang = $survey->language;
        fixLanguageConsistencyForQuestions($languagesToCheck, $surveyId, $baseLang);
    }

    /**
     * Update
     *
     * @param Survey $survey
     * @param string $sLang
     * @param string $surveyTitle
     * @return void
     */
    private function updateLanguage(Survey $survey, $sLang, $surveyTitle)
    {
        $oLanguageSettings = $this->modelSurveyLanguageSetting->find(
            'surveyls_survey_id=:surveyid AND surveyls_language=:langname',
            array(':surveyid' => $survey->sid, ':langname' => $sLang)
        );
        if (!$oLanguageSettings) {
            $oLanguageSettings = new SurveyLanguageSetting();
            $languagedetails = getLanguageDetails($sLang);
            $oLanguageSettings->surveyls_survey_id = $survey->sid;
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

    /**
     * Update
     *
     * @param Survey $survey
     * @param string $initBaseLanguage
     * @param array $aAvailableLanguages
     * @return void
     */
    private function cleanup(Survey $survey, $initBaseLanguage, $aAvailableLanguages)
    {
        fixLanguageConsistency(
            $survey->sid,
            implode(' ', $aAvailableLanguages),
            $initBaseLanguage
        );

        $oCriteria = new CDbCriteria();
        $oCriteria->compare(
            'surveyls_survey_id',
            $survey->sid
        );
        $oCriteria->addNotInCondition(
            'surveyls_language',
            $aAvailableLanguages
        );
        // Delete removed language cleanLanguagesFromSurvey do it
        // already why redo it (cleanLanguagesFromSurvey must be moved to model)
        // $this->modelSurveyLanguageSetting ->deleteAll($oCriteria);

        // Language fix : remove and add question/group
        cleanLanguagesFromSurvey(
            $survey->sid,
            implode(
                ' ',
                $survey->additionalLanguages
            ),
            $survey->language
        );
    }
}
