<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

/**
 * Array (Numbers): a numeric grid (Y subquestions × X columns). Each cell holds
 * a number, so it is summarised as the per-cell mean. The chart is a single
 * grouped chart: one row per Y subquestion, one bar per X column, the bar value
 * being the mean of the numbers entered for that cell.
 */
class ArrayNumbersProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();

        $groups = [];
        foreach ($this->question['subQuestions'] as $subQuestion) {
            $groups[$subQuestion['scale_id']][] = $subQuestion['qid'];
        }
        $rowQids = $groups[0] ?? [];
        $columnQids = $groups[1] ?? [];

        $data = [];
        $questionFields = [];
        foreach ($rowQids as $rowQid) {
            $row = $this->question['subQuestions'][$rowQid];
            $segments = [];
            $rowFields = [];
            foreach ($columnQids as $columnQid) {
                $column = $this->question['subQuestions'][$columnQid];
                $field = $this->rt . '_S' . $row['qid'] . '_S' . $column['qid'];
                $rowFields[] = $field;
                $segments[] = [
                    'key' => (string)$column['qid'],
                    'title' => $column['question'],
                    'value' => $this->meanOf($field),
                    'stats' => $this->statsOf($field),
                ];
            }
            $questionFields = array_merge($questionFields, $rowFields);
            $data[] = [
                'key' => $row['title'],
                'title' => $row['question'],
                'value' => empty($rowFields)
                    ? 0
                    : $this->read($this->batch->countAnyNonEmpty($rowFields)),
                'segments' => $segments,
            ];
        }

        $legend = !empty($data[0]['segments'])
            ? array_column($data[0]['segments'], 'title')
            : [];

        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $data,
            'total' => empty($questionFields)
                ? 0
                : $this->read($this->batch->countAnyNonEmpty($questionFields)),
        ];
    }

    /**
     * Deferred mean (sum / non-empty count) of a numeric column, resolved once
     * the batch has executed. Returns 0 when the column has no answers.
     *
     * @return callable
     */
    private function meanOf(string $field): callable
    {
        $sumAlias = $this->batch->sumValues($field);
        $countAlias = $this->batch->countNumeric($field);

        return function () use ($sumAlias, $countAlias): float {
            $count = $this->batch->value($countAlias);
            if ($count <= 0) {
                return 0;
            }
            return round($this->batch->value($sumAlias) / $count, 2);
        };
    }

    private function statsOf(string $field): callable
    {
        $sumAlias = $this->batch->sumValues($field);
        $countAlias = $this->batch->countNumeric($field);
        $medianAlias = $this->batch->medianValue($field);
        $minAlias = $this->batch->minValue($field);
        $maxAlias = $this->batch->maxValue($field);

        return function () use ($sumAlias, $countAlias, $medianAlias, $minAlias, $maxAlias): ?array {
            $count = $this->batch->value($countAlias);
            if ($count <= 0) {
                return null;
            }

            return [
                'mean' => round($this->batch->value($sumAlias) / $count, 2),
                'median' => round($this->batch->value($medianAlias), 2),
                'min' => round($this->batch->value($minAlias), 2),
                'max' => round($this->batch->value($maxAlias), 2),
            ];
        };
    }
}
