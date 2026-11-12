<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

class DualScaleProcessor extends AbstractQuestionProcessor
{
    #[\Override]
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    /**
     * Build one stacked bar per subquestion and scale of a dual-scale array question.
     *
     * @return array Chart plan with the question title, the legend and one data entry per subquestion and scale
     */
    #[\Override]
    public function process()
    {
        $this->rt();

        $scaleName = [
            0 => flattenText($this->question['attributes']['dualscale_headerA'] ?? gT('Scale A', 'unescaped'), false, true),
            1 => flattenText($this->question['attributes']['dualscale_headerB'] ?? gT('Scale B', 'unescaped'), false, true),
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
                    'title' => gT('No answer', 'unescaped'),
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
     * Build the legend from the answer labels of both scales, without duplicates, plus "No answer".
     *
     * @param array<int, array[]> $answersByScale Answer options grouped by scale id (0 and 1)
     * @return string[] Unique answer labels followed by the translated "No answer" label
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
        $noAnswer = gT('No answer', 'unescaped');
        if (!in_array($noAnswer, $legend, true)) {
            $legend[] = $noAnswer;
        }

        return $legend;
    }
}
