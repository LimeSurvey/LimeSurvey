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
}
