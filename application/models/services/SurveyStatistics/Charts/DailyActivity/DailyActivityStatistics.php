<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\DailyActivity;

use DateInterval;
use DatePeriod;
use DateTime;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartInterface;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Yii;

/**
 * Graph: Daily activity (responses per day) in the last 30 days.
 *
 * Always returns all 30 days with a count, even when 0.
 */
class DailyActivityStatistics implements StatisticsChartInterface
{
    /** @var StatisticsResponseFilters|null Filters to apply to the query */
    private $filters = null;

    /**
     * Run the daily activity statistics query.
     *
     * @param int $surveyId Survey ID
     * @param string $language Language code (not used here, included for consistency)
     * @return StatisticsChartDTO
     */
    public function run(int $surveyId, string $language = 'en'): StatisticsChartDTO
    {
        [$startDate, $endDate] = $this->getDateRange();

        $countsByDate = $this->fetchCounts($surveyId, $startDate);

        [$legend, $dataItems, $total] = $this->buildSeries($countsByDate, $startDate, $endDate);

        return new StatisticsChartDTO(
            "Daily responses (last 30 days)",
            $legend,
            $dataItems,
            $total,
            ['period' => 'last_30_days']
        );
    }

    /**
     * @param StatisticsResponseFilters $filters
     * @return void
     */
    public function setFilters(StatisticsResponseFilters $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * Get the start and end dates for the last 30 days.
     *
     * @return DateTime[] [$startDate, $endDate]
     */
    private function getDateRange(): array
    {
        $startDate = (new DateTime('today'))->modify('-29 days'); // include today + 29 past days
        $endDate = new DateTime('today'); // include today

        return [$startDate, $endDate];
    }

    /**
     * Query the database for counts of responses per day.
     *
     * @param int $surveyId
     * @param DateTime $startDate
     * @return array [date => count]
     */
    private function fetchCounts(int $surveyId, DateTime $startDate): array
    {
        $command = Yii::app()->db->createCommand()
            ->select(['DATE(submitdate) as response_date', 'COUNT(id) as responses', 'GROUP_CONCAT(id) as response_ids'])
            ->from("{{survey_$surveyId}}")
            ->where('submitdate IS NOT NULL AND submitdate >= :startDate', [':startDate' => $startDate->format('Y-m-d 00:00:00')]);


        // Apply filters if any
        if ($this->filters && $this->filters->count() > 0) {
            $filters = $this->filters->getFilters();
            if (!empty($filters['minId'])) {
                $command = $command->andWhere('id >= :minId', [':minId' => $filters['minId']]);
            }

            if (!empty($filters['maxId'])) {
                $command = $command->andWhere('id <= :maxId', [':maxId' => $filters['maxId']]);
            }

//            if (isset($filters['completed'])) {
//                $command = $command->andWhere('submitdate IS ' . ($filters['completed'] ? 'NOT' : '') . ' NULL');
//            }
        }

        $command = $command->group('DATE(submitdate)')->order('DATE(submitdate) ASC');

        $rows = $command->queryAll();

        $countsByDate = [];
        foreach ($rows as $row) {
            $countsByDate[$row['response_date']] = (int)$row['responses'];
        }

        return $countsByDate;
    }

    /**
     * Build a full daily series of the last 30 days,
     * filling in 0 for missing days.
     *
     * @param array $countsByDate [date => count]
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return array [$legend, $dataItems, $total]
     */
    private function buildSeries(array $countsByDate, DateTime $startDate, DateTime $endDate): array
    {
        $legend = [];
        $dataItems = [];
        $total = 0;

        $period = new DatePeriod($startDate, new DateInterval('P1D'), (clone $endDate)->modify('+1 day'));
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $count = $countsByDate[$day] ?? 0;

            $legend[] = $day;
            $dataItems[] = [
                'key' => $day,
                'title' => $day,
                'value' => $count,
            ];

            $total += $count;
        }

        return [$legend, $dataItems, $total];
    }
}
