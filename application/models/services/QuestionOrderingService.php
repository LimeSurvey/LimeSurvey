<?php

namespace LimeSurvey\Models\Services;

use Question;
use Yii;

/**
 * Question Ordering Service
 *
 * Service class for handling the ordering of subquestions, and answer options.
 */
class QuestionOrderingService
{
    /**
     * Get ordered answers for a question
     *
     * @param Question $question The question model
     * @param int|null $scaleId Optional scale ID filter
     * @param string|null $language Language for sorting
     * @return array Ordered answers
     */
    public function getOrderedAnswers(
        Question $question,
        $scaleId = null,
        $language = null
    ) {
        //reset answers set prior to this call
        $answerOptions = [
            0 => []
        ];

        foreach ($question->answers as $answer) {
            if ($scaleId !== null && $answer->scale_id != $scaleId) {
                continue;
            }
            $answerOptions[$answer->scale_id][] = $answer;
        }

        if ($scaleId !== null) {
            return $answerOptions[$scaleId];
        }

        return $this->sortAnswerOptions($answerOptions, $question, $language);
    }

    /**
     * Get ordered subquestions for a question
     *
     * @param Question $question The question model
     * @param int|null $scaleId Optional scale ID filter
     * @param string|null $language Language for sorting
     * @return array Ordered subquestions
     */
    public function getOrderedSubQuestions(
        Question $question,
        $scaleId = null,
        $language = null
    ) {
        // Get all subquestions
        $orderedSubquestions = $question->subquestions;

        // Group subquestions by scale_id
        $groupedSubquestions = [];
        foreach ($orderedSubquestions as $subquestion) {
            if ($scaleId !== null && $subquestion->scale_id != $scaleId) {
                continue;
            }
            $groupedSubquestions[$subquestion->scale_id][] = $subquestion;
        }

        // Return early if we're filtering by scale_id and it's empty
        if ($scaleId !== null) {
            return $groupedSubquestions[$scaleId] ?? [];
        }

        // Determine sorting strategy
        $sortStrategy = $this->determineSortStrategy($question, 'subquestions');

        return $this->applySorting(
            $groupedSubquestions,
            $question,
            'subquestions',
            $sortStrategy,
            $language
        );
    }

    /**
     * Sort answer options according to question attributes
     *
     * @param array $answerOptions The answer options to sort
     * @param Question $question The question model
     * @param string|null $language Optional language code
     * @return array Sorted answer options
     */
    public function sortAnswerOptions(
        array $answerOptions,
        Question $question,
        $language = null
    ) {
        // Determine sorting strategy
        $sortStrategy = $this->determineSortStrategy($question);

        return $this->applySorting(
            $answerOptions,
            $question,
            'answers',
            $sortStrategy,
            $language
        );
    }

    /**
     * Determine which sorting strategy to use
     *
     * @param Question $question
     * @param string $context 'answers' or 'subquestions'
     * @return string 'random', 'alphabetical', or 'normal'
     */
    private function determineSortStrategy(
        Question $question,
        string $context = 'answers'
    ) {
        if ($this->shouldOrderRandomly($question, $context)) {
            return 'random';
        }

        if ($this->shouldOrderAlphabetically($question, $context)) {
            return 'alphabetical';
        }

        return 'normal';
    }

    /**
     * Apply sorting to items (answers or subquestions) based on specified strategy
     *
     * @param array $groupedItems Items grouped by scale_id
     * @param Question $question The question model
     * @param string $context 'answers' or 'subquestions'
     * @param string $sortStrategy 'random', 'alphabetical', or 'normal'
     * @param string|null $language Language for alphabetical sorting
     * @return array Sorted items
     */
    private function applySorting(
        array $groupedItems,
        Question $question,
        string $context,
        string $sortStrategy,
        $language = null
    ) {
        switch ($sortStrategy) {
            case 'random':
                return $this->applyRandomSorting($groupedItems, $question, $context);

            case 'alphabetical':
                return $this->applyAlphabeticalSorting($groupedItems, $question, $context, $language);

            default: // 'normal'
                return $this->applyDefaultSorting($groupedItems, $context);
        }
    }

    /**
     * Apply random sorting to items
     *
     * @param array $groupedItems
     * @param Question $question
     * @param string $context 'answers' or 'subquestions'
     * @return array
     */
    private function applyRandomSorting(array $groupedItems, Question $question, string $context)
    {
        if ($context === 'subquestions') {
            return $this->applyRandomSortingToSubquestions($groupedItems, $question);
        }

        // For answers, we use a simpler approach
        foreach ($groupedItems as $scaleId => $scaleArray) {
            $keys = array_keys($scaleArray);
            shuffle($keys);

            $sortedItems = [];
            foreach ($keys as $key) {
                $sortedItems[$key] = $scaleArray[$key];
            }
            $groupedItems[$scaleId] = $sortedItems;
        }
        return $groupedItems;
    }

