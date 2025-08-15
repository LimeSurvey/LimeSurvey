<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use CDbCriteria;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class ContainConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'contain') {
            return true;
        }
        return false;
    }

    /**
     * Builds criteria for either one or multiple keys.
     * @param string|array $key
     * @param string|array $value
     * @return \CDbCriteria
     */
    public function execute($key, $value): object
    {
        $value = trim($value);
        $criteria = new CDbCriteria();

        if (is_array($key)) {
            $conditions = [];
            $params = [];

            foreach ($key as $index => $rawKey) {
                $quotedKey = $this->sanitizeKey($rawKey);
                $paramName = ":match$index";

                $conditions[] = "$quotedKey LIKE $paramName";
                $params[$paramName] = "%$value%";
            }

            $criteria->condition = implode(' OR ', $conditions);
            $criteria->params = $params;

            return $criteria;
        }
        $quotedKey = $this->sanitizeKey($key);

        $criteria->condition = "$quotedKey LIKE :match";
        $criteria->params = [':match' => "%$value%"];

        return $criteria;
    }
}
