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
            0 => $this->question['attributes']['dualscale_headerA'] ?? 'Scale A',
            1 => $this->question['attributes']['dualscale_headerB'] ?? 'Scale B',
        ];

        $answersByScale = [0 => [], 1 => []];
        foreach ($this->answers ?? [] as $ans) {
            $answersByScale[(int)$ans['scale_id']][] = $ans;
        }

        $qTitleBase = flattenText($this->question['question']);
        $charts = [];

        foreach ($this->question['subQuestions'] as $subQuestion) {
            $subCode = $subQuestion['title'];
            $subLabel = $subQuestion['question'] ?? $subCode;

            foreach ([0, 1] as $scaleId) {
                $field = $this->rt . "_S" . $subQuestion['qid'] . '#' . $scaleId;
                $legend = [];
                $dataItems = [];

                foreach ($answersByScale[$scaleId] as $ans) {
                    $code  = (string)$ans['code'];
                    $label = (string)$ans['answer'];

                    $legend[]    = $label;
                    $dataItems[] = [
                        'key' => $code,
                        'value' => $this->read($this->batch->countValue($field, $code)),
                        'title' => $label
                    ];
                }

                $legend[]    = 'NoAnswer';
                $dataItems[] = [
                    'key' => 'NoAnswer',
                    'value' => $this->read($this->batch->countBlank($field)),
                    'title' => 'No answer'
                ];

                $title = "{$qTitleBase} [{$scaleName[$scaleId]}] [{$subLabel}]";
                $charts[] = ['title' => $title, 'legend' => $legend, 'data' => $dataItems];
            }
        }

        return $charts;
    }
}
