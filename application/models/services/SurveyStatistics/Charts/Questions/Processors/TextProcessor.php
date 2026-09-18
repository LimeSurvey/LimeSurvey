<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

class TextProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();

        $totalResponses = $this->getTotalCount();
        $answered = $this->countFieldResponses($this->rt);

        $legend = ['Answer', 'NoAnswer'];
        $dataItems = [
            ['key' => 'Answer', 'title' => 'Answer', 'value' => $answered],
            [
                'key' => 'NoAnswer',
                'title' => 'No answer',
                'value' => fn(): int => $totalResponses() - $answered(),
            ],
        ];

        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $dataItems,
        ];
    }
}
