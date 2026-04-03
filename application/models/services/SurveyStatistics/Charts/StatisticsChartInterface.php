<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts;

use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;

/**
 * Interface for all statistics chart classes.
 *
 * Defines the contract that every chart/graph must follow
 * so that they can be executed consistently within StatisticsService.
 */
interface StatisticsChartInterface
{
    /**
     * Set filters for responses
     *
     * @param StatisticsResponseFilters $filters Filters to apply
     * @return void
     */
    public function setFilters(StatisticsResponseFilters $filters): void;

    /**
     * Run the chart generation logic.
     *
     * @param int $surveyId Survey ID
     * @param string $language Language code (optional, for localized text)
     * @return array Chart output(s)
     */
    public function run(int $surveyId, string $language = 'en');
}
