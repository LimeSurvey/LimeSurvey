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
        /** @var string question type */
        $questionType = $question->type;

        /** @var boolean */
        $advancedOnly = !empty($filters['advancedOnly']);

        return $this->getQuestionAttributes($questionType, $advancedOnly);
    }

    /**
     * Return the question attribute settings for the passed type (parameter)
     *
     * @param string $questionType : type of question (this is the attribute 'question_type' in table question_theme)
     * @param boolean $advancedOnly If true, only fetch advanced attributes
     * @return array<string,array> the attribute settings for this question type
     *                 returns values from getGeneralAttributesFromXml and getAdvancedAttributesFromXml if this fails
     *                 getAttributesDefinition and getDefaultSettings are returned
     *
     * @throws \CException
     */
    protected function getQuestionAttributes($questionType, $advancedOnly = false)
    {
        $xmlFilePath = \QuestionTheme::getQuestionXMLPathForBaseType($questionType);
        if ($advancedOnly) {
            $generalAttributes = [];
        } else {
            // Get attributes from config.xml
            $generalAttributes = $this->getGeneralAttibutesFromXml($xmlFilePath);
        }
        $advancedAttributes = $this->getAdvancedAttributesFromXml($xmlFilePath);

        /** @var array<string,array> An array of question attributes */
        $attributes = array_merge($generalAttributes, $advancedAttributes);

        // if empty, fall back to getting attributes from questionHelper
        // TODO: maybe we should drop this?
        if (empty($attributes)) {
            $attributes = [];
            $questionHelperAttributes = \LimeSurvey\Helpers\questionHelper::getAttributesDefinitions();
            /* Filter to get this question type setting */
            $aQuestionTypeAttributes = array_filter($questionHelperAttributes, function ($attribute) use ($questionType) {
                return stripos($attribute['types'], $questionType) !== false;
            });
            foreach ($aQuestionTypeAttributes as $attribute => $settings) {
                  $attributes[$attribute] = array_merge(
                      \QuestionAttribute::getDefaultSettings(),
                      array("category" => gT("Plugins")),
                      $settings,
                      array("name" => $attribute)
                  );
            }
        }
        return $attributes;
    }

    /**
     * Read question attributes from XML file and convert it to array
     *
     * @param string $xmlFilePath Path to XML
     *
     * @return array<string,array> The general attribute settings for this question type
     */
    protected function getGeneralAttibutesFromXml($xmlFilePath)
    {
        /** @var array<string,array> An array of question attributes */
        $attributes = [];

        if (file_exists($xmlFilePath)) {
            // load xml file
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(false);
            }
            $xmlConfig = simplexml_load_file($xmlFilePath);
            $xmlAttributes = json_decode(json_encode((array)$xmlConfig->generalattributes), true);
            // if only one attribute, then it doesn't return numeric index
            if (!empty($xmlAttributes) && !array_key_exists('0', $xmlAttributes['attribute'])) {
                $temp = $xmlAttributes['attribute'];
                unset($xmlAttributes);
                $xmlAttributes['attribute'][0] = $temp;
            }
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(true);
            }
        } else {
            return [];
        }

        // set $attributes array with attribute data
        if (!empty($xmlAttributes['attribute'])) {
            foreach ($xmlAttributes['attribute'] as $xmlAttribute) {
                /* settings the default value */
                $attributes[$xmlAttribute] = $this->getBaseDefinition();
                /* settings the xml value */
                $attributes[$xmlAttribute]['name'] = $xmlAttribute;
            }
        }

        return $attributes;
    }

    /**
     * Read question attributes from XML file and convert it to array
     *
     * @param string $xmlFilePath Path to XML
     *
     * @return array<string,array> The advanced attribute settings for this question type
     */
    protected function getAdvancedAttributesFromXml($xmlFilePath)
    {
        /** @var array<string,array> An array of question attributes */
        $attributes = [];

        if (file_exists($xmlFilePath)) {
            // load xml file
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(false);
            }
            $xmlConfig = simplexml_load_file($xmlFilePath);
            $xmlAttributes = json_decode(json_encode((array)$xmlConfig->attributes), true);
            // if only one attribute, then it doesn't return numeric index
            if (!empty($xmlAttributes) && !array_key_exists('0', $xmlAttributes['attribute'])) {
                $temp = $xmlAttributes['attribute'];
                unset($xmlAttributes);
                $xmlAttributes['attribute'][0] = $temp;
            }
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader(true);
            }
        } else {
            return [];
        }

        // set $attributes array with attribute data
        if (!empty($xmlAttributes['attribute'])) {
            foreach ($xmlAttributes['attribute'] as $attribute) {
                if (empty($attribute['name'])) {
                    /* Allow comments in attributes */
                    continue;
                }
                /* settings the default value */
                $attributes[$attribute['name']] = $this->getBaseDefinition();
                /* settings the xml value */
                foreach ($attribute as $property => $propertyValue) {
                    if ($property === 'options' && !empty($propertyValue)) {
                        foreach ($propertyValue['option'] as $option) {
                            if (isset($option['value'])) {
                                $value = is_array($option['value']) ? '' : $option['value'];
                                $attributes[$attribute['name']]['options'][$value] = $option['text'];
                            }
                        }
                    } else {
                        $attributes[$attribute['name']][$property] = $propertyValue;
                    }
                }
            }
        }

        return $attributes;
    }
}
