<?php

/**
 * RenderClass for Yes/No Question
 *  * The ia Array contains the following
 *  0 => string qid
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RenderYesNoRadio extends QuestionBaseRenderer
{
    /**
     * Returns the twig view used to render the answer part of the question,
     * depending on the display_type attribute (0 = button group, otherwise radio list).
     *
     * @return string
     */
    public function getMainView()
    {
        if ($this->getDisplayType() === 0) {
            return '/survey/questions/answer/yesno/buttons/item';
        }
        return '/survey/questions/answer/yesno/radio/item';
    }

    /**
     * Yes/No questions have no rows.
     *
     * @return void
     */
    public function getRows()
    {
        return;
    }

    /**
     * Renders the Yes/No question.
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]} Rendered answer HTML and the list of input names
     */
    public function render($sCoreClasses = '')
    {
        $this->registerAssets();

        // Null when not set: the hidden "java" field then gets no value attribute at all
        $sValue = $this->getFromSurveySession($this->sSGQA, null);

        $yChecked = $nChecked = $naChecked = '';
        if ($sValue == 'Y') {
            $yChecked = CHECKED;
        }

        if ($sValue == 'N') {
            $nChecked = CHECKED;
        }

        $noAnswer = false;
        if (($this->aFieldArray[6] != 'Y' && $this->aFieldArray[6] != 'S') && SHOW_NO_ANSWER == 1) {
            $noAnswer = true;
            if (PRESELECT_NO_ANSWER && empty($sValue)) {
                $naChecked = CHECKED;
            }
        }

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'name' => $this->sSGQA,
            'basename' => $this->sSGQA,
            'yChecked' => $yChecked,
            'nChecked' => $nChecked,
            'naChecked' => $naChecked,
            'noAnswer' => $noAnswer,
            'value' => $sValue,
            'displayType' => $this->getDisplayType(),
        ));

        $inputnames = [$this->sSGQA];
        return array($answer, $inputnames);
    }

    /**
     * Returns the display_type attribute as integer (0 = button group, 1 = radio list).
     *
     * @return int
     */
    private function getDisplayType(): int
    {
        return (int) $this->getQuestionAttribute('display_type');
    }
}
