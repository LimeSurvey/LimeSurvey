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
     * @param Question $question The question model
     * @param string $context 'answers' or 'subquestions'
     * @return string 'random', 'alphabetical', or 'normal'
     */
    public function determine(Question $question, string $context = 'answers'): string
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
    private function shouldOrderRandomly(Question $question, string $context = 'answers'): bool
    {
        if ($context === 'answers') {
            return ($question->getQuestionAttribute('random_order') == 1
                    && $this->answersRandomizable($question))
                || $question->getQuestionAttribute('answer_order') === 'random';
        }

        // For subquestions
        return $question->getQuestionAttribute('random_order') == 1
            || $question->getQuestionAttribute('subquestion_order') === 'random';
    }

    /**
     * Returns true if the items should be ordered alphabetically.
     *
     * @param Question $question The question model
     * @param string $context 'answers' or 'subquestions'
     * @return bool
     */
    private function shouldOrderAlphabetically(Question $question, string $context = 'answers'): bool
    {
        if ($context === 'answers') {
            $orderAttribute = $question->getQuestionAttribute('answer_order');
        } else {
            $orderAttribute = $question->getQuestionAttribute('subquestion_order');
        }

        return in_array($orderAttribute, ['alphabetical', 'random_alphabetical']);
    }

    /**
     * Checks if answers can be randomized for the given question type.
     *
     * Determines whether the question type supports answer randomization.
     * Currently returns false only for Array (F) and Array Dual Scale (1) question types.
     *
     * @param Question $question The question model to check
     * @return bool True if the question type supports answer randomization, false otherwise
     */
    private function answersRandomizable(Question $question): bool
    {
        return !in_array($question->type, [
            Question::QT_F_ARRAY,
            Question::QT_1_ARRAY_DUAL
        ]);
    }
}
