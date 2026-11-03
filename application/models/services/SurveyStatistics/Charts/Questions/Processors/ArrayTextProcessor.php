<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

/**
 * Handles Array (Texts) and Multiple short text questions: their free-text
 * answers have no chart data, so the processor emits an empty plan to claim a
 * statistics card slot. The client fetches the per-response answers from the
 * responses endpoint and renders them itself: Array (Texts) as a table
 * (responses × subquestions), Multiple short text as a per-response grid.
 */
class ArrayTextProcessor extends AbstractQuestionProcessor
{
    #[\Override]
    public function process()
    {
        return [
            'title' => $this->question['question'],
            'legend' => [],
            'data' => [],
        ];
    }
}
