<?php

namespace LimeSurvey\Models\Services;

/**
 * Base class for question attribute definition providers
 */

abstract class QuestionAttributeProvider
{
    /**
     * Returns question attribute definitions for the specified options from one source
     *
     * @param array<string,mixed> $options to use
     *
     * @return array<string,array> array of question attribute definitions
     */
    abstract public function getDefinitions($options = []);

    /**
     * Get default settings for an attribute, return an array of string|null
     * @return array<string,mixed>
     */
    protected static function getBaseDefinition()
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

    /**
     * Extracts the question type from the $options.
     * If it's not explicitly set, it tries to use a question object.
     *
     * @param array<string,mixed> $options
     *
     * @return
     */
    protected static function getQuestionType($options)
    {
        if (!empty($options['questionType'])) {
            return $options['questionType'];
        }
        if (!empty($options['question'])) {
            return $options['question']->type;
        }
        return '';
    }
}
