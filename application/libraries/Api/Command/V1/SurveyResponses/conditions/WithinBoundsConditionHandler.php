<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use CDbCriteria;
use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

/**
 * Keeps the responses whose location answer ("lat;lng[;city;...]", as the
 * map question stores it) lies inside a viewport given as
 * {south, west, north, east}. Answers that don't start with two numbers
 * never match.
 */
class WithinBoundsConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    private const EDGES = ['south', 'west', 'north', 'east'];

    /** "lat;lng" prefix: two decimals separated by a semicolon ([.] avoids
     * driver-specific backslash escaping inside the SQL literal) */
    private const LOCATION_PATTERN = '^-?[0-9]+([.][0-9]+)?;-?[0-9]+([.][0-9]+)?(;|$)';

    public function canHandle(string $operation): bool
    {
        return strtolower($operation) === 'withinbounds';
    }

    public function execute($key, $value): object
    {
        if (is_array($key)) {
            throw new InvalidArgumentException('Multiple keys are not supported for withinBounds conditions.');
        }
        $bounds = $this->parseBounds($value);

        $column = $this->sanitizeKey($key);
        $prefix = ':' . $this->stripKey($key) . 'Bounds';
        $latitude = $this->latitudeExpression($column);
        $longitude = $this->longitudeExpression($column);

        $conditions = ["$latitude BETWEEN {$prefix}South AND {$prefix}North"];
        $params = [
            "{$prefix}South" => $bounds['south'],
            "{$prefix}North" => $bounds['north'],
        ];

        if ($bounds['west'] !== null) {
            // A viewport across the antimeridian has west > east.
            $conditions[] = $bounds['west'] <= $bounds['east']
                ? "$longitude BETWEEN {$prefix}West AND {$prefix}East"
                : "($longitude >= {$prefix}West OR $longitude <= {$prefix}East)";
            $params["{$prefix}West"] = $bounds['west'];
            $params["{$prefix}East"] = $bounds['east'];
        }

        $criteria = new CDbCriteria();
        $criteria->condition = '(' . implode(' AND ', $conditions) . ')';
        $criteria->params = $params;

        return $criteria;
    }

    /**
     * Clamps latitude and wraps longitude; a viewport wider than the world
     * (Leaflet reports those when zoomed far out) drops the longitude test.
     *
     * @param mixed $value
     * @return array{south: float, north: float, west: float|null, east: float|null}
     */
    private function parseBounds($value): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('withinBounds needs {south, west, north, east}.');
        }
        foreach (self::EDGES as $edge) {
            if (!isset($value[$edge]) || !is_numeric($value[$edge])) {
                throw new InvalidArgumentException("withinBounds needs a numeric '$edge'.");
            }
        }

        $south = max(-90.0, min(90.0, (float)$value['south']));
        $north = max(-90.0, min(90.0, (float)$value['north']));
        if ($south > $north) {
            throw new InvalidArgumentException('withinBounds south must not exceed north.');
        }

        $wholeWorld = (float)$value['east'] - (float)$value['west'] >= 360;

        return [
            'south' => $south,
            'north' => $north,
            'west' => $wholeWorld ? null : $this->wrapLongitude((float)$value['west']),
            'east' => $wholeWorld ? null : $this->wrapLongitude((float)$value['east']),
        ];
    }

    private function wrapLongitude(float $longitude): float
    {
        return fmod(fmod($longitude + 180, 360) + 360, 360) - 180;
    }

    /**
     * Numeric latitude of a well-formed location answer, NULL otherwise.
     * The CASE guard keeps the cast away from junk values on every driver.
     */
    private function latitudeExpression(string $column): string
    {
        switch ($this->driver()) {
            case 'pgsql':
                return "(CASE WHEN $column ~ '" . self::LOCATION_PATTERN . "'"
                    . " THEN split_part($column, ';', 1)::numeric END)";
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                return "TRY_CAST(CASE WHEN CHARINDEX(';', $column) > 1"
                    . " THEN LEFT($column, CHARINDEX(';', $column) - 1) END AS DECIMAL(12, 6))";
            default:
                return "(CASE WHEN $column REGEXP '" . self::LOCATION_PATTERN . "'"
                    . " THEN CAST(SUBSTRING_INDEX($column, ';', 1) AS DECIMAL(12, 6)) END)";
        }
    }

    private function longitudeExpression(string $column): string
    {
        switch ($this->driver()) {
            case 'pgsql':
                return "(CASE WHEN $column ~ '" . self::LOCATION_PATTERN . "'"
                    . " THEN split_part($column, ';', 2)::numeric END)";
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                // Text after the first ';', cut at the next one when present.
                $rest = "SUBSTRING($column, CHARINDEX(';', $column) + 1, LEN($column))";
                return "TRY_CAST(CASE WHEN CHARINDEX(';', $column) > 1 THEN"
                    . " (CASE WHEN CHARINDEX(';', $rest) > 0"
                    . " THEN LEFT($rest, CHARINDEX(';', $rest) - 1) ELSE $rest END)"
                    . " END AS DECIMAL(12, 6))";
            default:
                return "(CASE WHEN $column REGEXP '" . self::LOCATION_PATTERN . "'"
                    . " THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX($column, ';', 2), ';', -1) AS DECIMAL(12, 6)) END)";
        }
    }

    private function driver(): string
    {
        return App()->db->getDriverName();
    }
}
