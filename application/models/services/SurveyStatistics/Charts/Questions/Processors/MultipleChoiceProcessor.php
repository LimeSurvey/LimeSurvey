<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

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
            $legend[] = $subQuestion['question'];
            $dataItems[] = [
                'key' => $subQuestion['title'],
                'title' => $subQuestion['question'],
                'value' => $counts[$field],
                'field' => $field,
            ];
        }

        if ($hasOther) {
            $field = $this->rt . '_Cother';
            $legend[] = 'other';
            $dataItems[] = ['key' => 'other', 'title' => 'Other', 'value' => $counts[$field], 'field' => $field];
        }

        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $dataItems,
        ];
    }
}
