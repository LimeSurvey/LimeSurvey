<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use CDbCriteria;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

/**
 * "The respondent put something here."
 *
 * Both tests are needed: a column nobody filled can hold NULL or an empty
 * string depending on whether the question was reached or merely skipped, and
 * either one on its own would miss half of the responses that did answer.
 */
class NotEmptyConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    public function canHandle(string $operation): bool
    {
        return strtolower($operation) === 'not-empty';
    }

    /**
     * @param string|array $key
     * @param string|array $value Unused; the test is about presence.
     */
    public function execute($key, $value): object
    {
        $criteria = new CDbCriteria();

        $keys = is_array($key) ? $key : [$key];
        $conditions = [];

        foreach ($keys as $rawKey) {
            $quotedKey = $this->sanitizeKey((string) $rawKey);
            $conditions[] = "($quotedKey IS NOT NULL AND $quotedKey <> '')";
        }

        if ($conditions !== []) {
            $criteria->condition = '(' . implode(' OR ', $conditions) . ')';
        }

        return $criteria;
    }
}
