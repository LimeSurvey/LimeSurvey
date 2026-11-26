<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\Sorting;

/**
 * SQL snippets for sorting the responses table, shared by the metadata columns
 * and the question type sorters.
 *
 * Every expression refers to the responses table by the alias the find command
 * gives it ("t"), so it stays valid when filters add subqueries.
 *
 * Values that are missing sort after the real ones in ascending order: first
 * the empty strings (a question shown but not answered, or its "No answer"
 * option), then NULL (a question not shown or not reached). Descending order
 * flips the whole order, so they come first there.
 */
final class SortSql
{
    /*
     * Value types: They decide how a column is sorted and which missing values it can hold.
     */
    /** The response ID itself: Never missing */
    public const TYPE_ID = 'id';
    /** Numbers and dates: Only NULL can be missing */
    public const TYPE_NUMBER_OR_DATE = 'numberOrDate';
    /** Text: Empty strings and NULL can be missing */
    public const TYPE_TEXT = 'text';
    /** Digits stored as text (e.g. the seed): Sorted by their numeric value */
    public const TYPE_DIGITS_AS_TEXT = 'digitsAsText';

    /** Alias of the responses table in the find command */
    private const TABLE_ALIAS = 't';

    /**
     * Quote a column of the responses table, qualified with the table alias.
     *
     * @param string $column Column name in the responses table
     * @return string Quoted, qualified column for use in SQL
     */
    public static function column(string $column): string
    {
        $db = \Yii::app()->db;
        return $db->quoteTableName(self::TABLE_ALIAS) . '.' . $db->quoteColumnName($column);
    }

    /**
     * SQL keyword for a sort direction.
     *
     * @param bool $descending True for descending order
     * @return string "ASC" or "DESC"
     */
    public static function direction(bool $descending): string
    {
        return $descending ? 'DESC' : 'ASC';
    }

    /**
     * Sort part that puts missing values after the real ones: Empty strings, then NULL.
     *
     * @param string $expression SQL expression of the sorted value
     * @param bool $descending True for descending order, which flips the groups too
     * @param bool $canBeEmpty False for numeric and date columns, which can't hold empty strings
     * @return string ORDER BY part
     */
    public static function missingLast(string $expression, bool $descending, bool $canBeEmpty = true): string
    {
        $emptyCase = $canBeEmpty ? " WHEN $expression = '' THEN 1" : '';
        return "CASE WHEN $expression IS NULL THEN 2$emptyCase ELSE 0 END " . self::direction($descending);
    }

    /**
     * Tie-breaker added after every sort: Newest response first, in both directions.
     *
     * @return string ORDER BY part
     */
    public static function newestFirst(): string
    {
        return self::column('id') . ' DESC';
    }

    /**
     * SQL length of a text expression, for sorting digits stored as text by their value.
     *
     * @param string $expression SQL text expression
     * @return string SQL expression returning the length
     */
    public static function length(string $expression): string
    {
        $driver = \Yii::app()->db->getDriverName();
        $function = in_array($driver, ['sqlsrv', 'dblib', 'mssql'], true) ? 'LEN' : 'LENGTH';
        return "$function($expression)";
    }

    /**
     * CASE expression mapping stored values to their rank, for orders that differ
     * from the order of the stored values (e.g. answer codes sorted by answer text).
     *
     * @param string $expression SQL expression of the stored value
     * @param array<string|int, int> $ranks Stored value => rank, at least one entry
     * @param int $otherwise Rank of the values that aren't in the list
     * @return string SQL expression returning the rank
     * @throws \InvalidArgumentException If no ranks are given
     */
    public static function rank(string $expression, array $ranks, int $otherwise): string
    {
        if (empty($ranks)) {
            throw new \InvalidArgumentException('A rank expression needs at least one value');
        }
        $db = \Yii::app()->db;
        $cases = '';
        foreach ($ranks as $value => $rank) {
            // Numeric codes become integer keys: Compare them as the strings they are stored as
            $cases .= ' WHEN ' . $db->quoteValue((string) $value) . ' THEN ' . (int) $rank;
        }
        return "CASE $expression$cases ELSE $otherwise END";
    }

    /**
     * ORDER BY parts for an expression of a given value type, without the tie-breaker.
     *
     * @param string $expression SQL expression of the sorted value
     * @param string $type One of the TYPE_* constants
     * @param bool $descending True for descending order
     * @return string[] ORDER BY parts
     * @throws \InvalidArgumentException If the type is unknown
     */
    public static function forType(string $expression, string $type, bool $descending): array
    {
        $direction = self::direction($descending);
        switch ($type) {
            case self::TYPE_ID:
                return ["$expression $direction"];
            case self::TYPE_NUMBER_OR_DATE:
                return [self::missingLast($expression, $descending, false), "$expression $direction"];
            case self::TYPE_DIGITS_AS_TEXT:
                return [
                    self::missingLast($expression, $descending),
                    self::length($expression) . " $direction",
                    "$expression $direction",
                ];
            case self::TYPE_TEXT:
                return [self::missingLast($expression, $descending), "LOWER($expression) $direction"];
            default:
                throw new \InvalidArgumentException("Unknown value type $type");
        }
    }
}
