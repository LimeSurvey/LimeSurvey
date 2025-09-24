<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use Question;

class MultipleChoiceProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = $this->question['sid'] . 'X' . $this->question['gid'] . 'X' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();

        $legend = [];
        $dataItems = [];
        $field = null;

        foreach ($this->question['subQuestions'] ?? [] as $subQuestion) {
            $field = $this->rt . $subQuestion['title'];
            $legend[] = $subQuestion['question'];

            $count = $this->getResponseCount($field);
            $dataItems[] = [
                'key' => $subQuestion['title'],
                'title' => $subQuestion['question'],
                'value' => $count,
            ];
        }

        $dataItems[] = [
            'key' => 'NotAnswered',
            'title' => 'Not Answered',
            'value' => $this->getResponseNotAnsweredCount($field),
        ];
        $legend[] = 'Not Answered';

        if ($this->question['other'] === Question::QT_Y_YES_NO_RADIO) {
            $field = $this->rt . 'other';
            $legend[] = 'other';
            $count = $this->getResponseCount($field);
            $dataItems[] = ['key' => 'other', 'title' => 'Other', 'value' => $count];
        }

        return new StatisticsChartDTO(
            $this->question['question'],
            $legend,
            $dataItems,
            null,
            ['question' => $this->question]
        );
    }
}
