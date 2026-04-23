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
        $subQuestions = array_values($this->question['subQuestions']);

        // Build the rank column names
        $rankColumns = [];
        foreach ($subQuestions as $subQuestion) {
            $rankColumns[] = substr($this->rt, 1) . '_S' . $subQuestion['qid'];
        }

        $codes = array_column($subQuestions, 'title');
        $labels = array_column($subQuestions, 'question');
        $items = $this->buildBatchItemsForSubquestions($rankColumns, $codes, $labels);

        // Re-assemble into per-item charts
        $rankCount = count($subQuestions);
        foreach ($subQuestions as $sqidx => $subQuestion) {
            $legends = [];
            $dataItems = [];
            for ($rank = 1; $rank <= $rankCount; $rank++) {
                $fieldName = 'RANK ' . $rank;
                $legends[] = $fieldName;
                $rankCol = $rankColumns[$rank - 1];
                $count = (int)(($items[$rankCol][1][$sqidx]['value'] ?? 0));
                $dataItems[] = [
                    'key' => $subQuestion['title'],
                    'title' => $fieldName,
                    'value' => $count,
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
