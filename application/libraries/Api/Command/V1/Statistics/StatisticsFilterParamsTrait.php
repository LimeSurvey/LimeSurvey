<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Statistics;

use InvalidArgumentException;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\ResponseFilters\ResponseFilterSet;
use LimeSurvey\Models\Services\SurveyStatistics\ResponseConditionValues;
use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Survey;

/**
 * Reads the Statistics response filters (minId, maxId, completed, search,
 * conditions, completion and filterSet) from the request, so every statistics
 * endpoint filters the same way.
 *
 * Expects the using class to hold a StatisticsResponseFilters in $filters, a
 * ResponseFilterSetService in $filterSetService and a Permission in $permission.
 */
trait StatisticsFilterParamsTrait
{
    /**
     * The minId, maxId, completed, search, conditions and completion params of
     * the request.
     *
     * Read from the command request, so REST (query strings) and MCP (JSON
     * values) callers get the same filters.
     *
     * @param Request $request
     * @return StatisticsResponseFilters The using class's $filters, filled in
     * @throws InvalidArgumentException on malformed conditions or completion,
     *     or when both completed and completion are given
     */
    public function getFilters(Request $request): StatisticsResponseFilters
    {
        $filterMap = [
            'minId' => 'setMinId',
            'maxId' => 'setMaxId',
            'completed' => 'setCompleted',
        ];

        foreach ($filterMap as $key => $method) {
            $value = $request->getData($key);
            if ($value !== null) {
                $this->filters->$method(
                    // REST passes query strings, MCP passes JSON booleans
                    $key === 'completed' ? ($value === true || $value === 'true') : (int)$value
                );
            }
        }

        $search = $request->getData('search');
        if ($search !== null) {
            $this->filters->setSearchTerms(is_array($search) ? $search : [$search]);
        }
        $conditions = $request->getData('conditions');
        if ($conditions !== null) {
            if (!is_array($conditions)) {
                throw new InvalidArgumentException('conditions must be an array');
            }
            $allowToken = false;
            foreach ($conditions as $condition) {
                $key = is_array($condition) ? ($condition['key'] ?? null) : null;
                if (is_string($key) && ResponseConditionValues::isTokenKey($key)) {
                    $allowToken = $this->permission->hasSurveyPermission((int)$request->getData('_id'), 'tokens');
                    break;
                }
            }
            $this->filters->setConditions($conditions, $allowToken);
        }
        $completion = $request->getData('completion');
        if ($completion !== null) {
            if (!is_string($completion)) {
                throw new InvalidArgumentException('completion must be a string');
            }
            if ($completion !== 'all' && $request->getData('completed') !== null) {
                throw new InvalidArgumentException('Use either completed or completion, not both.');
            }
            $this->filters->setCompletion($completion);
        }

        return $this->filters;
    }

    /**
     * The `filterSet` param: the filter as the user built it in the modal,
     * resolved into the response columns it means.
     *
     * @param Request $request
     * @param Survey $survey
     * @param string $language The language the filter's answer options are in
     * @return \LimeSurvey\Models\Services\ResponseFilters\ResolvedFilter[]
     * @throws InvalidArgumentException on a malformed or unresolvable filter,
     *     which run() turns into a bad request rather than a 500.
     * @throws PermissionDeniedException when a participant filter is sent by a
     *     caller who may not read the survey's participants.
     */
    private function resolveFilterSet(Request $request, Survey $survey, string $language): array
    {
        return $this->filterSetService->resolve(
            ResponseFilterSet::fromRequestValue($request->getData('filterSet', null)),
            $survey,
            fn(): array => createFieldMap($survey, 'full', false, false, $language)
        );
    }
}
