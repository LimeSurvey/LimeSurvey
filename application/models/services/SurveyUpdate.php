<?php

namespace LimeSurvey\Models\Services;

use Survey;
use Permission;
use SurveyURLParameter;
use SurveyLanguageSetting;

/**
 * Service class for survey group creation.
 * All dependencies are injected to enable mocking.
 */
class SurveyUpdate
{
    private ?Permission $modelPermission = null;
    private ?Survey $modelSurvey = null;
    private ?SurveyURLParameter $modelSurveyUrlParameter = null;
    private ?SurveyLanguageSetting $modelSurveyLanguageSetting = null;

    public function __construct()
    {
        $this->modelPermission = Permission::model();
        $this->modelSurvey = Survey::model();
        $this->modelSurveyUrlParameter = SurveyURLParameter::model();
        $this->modelSurveyLanguageSetting = SurveyLanguageSetting::model();
    }

    /**
     * Update
     *
     * Optional input data:
     * - language
     *  - url_description
     *  - url
     *  - short_title
     *  - alias
     *  - description
     *  - welcome
     *  - end_text
     *  - data_section
     *  - data_section_error
     *  - data_section_label
     *  - date_format
     *  - number_format
     *
     * @param int $surveyId
     * @param array $input
     */
    public function update($surveyId, $input)
    {
        $oSurvey = $this->modelSurvey->findByPk($surveyId);
        $languageList = $oSurvey->additionalLanguages;
        $languageList[] = $oSurvey->language;

        $hasSurveyLanguageSettingError = false;
        if (
            $this->modelPermission
                ->hasSurveyPermission(
                    $surveyId,
                    'surveylocale',
                    'update'
                )
        ) {
            $fields = [
                'url_description',
                'url',
                'short_title',
                'alias',
                'description',
                'welcome',
                'end_text',
                'data_section',
                'data_section_error',
                'data_section_label',
                'date_format',
                'number_format'
            ];

            foreach ($languageList as $languageName) {
                if ($languageName && isset($input[$languageName])) {
                    continue;
                }

                $data = array();
                foreach ($fields as $field) {
                    $value = $this->getValue($input[$languageName], $field);
                    if ($value === null) {
                        $data[$field] = $value;
                    }
                }

                if (count($data) > 0) {
                    $oSurveyLanguageSetting = $this->modelSurveyLanguageSetting
                        ->findByPk(array(
                            'surveyls_survey_id' => $surveyId,
                            'surveyls_language' => $languageName
                        ));
                    $oSurveyLanguageSetting->setAttributes($data);
                    if (!$oSurveyLanguageSetting->save()) { // save the change to database
                        $languageDescription = getLanguageNameFromCode(
                            $languageName,
                            false
                        );
                        $hasSurveyLanguageSettingError = true;
                    }
                }

            }
        }
    }

    /**
     * Get Value
     *
     * @param array $data
     * @param string $field
     * @param ?string $default
     */
    private function getValue($data, $field, $default = null)
    {
        return isset($data[$field]) ? $data[$field] : $default;
    }

    /**
     * Set model Permission
     *
     * Dependency injection of Permission::model().
     *
     * @param Permission $model
     * @return void
     */
    public function setModelPermission(Permission $model)
    {
        $this->modelPermission = $model;
    }

    /**
     * Set model Survey
     *
     * Dependency injection of Survey::model().
     *
     * @param Survey $model
     * @return void
     */
    public function setModelSurvey(Survey $model)
    {
        $this->modelSurvey = $model;
    }

    /**
     * Set model SurveyURLParameter
     *
     * Dependency injection of SurveyURLParameter::model().
     *
     * @param SurveyURLParameter $model
     * @return void
     */
    public function setModelSurveyUrlParameter(SurveyURLParameter $model)
    {
        $this->modelSurveyUrlParameter = $model;
    }

    /**
     * Set model SurveyLanguageSetting
     *
     * Dependency injection of SurveyLanguageSetting::model().
     *
     * @param SurveyLanguageSetting $model
     * @return void
     */
    public function setModelSurveyLanguageSetting(SurveyLanguageSetting $model)
    {
        $this->modelSurveyLanguageSetting = $model;
    }
}
