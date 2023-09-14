<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use Question;

trait OpHandlerSurveyTrait
{
    /**
     * Extracts and returns surveyId from context
     * @param OpInterface $op
     * @return int
     * @throws OpHandlerException
     */
    public function getSurveyIdFromContext(OpInterface $op)
    {
        $context = $op->getContext();
        $surveyId = isset($context['id']) ? (int)$context['id'] : null;
        if ($surveyId === null) {
            throw new OpHandlerException(
                printf(
                    'Missing survey id in context for entity %s',
                    $op->getEntityType()
                )
            );
        }
        return $surveyId;
    }

    /**
     * Gets a question from the question service, if the entity id is the qid,
     * and the survey contains this question.
     * @param OpInterface $op
     * @return Question
     * @throws OpHandlerException
     */
    public function getQuestion(OpInterface $op)
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $questionId = $op->getEntityId();
        $question = $this->questionService->getQuestionBySidAndQid(
            $surveyId,
            $questionId
        );
        if ($question === null) {
            throw new OpHandlerException(
                printf(
                    'No question found for entity %s',
                    $op->getEntityType()
                )
            );
        }
        return $question;
    }
}
