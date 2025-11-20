<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\SurveyOverview;

use CDbExpression;
use CDbTableSchema;
use InvalidArgumentException;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartInterface;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Yii;

/**
 * Survey Overview Statistics Chart Generator
 *
 * Generates overview statistics for a survey including:
 * - Total responses
 * - Total not submitted responses
 * - Completion rate
 * - Average completion time
 * - Number of completed submissions without answers
 * - Number of not submitted submissions without answers
 */
class SurveyOverviewStatistics implements StatisticsChartInterface
{
    /** @var int */
    private $surveyId = 0;

    /** @var array Allowed system fields that should be excluded from answer checking */
    private const SYSTEM_FIELDS = [
        'id', 'submitdate', 'lastpage', 'startlanguage',
        'seed', 'startdate', 'datestamp', 'token',
    ];

    /**
     * @inheritDoc
     */
    public function run(int $surveyId, string $language = 'en'): StatisticsChartDTO
    {
        $this->surveyId = $surveyId;

        $rows = $this->fetchStatisticsOverview();
        $rows['completionRate'] = round($rows['completionRate'] ?? 0, 2);
        $rows['completedWithoutAnswers'] = (int)$rows['completedWithoutAnswers'];
        $rows['incompletedWithoutAnswers'] = (int)$rows['incompletedWithoutAnswers'];
        $rows['incompleteResponses'] = (int)$rows['incompleteResponses'];
        if ($rows['avgCompletionTime'] !== null) {
            $rows['avgCompletionTime'] = round($rows['avgCompletionTime'] ?? 0, 2);
        }

        return new StatisticsChartDTO(
            'Survey Overview',
            [
                'Total Responses',
                'Completed Without Answers',
                'Not Completed Without Answers',
                'Incomplete Responses',
                'Completion Rate (%)',
                'Avg. Completion Time (s)',
            ],
            $rows,
            (int)$rows['totalResponses']
        );
    }

    /**
     * Fetch overview statistics for the survey
     *
     * @return array Array of statistics
     * @throws InvalidArgumentException If table doesn't exist or other DB error
     */
    private function fetchStatisticsOverview(): array
    {
        $tableName = '{{survey_' . $this->surveyId . '}}';

        // Validate table exists and get schema
        $tableSchema = Yii::app()->db->schema->getTable($tableName);
        if ($tableSchema === null) {
            throw new InvalidArgumentException("Survey table does not exist");
        }

        $coalesceSql = $this->buildCoalesceStatement($tableSchema);

        $selectParams = [
            'COUNT(id) AS totalResponses',
            'SUM(CASE WHEN submitdate IS NULL THEN 1 ELSE 0 END) AS incompleteResponses',
            "SUM(CASE WHEN submitdate IS NOT NULL AND {$coalesceSql} IS NULL THEN 1 ELSE 0 END) AS completedWithoutAnswers",
            "SUM(CASE WHEN submitdate IS NULL AND {$coalesceSql} IS NULL THEN 1 ELSE 0 END) AS incompletedWithoutAnswers",
            'ROUND(SUM(CASE WHEN submitdate IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(id), 0), 2) AS completionRate',
        ];

        // datestamps is not enabled, therefor we cannot calculate avg completion time
        $selectParams[] = isset($tableSchema->columns['startdate'])
            ? 'AVG(CASE WHEN submitdate IS NOT NULL THEN TIMESTAMPDIFF(SECOND, startdate, submitdate) END) AS avgCompletionTime'
            : new CDbExpression('NULL AS avgCompletionTime');

        // Build and execute query with proper parameter binding
        $command = Yii::app()->db->createCommand()
            ->select($selectParams)
            ->from($tableName);

        return $command->queryRow();
    }

    /**
     * Build a secure COALESCE statement for checking empty answers
     *
     * @param CDbTableSchema $tableSchema
     * @return string The COALESCE SQL fragment
     */
    private function buildCoalesceStatement(CDbTableSchema $tableSchema): string
    {
        $dynamicColumns = array_diff(array_keys($tableSchema->columns), self::SYSTEM_FIELDS);

        $coalesceParts = [];
        foreach ($dynamicColumns as $col) {
            $coalesceParts[] = "NULLIF(`" . $col . "`, '')";
        }

        return count($coalesceParts) > 0
            ? 'COALESCE(' . implode(', ', $coalesceParts) . ')'
            : 'NULL';
    }

    /**
     * @param StatisticsResponseFilters $filters
     * @return void
     */
    public function setFilters(StatisticsResponseFilters $filters): void
    {
    }
}
