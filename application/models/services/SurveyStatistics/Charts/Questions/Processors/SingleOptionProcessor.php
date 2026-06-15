<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

use Question;
use RuntimeException;
use Survey;

class SingleOptionProcessor extends AbstractQuestionProcessor
{
    /** @var array */
    private const SPECIAL_TYPES = [
        Question::QT_G_GENDER,
        Question::QT_Y_YES_NO_RADIO,
        Question::QT_I_LANGUAGE,
        Question::QT_5_POINT_CHOICE
    ];

    /**
     * @inheritDoc
     */
    public function rt(): void
    {
        $this->rt = 'Q' . $this->question['qid'];
    }

    /**
     * @inheritDoc
     * @return array Single chart plan
     */
    public function process(): array
    {
        $this->rt();

        $chart = $this->buildChartDataByType();

        $legend = $chart['legend'];
        $title = $chart['title'];
        $dataItems = $chart['data'];

        $this->addSpecialOptions($legend, $dataItems);

        return [
            'title' => $title,
            'legend' => $legend,
            'data' => $dataItems,
        ];
    }

    /**
     * Add special options based on question type
     * @param array $legend
     * @param array $dataItems
     */
    private function addSpecialOptions(array &$legend, array &$dataItems): void
    {
        if (
            in_array($this->question['type'], [Question::QT_L_LIST, Question::QT_EXCLAMATION_LIST_DROPDOWN], true)
            && $this->question['other'] === Question::QT_Y_YES_NO_RADIO
        ) {
            $this->addOtherOption($legend, $dataItems);
        }

        if ($this->question['type'] === Question::QT_O_LIST_WITH_COMMENT) {
            $this->addCommentOption($legend, $dataItems);
        }
    }

    /**
     * @param array $legend
     * @param array $dataItems
     */
    private function addOtherOption(array &$legend, array &$dataItems): void
    {
        $mfield = $this->rt . '_Cother';
        $legend[] = 'other';
        $dataItems[] = [
            'key' => 'other',
            'value' => $this->countFieldResponses($mfield),
            'title' => gT('Other')
        ];
    }

    /**
     * @param array $legend
     * @param array $dataItems
     */
    private function addCommentOption(array &$legend, array &$dataItems): void
    {
        $mfield = $this->rt . '_Ccomment';
        $legend[] = 'comment';
        $dataItems[] = [
            'key' => 'comment',
            'value' => $this->countFieldResponses($mfield),
            'title' => gT('Comments')
        ];
    }

    /**
     * Build chart data based on question type
     * @throws RuntimeException
     * @return array
     */
    private function buildChartDataByType(): array
    {
        $type = $this->question['type'];

        switch ($type) {
            case Question::QT_G_GENDER:
                $data = $this->handleGender();
                break;
            case Question::QT_Y_YES_NO_RADIO:
                $data = $this->handleYesNo();
                break;
            case Question::QT_I_LANGUAGE:
                $data = $this->handleLanguage();
                break;
            case Question::QT_5_POINT_CHOICE:
                $data = $this->handle5PointChoice();
                break;
            default:
                $data = $this->handleDefault();
        }

        return $data;
    }

    /**
     * @return array
     */
    private function handleGender(): array
    {
        $codes = ['F', 'M'];
        $labels = ['Female', 'Male'];

        [$legend, $items] = $this->buildItemsFromCodes($this->rt, $codes, $labels);
        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $items
        ];
    }

    /**
     * @return array
     */
    private function handleYesNo(): array
    {
        $codes = ['Y', 'N'];
        $labels = ['Yes', 'No'];

        [$legend, $items] = $this->buildItemsFromCodes($this->rt, $codes, $labels);
        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $items
        ];
    }

    /**
     * @return array
     */
    private function handleLanguage(): array
    {
        $languages = Survey::model()->findByPk($this->surveyId)->getAllLanguages();
        $codes = $languages;
        $labels = array_map(fn($l) => getLanguageNameFromCode($l, false), $languages);

        [, $items] = $this->buildItemsFromCodes($this->rt, $codes, $labels);
        $legend = array_column($items, 'title');

        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $items
        ];
    }

    /**
     * @return array
     */
    private function handle5PointChoice(): array
    {
        $codes = array_map('strval', range(1, 5));
        [$legend, $items] = $this->buildItemsFromCodes($this->rt, $codes, $codes);
        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $items
        ];
    }

    /**
     * @return array
     */
    private function handleDefault(): array
    {
        $codes = array_column($this->answers, 'code');
        $labels = array_map('flattenText', array_column($this->answers, 'answer'));

        [$legend, $items] = $this->buildItemsFromCodes($this->rt, $codes, $labels);

        return [
            'title' => $this->question['question'],
            'legend' => $legend,
            'data' => $items
        ];
    }
}
