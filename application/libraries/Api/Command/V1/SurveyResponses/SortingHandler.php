<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use GuzzleHttp\HandlerStack;

class SortingHandler
{
    private array $order = ['asc', 'desc'];

    public function canHandle(string $operation): bool
    {
        return in_array(strtolower($operation), $this->order);
    }

    public function execute(string $column, string $order): \CSort
    {
        $sort     = new \CSort();
        $sort->defaultOrder = "$column $order";
        return $sort;
    }
}
