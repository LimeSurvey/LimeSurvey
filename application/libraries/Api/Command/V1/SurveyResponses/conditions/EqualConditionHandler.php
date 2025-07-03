<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class EqualConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'equal') {
            return true;
        }
        return false;
    }

    public function execute(string $key, string $value): object
    {
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $key = App()->db->quoteColumnName($key);

        $criteria = new \CDbCriteria();
        $criteria->addColumnCondition([$key => $value]);

        return $criteria;
    }
}
