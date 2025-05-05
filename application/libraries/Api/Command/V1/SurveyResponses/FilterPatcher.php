<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use GuzzleHttp\HandlerStack;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\OptionConditionHandler;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions\TextConditionHandler;

class FilterPatcher
{
    private array $handlers = [];

    public function __construct()
    {
        $this->registerHandlers();
    }

    public function apply(array $filterParams, \LSDbCriteria &$criteria, \CSort &$sort)
    {
        if (!empty($filterParams['sort'])) {
            foreach ($filterParams['sort'] as $column => $order) {
                $op = (new SortingHandler());
                if ($op->canHandle($order)) {
                    $sort = $op->execute($column, $order);
                }
            }
        }

        if (!empty($filterParams['search'])) {
            foreach ($filterParams['search'] as $filterParam) {
                foreach ($this->handlers as $handler) {
                    $op = (new $handler());
                    if ($op->canHandle($filterParam)) {
                        $new_criteria = $op->execute('filter', $filterParam);
                        $criteria->mergeWith($new_criteria);
                    }
                }
            }
        }
    }

    public function registerHandlers(): void
    {
        //$this->addHandler(SortingHandler::class);
        $this->addHandler(OptionConditionHandler::class);
        $this->addHandler(TextConditionHandler::class);
    }

    public function addHandler(string $handler): void
    {
        $this->handlers[] = $handler;
    }
}
