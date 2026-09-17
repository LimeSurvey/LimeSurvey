<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

/**
 * Array (Texts): a free-text grid that cannot be aggregated into a chart, so it
 * has no chart data. It emits an empty plan only to claim a card slot in the
 * statistics list; the client renders the raw per-response answers as a table
 * (participant rows × subquestion columns) fetched from the responses endpoint.
 */
class ArrayTextProcessor extends AbstractQuestionProcessor
{
    public function process()
    {
        return [
            'title' => $this->question['question'],
            'legend' => [],
            'data' => [],
        ];
    }
}
