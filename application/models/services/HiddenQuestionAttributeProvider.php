<?php

namespace LimeSurvey\Models\Services;

/**
 * Provides question attribute definitions from question types
 */
class HiddenQuestionAttributeProvider extends QuestionAttributeProvider
{
    /** @inheritdoc */
    public function getDefinitions($options = [])
    {
        /** @var string question type */
        $questionType = self::getQuestionType($options);
        if (empty($questionType)) {
            return [];
        }

        return $this->getHiddenAttributes($questionType);
    }

    /**
     * Returns question attributes from hardcoded array and convert it to array
     * @param string $questionType     the question type to retrieve the attributes for
     * @return array<string,array> The hidden attribute settings for this question type
     */
    protected function getHiddenAttributes($questionType)
    {
        $attributes = [];
        $unallowedQuestionTypes = [];
        $hiddenAttributes = ['image'];
        if (!in_array($questionType, $unallowedQuestionTypes)) {
            foreach ($hiddenAttributes as $hiddenAttribute) {
                $attributes[$hiddenAttribute] = self::getBaseDefinition();
                $attributes[$hiddenAttribute]['name'] = $hiddenAttribute;
            }
        }
        return $attributes;
    }
}
