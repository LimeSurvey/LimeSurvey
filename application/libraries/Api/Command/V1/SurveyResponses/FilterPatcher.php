<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use GuzzleHttp\HandlerStack;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\EqualConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\ContainConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\MultiSelectConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\NullConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\RangeConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\DateRangeConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\GreaterThanConditionHandler;

class FilterPatcher
{
    private array $handlers = [];
    private array $filtersRequiredKeys = ['key', 'filterMethod', 'value'];
    private array $sortAllowedKeys = ['id', 'submitDate'];

    public function __construct()
    {
        $this->registerHandlers();
    }

    /**
     * @param array $filterParams
     * @param \LSDbCriteria $criteria
     * @param \CSort $sort
     * @param array $dataMap Static attribute map of the response transformer.
     * @param array $validColumns Real response columns (system + nested
     *   question/subquestion columns) used to validate filter keys. When empty
     *   no validation is performed, preserving the previous behaviour.
     * @param-out \CSort $sort
     */
    public function apply(
        array $filterParams,
        \LSDbCriteria &$criteria,
        \CSort &$sort,
        array $dataMap = array(),
        array $validColumns = array()
    ): void {
        $sort->defaultOrder = "id DESC";
        if (!empty($filterParams['sort'])) {
            $sortParams = array_intersect_key($filterParams['sort'], array_flip($this->sortAllowedKeys));
            foreach ($sortParams as $column => $order) {
                $op = (new SortingHandler());
                $key = $this->findMapKeyByValue($column, $dataMap);
                if ($op->canHandle($order)) {
                    $sort = $op->execute($key, $order);
                }
            }
        }   
        /*
         * http://ls-ce/rest/v1/survey-responses/132241?
         * filters[0][key]=132241X130X2110& filters[0][filterMethod]='contain'& filters[0][value]='Lorem'
         */
        if (!empty($filterParams['filters'])) {
            foreach ($filterParams['filters'] as $filterParam) {
                if (
                    !is_array($filterParam)
                    || !empty(array_diff_key(array_flip($this->filtersRequiredKeys), $filterParam))
                    || !is_string($filterParam['filterMethod'])
                ) {
                    continue;
                }
                $key = is_string($filterParam['key'])
                    ? $this->findMapKeyByValue($filterParam['key'], $dataMap)
                    : $filterParam['key'];
                // special case since 'completed' is returned in the responses and calculated on the fly,
                if ($key === 'completed') {
                    $key = 'submitdate';
                }
                // Validate the resolved key(s) against the survey's real columns so nested question/subquestion
                // columns are filtered at query level and unknown keys never reach the SQL.
                if (!$this->isAllowedKey($key, $dataMap, $validColumns)) {
                    continue;
                }

                foreach ($this->handlers as $handler) {
                    $op = (new $handler());
                    if (!$op instanceof HandlerInterface) {
                        throw new \InvalidArgumentException('Handler must implement HandlerInterface.');
                    }
                    $filterType = $filterParam['filterMethod'];
                    if ($op->canHandle($filterType)) {
                        $value = $filterParam['value'];
                        // check for null values
                        $new_criteria = (new NullConditionHandler())->execute($key, $value);
                        if (empty($new_criteria->condition)) {
                            $new_criteria = $op->execute($key, $value);
                        }
                        $criteria->mergeWith($new_criteria);
                    }
                }
            }
        }
    }

    /**
     * Strict check for callers that must not silently skip filters (e.g. exports).
     *
     * @param array $filters
     * @param array $dataMap
     * @param array $validColumns
     * @throws \InvalidArgumentException
     */
    public function validate(array $filters, array $dataMap, array $validColumns): void
    {
        foreach ($filters as $filter) {
            if (
                !is_array($filter)
                || !empty(array_diff_key(array_flip($this->filtersRequiredKeys), $filter))
                || !is_string($filter['key'])
                || !is_string($filter['filterMethod'])
            ) {
                throw new \InvalidArgumentException('Invalid filters specified');
            }

            $supported = false;
            foreach ($this->handlers as $handler) {
                $supported = $supported || (new $handler())->canHandle($filter['filterMethod']);
            }
            if (!$supported) {
                throw new \InvalidArgumentException('Invalid filter method specified');
            }

            $key = $this->findMapKeyByValue($filter['key'], $dataMap);
            if ($key === 'completed') {
                $key = 'submitdate';
            }
            if (empty($validColumns) || !$this->isAllowedKey($key, $dataMap, $validColumns)) {
                throw new \InvalidArgumentException('Invalid filter key specified');
            }
        }
    }

    public function registerHandlers(): void
    {
        $this->addHandler(EqualConditionHandler::class);
        $this->addHandler(ContainConditionHandler::class);
        $this->addHandler(RangeConditionHandler::class);
        $this->addHandler(DateRangeConditionHandler::class);
        $this->addHandler(MultiSelectConditionHandler::class);
        $this->addHandler(GreaterThanConditionHandler::class);
    }

    public function addHandler(string $handler): void
    {
        $this->handlers[] = $handler;
    }

    private function findMapKeyByValue(string $targetValue, array $dataMap): string
    {
        foreach ($dataMap as $originalKey => $properties) {
            if (isset($properties['key']) && $properties['key'] === $targetValue) {
                return $originalKey;
            }
        }
        return $targetValue;
    }

    /**
     * @param string|array $key
     * @param array $dataMap
     * @param array $validColumns
     * @return bool
     */
    private function isAllowedKey($key, array $dataMap, array $validColumns): bool
    {
        if (empty($validColumns)) {
            return true;
        }

        $keys = is_array($key) ? $key : [$key];
        if (empty($keys)) {
            return false;
        }

        $allowed = $this->allowedKeySet($dataMap, $validColumns);
        foreach ($keys as $singleKey) {
            if (!is_string($singleKey) || !isset($allowed[strtolower($singleKey)])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $dataMap
     * @param array $validColumns
     * @return array<string,true>
     */
    private function allowedKeySet(array $dataMap, array $validColumns): array
    {
        $set = [];
        foreach ($validColumns as $column) {
            $set[strtolower((string) $column)] = true;
        }
        foreach ($dataMap as $originalKey => $properties) {
            $set[strtolower((string) $originalKey)] = true;
            if (isset($properties['key'])) {
                $set[strtolower((string) $properties['key'])] = true;
            }
        }

        return $set;
    }
}
