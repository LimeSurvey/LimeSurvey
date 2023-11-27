<?php

namespace LimeSurvey\Models\Services\SurveyAggregateService;

/**
 * Survey Updater Service template configuration
 *
 */
class TemplateConfiguration
{
    /**
     * Update
     *
     * @param int $surveyId
     * @return void
     */
    public function update($surveyId)
    {
        // This will force the generation of the entry for survey group
        \TemplateConfiguration::checkAndcreateSurveyConfig(
            $surveyId
        );
    }
}
