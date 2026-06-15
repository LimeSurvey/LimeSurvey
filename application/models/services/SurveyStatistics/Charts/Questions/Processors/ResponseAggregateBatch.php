<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use CDbConnection;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use SurveyDynamic;

/**
 * Collects every aggregate needed for survey statistics and executes them
 * as conditional aggregates over a single scan of the responses table.
 *
 * Usage is two-phased:
 *  1. Registration: countValue()/countBlank()/countNonEmpty()/countTotal()
 *     each return an alias for the requested aggregate (deduplicated).
 *  2. execute() runs the merged SELECT (chunked only when the expression
 *     list is very large), after which value($alias) returns the count.
 */
final class ResponseAggregateBatch
{
    /** Hard cap of SELECT expressions per query to bound per-row CASE cost */
    public const MAX_EXPRESSIONS_PER_QUERY = 1500;

    private const KIND_VALUE = 'value';
    private const KIND_BLANK = 'blank';
    private const KIND_NON_EMPTY = 'nonEmpty';
    private const KIND_TOTAL = 'total';

    private int $surveyId;

    /** @var StatisticsResponseFilters|null */
    private $filters;

    /** @var array<string, array{kind: string, field: string, value: string}> alias => request */
    private array $requests = [];

    /** @var array<string, string> dedup key => alias */
    private array $aliasIndex = [];

    /** @var array<string, int> alias => count */
    private array $results = [];

    private bool $executed = false;

    public function __construct(int $surveyId, ?StatisticsResponseFilters $filters = null)
    {
        $this->surveyId = $surveyId;
        $this->filters = $filters;
    }

    /**
     * Count of rows where the column equals the given value.
     */
    public function countValue(string $field, string $value): string
    {
        return $this->register(self::KIND_VALUE, $field, $value);
    }

    /**
     * Count of rows where the column is NULL or empty.
     */
    public function countBlank(string $field): string
    {
        return $this->register(self::KIND_BLANK, $field, '');
    }

    /**
     * Count of rows where the column is neither NULL nor empty.
     */
    public function countNonEmpty(string $field): string
    {
        return $this->register(self::KIND_NON_EMPTY, $field, '');
    }

    /**
     * Total row count (with the response filters applied).
     */
    public function countTotal(): string
    {
        return $this->register(self::KIND_TOTAL, '', '');
    }

    /**
     * Execute all registered aggregates over the responses table.
     */
    public function execute(): void
    {
        if ($this->executed) {
            return;
        }

        $db = $this->getDb();
        $table = $db->quoteTableName('{{responses_' . $this->surveyId . '}}');
        $where = $this->buildWhere();

        foreach (array_chunk($this->requests, self::MAX_EXPRESSIONS_PER_QUERY, true) as $chunk) {
            $selects = [];
            foreach ($chunk as $alias => $request) {
                $selects[] = $this->buildExpression($db, $request)
                    . ' AS ' . $db->quoteColumnName($alias);
            }

            $sql = 'SELECT ' . implode(', ', $selects) . ' FROM ' . $table . $where;
            $row = $db->createCommand($sql)->queryRow() ?: [];

            foreach (array_keys($chunk) as $alias) {
                $this->results[$alias] = (int)($row[$alias] ?? 0);
            }
        }

        $this->executed = true;
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    /**
     * Resolved count for a previously registered alias (0 before execute()).
     */
    public function value(string $alias): int
    {
        return $this->results[$alias] ?? 0;
    }

    private function register(string $kind, string $field, string $value): string
    {
        $key = $kind . "\x1F" . $field . "\x1F" . $value;
        if (isset($this->aliasIndex[$key])) {
            return $this->aliasIndex[$key];
        }

        $alias = 'a' . count($this->requests);
        $this->aliasIndex[$key] = $alias;
        $this->requests[$alias] = ['kind' => $kind, 'field' => $field, 'value' => $value];

        return $alias;
    }

    private function buildExpression(CDbConnection $db, array $request): string
    {
        switch ($request['kind']) {
            case self::KIND_VALUE:
                $col = $db->quoteColumnName($request['field']);
                return 'SUM(CASE WHEN ' . $col . ' = ' . $db->quoteValue($request['value'])
                    . ' THEN 1 ELSE 0 END)';
            case self::KIND_BLANK:
                $col = $db->quoteColumnName($request['field']);
                return "SUM(CASE WHEN $col IS NULL OR $col = '' THEN 1 ELSE 0 END)";
            case self::KIND_NON_EMPTY:
                $col = $db->quoteColumnName($request['field']);
                return "SUM(CASE WHEN $col IS NOT NULL AND $col <> '' THEN 1 ELSE 0 END)";
            default:
                return 'COUNT(*)';
        }
    }

    private function buildWhere(): string
    {
        if ($this->filters === null) {
            return '';
        }

        $filters = $this->filters->getFilters();
        $conditions = [];

        if (isset($filters['completed'])) {
            $conditions[] = 'submitdate IS' . ($filters['completed'] ? ' NOT ' : ' ') . 'NULL';
        }

        if (isset($filters['minId'])) {
            $conditions[] = 'id >= ' . (int)$filters['minId'];
        }

        if (isset($filters['maxId'])) {
            $conditions[] = 'id <= ' . (int)$filters['maxId'];
        }

        return $conditions ? (' WHERE ' . implode(' AND ', $conditions)) : '';
    }

    private function getDb(): CDbConnection
    {
        return SurveyDynamic::model($this->surveyId)->getDbConnection();
    }
}
