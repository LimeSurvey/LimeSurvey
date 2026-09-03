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

        // The whole ranking is one JSON array of codes per response, ordered
        // by rank. Per-rank counts are registered as JSON-element aggregates
        // so they still run inside the batch's single scan.
        $column = substr($this->rt, 1);

        // max_subquestions caps how many items a respondent may rank; higher
        // positions cannot occur, so they get no aggregates.
        $rankCount = count($subQuestions);
        $maxSubquestions = (int)($this->question['attributes']['max_subquestions'] ?? 0);
        if ($maxSubquestions > 0 && $maxSubquestions < $rankCount) {
            $rankCount = $maxSubquestions;
        }

        // One combined chart: a single bar per answer option. The bar value is
        // the total number of responses that ranked the option (summed across
        // all positions), and the full per-rank breakdown ('ranks') is attached
        // so the client can show every position's count in a modal/table. The
        // client sorts highest-to-lowest and labels the bars by their
        // leaderboard place (1st, 2nd, ...).
        $legend = [];
        $dataItems = [];
        foreach ($subQuestions as $subQuestion) {
            $code = (string)$subQuestion['title'];
            $ranks = [];
            for ($rank = 1; $rank <= $rankCount; $rank++) {
                // count of responses that placed this option at this rank
                $ranks[] = [
                    'position' => $rank,
                    'value' => $this->read(
                        $this->batch->countJsonArrayValue($column, $rank - 1, $code)
                    ),
                ];
            }

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
