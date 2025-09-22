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
        $sortStrategy = $this->sortingStrategy->determine($question, 'subquestions');

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
        $sortStrategy = $this->sortingStrategy->determine($question);

        return $this->applySorting(
            $answerOptions,
            $question,
            'answers',
            $sortStrategy,
            $language
        );
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
        $this->randomizerHelper->initialize($question->sid);

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
            ] = $this->randomizerHelper->extractExcludedSubquestion(
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
}
