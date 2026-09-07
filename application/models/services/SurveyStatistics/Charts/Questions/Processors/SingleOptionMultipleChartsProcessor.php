<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

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
                return $this->buildStackedChart(...$this->numericScale(5));

            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                return $this->buildStackedChart(...$this->numericScale(10));

            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                return $this->buildStackedChart(['Y', 'U', 'N'], ['Yes', 'Uncertain', 'No']);

            case Question::QT_E_ARRAY_INC_SAME_DEC:
                return $this->buildStackedChart(['I', 'S', 'D'], ['Increase', 'Same', 'Decrease']);

            case Question::QT_F_ARRAY:
            case Question::QT_H_ARRAY_COLUMN:
                return $this->buildStackedChart(...$this->answerScale());

            default:
                return [];
        }
    }

    /**
     * @return array{0: string[], 1: string[]}
     */
    private function numericScale(int $max): array
    {
        $codes = array_map('strval', range(1, $max));
        return [$codes, $codes];
    }

    /**
     * @return array{0: string[], 1: string[]}
     */
    private function answerScale(): array
    {
        $codes = [];
        $labels = [];
        foreach ($this->answers as $answer) {
            if ((int)($answer['scale_id'] ?? 0) !== 0) {
                continue;
            }
            $codes[] = (string)$answer['code'];
            $labels[] = (string)$answer['answer'];
        }

        return [$codes, $labels];
    }

    /**
     * @param string[] $codes  Answer codes that form the segments
     * @param string[] $labels Display labels aligned with $codes
     * @return array Single chart plan
     */
    private function buildStackedChart(array $codes, array $labels): array
    {
        $fieldMap = [];
        foreach ($this->question['subQuestions'] as $qid => $subQuestion) {
            if ((int)($subQuestion['scale_id'] ?? 0) !== 0) {
                continue;
            }
            $fieldMap[$qid] = $this->rt . "_S" . $subQuestion['qid'];
        }

        $batch = $this->buildBatchItemsForSubquestions(array_values($fieldMap), $codes, $labels);

        $data = [];
        foreach ($fieldMap as $qid => $field) {
            [, $items] = $batch[$field] ?? [[], []];
            $data[] = [
                'key' => $this->question['subQuestions'][$qid]['title'],
                'title' => $this->question['subQuestions'][$qid]['question'],
                'segments' => $items,
            ];
        }

        $legend = !empty($data[0]['segments'])
            ? array_column($data[0]['segments'], 'title')
            : $labels;

        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $data,
        ];
    }
}
