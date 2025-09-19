<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;

class TextProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = $this->question['sid'] . 'X' . $this->question['gid'] . 'X' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();

        $legend = ['Answer', 'NoAnswer'];
        $count = $this->getResponseCount($this->rt, $this->question['sid']);
        $dataItems = [
            ['key' => 'Answer', 'title' => 'Answer', 'value' => $count],
            ['key' => 'NoAnswer', 'title' => 'No answer', 'value' => $this->getResponseNotAnsweredCount($this->rt, $this->question['sid'])],
        ];

        return new StatisticsChartDTO(
            $this->question['question'],
            $legend,
            $dataItems,
            $count,
            ['question' => $this->question]
        );
    }
}
