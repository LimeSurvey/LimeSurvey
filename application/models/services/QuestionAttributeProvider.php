<?php

namespace LimeSurvey\Models\Services;

/**
 * Base class for question attribute definition providers
 */

abstract class QuestionAttributeProvider
{

    /**
     * Returns question attribute definitions for the specified filters from one source
     *
     * @param \Question $question
     * @param array<string,mixed> $filters to use
     *
     * @return array<string,array> array of question attribute definitions
     */
    abstract public function getDefinitions($question, $filters = []);

    /**
     * Get default settings for an attribute, return an array of string|null
     * @return array<string,mixed>
     */
    protected function getBaseDefinition()
    {
        return [
            "name" => null,
            "caption" => '',
            "inputtype" => "text",
            "options" => null,
            "category" => gT("Attribute"),
            "default" => '',
            "help" => '',
            "value" => '',
            "sortorder" => 1000,
            "i18n" => false,
            "readonly" => false,
            "readonly_when_active" => false,
            "expression" => null,
        ];
    }
}
