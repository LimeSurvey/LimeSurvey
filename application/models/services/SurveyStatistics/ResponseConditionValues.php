<?php

namespace LimeSurvey\Models\Services\SurveyStatistics;

use InvalidArgumentException;
use LimeSurvey\Models\Services\ResponseFilters\NumericRangeSql;

/**
 * Validates and normalizes the values of statistics response conditions
 * before they are bound, so a bad value is a 400 and never a database error
 * (which on Postgres and MSSQL would surface as a 500).
 */
final class ResponseConditionValues
{
    public const KIND_INTEGER = 'integer';
    public const KIND_DECIMAL = 'decimal';
    public const KIND_DATE = 'date';
    public const KIND_DATETIME = 'datetime';
    public const KIND_TEXT = 'text';

    private const INT32_MAX = 2147483647;

    /**
     * Columns a condition may filter on, besides question columns and quotaExit.
     * Everything else (ipaddr, refurl, seed, startlanguage, ...) identifies
     * respondents and is refused; token needs the tokens permission.
     */
    public const SAFE_META_COLUMNS = ['id', 'submitdate', 'startdate', 'datestamp', 'lastpage'];

    private const QUESTION_COLUMN_PATTERN = '/^(Q\d+(_[A-Za-z0-9]+)*|\d+X\d+X\d+[A-Za-z0-9_#]*)$/';

    public static function isTokenKey(string $key): bool
    {
        return strtolower(trim($key)) === 'token';
    }

    /**
     * Whether a condition key is an answer column, quotaExit or a safe meta column.
     */
    public static function isAllowedKey(string $key): bool
    {
        return preg_match(self::QUESTION_COLUMN_PATTERN, $key) === 1
            || in_array($key, ['quotaExit', 'quota_exit'], true)
            || in_array(strtolower($key), self::SAFE_META_COLUMNS, true);
    }

    public static function kindOf(string $type, string $dbType): string
    {
        if (preg_match('/^date$/i', $dbType)) {
            return self::KIND_DATE;
        }
        if (preg_match('/^(datetime|timestamp|smalldatetime)/i', $dbType)) {
            return self::KIND_DATETIME;
        }
        if ($type === 'integer') {
            return self::KIND_INTEGER;
        }
        if ($type === 'double' || preg_match('/decimal|numeric|float|double|real|money/i', $dbType)) {
            return self::KIND_DECIMAL;
        }
        return self::KIND_TEXT;
    }

    public static function isBigint(string $dbType): bool
    {
        return preg_match('/bigint|bigserial|int8/i', $dbType) === 1;
    }

    /**
     * A value for an equal / multi-select condition.
     *
     * @param mixed $value
     * @return string The value to bind
     * @throws InvalidArgumentException
     */
    public static function equalValue($value, string $kind, bool $bigint = false): string
    {
        if (is_bool($value) || !(is_scalar($value))) {
            throw new InvalidArgumentException('Condition values must be strings or numbers');
        }
        switch ($kind) {
            case self::KIND_INTEGER:
                return self::integerValue($value, $bigint);
            case self::KIND_DECIMAL:
                return self::decimalValue($value);
            case self::KIND_DATE:
            case self::KIND_DATETIME:
                return self::dateValue($value)[0];
            default:
                return is_float($value) ? self::decimalValue($value) : (string)$value;
        }
    }

    /**
     * The bounds of a range condition. An empty bound (null or '') is open.
     *
     * @param mixed $bounds
     * @return array{0: ?string, 1: ?string, 2: bool} min, max and whether the
     *     max is exclusive (a date-only max on a datetime column becomes the
     *     start of the next day)
     * @throws InvalidArgumentException
     */
    public static function rangeBounds($bounds, string $kind, bool $bigint = false): array
    {
        if (!is_array($bounds) || array_keys($bounds) !== [0, 1]) {
            throw new InvalidArgumentException('A range needs a list of exactly two bounds');
        }
        [$min, $max] = $bounds;
        foreach ([$min, $max] as $bound) {
            if ($bound !== null && (is_bool($bound) || !is_scalar($bound))) {
                throw new InvalidArgumentException('Range bounds must be strings or numbers');
            }
        }
        $min = $min === '' ? null : $min;
        $max = $max === '' ? null : $max;
        if ($min === null && $max === null) {
            throw new InvalidArgumentException('Missing min and max range values');
        }

        $maxExclusive = false;
        if ($kind === self::KIND_DATE || $kind === self::KIND_DATETIME) {
            $min = $min === null ? null : self::dateValue($min)[0];
            if ($max !== null) {
                [$max, $dateOnly] = self::dateValue($max);
                if ($dateOnly && $kind === self::KIND_DATETIME) {
                    $max = date('Y-m-d', strtotime($max . ' +1 day'));
                    $maxExclusive = true;
                }
            }
            return [$min, $max, $maxExclusive];
        }

        $normalize = $kind === self::KIND_INTEGER
            ? static function ($bound) use ($bigint): string {
                return self::integerValue($bound, $bigint);
            }
            : static function ($bound): string {
                return self::decimalValue($bound);
            };

        return [
            $min === null ? null : $normalize($min),
            $max === null ? null : $normalize($max),
            false,
        ];
    }

    /**
     * Y-m-d or Y-m-d H:i:s only, checked as a real calendar date.
     *
     * @param mixed $value
     * @return array{0: string, 1: bool} the value and whether it is date-only
     * @throws InvalidArgumentException
     */
    public static function dateValue($value): array
    {
        if (is_string($value)) {
            foreach (['Y-m-d' => true, 'Y-m-d H:i:s' => false] as $format => $dateOnly) {
                $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
                if ($date !== false && $date->format($format) === $value) {
                    return [$value, $dateOnly];
                }
            }
        }
        throw new InvalidArgumentException('Dates must be valid and formatted Y-m-d or Y-m-d H:i:s');
    }

    /**
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public static function integerValue($value, bool $bigint = false): string
    {
        $string = is_int($value) ? (string)$value : (is_string($value) ? $value : '');
        if (preg_match('/^-?[0-9]{1,19}$/D', $string) !== 1) {
            throw new InvalidArgumentException('Value must be an integer');
        }
        $limit = $bigint ? '9223372036854775807' : (string)self::INT32_MAX;
        $digits = ltrim($string, '-');
        $digits = ltrim($digits, '0') === '' ? '0' : ltrim($digits, '0');
        if (strlen($digits) > strlen($limit) || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) > 0)) {
            throw new InvalidArgumentException('Integer value is out of range');
        }
        return $string;
    }

    /**
     * Keeps the validated numeric string as it is, so long digit strings
     * are not rounded.
     *
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public static function decimalValue($value): string
    {
        if (is_float($value)) {
            if (!is_finite($value) || abs($value) >= 1e20) {
                throw new InvalidArgumentException('Value must be a plain decimal number');
            }
            $value = rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');
        }
        $string = is_int($value) ? (string)$value : (is_string($value) ? $value : '');
        if (
            preg_match('/' . NumericRangeSql::PATTERN . '/D', $string) !== 1
            || strlen($string) > 40
        ) {
            throw new InvalidArgumentException('Value must be a plain decimal number');
        }
        return $string;
    }
}
