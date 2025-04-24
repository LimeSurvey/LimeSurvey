<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class OptionConditionHandler implements HandlerInterface
{
    private array $order = ['asc', 'desc'];

    public function canHandle(array $operation): bool
    {
        if (!empty($operation['type']) && $operation['type'] == 'option') {
            return true;
        }
        return false;
    }

    public function execute(string $key, array $params): object
    {
        $column = $params["survey"] . 'X' . $params["group"] . 'X' . $params["question"];
        $value = $params["value"];
        return new \CDbCriteria(array(
            'condition' => "$column = :match",
            'params'    => array(':match' => "$value")
        ));
    }
}
