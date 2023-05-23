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
        $survey = $this->modelSurvey->findByPk(
            $surveyId
        );

        $this->updateLanguageSettings(
            $survey,
            $input
        );
    }

    /**
     * Update language specific settings
     *
     * @param Survey $survey
     * @param array $input
     * @return void
     */
    protected function updateLanguageSettings(Survey $survey, $input)
    {
        if (isset($survey)) {
            $languageList = $survey->additionalLanguages;
            $languageList[] = $survey->language;
        }

        if (
            $this->modelPermission
                ->hasSurveyPermission(
                    $survey->sid,
                    'surveylocale',
                    'update'
                )
        ) {
            foreach ($languageList as $languageName) {
                $data = $this->getLanguageSettings(
                    $input,
                    $languageName
                );

                if ($data && count($data) > 0) {
                    $surveyLanguageSetting = $this->modelSurveyLanguageSetting
                        ->findByPk(array(
                            'surveyls_survey_id' => $survey->sid,
                            'surveyls_language' => $languageName
                        ));
                    $surveyLanguageSetting->setAttributes($data);
                    // save the change to database
                    if (!$surveyLanguageSetting->save()) {
                        // $languageDescription = getLanguageNameFromCode(
                        //    $languageName,
                        //    false
                        //);
                    }
                }
            }
        }
    }

    /**
     * Parse language settings from input data
     *
     * @param array $input
     * @param string $languageName
     * @return array
     */
    protected function getLanguageSettings($input, $languageName)
    {
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

        if (!isset($input[$languageName])) {
            return null;
        }

        $data = array();
        foreach ($fields as $field) {
            $value = $this->getValue(
                $input[$languageName],
                $field
            );
            if ($value === null) {
                $data[$field] = $value;
            }
        }

        return $data;
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
