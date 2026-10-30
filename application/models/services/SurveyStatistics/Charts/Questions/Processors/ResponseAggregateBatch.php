<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use CDbColumnSchema;
use CDbConnection;
use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSqlBuilder;
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
 * 
 *  @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class ResponseAggregateBatch
{
    /** Hard cap of SELECT expressions per query to bound per-row CASE cost */
    public const MAX_EXPRESSIONS_PER_QUERY = 1500;

    private const MAX_MEDIANS_PER_QUERY = 100;
    private const MAX_PARAMS_PER_QUERY = 2000;

    private const KIND_VALUE = 'value';
    private const KIND_JSON_ELEMENT = 'jsonElement';
    private const KIND_BLANK = 'blank';
    private const KIND_NON_EMPTY = 'nonEmpty';
    private const KIND_ANY_NON_EMPTY = 'anyNonEmpty';
    private const KIND_NUMERIC = 'numeric';
    private const KIND_TOTAL = 'total';
    private const KIND_SUM = 'sum';
    private const KIND_SUM_SQUARES = 'sumSquares';
    private const KIND_MIN = 'min';
    private const KIND_MAX = 'max';

    /** Plain decimal test for numeric answers stored in text columns */
    private const NUMERIC_PATTERN = '^-?[0-9]*\.?[0-9]+$';

    /** Separator joining the columns of a multi-field aggregate into one field key */
    private const FIELD_SEPARATOR = "\x1E";

    private int $surveyId;

    /** @var StatisticsResponseFilters|null */
    private $filters;

    /** @var ResolvedFilter[] The filter the user built in the modal, resolved. */
    private array $resolvedFilters;

    private ResponseFilterSqlBuilder $filterSqlBuilder;

    /** @var array<string, array{kind: string, field: string, value: string, numeric?: bool}> alias => request */
    private array $requests = [];

    /**
     * Median cannot be expressed as a one-scan conditional aggregate, so these
     * run as one ordered LIMIT/OFFSET query per field after the main pass.
     *
     * @var array<string, array{field: string, countAlias: string, numeric?: bool}> alias => request
     */
    private array $medianRequests = [];

    /** @var array<string, string> dedup key => alias */
    private array $aliasIndex = [];

    /** @var array<string, int|float> alias => count, or fractional sum for KIND_SUM */
    private array $results = [];

    private bool $executed = false;

    /**
     * @param ResolvedFilter[] $resolvedFilters
     */
    public function __construct(
        int $surveyId,
        ?StatisticsResponseFilters $filters = null,
        array $resolvedFilters = []
    ) {
        $this->surveyId = $surveyId;
        $this->filters = $filters;
        $this->resolvedFilters = $resolvedFilters;
        $this->filterSqlBuilder = new ResponseFilterSqlBuilder($surveyId);
    }

    /**
     * Count of rows where the column equals the given value.
     */
    public function countValue(string $field, string $value): string
    {
        return $this->register(self::KIND_VALUE, $field, $value);
    }

    /**
     * Count of rows where the JSON-array column holds the given value at the
     * given zero-based position (rankings store the whole answer as one JSON
     * array of codes, the element index being the rank).
     */
    public function countJsonArrayValue(string $field, int $position, string $value): string
    {
        return $this->register(
            self::KIND_JSON_ELEMENT,
            $field . self::FIELD_SEPARATOR . $position,
            $value
        );
    }

    /**
     * Count of rows where the column is NULL or empty.
     */
    public function countBlank(string $field, bool $numericColumn = false): string
    {
        return $this->register(self::KIND_BLANK, $field, '', $numericColumn);
    }

    /**
     * Count of rows where the column is neither NULL nor empty.
     */
    public function countNonEmpty(string $field, bool $numericColumn = false): string
    {
        return $this->register(self::KIND_NON_EMPTY, $field, '', $numericColumn);
    }

    /**
     * Count of rows where at least one of the given columns is neither NULL
     * nor empty
     *
     * @param string[] $fields
     */
    public function countAnyNonEmpty(array $fields): string
    {
        return $this->register(self::KIND_ANY_NON_EMPTY, implode(self::FIELD_SEPARATOR, $fields), '');
    }

    /**
     * Smallest numeric value in a column (empty cells ignored).
     */
    public function minValue(string $field, bool $numericColumn = false): string
    {
        return $this->register(self::KIND_MIN, $field, '', $numericColumn);
    }

    /**
     * Largest numeric value in a column (empty cells ignored).
     */
    public function maxValue(string $field, bool $numericColumn = false): string
    {
        return $this->register(self::KIND_MAX, $field, '', $numericColumn);
    }

    /**
     * Count of rows whose cell holds a numeric value. For numeric columns this
     * equals countNonEmpty(); for text columns non-numeric cells are excluded.
     * Use as the denominator of mean/variance so junk cells cannot skew them.
     */
    public function countNumeric(string $field, bool $numericColumn = false): string
    {
        return $this->register(self::KIND_NUMERIC, $field, '', $numericColumn);
    }

    public function medianValue(string $field, bool $numericColumn = false): string
    {
        $key = 'median' . "\x1F" . $field . "\x1F" . ($numericColumn ? 'n' : '');
        if (isset($this->aliasIndex[$key])) {
            return $this->aliasIndex[$key];
        }

        $alias = 'm' . count($this->medianRequests);
        $this->aliasIndex[$key] = $alias;
        $this->medianRequests[$alias] = [
            'field' => $field,
            'numeric' => $numericColumn,
            'countAlias' => $this->countNumeric($field, $numericColumn),
        ];

        return $alias;
    }

    /**
     * Total row count (with the response filters applied).
     */
    public function countTotal(): string
    {
        return $this->register(self::KIND_TOTAL, '', '');
    }

    /**
     * Sum of the numeric values in a column (non-numeric/empty cells count as
     * 0). Combined with countNonEmpty() this yields a column mean. The result
     * preserves up to 4 decimal places, so fractional answers (e.g. 1.5) are
     * not lost.
     */
    public function sumValues(string $field, bool $numericColumn = false): string
    {
        return $this->register(self::KIND_SUM, $field, '', $numericColumn);
    }

    /**
     * Sum of the squared numeric values in a column (non-numeric/empty cells
     * count as 0). Combined with sumValues() and countNonEmpty() this yields
     * the population variance/standard deviation without a second scan.
     */
    public function sumSquares(string $field, bool $numericColumn = false): string
    {
        return $this->register(self::KIND_SUM_SQUARES, $field, '', $numericColumn);
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
        [$where, $whereParams] = $this->buildWhere();

        foreach (array_chunk($this->requests, self::MAX_EXPRESSIONS_PER_QUERY, true) as $chunk) {
            $selects = [];
            foreach ($chunk as $alias => $request) {
                $selects[] = $this->buildExpression($db, $request)
                    . ' AS ' . $db->quoteColumnName($alias);
            }

            $sql = 'SELECT ' . implode(', ', $selects) . ' FROM ' . $table . $where;
            $row = $db->createCommand($sql)->queryRow(true, $whereParams) ?: [];

            foreach (array_keys($chunk) as $alias) {
                $value = $row[$alias] ?? 0;
                // Counts come back as whole numbers; KIND_SUM may be fractional.
                // "+ 0" yields an int or float, preserving decimal precision.
                $this->results[$alias] = is_numeric($value) ? $value + 0 : 0;
            }
        }

        $this->executeMedians($db, $table);

        $this->executed = true;
    }

    /**
     * Median via an ordered LIMIT/OFFSET sub-select: the middle value, or the
     * mean of the two middle values for even counts. There is no portable
     * single-scan SQL median, but the per-field sub-selects are merged into
     * chunked UNION ALL statements so resolving N fields (e.g. every cell of
     * an array question) does not cost N round trips.
     */
    private function executeMedians(CDbConnection $db, string $table): void
    {
        $selects = [];
        $params = [];
        foreach ($this->medianRequests as $alias => $request) {
            $count = (int)($this->results[$request['countAlias']] ?? 0);
            if ($count > 0) {
                [$select, $selectParams] = $this->buildMedianSelect($db, $table, $request, $count, $alias);
                $selects[] = $select;
                $params[] = $selectParams;
            } else {
                $this->results[$alias] = 0;
            }
        }

        $paramsPerSelect = count($params[0] ?? []);
        foreach (array_chunk($selects, self::medianChunkSize($paramsPerSelect), true) as $chunk) {
            $chunkParams = [];
            foreach (array_keys($chunk) as $index) {
                $chunkParams += $params[$index];
            }

            $rows = $db->createCommand(implode(' UNION ALL ', $chunk))->queryAll(true, $chunkParams);
            foreach ($rows as $row) {
                $value = $row['median'] ?? 0;
                $this->results[$row['alias']] = is_numeric($value) ? $value + 0 : 0;
            }
        }
    }

    private static function medianChunkSize(int $paramsPerSelect): int
    {
        return $paramsPerSelect === 0
            ? self::MAX_MEDIANS_PER_QUERY
            : max(1, min(self::MAX_MEDIANS_PER_QUERY, intdiv(self::MAX_PARAMS_PER_QUERY, $paramsPerSelect)));
    }

    /**
     * @return array{0: string, 1: array<string,mixed>}
     */
    private function buildMedianSelect(CDbConnection $db, string $table, array $request, int $count, string $alias): array
    {
        $col = $db->quoteColumnName($request['field']);
        $isNumericCell = $this->numericCellCheck($db, $request['field'], !empty($request['numeric']));
        [$where, $whereParams] = $this->buildWhere('_' . $alias);
        $where = $where === '' ? " WHERE $isNumericCell" : "$where AND $isNumericCell";

        $skip = intdiv($count - 1, 2);
        $take = ($count % 2 === 0) ? 2 : 1;

        // applyLimit() renders the limit/offset in the connection's dialect
        // (LIMIT/OFFSET on MySQL/Postgres, TOP / OFFSET-FETCH on SQL Server).
        $inner = $db->getCommandBuilder()->applyLimit(
            "SELECT CAST($col AS DECIMAL(30, 4)) AS v FROM $table$where ORDER BY v",
            $take,
            $skip
        );

        return [
            'SELECT ' . $db->quoteValue($alias) . ' AS ' . $db->quoteColumnName('alias')
                . ', AVG(v) AS ' . $db->quoteColumnName('median')
                . ' FROM (' . $inner . ') median_values',
            $whereParams,
        ];
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    /**
     * Resolved value for a previously registered alias (0 before execute()).
     * Counts are integers; KIND_SUM aggregates may be fractional.
     *
     * @return int|float
     */
    public function value(string $alias)
    {
        return $this->results[$alias] ?? 0;
    }

    private function register(string $kind, string $field, string $value, bool $numeric = false): string
    {
        $key = $kind . "\x1F" . $field . "\x1F" . $value . "\x1F" . ($numeric ? 'n' : '');
        if (isset($this->aliasIndex[$key])) {
            return $this->aliasIndex[$key];
        }

        $alias = 'a' . count($this->requests);
        $this->aliasIndex[$key] = $alias;
        $this->requests[$alias] = ['kind' => $kind, 'field' => $field, 'value' => $value, 'numeric' => $numeric];

        return $alias;
    }

    private function buildExpression(CDbConnection $db, array $request): string
    {
        $numeric = !empty($request['numeric']);
        switch ($request['kind']) {
            case self::KIND_VALUE:
                $col = $db->quoteColumnName($request['field']);
                return 'SUM(CASE WHEN ' . $col . ' = ' . $db->quoteValue($request['value'])
                    . ' THEN 1 ELSE 0 END)';
            case self::KIND_JSON_ELEMENT:
                [$field, $position] = explode(self::FIELD_SEPARATOR, $request['field']);
                return 'SUM(CASE WHEN '
                    . $this->jsonArrayElementCheck($db, $field, (int)$position, $request['value'])
                    . ' THEN 1 ELSE 0 END)';
            case self::KIND_BLANK:
                $col = $db->quoteColumnName($request['field']);
                return $numeric
                    ? "SUM(CASE WHEN $col IS NULL THEN 1 ELSE 0 END)"
                    : "SUM(CASE WHEN $col IS NULL OR $col = '' THEN 1 ELSE 0 END)";
            case self::KIND_NON_EMPTY:
                return 'SUM(CASE WHEN ' . $this->nonEmptyCheck($db, $request['field'], $numeric)
                    . ' THEN 1 ELSE 0 END)';
            case self::KIND_ANY_NON_EMPTY:
                $checks = array_map(
                    static function (string $field) use ($db): string {
                        $col = $db->quoteColumnName($field);
                        return "($col IS NOT NULL AND $col <> '')";
                    },
                    explode(self::FIELD_SEPARATOR, $request['field'])
                );
                return 'SUM(CASE WHEN ' . implode(' OR ', $checks) . ' THEN 1 ELSE 0 END)';
            case self::KIND_NUMERIC:
                return 'SUM(CASE WHEN ' . $this->numericCellCheck($db, $request['field'], $numeric)
                    . ' THEN 1 ELSE 0 END)';
            case self::KIND_SUM:
                $col = $db->quoteColumnName($request['field']);
                return 'SUM(CASE WHEN ' . $this->numericCellCheck($db, $request['field'], $numeric)
                    . " THEN CAST($col AS DECIMAL(30, 4)) ELSE 0 END)";
            case self::KIND_SUM_SQUARES:
                $col = $db->quoteColumnName($request['field']);
                $cast = "CAST($col AS DECIMAL(30, 4))";
                return 'SUM(CASE WHEN ' . $this->numericCellCheck($db, $request['field'], $numeric)
                    . " THEN $cast * $cast ELSE 0 END)";
            case self::KIND_MIN:
            case self::KIND_MAX:
                // No ELSE: empty cells yield NULL, which MIN/MAX ignore.
                $fn = $request['kind'] === self::KIND_MIN ? 'MIN' : 'MAX';
                $col = $db->quoteColumnName($request['field']);
                return "$fn(CASE WHEN " . $this->numericCellCheck($db, $request['field'], $numeric)
                    . " THEN CAST($col AS DECIMAL(30, 4)) END)";
            default:
                return 'COUNT(*)';
        }
    }

    /**
     * Predicate testing the element at a zero-based position of a JSON-array
     * column against a value. Extracting in SQL keeps per-rank counts inside
     * the shared single scan instead of pulling every row into PHP. The
     * merged query must survive cells that are not valid JSON (encrypted
     * rankings live in plain text columns): MySQL/MSSQL guard per row with
     * JSON_VALID()/ISJSON(); Postgres only gets the ->> operator when the
     * column really is json/jsonb, because on a text column the operator
     * itself is a hard SQL error.
     */
    private function jsonArrayElementCheck(CDbConnection $db, string $field, int $position, string $value): string
    {
        $col = $db->quoteColumnName($field);
        $quoted = $db->quoteValue($value);

        switch ($db->getDriverName()) {
            case 'pgsql':
                if (!$this->isJsonColumn($field)) {
                    return '1=0';
                }
                return "($col ->> $position) = $quoted";
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                $path = $db->quoteValue('$[' . $position . ']');
                return "(ISJSON($col) = 1 AND JSON_VALUE($col, $path) = $quoted)";
            default:
                $path = $db->quoteValue('$[' . $position . ']');
                return "(JSON_VALID($col) AND JSON_UNQUOTE(JSON_EXTRACT($col, $path)) = $quoted)";
        }
    }

    private function isJsonColumn(string $field): bool
    {
        $column = SurveyDynamic::model($this->surveyId)->getTableSchema()->getColumn($field);
        return $column !== null && preg_match('/^jsonb?$/i', (string)$column->dbType) === 1;
    }

    /**
     * Answered-cell predicate for a column. Numeric response columns (e.g.
     * numerical input's DECIMAL column) must not be compared to '': Postgres
     * rejects the comparison outright and MySQL coerces '' to 0, which would
     * misclassify legitimate 0 answers as blank — for those, blank is NULL.
     */
    private function nonEmptyCheck(CDbConnection $db, string $field, bool $numeric): string
    {
        $col = $db->quoteColumnName($field);
        return $numeric
            ? "$col IS NOT NULL"
            : "$col IS NOT NULL AND $col <> ''";
    }

    /**
     * Castable-cell predicate guarding every CAST(... AS DECIMAL). Numeric
     * answers of array questions live in text columns which may hold
     * non-numeric junk (API-submitted or legacy data); casting that fails the
     * whole query on Postgres and coerces to 0 (skewing sums) on MySQL, so
     * text columns get a per-driver numeric test.
     */
    private function numericCellCheck(CDbConnection $db, string $field, bool $numeric): string
    {
        $col = $db->quoteColumnName($field);
        if ($numeric) {
            return "$col IS NOT NULL";
        }

        switch ($db->getDriverName()) {
            case 'pgsql':
                $test = "$col ~ " . $db->quoteValue(self::NUMERIC_PATTERN);
                break;
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                $test = "TRY_CAST($col AS DECIMAL(30, 4)) IS NOT NULL";
                break;
            default:
                $test = "$col REGEXP " . $db->quoteValue(self::NUMERIC_PATTERN);
                break;
        }

        return "($col IS NOT NULL AND $col <> '' AND $test)";
    }

    /**
     * The two filter params combine with AND: each is already a self-contained
     * condition, and the legacy one keeps its meaning.
     *
     * @return array{0: string, 1: array<string,mixed>} Clause, and the values
     *     the caller has to bind. The suffix keeps placeholders unique when
     *     several selects are merged into one statement.
     */
    private function buildWhere(string $suffix = ''): array
    {
        $conditions = $this->legacyConditions();
        $params = [];

        $resolved = $this->filterSqlBuilder->build($this->resolvedFilters);
        if ($resolved['condition'] !== '') {
            $conditions[] = '(' . $resolved['condition'] . ')';
            $params = $resolved['params'];
        }

        if ($this->filters !== null && !empty($this->filters->getFilters()['conditions'])) {
            [$responseConditions, $conditionParams] = $this->buildResponseConditions(
                $this->filters->getFilters()['conditions'],
                $suffix
            );
            $conditions = array_merge($conditions, $responseConditions);
            $params += $conditionParams;
        }

        return [$conditions ? (' WHERE ' . implode(' AND ', $conditions)) : '', $params];
    }

    /**
     * The statistics sidebar's own filters, which inline their values.
     *
     * @return string[]
     */
    private function legacyConditions(): array
    {
        if ($this->filters === null) {
            return [];
        }

        $filters = $this->filters->getFilters();
        $conditions = [];

        if (isset($filters['completed'])) {
            $conditions[] = 'submitdate IS' . ($filters['completed'] ? ' NOT ' : ' ') . 'NULL';
        }
        $completionCondition = $this->buildCompletionCondition($filters);
        if ($completionCondition !== null) {
            $conditions[] = $completionCondition;
        }

        if (isset($filters['minId'])) {
            $conditions[] = 'id >= ' . (int)$filters['minId'];
        }

        if (isset($filters['maxId'])) {
            $conditions[] = 'id <= ' . (int)$filters['maxId'];
        }

        if (!empty($filters['search'])) {
            $conditions = array_merge($conditions, $this->buildSearchConditions($filters['search']));
        }

        return $conditions;
    }

    private function buildCompletionCondition(array $filters): ?string
    {
        if (!isset($filters['completion'])) {
            return null;
        }
        if ($filters['completion'] === 'screened_out') {
            if (!isset(SurveyDynamic::model($this->surveyId)->getTableSchema()->columns['quota_exit'])) {
                throw new InvalidArgumentException('This survey has no quota exit column');
            }
            return 'quota_exit IS NOT NULL AND quota_exit <> 0';
        }
        return 'submitdate IS ' . ($filters['completion'] === 'completed' ? 'NOT ' : '') . 'NULL';
    }

    /**
     * @return array{0: string[], 1: array<string,mixed>}
     */
    private function buildResponseConditions(array $filters, string $suffix = ''): array
    {
        $schema = SurveyDynamic::model($this->surveyId)->getTableSchema();
        $conditions = [];
        $params = [];
        foreach ($filters as $index => $filter) {
            $field = $filter['key'] === 'quotaExit' ? 'quota_exit' : $filter['key'];
            if (!isset($schema->columns[$field])) {
                throw new InvalidArgumentException('Unknown response column: ' . $field);
            }
            $col = $this->getDb()->quoteColumnName($field);
            if ($filter['filterMethod'] === 'range') {
                [$rangeCondition, $rangeParams] = $this->buildRangeCondition(
                    $col,
                    $schema->columns[$field],
                    array_values($filter['value']),
                    ':condition' . $suffix . '_' . $index . '_'
                );
                $conditions[] = $rangeCondition;
                $params += $rangeParams;
                continue;
            }
            $values = $filter['filterMethod'] === 'equal' ? [$filter['value']] : array_values($filter['value']);
            $holders = [];
            foreach ($values as $position => $value) {
                $holder = ':condition' . $suffix . '_' . $index . '_' . $position;
                $holders[] = $holder;
                $params[$holder] = $value;
            }
            switch ($filter['filterMethod']) {
                case 'equal':
                    $conditions[] = "$col = $holders[0]";
                    break;
                case 'multi-select':
                    $conditions[] = "$col IN (" . implode(', ', $holders) . ')';
                    break;
            }
        }
        return [$conditions, $params];
    }

    /**
     * Range predicate matching RangeConditionHandler: an empty bound leaves
     * that side open, so it is never bound into BETWEEN.
     *
     * Date/datetime columns compare directly against the bound strings,
     * integer columns compare directly as numbers, and anything else (numeric
     * answers stored as text) goes through rangeNumericExpression().
     *
     * @param array $bounds [min, max]
     * @return array{0: string, 1: array<string,float|string>}
     */
    private function buildRangeCondition(string $col, CDbColumnSchema $column, array $bounds, string $holderPrefix): array
    {
        $dateColumn = preg_match('/^(date|datetime|timestamp)/i', (string)$column->dbType) === 1;

        [$min, $max] = array_map(static function ($bound) use ($dateColumn) {
            if ($bound === null || $bound === '') {
                return null;
            }
            if ($dateColumn) {
                if (strtotime((string)$bound) === false) {
                    throw new InvalidArgumentException('Range bounds must be valid dates');
                }
                return (string)$bound;
            }
            if (!is_numeric($bound)) {
                throw new InvalidArgumentException('Range bounds must be numeric');
            }
            return (float)$bound;
        }, [$bounds[0] ?? null, $bounds[1] ?? null]);

        if ($min === null && $max === null) {
            throw new InvalidArgumentException('Missing min and max range values');
        }

        $number = $dateColumn || $column->type === 'integer' ? $col : $this->rangeNumericExpression($col);
        $minHolder = $holderPrefix . '0';
        $maxHolder = $holderPrefix . '1';

        if ($min === null) {
            return ["$number <= $maxHolder", [$maxHolder => $max]];
        }
        if ($max === null) {
            return ["$number >= $minHolder", [$minHolder => $min]];
        }
        return ["$number BETWEEN $minHolder AND $maxHolder", [$minHolder => $min, $maxHolder => $max]];
    }

    /**
     * Column read as a number, NULL when the cell is not numeric. Mirrors
     * RangeConditionHandler so statistics and response filters match the
     * same rows.
     */
    private function rangeNumericExpression(string $col): string
    {
        $db = $this->getDb();
        $pattern = $db->quoteValue(self::NUMERIC_PATTERN);
        $type = 'DECIMAL(30,10)';

        switch ($db->getDriverName()) {
            case 'pgsql':
                // Unconstrained NUMERIC: DECIMAL(30,10) raises "numeric field overflow" on long digit strings
                return "(CASE WHEN CAST($col AS TEXT) ~ $pattern THEN CAST($col AS NUMERIC) END)";
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                return "TRY_CAST($col AS $type)";
            default:
                return "(CASE WHEN CAST($col AS CHAR) REGEXP $pattern THEN CAST($col AS $type) END)";
        }
    }

    /**
     * One condition group per search term: the term must appear
     * (case-insensitively) in at least one free-text answer column; the terms
     * themselves combine with AND. A survey without any text answer column
     * cannot match a term, so the whole filter becomes FALSE.
     *
     * @param string[] $terms
     * @return string[]
     */
    private function buildSearchConditions(array $terms): array
    {
        $db = $this->getDb();
        $columns = $this->getSearchableColumns();
        if (empty($columns)) {
            return ['1=0'];
        }

        $conditions = [];
        // Backslash is the escape char used above for %, _ and \; declare it
        // explicitly so LIKE escapes consistently across drivers (some have no
        // default ESCAPE).
        $escapeClause = ' ESCAPE ' . $db->quoteValue('\\');
        foreach ($terms as $term) {
            $escaped = strtr(mb_strtolower((string)$term), ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $pattern = $db->quoteValue('%' . $escaped . '%');

            $likes = [];
            foreach ($columns as $column) {
                $likes[] = 'LOWER(' . $db->quoteColumnName($column) . ') LIKE ' . $pattern . $escapeClause;
            }

            $conditions[] = '(' . implode(' OR ', $likes) . ')';
        }

        return $conditions;
    }

    /**
     * @return string[]
     */
    private function getSearchableColumns(): array
    {
        $metaColumns = [
            'id', 'token', 'submitdate', 'lastpage', 'startlanguage',
            'seed', 'startdate', 'datestamp', 'ipaddr', 'refurl',
        ];

        $columns = [];
        foreach (SurveyDynamic::model($this->surveyId)->getTableSchema()->columns as $name => $column) {
            if (in_array(strtolower((string)$name), $metaColumns, true)) {
                continue;
            }
            if (!preg_match('/char|text/i', (string)$column->dbType)) {
                continue;
            }
            $columns[] = (string)$name;
        }

        return $columns;
    }

    private function getDb(): CDbConnection
    {
        return SurveyDynamic::model($this->surveyId)->getDbConnection();
    }
}
