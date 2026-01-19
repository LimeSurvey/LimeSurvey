<?php

namespace LimeSurvey\Models\Services\QuestionOrderingService;

use Question;
use Survey;

/**
 * Helper class for randomization operations
 */
class RandomizerHelper
{
    /**
     * Initialize the randomizer with a seed based on survey ID
     *
     * @param int $surveyId The survey ID to use for seeding
     * @param Survey|null $survey The survey object to use for seeding
     * @return void
     */
    public function initialize(int $surveyId, $survey = null): void
    {
        require_once(App()->basePath . '/libraries/MersenneTwister.php');
        \ls\mersenne\setSeed($surveyId, $survey);
    }

    /**
     * Apply random sorting to items
     *
     * @param array $groupedItems
     * @param Question $question
     * @param string $context 'answers' or 'subquestions'
     * @return array
     */
    public function applyRandomSorting(array $groupedItems, Question $question, string $context)
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
     * Apply random sorting to subquestions
     *
     * @param array $groupedSubquestions
     * @param Question $question
     * @param null|\Survey $survey
     * @return array
     */
    public function applyRandomSortingToSubquestions(
        array $groupedSubquestions,
        Question $question,
        $survey = null
    ) {
        $this->initialize($question->sid, $survey);

        // Check for excluded subquestion before randomization
        /* @param string|null $excludeAllOthers */
        $excludeAllOthers = $question->getQuestionAttribute('exclude_all_others');
        $excludedSubquestion = null;

        if (
            $excludeAllOthers !== '' && $excludeAllOthers !== null &&
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
                ($excludedSubquestion->question_order),
                0,
                [$excludedSubquestion]
            );
        }

        return $groupedSubquestions;
    }

    /**
     * Extract excluded subquestion from the grouped subquestions
     *
     * @param array $groupedSubquestions Subquestions grouped by scale_id
     * @param string $excludeAllOthers The code of the excluded subquestion
     * @return array [excludedSubquestion, updatedGroupedSubquestions]
     */
    public function extractExcludedSubquestion(
        array $groupedSubquestions,
        string $excludeAllOthers
    ): array {
        $excludedSubquestion = null;

        if (empty($excludeAllOthers)) {
            return [$excludedSubquestion, $groupedSubquestions];
        }

        foreach ($groupedSubquestions as $scaleId => $scaleArray) {
            foreach ($scaleArray as $key => $subquestion) {
                if ($subquestion->title == $excludeAllOthers) {
                    $excludedSubquestion = $subquestion;
                    unset($groupedSubquestions[$scaleId][$key]);
                    $groupedSubquestions[$scaleId] = array_values($groupedSubquestions[$scaleId]);
                    break 2;
                }
            }
        }

        return [$excludedSubquestion, $groupedSubquestions];
    }
}
