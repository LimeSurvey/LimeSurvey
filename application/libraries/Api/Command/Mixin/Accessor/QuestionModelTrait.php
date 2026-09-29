<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use Question;

trait QuestionModelTrait
{
    private $question = null;

    /**
     * Get Question
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return Question
     */
    public function getQuestionModel($id): ?Question
    {
        if (!$this->question) {
            $this->question = Question::model()->findByPk($id);
        }

        return $this->question;
    }

    /**
     * Set Question
     *
     * Used to set mock record during testing.
     *
     * @param Question $question
     * @return void
     */
    public function setQuestionModel(Question $question)
    {
        $this->question = $question;
    }
}
