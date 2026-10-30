<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses;

use CDbCriteria;
use InvalidArgumentException;

/**
 * Single source of truth for the completion states and their predicates, used
 * by the responses list, the response export and the statistics.
 *
 * The states do not overlap: a response is completed (submitdate set),
 * screened out (quota_exit set) or incomplete (neither).
 *
 * quota_exit is only written when the survey has savequotaexit = Y, so
 * screened_out only finds responses of surveys with that setting on; without
 * it, screened-out responses count as incomplete.
 */
final class CompletionFilter
{
    public const STATE_ALL = 'all';
    public const STATE_COMPLETED = 'completed';
    public const STATE_INCOMPLETE = 'incomplete';
    public const STATE_SCREENED_OUT = 'screened_out';

    public const STATES = [
        self::STATE_ALL,
        self::STATE_COMPLETED,
        self::STATE_INCOMPLETE,
        self::STATE_SCREENED_OUT,
    ];

    /**
     * @throws InvalidArgumentException when the state is not one of STATES
     */
    public static function assertValid(string $state): void
    {
        if (!in_array($state, self::STATES, true)) {
            throw new InvalidArgumentException(
                'Invalid completion filter. Allowed values: ' . implode(', ', self::STATES) . '.'
            );
        }
    }

    /**
     * The SQL predicate of a state, null for 'all'.
     *
     * @param bool $hasQuotaExit whether the responses table has a quota_exit column
     * @throws InvalidArgumentException
     */
    public static function predicate(string $state, bool $hasQuotaExit): ?string
    {
        self::assertValid($state);
        switch ($state) {
            case self::STATE_COMPLETED:
                return 'submitdate IS NOT NULL';
            case self::STATE_SCREENED_OUT:
                if (!$hasQuotaExit) {
                    throw new InvalidArgumentException('This survey has no quota exit column.');
                }
                return 'quota_exit IS NOT NULL AND quota_exit <> 0';
            case self::STATE_INCOMPLETE:
                return $hasQuotaExit
                    ? 'submitdate IS NULL AND (quota_exit IS NULL OR quota_exit = 0)'
                    : 'submitdate IS NULL';
            default:
                return null;
        }
    }

    public static function apply(CDbCriteria $criteria, string $state, array $columns): void
    {
        $predicate = self::predicate($state, in_array('quota_exit', $columns, true));
        if ($predicate !== null) {
            $criteria->addCondition($predicate);
        }
    }
}
