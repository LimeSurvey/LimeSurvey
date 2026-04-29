<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;

class TextProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();

        $totalResponses = AbstractQuestionProcessor::getTotalCount($this->surveyId);
        $answered = $this->countFieldResponses($this->rt);
        $notAnswered = $totalResponses - $answered;

        $legend = ['Answer', 'NoAnswer'];
        $dataItems = [
            ['key' => 'Answer', 'title' => 'Answer', 'value' => $answered],
            ['key' => 'NoAnswer', 'title' => 'No answer', 'value' => $notAnswered],
        ];

        return new StatisticsChartDTO(
            $this->question['question'],
            $legend,
            $dataItems,
            $this->calculateTotal($dataItems),
            ['question' => $this->question]
        );
    }
}
