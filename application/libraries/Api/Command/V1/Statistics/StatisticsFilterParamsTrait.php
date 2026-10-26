<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Statistics;

use LimeSurvey\Models\Services\SurveyStatistics\StatisticsResponseFilters;
use Yii;

/**
 * Reads the Statistics response filters (minId, maxId, completed, search)
 * from the query string, so every statistics endpoint filters the same way.
 *
 * Expects the using class to hold a StatisticsResponseFilters in $filters.
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
}
