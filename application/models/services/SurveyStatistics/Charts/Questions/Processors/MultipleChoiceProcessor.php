<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use Question;

class MultipleChoiceProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    public function process()
    {

        $legend = [];
        $dataItems = [];
        $field = null;

        foreach ($this->question['subQuestions'] ?? [] as $subQuestion) {
            $field = $this->rt . "_S" . $subQuestion['qid'];
            $legend[] = $subQuestion['question'];

            $count = $this->getResponseCount($field);
            $dataItems[] = [
                'key' => $subQuestion['title'],
                'title' => $subQuestion['question'],
                'value' => $count,
            ];
        }

        if ($this->question['other'] === Question::QT_Y_YES_NO_RADIO) {
            $field = $this->rt . '_Cother';
            $legend[] = 'other';
            $count = $this->getResponseCount($field);
            $dataItems[] = ['key' => 'other', 'title' => 'Other', 'value' => $count];
        }

        return new StatisticsChartDTO(
            $this->question['question'],
            $legend,
            $dataItems,
            $this->calculateTotal($dataItems),
            ['question' => $this->question]
        );
    }
}
