<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class TextConditionHandler implements HandlerInterface
{
    public function canHandle(array $operation): bool
    {
        if (!empty($operation['type']) && $operation['type'] == 'text') {
            return true;
        }
        return false;
    }

    public function execute(string $key, array $params): object
    {
        $column = $params["survey"] . 'X' . $params["group"] . 'X' . $params["question"];
        $value = $params["value"];
        return new \CDbCriteria(array(
            'condition' => "$column LIKE :match",
            'params'    => array(':match' => "%$value%")
        ));
    }
}
