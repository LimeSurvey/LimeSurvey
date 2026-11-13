<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

class SortingHandler
{
    private array $order = ['asc', 'desc'];

    public function canHandle(string $operation): bool
    {
        return in_array(strtolower($operation), $this->order);
    }

    /**
     * @param string $column
     * @param string $order
     * @return \CSort
     */
    public function execute($column, $order): \CSort
    {
        $sort     = new \CSort();
        $sort->defaultOrder = "$column $order";
        return $sort;
    }
}
