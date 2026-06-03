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

        $fieldNames = [];
        foreach ($this->question['subQuestions'] ?? [] as $subQuestion) {
            $fieldNames[] = $this->rt . '_S' . $subQuestion['qid'];
        }
        $hasOther = $this->question['other'] === Question::QT_Y_YES_NO_RADIO;
        if ($hasOther) {
            $fieldNames[] = $this->rt . '_Cother';
        }

        $counts = $this->batchGetResponseCounts($fieldNames);

        foreach ($this->question['subQuestions'] ?? [] as $subQuestion) {
            $field = $this->rt . "_S" . $subQuestion['qid'];
            $count = $counts[$field] ?? 0;
            $legend[] = $subQuestion['question'];
            $dataItems[] = [
                'key' => $subQuestion['title'],
                'title' => $subQuestion['question'],
                'value' => $count,
            ];
        }

        if ($hasOther) {
            $field = $this->rt . '_Cother';
            $legend[] = 'other';
            $dataItems[] = ['key' => 'other', 'title' => 'Other', 'value' => $counts[$field] ?? 0];
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
