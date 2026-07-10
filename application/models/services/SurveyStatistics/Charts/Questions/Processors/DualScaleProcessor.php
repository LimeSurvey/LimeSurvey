<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

class DualScaleProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();

        $scaleName = [
            0 => flattenText($this->question['attributes']['dualscale_headerA'] ?? 'Scale A', false, true),
            1 => flattenText($this->question['attributes']['dualscale_headerB'] ?? 'Scale B', false, true),
        ];

        $answersByScale = [0 => [], 1 => []];
        foreach ($this->answers ?? [] as $ans) {
            $answersByScale[(int)$ans['scale_id']][] = $ans;
        }

        $data = [];
        foreach ($this->question['subQuestions'] as $subQuestion) {
            foreach ([0, 1] as $scaleId) {
                $field = $this->rt . "_S" . $subQuestion['qid'] . '#' . $scaleId;

                $segments = [];
                foreach ($answersByScale[$scaleId] as $ans) {
                    $code = (string)$ans['code'];
                    $segments[] = [
                        'key' => $code,
                        'title' => (string)$ans['answer'],
                        'value' => $this->read($this->batch->countValue($field, $code)),
                    ];
                }
                $segments[] = [
                    'key' => 'NoAnswer',
                    'title' => 'No answer',
                    'value' => $this->read($this->batch->countBlank($field)),
                ];

                $data[] = [
                    'key' => $subQuestion['title'] . '#' . $scaleId,
                    'title' => $subQuestion['question'] ?? $subQuestion['title'],
                    'scaleTitle' => (string)$scaleName[$scaleId],
                    'segments' => $segments,
                ];
            }
        }

        return [
            'title' => $this->question['question'],
            'legend' => $this->buildLegend($answersByScale),
            'data' => $data,
        ];
    }

    /**
     * @param array<int, array[]> $answersByScale
     * @return string[]
     */
    private function buildLegend(array $answersByScale): array
    {
        $legend = [];
        foreach ([0, 1] as $scaleId) {
            foreach ($answersByScale[$scaleId] as $ans) {
                $label = (string)$ans['answer'];
                if (!in_array($label, $legend, true)) {
                    $legend[] = $label;
                }
            }
        }
        $legend[] = 'NoAnswer';

        return $legend;
    }
}
