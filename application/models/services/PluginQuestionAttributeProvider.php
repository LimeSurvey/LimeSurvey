<?php

namespace LimeSurvey\Models\Services;

/**
 * Provides question attribute definitions from plugins
 */

class PluginQuestionAttributeProvider extends QuestionAttributeProvider
{
    /** @inheritdoc */
    public function getDefinitions($options = [])
    {
        /** @var string question type */
        $questionType = self::getQuestionType($options);
        if (empty($questionType)) {
            return [];
        }

        return $this->getAttributesFromPlugin($questionType);
    }

    /**
     * Returns the question attributes added by plugins ('newQuestionAttributes' event) for
     * the specified question type.
     *
     * @param string $questionType     the question type to retrieve the attributes for.
     *
     * @return array<string,array>    the question attributes added by plugins
     */
    protected function getAttributesFromPlugin($questionType)
    {
        $event = new \LimeSurvey\PluginManager\PluginEvent('newQuestionAttributes');
        $result = App()->getPluginManager()->dispatchEvent($event);

        $allPluginAttributes = (array) $result->get('questionAttributes');
        if (empty($allPluginAttributes)) {
            return [];
        }

        $questionAttributeHelper = new QuestionAttributeHelper();

        // Filter to get this question type attributes
        $questionTypeAttributes = $questionAttributeHelper->filterAttributesByQuestionType($allPluginAttributes, $questionType);

        // Complete category if missing
        $questionTypeAttributes = $questionAttributeHelper->fillMissingCategory($questionTypeAttributes, gT('Plugin'));

        return $questionTypeAttributes;
    }
}
