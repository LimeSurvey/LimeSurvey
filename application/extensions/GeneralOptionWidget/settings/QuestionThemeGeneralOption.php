<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\FormElement;

class QuestionThemeGeneralOption extends GeneralOption
{
    /**
     * @param string $currentSetQuestionTheme
     * @param array $options
     */
    public function __construct($currentSetQuestionTheme, array $options)
    {
        $this->name = 'question_template';
        $this->title = gT('Question theme');
        $this->inputType = 'questiontheme';
        $this->formElement = new FormElement(
            'question_template',
            null,
            gT("Use a customized question theme for this question"),
            $currentSetQuestionTheme,
            $options
        );
    }

    /**
     * Factory method to get setting data.
     *
     * @param Question $question
     * @param string $questionType
     * @param string $currentSetQuestionTheme
     * @return self
     */
    public static function make(Question $question, $questionType, $currentSetQuestionTheme)
    {
        $aQuestionTemplateList = QuestionTemplate::getQuestionTemplateList($questionType);
        $aQuestionTemplateAttributes = $question->question_theme_name;

        $aOptionsArray = [];
        foreach ($aQuestionTemplateList as $code => $value) {
            $aOptionsArray[] = [
                'value' => $code,
                'text' => $value['title']
            ];
        }

        if ($currentSetQuestionTheme == null) {
            $currentSetQuestionTheme = (isset($aQuestionTemplateAttributes['value']) && $aQuestionTemplateAttributes['value'] !== '')
                ? $aQuestionTemplateAttributes['value']
                : 'core';
        }

        return new QuestionThemeGeneralOption(
            $currentSetQuestionTheme,
            $aOptionsArray
        );
    }
}
