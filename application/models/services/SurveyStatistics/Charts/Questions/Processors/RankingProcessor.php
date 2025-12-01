<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;

class RankingProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = $this->question['type'] . 'Q' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();
        $charts = [];
        $i = 0;

        foreach ($this->question['subQuestions'] as $subQuestion) {
            $title = flattenText($this->question['question']) . " [{$subQuestion['question']}]";
            $dataItems = [];
            $legend = [];

            foreach ($this->answers as $answer) {
                $rt = $this->rt . "_R" . $answer['aid'];

                if ((int)$answer->scale_id === 0) {
                    $value = $this->getResponseCount($rt, $answer['code']);
                    $dataItems[] = ['key' => $answer['code'], 'title' => $answer['answer'], 'value' => $value];
                }
            }
            $legend[] = 'NoAnswer';
            $dataItems[] = ['key' => 'NoAnswer', 'value' => 0, 'title' => 'No answer'];

            $charts[] = new StatisticsChartDTO($title, $legend, $dataItems, null, ['question' => $this->question]);
            $i++;
        }

        return $charts;
    }
}
