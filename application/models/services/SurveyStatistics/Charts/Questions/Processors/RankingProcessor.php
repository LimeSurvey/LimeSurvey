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

        // One response column per rank slot; each holds the code of the answer
        // placed at that rank.
        $rankColumns = [];
        foreach ($subQuestions as $subQuestion) {
            $rankColumns[] = substr($this->rt, 1) . '_S' . $subQuestion['qid'];
        }

        $codes = array_column($subQuestions, 'title');
        $labels = array_column($subQuestions, 'question');
        $items = $this->buildBatchItemsForSubquestions($rankColumns, $codes, $labels);

        $rankCount = count($subQuestions);

        // One combined chart: a single bar per answer option. The bar value is
        // the total number of responses that ranked the option (summed across
        // all positions), and the full per-rank breakdown ('ranks') is attached
        // so the client can show every position's count in a modal/table. The
        // client sorts highest-to-lowest and labels the bars by their
        // leaderboard place (1st, 2nd, ...).
        $legend = [];
        $dataItems = [];
        $position = 0;
        foreach ($subQuestions as $subQuestion) {
            $ranks = [];
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
