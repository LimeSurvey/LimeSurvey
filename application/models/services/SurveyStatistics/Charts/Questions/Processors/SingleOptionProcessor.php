<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\StatisticsChartDTO;
use Question;
use Survey;

class SingleOptionProcessor extends AbstractQuestionProcessor
{
    public function rt(): void
    {
        $this->rt = $this->surveyId . 'X' . $this->question['gid'] . 'X' . $this->question['qid'];
    }

    public function process()
    {
        $this->rt();

        $chart = $this->buildChartDataByType();
        $legend = $chart['legend'];
        $title = $chart['title'];
        $dataItems = $chart['data'];

        if (
            in_array($this->question['type'], [Question::QT_L_LIST, Question::QT_EXCLAMATION_LIST_DROPDOWN])
            && $this->question['other'] == Question::QT_Y_YES_NO_RADIO
        ) {
            $mfield = $this->rt . 'other';
            $legend[] = 'other';
            $count = $this->getResponseCount($mfield, $this->surveyId);
            $dataItems[] = ['key' => 'other', 'value' => $count, 'title' => 'Other'];
        }
        if ($this->question['type'] == Question::QT_O_LIST_WITH_COMMENT) {
            $mfield = $this->rt . 'comment';
            $legend[] = 'comment';
            $count = $this->getResponseCount($mfield, $this->surveyId);
            $dataItems[] = ['key' => 'comment', 'value' => $count, 'title' => 'Comments'];
        }

        $legend[] = 'NoAnswer';
        $dataItems[] = ['key' => 'NoAnswer', 'value' => 0, 'title' => 'No answer'];

        return new StatisticsChartDTO(
            $title,
            $legend,
            $dataItems,
            array_sum(array_column($dataItems, 'value')),
            ['question' => $this->question]
        );
    }

    private function buildChartDataByType(): array
    {
        switch ($this->question['type']) {
            case Question::QT_G_GENDER:
                return $this->handleGender();

            case Question::QT_Y_YES_NO_RADIO:
                return $this->handleYesNo();

            case Question::QT_I_LANGUAGE:
                return $this->handleLanguage();

            case Question::QT_5_POINT_CHOICE:
                return $this->handle5PointChoice();

            default:
                return $this->handleDefault();
        }
    }

    private function handleGender(): array
    {
        $codes = ['F', 'M'];
        $labels = ['Female', 'Male'];

        [$legend, $items] = $this->buildItemsFromCodes($this->rt, $this->surveyId, $codes, $labels);
        return ['title' => $this->question['question'], 'legend' => $legend, 'data' => $items];
    }

    private function handleYesNo(): array
    {
        $codes = ['Y', 'N'];
        $labels = ['Yes', 'No'];

        [$legend, $items] = $this->buildItemsFromCodes($this->rt, $this->surveyId, $codes, $labels);
        return ['title' => $this->question['title'], 'legend' => $legend, 'data' => $items];
    }

    private function handleLanguage(): array
    {
        /// TODO: REMOVE
        $langs = Survey::model()->findByPk($this->surveyId)->getAllLanguages();
        $codes = $langs;
        $labels = array_map(fn($l) => getLanguageNameFromCode($l, false), $langs);

        [, $items] = $this->buildItemsFromCodes($this->rt, $this->surveyId, $codes, $labels);
        $legend = array_column($items, 'title');
        return ['title' => $this->question['question'], 'legend' => $legend, 'data' => $items];
    }

    private function handle5PointChoice(): array
    {
        $codes = array_map('strval', range(1, 5));
        [$legend, $items] = $this->buildItemsFromCodes($this->rt, $this->surveyId, $codes, $codes);
        return ['title' => $this->question['question'], 'legend' => $legend, 'data' => $items];
    }

    private function handleDefault(): array
    {
        $legend = [];
        $items = [];

        foreach ($this->answers as $answer) {
            $code = $answer['code'];
            $title = flattenText($answer['answer']);

            $legend[] = $title;
            $count = $this->getResponseCount($this->rt, $this->surveyId, $code);
            $items[] = ['key' => $code, 'title' => $title, 'value' => $count];
        }

        return ['title' => $this->question['question'], 'legend' => $legend, 'data' => $items];
    }
}
