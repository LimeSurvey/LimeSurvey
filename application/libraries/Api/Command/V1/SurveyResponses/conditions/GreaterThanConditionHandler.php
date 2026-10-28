<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

/**
 * Strict "greater than" comparison on a numeric column, e.g. to count the
 * responses listed before a given response id under the default "id DESC" sort.
 */
class GreaterThanConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    /**
     * @param string $operation
     * @return bool
     */
    public function canHandle(string $operation): bool
    {
        return strtolower($operation) === 'greaterthan';
    }

    /**
     * Builds a "key > value" criteria. Unlike the range handler no CAST is
     * used, so the condition works on every supported database.
     *
     * @param string|array $key
     * @param string|array $value
     * @return \CDbCriteria
     * @throws InvalidArgumentException If more than one key or a non-numeric value is sent.
     */
    public function execute($key, $value): object
    {
        if (is_array($key)) {
            throw new InvalidArgumentException('Multiple keys are not supported for greaterThan conditions.');
        }
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Invalid greaterThan value sent.');
        }

        $quotedKey = $this->sanitizeKey($key);
        $param = ':' . $this->stripKey($key) . 'GreaterThan';

        $criteria = new \CDbCriteria();
        $criteria->condition = "$quotedKey > $param";
        $criteria->params = [$param => $value + 0];

        return $criteria;
    }
}
