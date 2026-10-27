<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use CDbCriteria;
use InvalidArgumentException;

final class CompletionFilter
{
    public static function apply(CDbCriteria $criteria, string $state, array $columns): void
    {
        if (!in_array($state, ['all', 'completed', 'incomplete', 'screened_out'], true)) {
            throw new InvalidArgumentException('Invalid completion filter.');
        }
        if ($state === 'all') {
            return;
        }
        if ($state === 'screened_out') {
            if (!in_array('quota_exit', $columns, true)) {
                throw new InvalidArgumentException('This survey has no quota exit column.');
            }
            $criteria->addCondition('quota_exit IS NOT NULL AND quota_exit <> 0');
            return;
        }
        $criteria->addCondition('submitdate IS ' . ($state === 'completed' ? 'NOT ' : '') . 'NULL');
    }
}
