<?php

namespace LimeSurvey\Models\Services;

use Survey;
use Permission;
use SurveyURLParameter;
use SurveyLanguageSetting;
use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

/**
 * Service SurveyLanguageSettingsUpdater
 *
 * Service class for survey language setting updating.
 *
 * Dependencies are injected to enable mocking.
 */
class SurveyLanguageSettingsUpdater
{
    private ?Permission $modelPermission = null;
    private ?Survey $modelSurvey = null;
    private ?SurveyURLParameter $modelSurveyUrlParameter = null;
    private ?SurveyLanguageSetting $modelSurveyLanguageSetting = null;
    private $fieldMap = [
        'url_description' => 'url_description',
        'url' => 'url',
        'short_title' => 'short_title',
        'alias' => 'alias',
        'description' => 'description',
        'welcome' => 'welcome',
        'end_text' => 'end_text',
        'data_section' => 'data_section',
        'data_section_error' => 'data_section_error',
        'data_section_label' => 'data_section_label',
        'date_format' => 'date_format',
        'number_format' => 'number_format',
    ];
    private $inputFields = [];

    public function __construct()
    {
        $this->modelPermission = Permission::model();
        $this->modelSurvey = Survey::model();
        $this->modelSurveyUrlParameter = SurveyURLParameter::model();
        $this->modelSurveyLanguageSetting = SurveyLanguageSetting::model();
        $this->inputFields = array_keys($this->fieldMap);
    }

    /**
     * Update
     *
     * Input is an array of language specific settings keyed by language code.
     * Each element is an array with one or more of the follow keys:
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
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @throws ExceptionPermissionDenied
     * @return void
     */
    public function update($surveyId, $input)
    {
        $hasPermission = $this->modelPermission
            ->hasSurveyPermission(
                $surveyId,
                'surveylocale',
                'update'
            );
        if ($hasPermission == false) {
            throw new ExceptionPermissionDenied('Permission denied');
        }

        $survey = $this->modelSurvey->findByPk(
            $surveyId
        );
        if (!$survey) {
            throw new ExceptionNotFound;
        }

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
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @return void
     */
    protected function updateLanguageSettings(Survey $survey, $input)
    {
        $languageList = $survey->additionalLanguages;
        $languageList[] = $survey->language;

        foreach ($languageList as $languageCode) {
            $data = $this->getLanguageSettingsData(
                $input,
                $languageCode
            );

            if (empty($data)) {
                continue;
            }

            $surveyLanguageSetting = $this->modelSurveyLanguageSetting
                ->findByPk(array(
                    'surveyls_survey_id' => $survey->sid,
                    'surveyls_language' => $languageCode
                ));
            if (!$surveyLanguageSetting) {
                throw new ExceptionNotFound('Language settings not found');
            }

            $surveyLanguageSetting->setAttributes($data);
            if (!$surveyLanguageSetting->save()) {
                throw new ExceptionPersistError('Failed saving language settings');
            }
        }
    }

    /**
     * Parse language settings from input data
     *
     * @param array $input
     * @param string $languageCode
     * @return array
     */
    protected function getLanguageSettingsData($input, $languageCode)
    {
        if (
            !is_array($input)
            || !isset($input[$languageCode])
            || !is_array($input[$languageCode])
        ) {
            return null;
        }

        $data = array();
        foreach ($this->inputFields as $inputField) {
            $field = $this->fieldMap[$inputField];
            $value = $this->getValue(
                $input[$languageCode],
                $inputField
            );
            if ($value !== null) {
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
