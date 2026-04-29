<?php

namespace LimeSurvey\Models\Services\QuestionOrderingService;

use Question;
use Yii;
use LimeSurvey\Helpers\SortHelper;
use LimeSurvey\Models\Services\QuestionOrderingService\SortingStrategy;
use LimeSurvey\Models\Services\QuestionOrderingService\RandomizerHelper;

/**
 * Question Ordering Service
 *
 * Service class for handling the ordering of subquestions, and answer options.
 */
class QuestionOrderingService
{
    /** @var SortingStrategy */
    private $sortingStrategy;

    /** @var RandomizerHelper */
    private $randomizerHelper;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sortingStrategy = new SortingStrategy();
        $this->randomizerHelper = new RandomizerHelper();
    }

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

        // If no answers match the criteria, return empty array
        if (empty($answerOptions)) {
            return [];
        }

        // Determine sorting strategy and apply it
        $sortStrategy = $this->sortingStrategy->determine($question);
        $sortedOptions = $this->applySorting(
            $answerOptions,
            $question,
            'answers',
            $sortStrategy,
            $language
        );

        // Return only the requested scale if specified
        if ($scaleId !== null) {
            return $sortedOptions[$scaleId] ?? [];
        }

        return $sortedOptions;
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

        // If no subquestions match the criteria, return empty array
        if (empty($groupedSubquestions)) {
            return [];
        }

        // Determine sorting strategy
        $sortStrategy = $this->sortingStrategy->determine($question, 'subquestions');

        // Apply sorting
        $sortedGroups = $this->applySorting(
            $groupedSubquestions,
            $question,
            'subquestions',
            $sortStrategy,
            $language
        );

        // Return only the requested scale if specified
        if ($scaleId !== null) {
            return $sortedGroups[$scaleId] ?? [];
        }

        return $sortedGroups;
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
                return $this->randomizerHelper->applyRandomSorting($groupedItems, $question, $context);

            case 'alphabetical':
                return $this->applyAlphabeticalSorting($groupedItems, $question, $context, $language);

            default: // 'normal'
                return $this->applyDefaultSorting($groupedItems, $context);
        }
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
            SortHelper::getInstance($language)->$sortMethod(
                $sorted,
                SortHelper::SORT_STRING
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
}
