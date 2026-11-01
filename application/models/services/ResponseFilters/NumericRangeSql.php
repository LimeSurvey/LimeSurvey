<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use CDbConnection;

/**
 * SQL reading a response column as a number, shared by the responses range
 * condition and the statistics range condition so both match the same rows.
 */
final class NumericRangeSql
{
    /**
     * Plain decimal test. Numeric answers live in text columns that may hold
     * anything, and '' and NULL both have to fall out of the range rather
     * than become a zero.
     */
    public const PATTERN = '^-?[0-9]*\.?[0-9]+$';

    /**
     * Wide and exact. An unsigned integer would truncate 2.5 to 2 and wrap -3
     * to 18446744073709551613, and is not a cast target on Postgres or MSSQL.
     */
    public const TYPE = 'DECIMAL(30,10)';

    /**
     * The column read as a number, or as nothing when it does not hold one.
     *
     * A cell that cannot be read as a number yields NULL, which makes the
     * comparison unknown and drops the row, rather than MySQL's silent 0
     * which would pull every junk row into any range spanning zero.
     *
     * The guard casts to text first so the expression is valid whether the
     * column is numeric (id, seed) or the varchar that answers are stored in.
     * Testing inside CASE also keeps Postgres from evaluating the cast on a
     * cell that would make it fail.
     *
     * @param string $quotedKey Already quoted column name
     */
    public static function expression(CDbConnection $db, string $quotedKey, ?string $driver = null): string
    {
        $pattern = $db->quoteValue(self::PATTERN);

        switch ($driver ?? $db->getDriverName()) {
            case 'pgsql':
                // Unconstrained NUMERIC: no overflow on long digit strings
                return "(CASE WHEN CAST($quotedKey AS TEXT) ~ $pattern THEN CAST($quotedKey AS NUMERIC) END)";
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                // TRY_CAST already answers with NULL instead of failing.
                return 'TRY_CAST(' . $quotedKey . ' AS ' . self::TYPE . ')';
            default:
                return "(CASE WHEN CAST($quotedKey AS CHAR) REGEXP $pattern THEN CAST($quotedKey AS "
                    . self::TYPE . ') END)';
        }
    }
}
