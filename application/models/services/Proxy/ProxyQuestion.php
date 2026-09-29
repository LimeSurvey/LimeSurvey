<?php

namespace LimeSurvey\Models\Services\Proxy;

use Question;

/**
 * ProxyQuestion Service
 *
 * Wraps static methods on Question to make them injectable to other services.
 */
class ProxyQuestion
{
    /**
     * Returns the highest question_order value that exists for a questiongroup inside the related questions.
     * ($question->question_order).
     *
     * @param int $questionGroupId  the question group id
     *
     * @return int|null question highest order number or null if there are no questions belonging to the group
     */
    public static function getHighestQuestionOrderNumberInGroup($questionGroupId)
    {
        return Question::getHighestQuestionOrderNumberInGroup($questionGroupId);
    }

    /**
     * Queries the database for the maximum sort order of questions inside question group.
     *
     * @param integer $gid
     * @return integer
     */
    public static function getMaxQuestionOrder($gid)
    {
        return getMaxQuestionOrder($gid);
    }
}
