<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use Permission;
use Survey;

/**
 * Resolves the filter a user built in the filter modal into the response
 * columns it means, the same way for the responses list, the export and the
 * statistics.
 */
class ResponseFilterSetService
{
    private Permission $permission;

    /**
     * @param Permission $permission Checks that a participant filter may be used
     */
    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    /**
     * Resolve the filter against the survey's field map.
     *
     * @param ResponseFilterSet $filterSet The filter as the user built it
     * @param Survey $survey The survey the responses belong to
     * @param callable(): array $questionFieldMap Builds the survey's field map,
     *     in the language the filter's answer options are given in; only
     *     called when there is a filter to resolve
     * @return ResolvedFilter[] Empty when there is no filter
     * @throws InvalidArgumentException on a malformed or unresolvable filter
     * @throws PermissionDeniedException when a participant filter is sent by a
     *     user who may not read the survey's participants
     */
    public function resolve(ResponseFilterSet $filterSet, Survey $survey, callable $questionFieldMap): array
    {
        if ($filterSet->isEmpty()) {
            return [];
        }

        if (
            $filterSet->hasParticipantFilter()
            && !$this->permission->hasSurveyPermission((int)$survey->sid, 'tokens')
        ) {
            throw new PermissionDeniedException();
        }

        $resolver = new ResponseFilterResolver(
            QuestionColumnMap::fromQuestionFieldMap($questionFieldMap()),
            ParticipantResolver::fromSurvey($survey)
        );

        return $resolver->resolve($filterSet);
    }
}
