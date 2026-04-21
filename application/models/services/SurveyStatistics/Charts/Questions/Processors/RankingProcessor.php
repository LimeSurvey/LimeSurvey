<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;

class RankingProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = $this->question['type'] . 'Q' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();
        $charts = [];

        $model = \SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();

        foreach ($this->question['subQuestions'] as $subQuestion) {
            $title = $subQuestion['title'];
            $legends = [];
            $dataItems = [];
            $index = 0;
            $fields = [];
            foreach ($this->question['subQuestions'] as $subQuestionInner) {
                $index++;
                $fieldName = "RANK {$index}";
                $legends[] = $fieldName;
                $fields[] = "SUM(CASE WHEN " . $db->quoteColumnName(substr($this->rt, 1) . "_S" . $subQuestionInner['qid']) . " = :title THEN 1 ELSE 0 END) AS " . $db->quoteColumnName($fieldName);
            }
            $currentResults = $this->getAggregateResponses($title, $fields);
            foreach ($legends as $fieldName) {
                $dataItems[] = [
                    'key' => $subQuestion['title'],
                    'title' => $fieldName,
                    'value' => $currentResults[$fieldName]
                ];
            }
            $charts[] = new StatisticsChartDTO($this->question['question'] . ": " . $subQuestion['question'], $legends, $dataItems, $this->calculateTotal($dataItems), ['question' => $this->question]);
        }

        /*$legends[] = 'NoAnswer';
        $dataItems[] = ['key' => 'NoAnswer', 'value' => 0, 'title' => 'No answer'];

        $charts[] = new StatisticsChartDTO($title, $legends, $dataItems, $this->calculateTotal($dataItems), ['question' => $this->question]);*/

        return $charts;
    }
}
