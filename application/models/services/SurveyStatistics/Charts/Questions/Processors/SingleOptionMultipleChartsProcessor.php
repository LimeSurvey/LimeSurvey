<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use Question;

class SingleOptionMultipleChartsProcessor extends AbstractQuestionProcessor
{
    public function process()
    {
        return $this->buildChartDataByType();
    }

    private function buildChartDataByType(): array
    {
        switch ($this->question['type']) {
            case Question::QT_A_ARRAY_5_POINT:
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                return $this->handleArray5Or10();

            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                return $this->handleYesUncertainNo();

            case Question::QT_E_ARRAY_INC_SAME_DEC:
                return $this->handleIncSameDec();

            case Question::QT_F_ARRAY:
            case Question::QT_H_ARRAY_COLUMN:
                return $this->handleFOrHArray();

            default:
                return [];
        }
    }

    private function handleArray5Or10(): array
    {
        $charts = [];
        $max = $this->question['type'] == Question::QT_A_ARRAY_5_POINT ? 5 : 10;
        $codes = array_map('strval', range(1, $max));

        foreach ($this->question['subQuestions'] as $subQuestion) {
            $rt = $this->rt . "_S" . $subQuestion['qid'];
            [$legend, $items] = $this->buildItemsFromCodes($rt, $codes, $codes);
            $title = $this->question['question'] . '(' . $subQuestion['question'] . ')';

            $charts[] = new StatisticsChartDTO($title, $legend, $items, $this->calculateTotal($items), ['question' => $this->question]);
        }

        return $charts;
    }

    private function handleYesUncertainNo(): array
    {
        $codes = ['Y', 'N', 'U'];
        $labels = ['Yes', 'No', 'Uncertain'];
        $charts = [];

        foreach ($this->question['subQuestions'] as $subQuestion) {
            $rt = $this->rt . "_S" . $subQuestion['qid'];
            [$legend, $items] = $this->buildItemsFromCodes($rt, $codes, $labels);
            $title = $this->question['question'] . "[{$subQuestion['question']}]";

            $charts[] = new StatisticsChartDTO($title, $legend, $items, $this->calculateTotal($items), ['question' => $this->question]);
        }

        return $charts;
    }

    private function handleIncSameDec(): array
    {
        foreach ($this->question['subQuestions'] as $subQuestion) {
            $title = $this->question['question'] . "[{$subQuestion['question']}]";
            $codes = ['I', 'S', 'D'];
            $labels = ['Increase', 'Same', 'Decrease'];
            $rt = $this->rt . "_S" . $subQuestion['qid'];
            [$legend, $items] = $this->buildItemsFromCodes($rt, $codes, $labels);

            $charts[] = new StatisticsChartDTO($title, $legend, $items, $this->calculateTotal($items), ['question' => $this->question]);
        }

        return $charts;
    }

    private function handleFOrHArray(): array
    {
        $mainQuestionTitle = $this->question['question'];
        $stats = [];

        foreach ($this->question['subQuestions'] as $subQuestion) {

            $title = $mainQuestionTitle . "[{$subQuestion['question']}]";
            $legend = [];
            $items = [];

            if ((int)$subQuestion['scale_id'] === 0) {
                $count = $this->getResponseCount($this->rt . "_S" . $subQuestion['qid']);
                $legend[] = $subQuestion['question'];
                $items[] = ['key' => $subQuestion['title'], 'value' => $count, 'title' => $subQuestion['question']];
            }

            $stats[] = new StatisticsChartDTO($title, $legend, $items, $this->calculateTotal($items), ['question' => $this->question]);
        }

        return $stats;
    }
}
