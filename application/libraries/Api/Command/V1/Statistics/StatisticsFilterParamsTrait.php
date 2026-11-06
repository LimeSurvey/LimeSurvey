<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Statistics;

use InvalidArgumentException;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\ResponseFilters\ParticipantResolver;
use LimeSurvey\Models\Services\ResponseFilters\QuestionColumnMap;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterResolver;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Survey;
use Yii;

/**
 * Reads the Statistics response filters (minId, maxId, completed, search and
 * filterSet) from the request, so every statistics endpoint filters the same
 * way.
 *
 * Expects the using class to hold a StatisticsResponseFilters in $filters and
 * a Permission in $permission.
 */
trait StatisticsFilterParamsTrait
{
    public function getFilters(): StatisticsResponseFilters
    {
        $params = Yii::app()->getRequest()->getQueryParams();
        $filterMap = [
            'minId' => 'setMinId',
            'maxId' => 'setMaxId',
            'completed' => 'setCompleted',
        ];

        foreach ($filterMap as $key => $method) {
            $value = $params[$key] ?? null;
            if ($value !== null) {
                $this->filters->$method(
                    $key === 'completed' ? $value === 'true' : (int)$value
                );
            }
        }

        $search = $params['search'] ?? null;
        if ($search !== null) {
            $this->filters->setSearchTerms(is_array($search) ? $search : [$search]);
        }

        return $this->filters;
    }

    /**
     * The `filterSet` param: the filter as the user built it in the modal,
     * resolved into the response columns it means.
     *
     * @return \LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter[]
     * @throws InvalidArgumentException on a malformed or unresolvable filter,
     *     which run() turns into a bad request rather than a 500.
     * @throws PermissionDeniedException when a participant filter is sent by a
     *     caller who may not read the survey's participants.
     */
    private function resolveFilterSet(Request $request, Survey $survey, string $language): array
    {
        $filterSet = ResponseFilterSet::fromRequestValue($request->getData('filterSet', null));
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
            QuestionColumnMap::fromQuestionFieldMap(
                createFieldMap($survey, 'full', false, false, $language)
            ),
            ParticipantResolver::fromSurvey($survey)
        );

        return $resolver->resolve($filterSet);
    }
}
