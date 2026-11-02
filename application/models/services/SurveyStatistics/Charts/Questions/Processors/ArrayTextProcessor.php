<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

/**
 * Handles only Array (Texts) questions: this free-text grid has no chart data,
 * so the processor emits an empty plan to claim a statistics card slot. The
 * client renders the per-response answers as a table (responses × subquestions)
 * fetched from the responses endpoint. K and Q response grids use the client
 * response-grid component instead.
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
