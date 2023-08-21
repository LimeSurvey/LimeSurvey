<?php

namespace LimeSurvey\Models\Services;

/**
 * Provides question attribute definitions from question themes
 */

class ThemeQuestionAttributeProvider extends QuestionAttributeProvider
{
    /** @inheritdoc */
    public function getDefinitions($options = [])
    {
        /** @var string question theme from the filter or, if not set, from the question */
        $questionTheme = self::getQuestionTheme($options);
        if (empty($questionTheme) || $questionTheme == \Question::DEFAULT_QUESTION_THEME) {
            return [];
        }

        /** @var string question type */
        $questionType = self::getQuestionType($options);
        if (empty($questionType)) {
            return [];
        }

        return $this->getAttributesFromQuestionTheme($questionTheme, $questionType);
    }

    /**
     * Gets the additional attributes for an extended theme from xml file.
     * If there are no attributes, an empty array is returned
     *
     * @param string $questionThemeName the question theme name (see table question theme "name")
     * @param string $questionType   the extended typ (see table question_themes "extends")
     * @return array<string,array> additional attributes for an extended theme or empty array
     */
    protected function getAttributesFromQuestionTheme($questionThemeName, $questionType)
    {
        /** @var array<string,array> An array of question attributes */
        $attributes = array();

        $questionTheme = \QuestionTheme::model()->findByAttributes([], 'name = :name AND extends = :extends', ['name' => $questionThemeName, 'extends' => $questionType]);
        if ($questionTheme !== null) {
            $xmlFilePath = $questionTheme->getXmlPath() . '/config.xml';
            $extensionConfig = \ExtensionConfig::loadFromFile($xmlFilePath);
        }

        if (!empty($extensionConfig)) {
            $xmlAttributes = $extensionConfig->getNodeAsArray('attributes');
            if (!empty($xmlAttributes['attribute']['name'])) {
                // Only one attribute set in config: need an array of attributes
                $xmlAttributes['attribute'] = array($xmlAttributes['attribute']);
            }
            // Create array of attribute with name as key
            foreach ($xmlAttributes['attribute'] as $attribute) {
                if (!empty($attribute['name'])) {
                    $attributes[$attribute['name']] = array_merge(self::getBaseDefinition(), $attribute);
                }
            }
        }

        return $attributes;
    }

    /**
     * Extracts the question theme from the $options.
     * If it's not explicitly set, it tries to use a question object.
     *
     * @param array<string,mixed> $options
     *
     * @return
     */
    private static function getQuestionTheme($options)
    {
        if (!empty($options['questionTheme'])) {
            return $options['questionTheme'];
        }
        if (!empty($options['question'])) {
            return $options['question']->question_theme_name;
        }
        return '';
    }
}
