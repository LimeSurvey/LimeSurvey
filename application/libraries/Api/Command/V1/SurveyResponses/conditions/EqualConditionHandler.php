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

    public function execute(string|array $key, string $value): object
    {
        $criteria = new \CDbCriteria();

        if (is_array($key)) {
            $conditions = [];

            foreach ($key as $rawKey) {
                $sanitizedKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $rawKey);
                $quotedKey = App()->db->quoteColumnName($sanitizedKey);
                $conditions[] = "$quotedKey = :value";
            }

            $criteria->condition = implode(' OR ', $conditions);
            $criteria->params = [':value' => $value];

            return $criteria;
        }

        $sanitizedKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $quotedKey = App()->db->quoteColumnName($sanitizedKey);

        $criteria->addColumnCondition([$quotedKey => $value]);

        return $criteria;
    }

}