    /**
     * Apply alphabetical sorting to items
     *
     * @param array $groupedItems
     * @param Question $question
     * @param string $context 'answers' or 'subquestions'
     * @param string|null $language
     * @return array
     */
    private function applyAlphabeticalSorting(
        array $groupedItems,
        Question $question,
        string $context,
        $language = null
    ) {
        if (empty($language) || !in_array($language, $question->survey->allLanguages)) {
            $language = $question->survey->language;
        }

        $orderAttribute = $context === 'answers' ? 'answer_order' : 'subquestion_order';
        $textField = $context === 'answers' ? 'answer' : 'question';
        $l10nField = $context === 'answers' ? 'answerl10ns' : 'questionl10ns';

        foreach ($groupedItems as $scaleId => &$scaleArray) {
            $sorted = [];
            foreach ($scaleArray as $key => $item) {
                $l10nCollection = $item->$l10nField;
                /** @var array<string, \AnswerL10n|\QuestionL10n> $l10nCollection */
                $sorted[$key] = $l10nCollection[$language]->$textField;
            }

            $itemOrder = $question->getQuestionAttribute($orderAttribute);
            $isReverseOrder = ($itemOrder == 'random_alphabetical' && mt_rand(0, 1) == 1);

            // Apply appropriate sorting direction
            $sortMethod = $isReverseOrder ? 'arsort' : 'asort';
            \LimeSurvey\Helpers\SortHelper::getInstance($language)->$sortMethod(
                $sorted,
                \LimeSurvey\Helpers\SortHelper::SORT_STRING
            );

            $sortedItems = [];
            foreach ($sorted as $key => $text) {
                $sortedItems[] = $scaleArray[$key];
            }
            $scaleArray = $sortedItems;
        }

        return $groupedItems;
    }

    /**
     * Apply default sorting by item's own sort order
     *
     * @param array $groupedItems
     * @param string $context 'answers' or 'subquestions'
     * @return array
     */
    private function applyDefaultSorting(array $groupedItems, string $context)
    {
        $sortField = $context === 'answers' ? 'sortorder' : 'question_order';

        foreach ($groupedItems as $scaleId => &$scaleArray) {
            usort($scaleArray, function ($a, $b) use ($sortField) {
                if ($a->$sortField == $b->$sortField) {
                    return 0;
                }
                return $a->$sortField < $b->$sortField ? -1 : 1;
            });
        }
        return $groupedItems;
    }

    /**
     * Extract excluded subquestion based on 'exclude_all_others' attribute
     *
     * @param array $groupedSubquestions
     * @param string $excludeAllOthers
     * @return array [excludedSubquestion, updatedGroupedSubquestions]
     */
    private function extractExcludedSubquestion(array $groupedSubquestions, string $excludeAllOthers)
    {
        $excludedSubquestion = null;

        foreach ($groupedSubquestions as $scaleId => &$scaleArray) {
            foreach ($scaleArray as $key => $subquestion) {
                if ($subquestion->title == $excludeAllOthers) {
                    $excludedSubquestion = $subquestion;
                    unset($scaleArray[$key]);
                    // Reindex the array to ensure no gaps in numeric indices
                    $scaleArray = array_values($scaleArray);
                    break 2;
                }
            }
        }

        return [$excludedSubquestion, $groupedSubquestions];
    }

    /**
     * Apply random sorting to subquestions
     *
     * @param array $groupedSubquestions
     * @param Question $question
     * @return array
     */
    private function applyRandomSortingToSubquestions(
        array $groupedSubquestions,
        Question $question
    ) {
        $this->initializeRandomizer($question->sid);

        // Check for excluded subquestion before randomization
        $excludeAllOthers = $question->getQuestionAttribute('exclude_all_others');
        $excludedSubquestion = null;

        if (
            $excludeAllOthers != '' &&
            ($question->getQuestionAttribute('random_order') == 1 ||
                $question->getQuestionAttribute('subquestion_order') == 'random')
        ) {
            [
                $excludedSubquestion,
                $groupedSubquestions
            ] = $this->extractExcludedSubquestion(
                $groupedSubquestions,
                $excludeAllOthers
            );
        }

        // Apply random sorting to each scale group
        foreach ($groupedSubquestions as $scaleId => &$scaleArray) {
            $scaleArray = \ls\mersenne\shuffle($scaleArray);
        }

        // Reinsert excluded subquestion if needed
        if ($excludedSubquestion !== null) {
            $scaleId = $excludedSubquestion->scale_id;
            array_splice(
                $groupedSubquestions[$scaleId],
                ($excludedSubquestion->question_order - 1),
                0,
                [$excludedSubquestion]
            );
        }

        return $groupedSubquestions;
    }

    /**
     * Returns true if the items should be ordered randomly.
     *
     * @param Question $question The question model
     * @param string $context 'answers' or 'subquestions'
     * @return bool
     */
    private function shouldOrderRandomly(
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
    private function shouldOrderAlphabetically(
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

    /**
     * Get the MersenneTwister instance and set the seed
     *
     * @param int $seed The seed value
     * @return void
     */
    private function initializeRandomizer($seed)
    {
        require_once(Yii::app()->basePath . '/libraries/MersenneTwister.php');
        \ls\mersenne\setSeed($seed);
    }
}
