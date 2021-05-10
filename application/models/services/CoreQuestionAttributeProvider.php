<?php

namespace LimeSurvey\Models\Services;

/**
 * Provides question attribute definitions from question types
 */

class CoreQuestionAttributeProvider extends QuestionAttributeProvider
{
    /** @inheritdoc */
    public function getDefinitions($question, $filters = [])
    {
        $dummyAttribute = [
            'category'  => gT("Dummy attributes"),
            'caption'   => gT("Dummy question type attribute"),
        ];

        return [
            'dummyCoreAttribute' => array_merge($this->getBaseDefinition(), $dummyAttribute)
        ];
    }
}
