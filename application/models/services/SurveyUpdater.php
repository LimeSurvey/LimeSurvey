<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\SurveyUpdater\{
    LanguageSettings,
    GeneralSettings,
    UrlParams
};
use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

/**
 * Survey Updater Service
 *
 * Service class for update survey settings.
 *
 * Dependencies are injected to enable mocking.
 */
class SurveyUpdater
{
    private ?LanguageSettings $languageSettings = null;
    private ?GeneralSettings $generalSettings = null;
    private ?UrlParams $urlParams = null;
    private ?ExpressionManager $expressionManager = null;

    public function __construct(
        LanguageSettings $languageSettings,
        GeneralSettings $generalSettings,
        UrlParams $urlParams,
        ExpressionManager $expressionManager
    )
    {
        $this->languageSettings = $languageSettings;
        $this->generalSettings = $generalSettings;
        $this->urlParams = $urlParams;
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
        $this->languageSettings->update(
            $surveyId,
            $input
        );

        $meta = $this->generalSettings->update(
            $surveyId,
            $input
        );

        if (!empty($input['url_params'])) {
            $this->urlParams->update(
                $surveyId,
                $input['url_params']
            );
        }

        $this->expressionManager
            ->reset($surveyId);

        return $meta;
    }
}
