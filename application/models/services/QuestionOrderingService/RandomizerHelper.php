<?php

namespace LimeSurvey\Models\Services\QuestionOrderingService;

use Yii;

/**
 * Helper class for randomization operations
 */
class RandomizerHelper
{
    /**
     * Initialize the randomizer with a seed based on survey ID
     *
     * @param int $surveyId The survey ID to use for seeding
     * @return void
     */
    public function initialize(int $surveyId): void
    {
        require_once(App()->basePath . '/libraries/MersenneTwister.php');
        \ls\mersenne\setSeed($surveyId);
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