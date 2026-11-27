<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

class TextProcessor extends AbstractQuestionProcessor
{
    #[\Override]
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    /**
     * Count answered and unanswered responses of a text question.
     *
     * @return array Chart plan with the question title, the legend and the "Answer" and "No answer" data items
     */
    #[\Override]
    public function process()
    {
        $this->rt();

        $totalResponses = $this->getTotalCount();
        $answered = $this->countFieldResponses($this->rt);

        $legend = ['Answer', 'NoAnswer'];
        $dataItems = [
            ['key' => 'Answer', 'title' => gT('Answer', 'unescaped'), 'value' => $answered],
            [
                'key' => 'NoAnswer',
                'title' => gT('No answer', 'unescaped'),
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
