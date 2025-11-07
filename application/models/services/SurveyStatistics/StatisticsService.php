<?php

namespace LimeSurvey\Models\Services\SurveyStatistics;

use InvalidArgumentException;
use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\DailyActivity\DailyActivityStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\QuestionStatistics;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartInterface;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\SurveyOverview\SurveyOverviewStatistics;
use RuntimeException;
use Survey;

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

    /** @var array Gathered chart results */
    private array $output = [];

    /** @var StatisticsResponseFilters $filters */
    private StatisticsResponseFilters $filters;


    public function __construct()
    {
        $this->charts = [
            QuestionStatistics::class,
        ];
    }

    /**
     * Set survey context (ID + language).
     *
     * @param int|string $surveyId Survey ID
     * @param string $language Language code (default = "en")
     * @return $this
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function setSurvey($surveyId, string $language = 'en'): self
    {
        // Validate survey ID
        if (!is_numeric($surveyId) || $surveyId < 1) {
            throw new InvalidArgumentException('Invalid survey ID');
        }

        //Validate survey existence
        $survey = Survey::model()->findByPk($surveyId);
        if (empty($survey)) {
            throw new NotFoundException('Survey not found');
        }

        // Validate language code
        $allowedLanguages = $survey->getAllLanguages();
        if (!in_array($language, $allowedLanguages, true)) {
            throw new InvalidArgumentException('Invalid language code');
        }

        $this->surveyId = (int)$surveyId;
        $this->language = $language;
        return $this;
    }

    /**
     * Set response filters to apply to all chart processors.
     * @param StatisticsResponseFilters $filters
     * @return void
     */
    public function setFilters(StatisticsResponseFilters $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * Register a chart type processor by class name.
     * @param string $chart
     * @return $this
     */
    public function setChart(string $chart): StatisticsService
    {
        if (!class_exists($chart)) {
            throw new InvalidArgumentException('Chart type ' . $chart . ' does not exist');
        }

        $this->charts[] = $chart;
        return $this;
    }

    /**
     * Execute statistics generation for all configured graphs.
     *
     * @param array $specificCharts List of specific chart classes to run
     * @return array Combined output from all graph processors
     * @throws RuntimeException
     */
    public function run(array $specificCharts = []): array
    {
        if (!isset($this->surveyId)) {
            throw new RuntimeException('Survey ID must be set before running statistics');
        }

        if (!empty($specificCharts)) {
            $invalidCharts = array_diff($specificCharts, $this->charts);
            if (!empty($invalidCharts)) {
                throw new InvalidArgumentException('Invalid chart types specified: ' . implode(', ', $invalidCharts));
            }
            $this->charts = array_intersect($this->charts, $specificCharts);
        }

        foreach ($this->charts as $chart) {
            if (!class_exists($chart)) {
                continue;
            }

            $chartObj = new $chart();
            if (!($chartObj instanceof StatisticsChartInterface)) {
                continue;
            }

            if (!empty($this->filters) && count($this->filters->getFilters()) > 0) {
                $chartObj->setFilters($this->filters);
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
