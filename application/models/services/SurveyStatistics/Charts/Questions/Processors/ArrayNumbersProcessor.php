<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

class ArrayNumbersProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();
        $charts = [];
        [$min, $max, $step] = $this->getValues();
        $values = [];
        $groups = [];

        for ($i = $min; $i <= $max; $i += $step) {
            $values[] = $i;
        }
        $strValues = array_map('strval', $values);

        foreach ($this->question['subQuestions'] as $o) {
            $groups[$o['scale_id']][] = $o['qid'];
        }

        $groupedSubQuestions = array_merge(
            ...array_map(
                fn($c0) => array_map(fn($c1) => "{$c0}_{$c1}", $groups[1]),
                $groups[0]
            )
        );

        $fieldMeta = []; // fieldName => [subQuestion1, subQuestion2]
        foreach ($groupedSubQuestions as $questionIdConcat) {
            $questionId = explode('_', $questionIdConcat);
            $subQuestion1 = $this->question['subQuestions'][$questionId[0]];
            $subQuestion2 = $this->question['subQuestions'][$questionId[1]];
            $field = $this->rt . '_S' . $subQuestion1['qid'] . '_S' . $subQuestion2['qid'];
            $fieldMeta[$field] = [$subQuestion1, $subQuestion2];
        }

        $batch = $this->buildBatchItemsForSubquestions(array_keys($fieldMeta), $strValues, $strValues);

        foreach ($fieldMeta as $field => [$subQuestion1, $subQuestion2]) {
            [$legend, $dataItems] = $batch[$field];
            $charts[] = [
                'title' => $this->question['question'] . ' [' . $subQuestion1['question'] . '] [' . $subQuestion2['question'] . ']',
                'legend' => $legend,
                'data' => $dataItems,
            ];
        }

        return $charts;
    }

    private function getValues()
    {
        $minValue = 1;
        $maxValue = 10;
        $attributes = $this->question['attributes'] ?? [];
        $checkbox = $attributes['multiflexible_checkbox'] ?? 0;
        $multiflexibleMin = $attributes['multiflexible_min'] ?? '';
        $multiflexibleMax = $attributes['multiflexible_max'] ?? '';
        $multiflexibleStep = $attributes['multiflexible_step'] ?? '';
        $reverse = $attributes['reverse'] ?? 0;

        if ($checkbox != 0) {
            return [0, 1, 1];
        }

        if (trim((string) $multiflexibleMax) != '' && trim((string) $multiflexibleMin) == '') {
            $maxValue = $multiflexibleMax;
        }

        if (trim((string) $multiflexibleMin) != '' && trim((string) $multiflexibleMax) == '') {
            $minValue = $multiflexibleMin;
            $maxValue = $multiflexibleMin + 10;
        }

        if (trim((string) $multiflexibleMin) != '' && trim((string) $multiflexibleMax) != '') {
            if ($multiflexibleMin < $multiflexibleMax) {
                $minValue = $multiflexibleMin;
                $maxValue = $multiflexibleMax;
            }
        }

        $stepValue = (trim((string) $multiflexibleStep) != '' && $multiflexibleStep > 0) ? $multiflexibleStep : 1;

        if ((int) $reverse === 1) {
            [$minValue, $maxValue] = [$maxValue, $minValue];
            $stepValue = -$stepValue;
        }

        return [$minValue, $maxValue, $stepValue];
    }
}
