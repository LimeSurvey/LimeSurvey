<?php

namespace LimeSurvey\Models\Services\QuestionOrderingService;

/**
 * Helper class for randomization operations
 */
class RandomizerHelper
{
    /**
     * Get the MersenneTwister instance and set the seed
     *
     * @param int $seed The seed value
     * @return void
     */
    public function initialize($seed)
    {
        require_once(App()->basePath . '/libraries/MersenneTwister.php');
        \ls\mersenne\setSeed($seed);
    }

    /**
     * Extract excluded subquestion based on 'exclude_all_others' attribute
     *
     * @param array $groupedSubquestions
     * @param string $excludeAllOthers
     * @return array [excludedSubquestion, updatedGroupedSubquestions]
     */
    public function extractExcludedSubquestion(
        array $groupedSubquestions,
        string $excludeAllOthers
    )
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
}