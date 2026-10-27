<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use CDbCriteria;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

/**
 * "The respondent left this alone." The mirror of NotEmptyConditionHandler.
 *
 * Several keys are AND'd rather than OR'd: asking for an unanswered question
 * means every one of its columns is unanswered, where asking for an answered
 * one means any of them is.
 */
class EmptyConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    public function canHandle(string $operation): bool
    {
        return strtolower($operation) === 'empty';
    }

    /**
     * @param string|array $key
     * @param string|array $value Unused; the test is about absence.
     */
    public function execute($key, $value): object
    {
        $criteria = new CDbCriteria();

        $keys = is_array($key) ? $key : [$key];
        $conditions = [];

        foreach ($keys as $rawKey) {
            $quotedKey = $this->sanitizeKey((string) $rawKey);
            $conditions[] = "($quotedKey IS NULL OR $quotedKey = '')";
        }

        if ($conditions !== []) {
            $criteria->condition = '(' . implode(' AND ', $conditions) . ')';
        }

        return $criteria;
    }
}
