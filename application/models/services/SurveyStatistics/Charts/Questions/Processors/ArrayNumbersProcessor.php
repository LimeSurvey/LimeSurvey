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
        foreach ($rowQids as $rowQid) {
            $row = $this->question['subQuestions'][$rowQid];
            $segments = [];
            foreach ($columnQids as $columnQid) {
                $column = $this->question['subQuestions'][$columnQid];
                $field = $this->rt . '_S' . $row['qid'] . '_S' . $column['qid'];
                $segments[] = [
                    'key' => (string)$column['qid'],
                    'title' => $column['question'],
                    'value' => $this->meanOf($field),
                ];
            }
            $data[] = [
                'key' => $row['title'],
                'title' => $row['question'],
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
        $countAlias = $this->batch->countNonEmpty($field);

        return function () use ($sumAlias, $countAlias): int {
            $count = $this->batch->value($countAlias);
            if ($count <= 0) {
                return 0;
            }
            return (int) round($this->batch->value($sumAlias) / $count);
        };
    }
}
