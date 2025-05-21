<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use GuzzleHttp\HandlerStack;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\EqualConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\ContainConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\MultiSelectConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\RangeConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\DateRangeConditionHandler;

class FilterPatcher
{
    private array $handlers = [];
    private array $filtersRequiredKeys = ['key', 'filterMethod', 'value'];

    public function __construct()
    {
        $this->registerHandlers();
    }

    /**
     * @param-out \CSort $sort
     */
    public function apply(array $filterParams, \LSDbCriteria &$criteria, \CSort &$sort, $dataMap = array()): void
    {
        $sort->defaultOrder = "id ASC";
        if (!empty($filterParams['sort'])) {
            foreach ($filterParams['sort'] as $column => $order) {
                $op = (new SortingHandler());
                $key = $this->findMapKeyByValue($column, $dataMap);
                if ($op->canHandle($order)) {
                    $sort = $op->execute($key, $order);
                }
            }
        }

        /*
         * http://ls-ce/rest/v1/survey-responses/132241?
         * filters[0][key]=132241X130X2110&
         * filters[0][filterMethod]='contain'&
         * filters[0][value]='Lorem'
         */
        if (!empty($filterParams['filters'])) {
            foreach ($filterParams['filters'] as $filterParam) {
                if (!empty(array_diff_key(array_flip($this->filtersRequiredKeys), $filterParam))) {
                    continue;
                }
                foreach ($this->handlers as $handler) {
                    $op = (new $handler());
                    $filterType = $filterParam['filterMethod'];
                    $key = $this->findMapKeyByValue($filterParam['key'], $dataMap);
                    if ($op->canHandle($filterType)) {
                        $new_criteria = $op->execute($key, $filterParam['value']);
                        $criteria->mergeWith($new_criteria);
                    }
                }
            }
        }
    }

    public function registerHandlers(): void
    {
        //$this->addHandler(SortingHandler::class);
        $this->addHandler(EqualConditionHandler::class);
        $this->addHandler(ContainConditionHandler::class);
        $this->addHandler(RangeConditionHandler::class);
        $this->addHandler(DateRangeConditionHandler::class);
        $this->addHandler(MultiSelectConditionHandler::class);

    }

    public function addHandler(string $handler): void
    {
        $this->handlers[] = $handler;
    }

    private function findMapKeyByValue($targetValue, $dataMap) {
        foreach ($dataMap as $originalKey => $properties) {
            if (isset($properties['key']) && $properties['key'] === $targetValue) {
                return $originalKey;
            }
        }
        return $targetValue;
    }
}
