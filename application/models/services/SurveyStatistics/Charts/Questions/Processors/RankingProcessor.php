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
                $rankCol = $rankColumns[$rank - 1];
                // count of responses that placed this option at this rank
                $ranks[] = [
                    'position' => $rank,
                    'value' => $items[$rankCol][1][$position]['value'],
                ];
            }
            $position++;

            $legend[] = $subQuestion['question'];
            $dataItems[] = [
                'key' => $subQuestion['title'],
                'title' => $subQuestion['question'],
                // Total times ranked (any position) drives the bar and ordering.
                'value' => function () use ($ranks) {
                    $total = 0;
                    foreach ($ranks as $rankRow) {
                        $total += (int) $rankRow['value']();
                    }
                    return $total;
                },
                'ranks' => $ranks,
            ];
        }

        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $dataItems,
        ];
    }
}
