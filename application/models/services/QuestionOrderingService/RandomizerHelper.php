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

        $keepCodes = $this->getKeepCodes($question);

        return $this->applyRandomSortingToAnswers($groupedItems, $keepCodes);
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

        // Only keep_codes_order pins positions; exclusive options (exclude_all_others) are randomized like any other
        return $this->applyRandomSortingToSubquestionGroups(
            $groupedSubquestions,
            $this->getKeepCodes($question)
        );
    }

    /**
     * Extract and normalize keep_codes_order for a question.
     *
     * @param Question $question
     * @return string[]
     */
    private function getKeepCodes(Question $question): array
    {
        return $this->splitCodes($question->getQuestionAttribute('keep_codes_order'));
    }

    /**
     * Split a semicolon-separated list of codes into trimmed, non-empty codes.
     *
     * @param string|null $codesRaw
     * @return string[]
     */
    private function splitCodes($codesRaw): array
    {
        if ($codesRaw === null || trim((string) $codesRaw) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(';', (string) $codesRaw)),
            function ($code) {
                return $code !== '';
            }
        ));
    }

    /**
     * Apply random sorting to grouped answers, respecting keep_codes_order.
     *
     * @param array $groupedItems
     * @param string[] $keepCodes
     * @return array
     */
    private function applyRandomSortingToAnswers(array $groupedItems, array $keepCodes): array
    {
        foreach ($groupedItems as $scaleId => $scaleArray) {
            if (empty($scaleArray)) {
                $groupedItems[$scaleId] = [];
                continue;
            }

            if (empty($keepCodes)) {
                $keys = array_keys($scaleArray);
                shuffle($keys);

                $sortedItems = [];
                foreach ($keys as $key) {
                    $sortedItems[] = $scaleArray[$key];
                }
                $groupedItems[$scaleId] = $sortedItems;
                continue;
            }

            $fixedByIndex = [];
            $floating = [];
            foreach ($scaleArray as $index => $answer) {
                if (in_array($answer->code, $keepCodes, true)) {
                    $fixedByIndex[$index] = $answer;
                } else {
                    $floating[] = $answer;
                }
            }

            if (!empty($floating)) {
                shuffle($floating);
            }

            $sortedItems = [];
            $floatingIndex = 0;
            $total = count($scaleArray);
            for ($i = 0; $i < $total; $i++) {
                if (array_key_exists($i, $fixedByIndex)) {
                    $sortedItems[] = $fixedByIndex[$i];
                } else {
                    $sortedItems[] = $floating[$floatingIndex++];
                }
            }

            $groupedItems[$scaleId] = $sortedItems;
        }

        return $groupedItems;
    }

    /**
     * Apply random sorting to grouped subquestions, respecting keep_codes_order.
     *
     * @param array $groupedSubquestions
     * @param string[] $keepCodes
     * @return array
     */
    private function applyRandomSortingToSubquestionGroups(
        array $groupedSubquestions,
        array $keepCodes
    ): array {
        foreach ($groupedSubquestions as $scaleId => &$scaleArray) {
            if (empty($scaleArray)) {
                $scaleArray = [];
                continue;
            }

            if (empty($keepCodes)) {
                $scaleArray = \ls\mersenne\shuffle($scaleArray);
                continue;
            }

            $fixedByIndex = [];
            $floating = [];
            foreach ($scaleArray as $index => $subquestion) {
                if (in_array($subquestion->title, $keepCodes, true)) {
                    $fixedByIndex[$index] = $subquestion;
                } else {
                    $floating[] = $subquestion;
                }
            }

            if (!empty($floating)) {
                $floating = \ls\mersenne\shuffle($floating);
            }

            $sortedSubquestions = [];
            $floatingIndex = 0;
            $total = count($scaleArray);
            for ($i = 0; $i < $total; $i++) {
                if (array_key_exists($i, $fixedByIndex)) {
                    $sortedSubquestions[] = $fixedByIndex[$i];
                } else {
                    $sortedSubquestions[] = $floating[$floatingIndex++];
                }
            }

            $scaleArray = $sortedSubquestions;
        }

        return $groupedSubquestions;
    }
}
