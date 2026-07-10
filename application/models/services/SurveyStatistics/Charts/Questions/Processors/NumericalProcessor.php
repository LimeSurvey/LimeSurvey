<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

/**
 * Numerical input: no chart, one data row whose `stats` object feeds the
 * descriptive-statistics table. The response column is DECIMAL, so all
 * aggregates run in numeric-column mode (blank = NULL).
 */
class NumericalProcessor extends AbstractQuestionProcessor
{
    public function process()
    {
        $this->rt();
        $field = $this->rt;

        $answered = $this->read($this->batch->countNonEmpty($field, true));

        return [
            'title' => $this->question['question'],
            'legend' => [],
            'data' => [
                [
                    'key' => $this->question['title'],
                    'title' => $this->question['question'],
                    'value' => $answered,
                    'stats' => $this->statsOf($field),
                ],
            ],
            'total' => $answered,
        ];
    }

    /**
     * Deferred stats; null when nobody answered. Standard deviation is the
     * population deviation (÷ n, like legacy): variance = Σx²/n − mean².
     */
    private function statsOf(string $field): callable
    {
        $countAlias = $this->batch->countNonEmpty($field, true);
        $sumAlias = $this->batch->sumValues($field, true);
        $sumSquaresAlias = $this->batch->sumSquares($field, true);
        $medianAlias = $this->batch->medianValue($field, true);
        $minAlias = $this->batch->minValue($field, true);
        $maxAlias = $this->batch->maxValue($field, true);

        return function () use ($countAlias, $sumAlias, $sumSquaresAlias, $medianAlias, $minAlias, $maxAlias): ?array {
            $count = (int)$this->batch->value($countAlias);
            if ($count <= 0) {
                return null;
            }

            $sum = $this->batch->value($sumAlias);
            $mean = $sum / $count;
            $variance = max(0, $this->batch->value($sumSquaresAlias) / $count - $mean * $mean);

            return [
                'count' => $count,
                'sum' => round($sum, 2),
                'standardDeviation' => round(sqrt($variance), 2),
                'mean' => round($mean, 2),
                'min' => round($this->batch->value($minAlias), 2),
                'max' => round($this->batch->value($maxAlias), 2),
                'median' => round($this->batch->value($medianAlias), 2),
            ];
        };
    }
}
