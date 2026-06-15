<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

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
        $subQuestions = $this->question['subQuestions'];

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
        $index = 0;
        foreach ($subQuestions as $subQuestion) {
            $index++;
            $legends = [];
            $dataItems = [];
            for ($rank = 1; $rank <= $rankCount; $rank++) {
                $fieldName = 'RANK ' . $rank;
                $legends[] = $fieldName;
                $rankCol = $rankColumns[$rank - 1];
                $dataItems[] = [
                    'key' => $subQuestion['title'],
                    'title' => $fieldName,
                    'value' => $items[$rankCol][1][$index - 1]['value'],
                ];
            }
            $charts[] = [
                'title' => $this->question['question'] . ': ' . $subQuestion['question'],
                'legend' => $legends,
                'data' => $dataItems,
            ];
        }

        return $charts;
    }
}
