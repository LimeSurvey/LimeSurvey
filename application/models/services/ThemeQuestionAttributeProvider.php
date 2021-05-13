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
        if (empty($filters['questionTheme'])) {
            return [];
        }

        /** @var string question type */
        $questionType = $question->type;

        /** @var string question theme */
        $questionTheme = $filters['questionTheme'];

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

        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(false);
        }
        $questionTheme = \QuestionTheme::model()->findByAttributes([], 'name = :name AND extends = :extends', ['name' => $questionThemeName, 'extends' => $questionType]);
        if ($questionTheme !== null) {
            $xmlConfig = simplexml_load_file(App()->getConfig('rootdir') . '/' . $questionTheme['xml_path'] . '/config.xml');
            $xmlAttributes = json_decode(json_encode((array)$xmlConfig->attributes), true);
        }
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(true);
        }

        if (!empty($xmlAttributes)) {
            if (!empty($xmlAttributes['attribute']['name'])) {
                // Only one attribute set in config: need an array of attributes
                $xmlAttributes['attribute'] = array($xmlAttributes['attribute']);
            }
            // Create array of attribute with name as key
            foreach ($xmlAttributes['attribute'] as $attribute) {
                if (!empty($attribute['name'])) {
                    $attributes[$attribute['name']] = array_merge($this->getBaseDefinition(), $attribute);
                }
            }
        }

        return $attributes;
    }
}
