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
    private ?ExpressionManager $expressionManager = null;

    public function __construct(
        SurveyUpdaterLanguageSettings $surveyUpdaterLanguageSettings,
        SurveyUpdaterGeneralSettings $surveyUpdaterGeneralSettings,
        SurveyUpdaterUrlParams $surveyUpdaterUrlParams,
        ExpressionManager $expressionManager
    )
    {
        $this->surveyUpdaterLanguageSettings = $surveyUpdaterLanguageSettings;
        $this->surveyUpdaterGeneralSettings = $surveyUpdaterGeneralSettings;
        $this->surveyUpdaterUrlParams = $surveyUpdaterUrlParams;
        $this->expressionManager = $expressionManager;
    }

    /**
     * Update
     *
     * @param int $surveyId
     * @param array $input
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @throws ExceptionPermissionDenied
     * @return array
     */
    public function update($surveyId, array $input)
    {
        $this->surveyUpdaterLanguageSettings->update(
            $surveyId,
            $input
        );

        $meta = $this->surveyUpdaterGeneralSettings->update(
            $surveyId,
            $input
        );

        if (!empty($input['url_params'])) {
            $this->surveyUpdaterUrlParams->update(
                $surveyId,
                $input['url_params']
            );
        }

        $this->expressionManager
            ->reset($surveyId);

        return $meta;
    }
}
