<?php

namespace LimeSurvey\Models\Services\Proxy;

use QuestionGroup;

/**
 * Proxy Expression Manager Service
 *
 * Wraps static QuestionGroup function(s) to make them injectable into services.
 *
 */
class ProxyQuestionGroup
{
    /**
     * @see \QuestionGroup::deleteWithDependency
     * @param int $questionGroupId
     * @param int $surveyId
     * @return int|null
     */
    public function deleteQuestionGroupWithDependency(int $questionGroupId, int $surveyId)
    {
        return QuestionGroup::deleteWithDependency($questionGroupId, $surveyId);
    }

    /**
     * Checks if questions of other groups have conditions on questions of this group.
     *
     * @see getGroupDepsForConditions()
     * @param int $questionGroupId
     * @param int $surveyId
     * @return bool
     */
    public function hasDependentConditions(int $questionGroupId, int $surveyId): bool
    {
        return getGroupDepsForConditions($surveyId, 'all', $questionGroupId, 'by-targgid') !== null;
    }
}
