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
 * screened_out is refused for surveys without that setting.
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
     * @param string $state Completion state
     * @param bool $savesQuotaExit Whether the survey records quota exits, see savesQuotaExit()
     * @return string|null
     * @throws InvalidArgumentException
     */
    public static function predicate(string $state, bool $savesQuotaExit): ?string
    {
        self::assertValid($state);
        switch ($state) {
            case self::STATE_COMPLETED:
                return 'submitdate IS NOT NULL';
            case self::STATE_SCREENED_OUT:
                if (!$savesQuotaExit) {
                    throw new InvalidArgumentException(
                        'This survey does not save quota exits, so screened-out responses cannot be filtered.'
                    );
                }
                return 'quota_exit IS NOT NULL AND quota_exit <> 0';
            case self::STATE_INCOMPLETE:
                return $savesQuotaExit
                    ? 'submitdate IS NULL AND (quota_exit IS NULL OR quota_exit = 0)'
                    : 'submitdate IS NULL';
            default:
                return null;
        }
    }

    /**
     * Add the predicate of a state to the criteria.
     *
     * @param CDbCriteria $criteria Criteria to extend
     * @param string $state Completion state
     * @param bool $savesQuotaExit Whether the survey records quota exits, see savesQuotaExit()
     * @return void
     * @throws InvalidArgumentException
     */
    public static function apply(CDbCriteria $criteria, string $state, bool $savesQuotaExit): void
    {
        $predicate = self::predicate($state, $savesQuotaExit);
        if ($predicate !== null) {
            $criteria->addCondition($predicate);
        }
    }

    /**
     * Whether the survey records quota exits: the setting is on and the responses table has the column.
     *
     * @param int $surveyId Survey id
     * @return bool
     */
    public static function savesQuotaExit(int $surveyId): bool
    {
        $survey = \Survey::model()->findByPk($surveyId);
        return $survey !== null
            && $survey->getIsSaveQuotaExit()
            && isset(\SurveyDynamic::model($surveyId)->getTableSchema()->columns['quota_exit']);
    }
}
