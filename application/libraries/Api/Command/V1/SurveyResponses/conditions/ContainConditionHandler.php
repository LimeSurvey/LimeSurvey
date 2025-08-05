<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class ContainConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        if ($operation == 'contain') {
            return true;
        }
        return false;
    }

    /**
     * Builds criteria for either one or multiple keys.
     * @param string|array $key
     * @param string $value
     * @return \CDbCriteria
     */
    public function execute($key, $value): object
    {
        $value = trim($value);
        $criteria = new \CDbCriteria();

        if (is_array($key)) {
            $conditions = [];
            $params = [];

            foreach ($key as $index => $rawKey) {
                $sanitizedKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $rawKey);
                $quotedKey = App()->db->quoteColumnName($sanitizedKey);
                $paramName = ":match$index";

                $conditions[] = "$quotedKey LIKE $paramName";
                $params[$paramName] = "%$value%";
            }

            $criteria->condition = implode(' OR ', $conditions);
            $criteria->params = $params;

            return $criteria;
        }

        $sanitizedKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $quotedKey = App()->db->quoteColumnName($sanitizedKey);

        $criteria->condition = "$quotedKey LIKE :match";
        $criteria->params = [':match' => "%$value%"];

        return $criteria;
    }
}
