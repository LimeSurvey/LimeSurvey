<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\DailyActivity;

use DateInterval;
use DatePeriod;
use DateTime;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartInterface;
use PDO;
use Yii;

/**
 * Graph: Daily activity (responses per day) in the last 30 days.
 *
 * Always returns all 30 days with a count, even when 0.
 */
class DailyActivityStatistics implements StatisticsChartInterface
{
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
        $sql = "
            SELECT DATE(submitdate) as response_date, COUNT(id) as responses
            FROM {{survey_$surveyId}}
            WHERE submitdate IS NOT NULL
              AND submitdate >= :startDate
            GROUP BY DATE(submitdate)
            ORDER BY response_date ASC
        ";

        $command = Yii::app()->db->createCommand($sql);
        $command->bindValue(':startDate', $startDate->format('Y-m-d 00:00:00'), PDO::PARAM_STR);

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

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate->modify('+1 day'));
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
