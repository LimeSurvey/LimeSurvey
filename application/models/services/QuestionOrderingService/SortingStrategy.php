<?php

namespace LimeSurvey\Models\Services\QuestionOrderingService;

use Question;

/**
 * Determines the sorting strategy for question elements
 */
class SortingStrategy
{
    /**
     * Determine which sorting strategy to use
     *
     * @param Question $question
     * @param string $context 'answers' or 'subquestions'
     * @return string 'random', 'alphabetical', or 'normal'
     */
    public function determine(Question $question, string $context = 'answers')
    {
        if ($this->shouldOrderRandomly($question, $context)) {
            return 'random';
        }

        if ($this->shouldOrderAlphabetically($question, $context)) {
            return 'alphabetical';
        }

        return 'normal';
    }

    /**
     * Returns true if the items should be ordered randomly.
     *
     * @param Question $question The question model
     * @param string $context 'answers' or 'subquestions'
     * @return bool
     */
    public function shouldOrderRandomly(
        Question $question,
        $context = 'answers'
    ) {
        $answerOrder = $question->getQuestionAttribute(
            $context === 'answers' ? 'answer_order' : 'subquestion_order'
        );
        if (!is_null($answerOrder)) {
            return $answerOrder == 'random';
        }

// Fall back to random_order attribute for both contexts
        $randomOrder = $question->getQuestionAttribute('random_order') == 1;

// For answers, we need additional check for subquestions
        if ($context === 'answers') {
            return $randomOrder
                && $question->getQuestionType()->subquestions == 0;
        }

        return $randomOrder;
    }

    /**
     * Returns true if the items should be ordered alphabetically.
     *
     * @param Question $question The question model
     * @param string $context 'answers' or 'subquestions'
     * @return bool
     */
    public function shouldOrderAlphabetically(
        Question $question,
        string $context = 'answers'
    ) {
        $orderAttribute = $context
        === 'answers' ? 'answer_order' : 'subquestion_order';
        $answerOrder = $question->getQuestionAttribute($orderAttribute);

        if (!is_null($answerOrder)) {
            return $answerOrder == 'alphabetical'
                || $answerOrder == 'random_alphabetical';
        }

        // Fall back to alphasort attribute for answers, but subquestions don't typically have this option
        if ($context === 'answers') {
            return $question->getQuestionAttribute('alphasort') == 1;
        }

        return false;
    }
}