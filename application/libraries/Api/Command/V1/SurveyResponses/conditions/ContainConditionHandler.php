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
     * An array value lists alternatives: the row matches when any of them is
     * contained in any of the keys.
     */
    public function execute($key, $value): object
    {
        $values = array_map('trim', is_array($value) ? array_values($value) : [$value]);
        $keys = is_array($key) ? $key : [$key];
        $criteria = new CDbCriteria();

        $conditions = [];
        $params = [];
        foreach ($keys as $rawKey) {
            $quotedKey = $this->sanitizeKey($rawKey);
            foreach ($values as $item) {
                $paramName = CDbCriteria::PARAM_PREFIX . CDbCriteria::$paramCount++;
                $conditions[] = "$quotedKey LIKE $paramName";
                $params[$paramName] = "%$item%";
            }
        }

        $criteria->condition = count($conditions) > 1
            ? '(' . implode(' OR ', $conditions) . ')'
            : ($conditions[0] ?? '');
        $criteria->params = $params;

        return $criteria;
    }
}
