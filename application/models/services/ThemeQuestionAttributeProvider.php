<?php

namespace LimeSurvey\Models\Services;

/**
 * Provides question attribute definitions from question themes
 */

class ThemeQuestionAttributeProvider extends QuestionAttributeProvider
{
    /** @inheritdoc */
    public function getDefinitions($question, $filters = [])
    {
        $dummyAttribute = [
            'category'  => gT("Dummy attributes"),
            'caption'   => gT("Dummy question theme attribute"),
        ];

        return [
            'dummyThemeAttribute' => array_merge($this->getBaseDefinition(), $dummyAttribute)
        ];
    }
}
