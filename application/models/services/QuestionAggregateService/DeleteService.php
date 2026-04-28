<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Answer;
use AnswerL10n;
use CDbCriteria;
use Condition;
use Question;
use QuestionL10n;
use LimeSurvey\Models\Services\{
    Proxy\ProxyExpressionManager,
    Exception\NotFoundException,
    Exception\PersistErrorException,
    Exception\QuestionHasConditionsException
};

/**
 * Question Aggregate Delete Service
 */
class DeleteService
{
    private Question $modelQuestion;
    private QuestionL10n $modelQuestionL10n;
    private Condition $modelCondition;
    private ProxyExpressionManager $proxyExpressionManager;

    public function __construct(
        Question $modelQuestion,
        QuestionL10n $modelQuestionL10n,
        Condition $modelCondition,
        ProxyExpressionManager $proxyExpressionManager
    ) {
        $this->modelQuestion = $modelQuestion;
        $this->modelQuestionL10n = $modelQuestionL10n;
        $this->modelCondition = $modelCondition;
        $this->proxyExpressionManager = $proxyExpressionManager;
    }

    /**
     * Function responsible for deleting a question.
     *
     * @param int $surveyId
     * @param int $questionId
     * @return void
     * @throws QuestionHasConditionsException
     * @throws \CDbException
     * @throws PersistErrorException
     */
    public function delete($surveyId, $questionId)
    {
        $question = $this->modelQuestion->findByAttributes([
            'qid' => $questionId,
            'sid' => $surveyId
        ]);
        if (empty($question)) {
            throw new NotFoundException();
        }

        $this->proxyExpressionManager
            ->revertUpgradeConditionsToRelevance($surveyId, $questionId);

        // Check if any other questions have conditions which rely on this question.
        // - Don't delete if there are.
        $conditions = $this->modelCondition
                        ->findAllByAttributes(['cqid' => $questionId]);
        // There are conditions dependent on this question
        if (!empty($conditions)) {
            $message = gT(
                'Question could not be deleted. '
                . 'There are conditions for other questions that rely '
                . 'on this question. '
                . 'You cannot delete this question until those conditions '
                . 'are removed.'
            );
            throw new QuestionHasConditionsException($message);
        }

        $this->modelQuestionL10n
            ->deleteAllByAttributes(['qid' => $questionId]);

        if (!$question->delete()) {
            throw new PersistErrorException();
        }
    }

    /**
     * Function responsible for deleting an answer from a question.
     * It also deletes all languages for this answer.
     *
     * @param int $surveyId
     * @param int $questionId
     * @param int $answerId
     * @throws PersistErrorException
     *
     */
    public function deleteAnswer($answerId)
    {
        $answer = Answer::model()->findByAttributes(['aid' => $answerId]);
        $aidsCriteria = (new CDbCriteria())->addInCondition('aid', [$answerId]);
        AnswerL10n::model()->deleteAll($aidsCriteria);
        try {
            $answer->delete();
        } catch (\CDbException $e) {
            throw new PersistErrorException();
        }
    }
}
