<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

/**
 * Service SurveyUpdater
 *
 * Service class for update survey settings.
 *
 * Dependencies are injected to enable mocking.
 */
class SurveyUpdater
{
    private ?SurveyUpdaterLanguageSettings $surveyUpdaterLanguageSettings = null;
    private ?SurveyUpdaterGeneralSettings $surveyUpdaterGeneralSettings = null;
    private ?SurveyUpdaterUrlParams $surveyUpdaterUrlParams = null;

    public function __construct(
        SurveyUpdaterLanguageSettings $surveyUpdaterLanguageSettings,
        SurveyUpdaterGeneralSettings $surveyUpdaterGeneralSettings,
        SurveyUpdaterUrlParams $surveyUpdaterUrlParams
    )
    {
        $this->surveyUpdaterLanguageSettings = $surveyUpdaterLanguageSettings;
        $this->surveyUpdaterGeneralSettings = $surveyUpdaterGeneralSettings;
        $this->surveyUpdaterUrlParams = $surveyUpdaterUrlParams;
    }

    /**
     * Update
     *
     * @param int $surveyId
     * @param array $input
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @throws ExceptionPermissionDenied
     * @return boolean
     */
    public function update($surveyId, array $input)
    {
        $this->surveyUpdaterLanguageSettings->update(
            $surveyId,
            $input
        );

        $this->surveyUpdaterGeneralSettings->update(
            $surveyId,
            $input
        );

        if (!empty($input['url_params'])) {
            $this->surveyUpdaterUrlParams->update(
                $surveyId,
                $input['url_params']
            );
        }

        return true;
    }
}
