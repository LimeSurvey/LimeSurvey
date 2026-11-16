<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * Resolves `source: "surveyData"` rows — the six response-table fields the
 * modal offers, which map straight onto real columns and need no field map.
 *
 * Each field has exactly one control in the UI, so each resolves to exactly one
 * condition, or to none when the user left the value blank:
 *
 *   id, seed                  → number range   (numberMin / numberMax)
 *   submitdate, datestamp     → date range     (dateFrom / dateTo)
 *   completed                 → submitdate IS (NOT) NULL  (included)
 *   startlanguage             → IN (...)       (languages)
 *
 * "Completed" is not a column: a response counts as complete when it has a
 * submit date, which is how the responses grid already derives the flag it
 * shows.
 */
class SurveyDataResolver
{
    /** The column behind the "Included responses" field. */
    private const COMPLETED_COLUMN = ResponseFilter::FIELD_SUBMIT_DATE;

    public function resolve(ResponseFilter $filter): ResolvedFilter
    {
        if ($filter->getSource() !== ResponseFilter::SOURCE_SURVEY_DATA) {
            throw new InvalidArgumentException('SurveyDataResolver only resolves surveyData filters.');
        }

        $field = $filter->getField();

        switch ($field) {
            case ResponseFilter::FIELD_RESPONSE_ID:
            case ResponseFilter::FIELD_SEED:
                $condition = $this->numberRange($field, $filter);
                break;
            case ResponseFilter::FIELD_SUBMIT_DATE:
            case ResponseFilter::FIELD_LAST_ACTION:
                $condition = $this->dateRange($field, $filter);
                break;
            case ResponseFilter::FIELD_COMPLETED:
                $condition = $this->completed($filter);
                break;
            case ResponseFilter::FIELD_LANGUAGE:
                $condition = $this->language($filter);
                break;
            default:
                // Unreachable via ResponseFilter, which rejects unknown fields.
                // Kept so a future field added to the contract fails loudly here
                // instead of silently filtering nothing.
                throw new InvalidArgumentException("No resolver for survey data field: $field.");
        }

        return new ResolvedFilter(
            $filter->getJoin(),
            $condition === null ? [] : [$condition]
        );
    }

    private function numberRange(string $field, ResponseFilter $filter): ?ResolvedCondition
    {
        $min = $filter->getNumberMin();
        $max = $filter->getNumberMax();

        if ($min === null && $max === null) {
            return null;
        }

        return new ResolvedCondition(
            [$field],
            ResolvedCondition::OPERATOR_RANGE,
            [$min ?? '', $max ?? '']
        );
    }

    private function dateRange(string $field, ResponseFilter $filter): ?ResolvedCondition
    {
        $from = $filter->getDateFrom();
        $to = $filter->getDateTo();

        if ($from === null && $to === null) {
            return null;
        }

        return new ResolvedCondition(
            [$field],
            ResolvedCondition::OPERATOR_DATE_RANGE,
            [$from ?? '', $to ?? '']
        );
    }

    /**
     * "All" is the default and means no restriction, so it resolves to nothing
     * rather than to a condition that matches every row.
     */
    private function completed(ResponseFilter $filter): ?ResolvedCondition
    {
        $included = $filter->getIncluded();

        if ($included === ResponseFilter::INCLUDED_ALL) {
            return null;
        }

        return new ResolvedCondition(
            [self::COMPLETED_COLUMN],
            ResolvedCondition::OPERATOR_NULL,
            $included === ResponseFilter::INCLUDED_COMPLETE ? 'true' : 'false'
        );
    }

    private function language(ResponseFilter $filter): ?ResolvedCondition
    {
        $languages = $filter->getLanguages();

        if ($languages === []) {
            return null;
        }

        return new ResolvedCondition(
            [ResponseFilter::FIELD_LANGUAGE],
            ResolvedCondition::OPERATOR_MULTI_SELECT,
            $languages
        );
    }
}
