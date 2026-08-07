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
        $subQuestions = $this->question['subQuestions'];

        $column = substr($this->rt, 1);
        $rawValues = $this->fetchColumnValues($column);

        $rankCount = count($subQuestions);

        $counts = [];
        foreach ($rawValues as $rawValue) {
            if ($rawValue === null || $rawValue === '') {
                continue;
            }
            $ranking = json_decode((string)$rawValue, true);
            if (!is_array($ranking)) {
                continue;
            }
            $rank = 0;
            foreach ($ranking as $itemCode) {
                $rank++;
                if ($rank > $rankCount) {
                    break;
                }
                $itemCode = (string)$itemCode;
                $counts[$itemCode][$rank] = ($counts[$itemCode][$rank] ?? 0) + 1;
            }
        }

        foreach ($subQuestions as $subQuestion) {
            $itemCode = (string)$subQuestion['title'];
            $legends = [];
            $dataItems = [];
            for ($rank = 1; $rank <= $rankCount; $rank++) {
                $fieldName = 'RANK ' . $rank;
                $legends[] = $fieldName;
                $dataItems[] = [
                    'key' => $subQuestion['title'],
                    'title' => $fieldName,
                    'value' => (int)($counts[$itemCode][$rank] ?? 0),
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
