<?php

namespace LimeSurvey\Models\Services\SurveyStatistics;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\DailyActivity\DailyActivityStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\QuestionStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartInterface;

/**
 * Service layer for survey statistics.
 *
 * Acts as the entry point for generating statistics across all
 * configured graph processors (e.g., question-level statistics).
 */
class StatisticsService
{
    /** @var int|string Current survey ID */
    private $surveyId;

    /** @var string Active survey language */
    private string $language;

    /** @var array List of charts classes to run */
    private array $charts;

    /** @var array Gathered chart results*/
    private array $output = [];


    public function __construct()
    {
        $this->charts = [
            DailyActivityStatistics::class,
            QuestionStatistics::class,
        ];
    }

    /**
     * Set survey context (ID + language).
     *
     * @param int|string $surveyId Survey ID
     * @param string $language Language code (default = "en")
     * @return $this
     */
    public function setSurvey($surveyId, string $language = 'en'): self
    {
        $this->surveyId = $surveyId;
        $this->language = $language;
        return $this;
    }

    /**
     * Execute statistics generation for all configured graphs.
     *
     * @return array Combined output from all graph processors
     */
    public function run()
    {
        foreach ($this->charts as $chart) {
            if (!class_exists($chart)) {
                continue;
            }

            $chartObj = new $chart();
            if (!($chartObj instanceof StatisticsChartInterface)) {
                continue;
            }

            $data = $chartObj->run($this->surveyId, $this->language);
            $data = is_array($data) ? $data : [$data];

            $this->handleChartOutput($data);
        }

        return $this->output;
    }

    private function handleChartOutput(array $data): void
    {
        foreach ($data as $chart) {
            if ($chart instanceof StatisticsChartDTO) {
                $this->output[] = $chart->toArray();
            }

            if (is_array($chart)) {
                $this->output = array_merge($this->output, array_map(function ($item) {
                    return $item->toArray();
                }, $chart));
            }
        }
    }
}
