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

        $legends = [];
        $dataItems = [];
        $codes = [];
        $fields = [];
        $fieldNames = [];
        $title = flattenText($this->question['question']);
        $model = \SurveyDynamic::model($this->surveyId);
        $db = $model->getDbConnection();
        $initialize = true;

        foreach ($this->question['subQuestions'] as $subQuestion) {
            $index = 0;
            foreach ($this->question['subQuestions'] as $subQuestionInner) {
                $index++;
                $fields[] = "SUM(CASE WHEN " . substr($this->rt, 1) . "_S" . $subQuestion['qid'] . " = :field{$index} THEN 1 ELSE 0 END) AS " . $db->quoteColumnName($subQuestionInner['title']);
                if ($initialize) {
                    $fieldNames["field{$index}"] = $subQuestionInner['title'];
                    $codes[$subQuestionInner['title']] = 0;
                }
            }
            $currentResults = $this->getAggregateResponses($fieldNames, $fields);
            foreach ($fieldNames as $fieldName) {
                $codes[$fieldName] += $currentResults[$fieldName];
            }
            $initialize = false;
        }

        // Ranking questions now use subquestions for items to rank
        foreach ($this->question['subQuestions'] as $subQuestion) {
            $legends[] = flattenText($this->question['question']) . " [{$subQuestion['question']}]";

            $dataItems[] = [
                'key' => $subQuestion['title'],
                'title' => sprintf(gT('Rank %s'), $subQuestion['question']),
                'value' => $codes[$subQuestion['title']]
            ];
        }
        $legends[] = 'NoAnswer';
        $dataItems[] = ['key' => 'NoAnswer', 'value' => 0, 'title' => 'No answer'];

        $charts[] = new StatisticsChartDTO($title, $legends, $dataItems, $this->calculateTotal($dataItems), ['question' => $this->question]);

        return $charts;
    }
}
