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

        // Ranking questions now use subquestions for items to rank
        foreach ($this->question['subQuestions'] as $subQuestion) {
            $title = flattenText($this->question['question']) . " [{$subQuestion['question']}]";
            $dataItems = [];
            $legend = [];

            $rt = $this->rt . "_S" . $subQuestion['qid'];
            $value = $this->getResponseCount($rt, $subQuestion['title']);
            $dataItems[] = [
                'key' => $subQuestion['title'],
                'title' => sprintf(gT('Rank %s'), $subQuestion['title']),
                'value' => $value
            ];
        }
        $legend[] = 'NoAnswer';
        $dataItems[] = ['key' => 'NoAnswer', 'value' => 0, 'title' => 'No answer'];

        $charts[] = new StatisticsChartDTO($title, $legend, $dataItems, $this->calculateTotal($dataItems), ['question' => $this->question]);

        return $charts;
    }
}
