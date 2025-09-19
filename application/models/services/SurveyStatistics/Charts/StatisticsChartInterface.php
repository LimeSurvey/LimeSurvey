<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts;

/**
 * Interface for all statistics chart classes.
 *
 * Defines the contract that every chart/graph must follow
 * so that they can be executed consistently within StatisticsService.
 */
interface StatisticsChartInterface
{
    /**
     * Run the chart generation logic.
     *
     * @param int $surveyId Survey ID
     * @param string $language Language code (optional, for localized text)
     * @return array Chart output(s)
     */
    public function run(int $surveyId, string $language = 'en');
}
