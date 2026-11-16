<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use Question;

class MultipleChoiceProcessor extends AbstractQuestionProcessor
{
    #[\Override]
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    #[\Override]
    public function process()
    {
        $legend = [];
        $dataItems = [];

        $fieldNames = [];
        foreach ($this->question['subQuestions'] ?? [] as $subQuestion) {
            $fieldNames[] = $this->rt . '_S' . $subQuestion['qid'];
        }
        $hasOther = $this->question['other'] === Question::QT_Y_YES_NO_RADIO;
        $otherField = $this->rt . '_Cother';

        $counts = $this->batchGetResponseCounts($fieldNames);
        if ($hasOther) {
            $survey = \Survey::model()->findByPk($this->surveyId);
            $counts[$otherField] = $survey && $survey->hasOtherCheckedColumn($this->question['qid'])
                ? $this->read($this->batch->countAnyNonEmpty([$otherField, $this->rt . '_Cotherchecked']))
                : $this->countFieldResponses($otherField);
        }

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
            $legend[] = 'other';
            $dataItems[] = ['key' => 'other', 'title' => 'Other', 'value' => $counts[$otherField], 'field' => $otherField];
        }

        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $dataItems,
        ];
    }
}
