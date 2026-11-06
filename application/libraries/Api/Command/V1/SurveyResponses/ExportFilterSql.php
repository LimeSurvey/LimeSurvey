<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use CDbCriteria;
use LimeSurvey\Models\Services\ExportSurveyResultsService;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSqlBuilder;
use Yii;

/**
 * Turns the export's filters into what each exporter takes: criteria for the
 * native exporter, one plain SQL condition for the legacy plugin exporters.
 */
class ExportFilterSql
{
    private ExportSurveyResultsService $exportSurvey;

    public function __construct(ExportSurveyResultsService $exportSurvey)
    {
        $this->exportSurvey = $exportSurvey;
    }

    /**
     * Criteria for the native exporter, which reads the responses through
     * SurveyDynamic.
     *
     * @param int $surveyId
     * @param ResolvedFilter[] $resolvedFilters
     * @return CDbCriteria
     */
    public function buildFilterCriteria(int $surveyId, array $resolvedFilters): CDbCriteria
    {
        return (new ResponseFilterCriteriaBuilder($surveyId))->build($resolvedFilters);
    }

    /**
     * The legacy exporter's condition: the column filters and the
     * condition-designer filter, both self-contained, combined with AND.
     *
     * @param int $surveyId
     * @param string $responsesTable The survey's responses table, as {{...}} or real name
     * @param array $filters Column filters, as the responses list sends them
     * @param ResolvedFilter[] $resolvedFilters
     * @return string
     */
    public function buildLegacyCondition(
        int $surveyId,
        string $responsesTable,
        array $filters,
        array $resolvedFilters
    ): string {
        return $this->combineSqlFilters(
            $this->buildLegacySqlFilter($responsesTable, $filters),
            $this->buildFilterSetSqlFilter($surveyId, $responsesTable, $resolvedFilters)
        );
    }

    /**
     * The legacy exporter takes no bound values, so they are quoted into the
     * condition itself.
     *
     * @param string $condition
     * @param array $params
     * @return string
     */
    public function inlineParams(string $condition, array $params): string
    {
        $position = 0;

        return preg_replace_callback('/\?|:[A-Za-z0-9_]+/', static function ($match) use ($params, &$position) {
            $key = $match[0] === '?' ? $position++ : $match[0];
            return array_key_exists($key, $params)
                ? Yii::app()->db->quoteValue($params[$key])
                : $match[0];
        }, $condition);
    }

    /**
     * Combine conditions with AND, leaving out empty ones.
     *
     * @param string ...$filters
     * @return string
     */
    public function combineSqlFilters(string ...$filters): string
    {
        $parts = array_values(array_filter($filters, static function ($filter) {
            return $filter !== '';
        }));

        if (count($parts) < 2) {
            return $parts[0] ?? '';
        }

        return implode(' AND ', array_map(static function ($part) {
            return '(' . $part . ')';
        }, $parts));
    }

    /**
     * The column filters, built as for the native export and turned into a
     * plain condition.
     *
     * @param string $responsesTable
     * @param array $filters
     * @return string
     */
    private function buildLegacySqlFilter(string $responsesTable, array $filters): string
    {
        if (empty($filters)) {
            return '';
        }

        $criteria = $this->exportSurvey->setFilters($filters)->buildFilterCriteria();
        if ($criteria === null || empty($criteria->condition)) {
            return '';
        }

        $condition = preg_replace_callback(
            "/'(?:[^']|'')*'|[`\"\\[][\\w-]+[`\"\\]]/",
            static function (array $match) use ($responsesTable): string {
                return $match[0][0] === "'" ? $match[0] : $responsesTable . '.' . $match[0];
            },
            $criteria->condition
        );
        return $this->inlineParams($condition, $criteria->params);
    }

    /**
     * The condition-designer filter.
     *
     * @param int $surveyId
     * @param string $responsesTable
     * @param ResolvedFilter[] $resolvedFilters
     * @return string
     */
    private function buildFilterSetSqlFilter(int $surveyId, string $responsesTable, array $resolvedFilters): string
    {
        if ($resolvedFilters === []) {
            return '';
        }

        $table = Yii::app()->db->schema->getTable($responsesTable);

        $built = (new ResponseFilterSqlBuilder($surveyId, $table ? $table->name : null))
            ->build($resolvedFilters);

        return $built['condition'] === ''
            ? ''
            : $this->inlineParams($built['condition'], $built['params']);
    }
}
