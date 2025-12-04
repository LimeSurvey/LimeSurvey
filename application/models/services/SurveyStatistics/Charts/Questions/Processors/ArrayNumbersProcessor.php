<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use Question;

class ArrayNumbersProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = $this->question['sid'] . 'X' . $this->question['gid'] . 'X' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();
        $charts = [];
        [$min, $max, $step] = $this->getValues();
        $values = [];

        for ($i = $min; $i <= $max; $i += $step) {
            $values[] = $i;
        }


        foreach ($this->question['subQuestions'] as $o) {
            $groups[$o['scale_id']][] = $o['qid'];
        }

        $groupedSubQuestions = array_merge(
            ...array_map(
                fn($c0) => array_map(fn($c1) => "{$c0}_{$c1}", $groups[1]),
                $groups[0]
            )
        );

        foreach ($groupedSubQuestions as $questionIdConcat) {
            $questionId = explode('_', $questionIdConcat);
            $subQuestion1 = $this->question['subQuestions'][$questionId[0]];
            $subQuestion2 = $this->question['subQuestions'][$questionId[1]];

            $field = $this->rt . $subQuestion1['title'] . '_' . $subQuestion2['title'];
            [$legend, $dataItems] = $this->buildItemsFromCodes($field, $values, $values);

            $charts[] = new StatisticsChartDTO(
                $this->question['question'] . ' [' . $subQuestion1['question'] . '] [' . $subQuestion2['question'] . ']',
                $legend,
                $dataItems,
                $this->calculateTotal($dataItems),
                ['question' => $this->question]
            );
        }

        return $charts;
    }

    private function getValues()
    {
        $minValue = 1;
        $maxValue = 10;
        $attributes = $this->question['attributes'] ?? [];

        if ($attributes['multiflexible_checkbox'] != 0) {
            return [0, 1, 1];
        }

        if (trim((string) $attributes['multiflexible_max']) != '' && trim((string) $attributes['multiflexible_min']) == '') {
            $maxValue = $attributes['multiflexible_max'];
        }

        if (trim((string) $attributes['multiflexible_min']) != '' && trim((string) $attributes['multiflexible_max']) == '') {
            $minValue = $attributes['multiflexible_min'];
            $maxValue = $attributes['multiflexible_min'] + 10;
        }

        if (trim((string) $attributes['multiflexible_min']) != '' && trim((string) $attributes['multiflexible_max']) != '') {
            if ($attributes['multiflexible_min'] < $attributes['multiflexible_max']) {
                $minValue = $attributes['multiflexible_min'];
                $maxValue = $attributes['multiflexible_max'];
            }
        }

        $stepValue = (trim((string) $attributes['multiflexible_step']) != '' && $attributes['multiflexible_step'] > 0) ? $attributes['multiflexible_step'] : 1;

        if ((int) $attributes['reverse'] === 1) {
            [$minValue, $maxValue] = [$maxValue, $minValue];
            $stepValue = -$stepValue;
        }

        return [$minValue, $maxValue, $stepValue];
    }
}
