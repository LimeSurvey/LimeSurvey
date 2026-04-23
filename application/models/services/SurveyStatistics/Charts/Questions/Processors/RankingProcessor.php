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
        $subQuestions = $this->question['subQuestions'];

        // Build the rank column names
        $rankColumns = [];
        foreach ($subQuestions as $subQuestion) {
            $rankColumns[] = $db->quoteColumnName(substr($this->rt, 1) . '_S' . $subQuestion['qid']);
        }
        $columnLimit = 1500;
        $exceedsLimit = count($subQuestions) ** 2 > $columnLimit;

        // Build all cases with aliases SQ{sqid}_RANK{i}
        $params = [];
        $selectParts = [];
        foreach ($subQuestions as $sqid => $subQuestion) {
            $paramKey = ':title_' . $sqid;
            $params[$paramKey] = $subQuestion['title'];
            $rank = 0;
            foreach ($rankColumns as $rankCol) {
                $rank++;
                $alias = 'SQ' . $sqid . '_RANK' . $rank;
                $selectParts[] = "SUM(CASE WHEN {$rankCol} = {$paramKey} THEN 1 ELSE 0 END) AS " . $db->quoteColumnName($alias);
            }
        }

        // empty chart if exceeds limit TODO: revisit to split in chunks if needed
        $row = $exceedsLimit ? [] : $this->getAggregateResponses($selectParts, $params);

        // Re-assemble into charts
        foreach ($subQuestions as $sqid => $subQuestion) {
            $legends   = [];
            $dataItems = [];
            $rankCount = count($subQuestions);
            for ($rank = 1; $rank <= $rankCount; $rank++) {
                $fieldName = 'RANK ' . $rank;
                $legends[] = $fieldName;
                $alias = 'SQ' . $sqid . '_RANK' . $rank;
                $dataItems[] = [
                    'key' => $subQuestion['title'],
                    'title' => $fieldName,
                    'value' => (int)($row[$alias] ?? 0),
                ];
            }
            $charts[] = new StatisticsChartDTO(
                $this->question['question'] . ': ' . $subQuestion['question'],
                $legends,
                $dataItems,
                $this->calculateTotal($dataItems),
                ['question' => $this->question]
            );
        }

        return $charts;
    }
}
