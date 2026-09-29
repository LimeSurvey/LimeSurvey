<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use CDbConnection;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Response;
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
    private const SQL_SERVER_NUMERIC_PATTERN = '^\s*[+-]?[0-9]*\.?[0-9]+$';

    /** Separator joining the columns of a multi-field aggregate into one field key */
    private const FIELD_SEPARATOR = "\x1E";

    private int $surveyId;

    /** @var StatisticsResponseFilters|null */
    private $filters;

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

    /** @var array<string, true>|null Response columns whose values are encrypted at rest */
    private ?array $encryptedFields = null;

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
        $where = $this->buildWhere();

        $plainRequests = [];
        $encryptedRequests = [];
        foreach ($this->requests as $alias => $request) {
            if ($this->requestUsesEncryptedField($request)) {
                $encryptedRequests[$alias] = $request;
            } else {
                $plainRequests[$alias] = $request;
            }
        }

        foreach (array_chunk($plainRequests, self::MAX_EXPRESSIONS_PER_QUERY, true) as $chunk) {
            $selects = [];
            foreach ($chunk as $alias => $request) {
                $selects[] = $this->buildExpression($db, $request)
                    . ' AS ' . $db->quoteColumnName($alias);
            }

            $sql = 'SELECT ' . implode(', ', $selects) . ' FROM ' . $table . $where;
            $row = $db->createCommand($sql)->queryRow() ?: [];

            foreach (array_keys($chunk) as $alias) {
                $value = $row[$alias] ?? 0;
                // Counts come back as whole numbers; KIND_SUM may be fractional.
                // "+ 0" yields an int or float, preserving decimal precision.
                $this->results[$alias] = is_numeric($value) ? $value + 0 : 0;
            }
        }

        if ($encryptedRequests !== []) {
            $this->executeEncrypted($db, $table, $where, $encryptedRequests);
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
        foreach ($this->medianRequests as $alias => $request) {
            if ($this->isEncryptedField($request['field'])) {
                // Encrypted medians are calculated during executeEncrypted().
                continue;
            }
            $count = (int)($this->results[$request['countAlias']] ?? 0);
            if ($count > 0) {
                $selects[] = $this->buildMedianSelect($db, $table, $request, $count, $alias);
            } else {
                $this->results[$alias] = 0;
            }
        }

        foreach (array_chunk($selects, self::MAX_MEDIANS_PER_QUERY) as $chunk) {
            $rows = $db->createCommand(implode(' UNION ALL ', $chunk))->queryAll();
            foreach ($rows as $row) {
                $value = $row['median'] ?? 0;
                $this->results[$row['alias']] = is_numeric($value) ? $value + 0 : 0;
            }
        }
    }

    /**
     * Aggregate encrypted response columns in PHP after decrypting each
     * distinct ciphertext. Encryption is deterministic, so grouping in SQL
     * avoids one decrypt per response row while preserving row counts through
     * the group's weight.
     *
     * @param array<string, array{kind: string, field: string, value: string, numeric?: bool}> $requests
     */
    private function executeEncrypted(
        CDbConnection $db,
        string $table,
        string $where,
        array $requests
    ): void {
        $fields = [];
        foreach ($requests as $request) {
            foreach ($this->requestFields($request) as $field) {
                $fields[$field] = true;
            }
        }

        $medianRequests = [];
        foreach ($this->medianRequests as $alias => $request) {
            if ($this->isEncryptedField($request['field'])) {
                $medianRequests[$alias] = $request;
            }
        }

        $results = [];
        foreach ($requests as $alias => $request) {
            $results[$alias] = $this->initialAggregateValue($request);
        }

        $medianValues = array_fill_keys(array_keys($medianRequests), []);
        foreach (array_keys($fields) as $field) {
            $column = $db->quoteColumnName($field);
            $valueAlias = $db->quoteColumnName('encrypted_value');
            $countAlias = $db->quoteColumnName('value_count');
            $sql = "SELECT $column AS $valueAlias, COUNT(*) AS $countAlias"
                . " FROM $table$where GROUP BY $column";
            $reader = $db->createCommand($sql)->query();

            while (($group = $reader->read()) !== false) {
                $value = $group['encrypted_value'] ?? null;
                $weight = (int)($group['value_count'] ?? 0);
                if ($value !== null && $value !== '') {
                    try {
                        $value = Response::decryptSingle($value);
                    } catch (\SodiumException) {
                        continue;
                    }
                }

                $row = [$field => $value];
                foreach ($requests as $alias => $request) {
                    if ($this->requestFields($request)[0] !== $field) {
                        continue;
                    }
                    $results[$alias] = $this->accumulateValue(
                        $results[$alias],
                        $this->valueForRequest($row, $request),
                        $request,
                        $weight
                    );
                }
                foreach ($medianRequests as $alias => $request) {
                    if ($request['field'] !== $field) {
                        continue;
                    }
                    $numericValue = $this->normalizedNumericValue($value, !empty($request['numeric']));
                    if ($numericValue !== null) {
                        $medianValues[$alias][] = ['value' => $numericValue, 'weight' => $weight];
                    }
                }
            }
        }

        foreach ($results as $alias => $result) {
            $this->results[$alias] = $this->finalizeAggregateValue($result);
        }
        foreach ($medianValues as $alias => $values) {
            $this->results[$alias] = $this->weightedMedian($values);
        }
    }

    /**
     * @param array<int, array{value: float, weight: int}> $values
     * @return int|float
     */
    private function weightedMedian(array $values)
    {
        if ($values === []) {
            return 0;
        }

        usort($values, static fn(array $left, array $right): int => $left['value'] <=> $right['value']);
        $count = array_sum(array_column($values, 'weight'));
        $lowerPosition = intdiv($count - 1, 2);
        $upperPosition = intdiv($count, 2);
        $lower = null;
        $cumulative = 0;
        foreach ($values as $entry) {
            $cumulative += $entry['weight'];
            if ($lower === null && $cumulative > $lowerPosition) {
                $lower = $entry['value'];
            }
            if ($cumulative > $upperPosition) {
                return ($lower + $entry['value']) / 2;
            }
        }

        return 0;
    }

    private function valueForRequest(array $row, array $request)
    {
        if ($request['kind'] === self::KIND_ANY_NON_EMPTY) {
            foreach ($this->requestFields($request) as $field) {
                if (($row[$field] ?? null) !== null && ($row[$field] ?? '') !== '') {
                    return true;
                }
            }
            return false;
        }

        if ($request['kind'] === self::KIND_JSON_ELEMENT) {
            [$field, $position] = explode(self::FIELD_SEPARATOR, $request['field']);
            $decoded = json_decode((string)($row[$field] ?? ''), true);
            return is_array($decoded) ? ($decoded[(int)$position] ?? null) : null;
        }

        return $row[$request['field']] ?? null;
    }

    /** @return int|float */
    private function aggregateValues(array $values, array $request)
    {
        $result = $this->initialAggregateValue($request);
        foreach ($values as $value) {
            $result = $this->accumulateValue($result, $value, $request);
        }

        return $this->finalizeAggregateValue($result);
    }

    /** @return int|float|null */
    private function initialAggregateValue(array $request)
    {
        return in_array($request['kind'], [self::KIND_MIN, self::KIND_MAX], true) ? null : 0;
    }

    /** @param int|float|null $result @return int|float|null */
    private function accumulateValue($result, $value, array $request, int $weight = 1)
    {
        switch ($request['kind']) {
            case self::KIND_VALUE:
            case self::KIND_JSON_ELEMENT:
                return $result + ($value !== null && (string)$value === $request['value'] ? $weight : 0);
            case self::KIND_BLANK:
                return $result + ($value === null || (!$request['numeric'] && $value === '') ? $weight : 0);
            case self::KIND_NON_EMPTY:
            case self::KIND_ANY_NON_EMPTY:
                $answered = $request['kind'] === self::KIND_ANY_NON_EMPTY
                    ? $value === true
                    : $value !== null && ($request['numeric'] || $value !== '');
                return $result + ($answered ? $weight : 0);
            case self::KIND_NUMERIC:
                return $result + ($this->isNumericValue($value, !empty($request['numeric'])) ? $weight : 0);
            case self::KIND_SUM:
                $numericValue = $this->normalizedNumericValue($value, !empty($request['numeric']));
                return $result + ($numericValue ?? 0) * $weight;
            case self::KIND_SUM_SQUARES:
                $numericValue = $this->normalizedNumericValue($value, !empty($request['numeric']));
                return $result + ($numericValue === null ? 0 : $numericValue ** 2 * $weight);
            case self::KIND_MIN:
            case self::KIND_MAX:
                $numericValue = $this->normalizedNumericValue($value, !empty($request['numeric']));
                if ($numericValue === null) {
                    return $result;
                }
                return $result === null
                    ? $numericValue
                    : ($request['kind'] === self::KIND_MIN ? min($result, $numericValue) : max($result, $numericValue));
            default:
                return $result + 1;
        }
    }

    /** @param int|float|null $result @return int|float */
    private function finalizeAggregateValue($result)
    {
        return $result === null ? 0 : $result;
    }

    private function isNumericValue($value, bool $numericColumn = false): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return $numericColumn
            ? is_numeric($value)
            : preg_match('/' . $this->numericPattern() . '/', (string)$value) === 1;
    }

    private function numericPattern(): string
    {
        return $this->numericPatternForDriver($this->getDb()->getDriverName());
    }

    private function numericPatternForDriver(string $driverName): string
    {
        switch ($driverName) {
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                return self::SQL_SERVER_NUMERIC_PATTERN;
            default:
                return self::NUMERIC_PATTERN;
        }
    }

    private function normalizedNumericValue($value, bool $numericColumn = false): ?float
    {
        return $this->isNumericValue($value, $numericColumn)
            ? round((float)$value, 4)
            : null;
    }

    private function requestUsesEncryptedField(array $request): bool
    {
        if ($request['kind'] === self::KIND_ANY_NON_EMPTY) {
            return false;
        }

        foreach ($this->requestFields($request) as $field) {
            if ($this->isEncryptedField($field)) {
                return true;
            }
        }
        return false;
    }

    /** @return string[] */
    private function requestFields(array $request): array
    {
        if ($request['kind'] === self::KIND_JSON_ELEMENT) {
            return [explode(self::FIELD_SEPARATOR, $request['field'], 2)[0]];
        }

        return $request['kind'] === self::KIND_ANY_NON_EMPTY
            ? explode(self::FIELD_SEPARATOR, $request['field'])
            : [$request['field']];
    }

    private function isEncryptedField(string $field): bool
    {
        if ($this->encryptedFields === null) {
            $this->encryptedFields = array_fill_keys(Response::getEncryptedAttributes($this->surveyId), true);
        }
        return isset($this->encryptedFields[$field]);
    }

    private function buildMedianSelect(CDbConnection $db, string $table, array $request, int $count, string $alias): string
    {
        $col = $db->quoteColumnName($request['field']);
        $isNumericCell = $this->numericCellCheck($db, $request['field'], !empty($request['numeric']));
        $where = $this->buildWhere();
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

        return 'SELECT ' . $db->quoteValue($alias) . ' AS ' . $db->quoteColumnName('alias')
            . ', AVG(v) AS ' . $db->quoteColumnName('median')
            . ' FROM (' . $inner . ') median_values';
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

        if (!empty($filters['search'])) {
            $conditions = array_merge($conditions, $this->buildSearchConditions($filters['search']));
        }

        return $conditions ? (' WHERE ' . implode(' AND ', $conditions)) : '';
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
