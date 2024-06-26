<?php

namespace LimeSurvey\Models\Services\SurveyAggregateService;

use Survey;
use Permission;
use SurveyLanguageSetting;
use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

/**
 * Survey Updater Service LanguageSettings
 *
 * Service class for survey language setting updating.
 *
 * Dependencies are injected to enable mocking.
 */
class LanguageSettings
{
    private ?Permission $modelPermission;
    private ?Survey $modelSurvey;
    private ?SurveyLanguageSetting $modelSurveyLanguageSetting;

    private $inputFields = [
        'surveyls_url',
        'surveyls_urldescription',
        'surveyls_title',
        'surveyls_alias',
        'surveyls_description',
        'surveyls_welcometext',
        'surveyls_endtext',
        'surveyls_policy_notice',
        'surveyls_policy_error',
        'surveyls_policy_notice_label',
        'surveyls_dateformat',
        'surveyls_numberformat'
    ];

    public function __construct(
        Permission $modelPermission,
        Survey $modelSurvey,
        SurveyLanguageSetting $modelSurveyLanguageSetting
    ) {
        $this->modelPermission = $modelPermission;
        $this->modelSurvey = $modelSurvey;
        $this->modelSurveyLanguageSetting = $modelSurveyLanguageSetting;
    }

    /**
     * Update
     *
     * Input is an array of language specific settings keyed by language code.
     * Each element is an array with one or more of the follow keys:
     *  - url
     *  - url_description
     *  - short_title
     *  - alias
     *  - description
     *  - welcome
     *  - end_text
     *  - policy_notice
     *  - policy_error
     *  - policy_notice_label
     *  - date_format
     *  - number_format
     *
     * @param int $surveyId
     * @param array $input
     * @return boolean
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function update($surveyId, $input)
    {
        $this->checkUpdatePermission($surveyId);
        $survey = $this->modelSurvey->findByPk(
            $surveyId
        );
        if (!$survey) {
            throw new NotFoundException();
        }

        $this->updateLanguageSettings(
            $survey,
            $input
        );

        return true;
    }

    /**
     * Update language specific settings
     *
     * @param Survey $survey
     * @param array $input
     * @return void
     * @throws NotFoundException
     * @throws PersistErrorException
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
                ->findByPk(
                    array(
                        'surveyls_survey_id' => $survey->sid,
                        'surveyls_language' => $languageCode
                    )
                );
            if (!$surveyLanguageSetting) {
                throw new NotFoundException(
                    'Language settings not found'
                );
            }

            $surveyLanguageSetting->setAttributes($data);
            if (!$surveyLanguageSetting->save()) {
                $e = new PersistErrorException(
                    sprintf(
                        'Failed saving language settings for survey #%s and language "%s"',
                        $survey->sid,
                        $languageCode
                    )
                );
                $e->setErrorModel($surveyLanguageSetting);
                throw $e;
            }
        }
    }

    /**
     * Parse language settings from input data
     *
     * @param array $input
     * @param string $languageCode
     * @return ?array
     */
    private function getLanguageSettingsData($input, $languageCode)
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
            $value = $this->getValue(
                $input[$languageCode],
                $inputField
            );
            if ($value !== null) {
                $data[$inputField] = $value;
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
     * @param int $surveyId
     * @return void
     * @throws PermissionDeniedException
     */
    public function checkUpdatePermission(int $surveyId)
    {
        $hasPermission = $this->modelPermission
            ->hasSurveyPermission(
                $surveyId,
                'surveylocale',
                'update'
            );
        if (!$hasPermission) {
            throw new PermissionDeniedException(
                'Permission denied'
            );
        }
    }
}
