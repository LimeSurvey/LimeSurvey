<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

/**
 * Multiple numerical input. It emits an empty plan only to claim a card slot in the
 * statistics list; the client renders the raw per-response answers as a grid.
 */
class MultipleNumericalProcessor extends AbstractQuestionProcessor
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
