<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * Resolves a whole filter set: each row to the resolver for its source.
 *
 * The single entry point between the filter model and the columns it means, so
 * responses and statistics can share one translation and differ only in how
 * they turn the result into SQL.
 */
class ResponseFilterResolver
{
    private QuestionResolver $questionResolver;
    private SurveyDataResolver $surveyDataResolver;
    private ParticipantResolver $participantResolver;

    public function __construct(
        QuestionColumnMap $columnMap,
        ParticipantResolver $participantResolver
    ) {
        $this->questionResolver = new QuestionResolver($columnMap);
        $this->surveyDataResolver = new SurveyDataResolver();
        $this->participantResolver = $participantResolver;
    }

    /**
     * @return ResolvedFilter[] In the order they were given, so the joins
     *     between them still fold left to right.
     */
    public function resolve(ResponseFilterSet $filterSet): array
    {
        $resolved = [];

        foreach ($filterSet->all() as $filter) {
            $resolved[] = $this->resolveOne($filter);
        }

        return $resolved;
    }

    private function resolveOne(ResponseFilter $filter): ResolvedFilter
    {
        switch ($filter->getSource()) {
            case ResponseFilter::SOURCE_QUESTION:
                return $this->questionResolver->resolve($filter);
            case ResponseFilter::SOURCE_SURVEY_DATA:
                return $this->surveyDataResolver->resolve($filter);
            case ResponseFilter::SOURCE_PARTICIPANT:
                return $this->participantResolver->resolve($filter);
            default:
                // Unreachable: ResponseFilter rejects unknown sources. Kept so
                // a source added to the contract without a resolver fails here
                // rather than filtering nothing.
                throw new InvalidArgumentException(
                    'No resolver for filter source: ' . $filter->getSource() . '.'
                );
        }
    }
}
