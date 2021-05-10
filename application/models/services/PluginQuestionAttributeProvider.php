<?php

namespace LimeSurvey\Models\Services;

/**
 * Provides question attribute definitions from plugins
 */

class PluginQuestionAttributeProvider extends QuestionAttributeProvider
{
    /** @inheritdoc */
    public function getDefinitions($question, $filters = [])
    {
        $dummyAttribute = [
            'category'  => gT("Dummy attributes"),
            'caption'   => gT("Dummy plugin attribute"),
        ];

        return [
            'dummyPluginAttribute' => array_merge($this->getBaseDefinition(), $dummyAttribute)
        ];
    }
}
