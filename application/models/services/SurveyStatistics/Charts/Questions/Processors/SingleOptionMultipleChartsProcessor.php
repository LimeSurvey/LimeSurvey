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

        $fieldMap = [];
        foreach ($this->question['subQuestions'] as $qid => $subQuestion) {
            $fieldMap[$qid] = $this->rt . "_S" . $subQuestion['qid'];
        }

        $batch = $this->buildBatchItemsForSubquestions(array_values($fieldMap), $codes, $codes);

        foreach ($this->question['subQuestions'] as $qid => $subQuestion) {
            $field = $fieldMap[$qid];
            [$legend, $items] = $batch[$field];
            $title = $this->question['question'] . '(' . $subQuestion['question'] . ')';
            $charts[] = new StatisticsChartDTO($title, $legend, $items, $this->calculateTotal($items), ['question' => $this->question]);
        }

        return $charts;
    }

    private function handleYesUncertainNo(): array
    {
        $codes = ['Y', 'N', 'U'];
        $labels = ['Yes', 'No', 'Uncertain'];

        $fieldMap = [];
        foreach ($this->question['subQuestions'] as $qid => $subQuestion) {
            $fieldMap[$qid] = $this->rt . "_S" . $subQuestion['qid'];
        }

        $batch = $this->buildBatchItemsForSubquestions(array_values($fieldMap), $codes, $labels);

        $charts = [];
        foreach ($this->question['subQuestions'] as $qid => $subQuestion) {
            $field = $fieldMap[$qid];
            [$legend, $items] = $batch[$field];
            $title = $this->question['question'] . '[' . $subQuestion['question'] . ']';
            $charts[] = new StatisticsChartDTO($title, $legend, $items, $this->calculateTotal($items), ['question' => $this->question]);
        }

        return $charts;
    }

    private function handleIncSameDec(): array
    {
        $codes = ['I', 'S', 'D'];
        $labels = ['Increase', 'Same', 'Decrease'];

        $fieldMap = [];
        foreach ($this->question['subQuestions'] as $qid => $subQuestion) {
            $fieldMap[$qid] = $this->rt . "_S" . $subQuestion['qid'];
        }

        $batch = $this->buildBatchItemsForSubquestions(array_values($fieldMap), $codes, $labels);

        $charts = [];
        foreach ($this->question['subQuestions'] as $qid => $subQuestion) {
            $field = $fieldMap[$qid];
            [$legend, $items] = $batch[$field];
            $title = $this->question['question'] . '[' . $subQuestion['question'] . ']';
            $charts[] = new StatisticsChartDTO($title, $legend, $items, $this->calculateTotal($items), ['question' => $this->question]);
        }

        return $charts;
    }

    private function handleFOrHArray(): array
    {
        $mainQuestionTitle = $this->question['question'];

        $scale0Fields = [];
        foreach ($this->question['subQuestions'] as $subQuestion) {
            if ((int)$subQuestion['scale_id'] === 0) {
                $scale0Fields[] = $this->rt . "_S" . $subQuestion['qid'];
            }
        }
        $counts = $this->batchGetResponseCounts($scale0Fields);

        $stats = [];
        foreach ($this->question['subQuestions'] as $subQuestion) {
            $title = $mainQuestionTitle . "[{$subQuestion['question']}]";
            $legend = [];
            $items = [];

            if ((int)$subQuestion['scale_id'] === 0) {
                $field = $this->rt . "_S" . $subQuestion['qid'];
                $count = $counts[$field] ?? 0;
                $legend[] = $subQuestion['question'];
                $items[] = ['key' => $subQuestion['title'], 'value' => $count, 'title' => $subQuestion['question']];
            }

            $stats[] = new StatisticsChartDTO($title, $legend, $items, $this->calculateTotal($items), ['question' => $this->question]);
        }

        return $stats;
    }
}
