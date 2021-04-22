<?php

namespace LimeSurvey\Models\Services;

class QuestionAttributeHelper
{
    /**
     * Merges the 'base' attributes (ex: core question attributes) with the extended question attributes
     * (ex: question theme attributes). It also removes all attributes where extended attribute's inputType is
     * empty.
     * If an extended attribute's name cannot be determined, it's omitted.
     *
     * @param array $aBaseAttributes    the base set of attributes
     * @param array $aExtendedAttributes    the attributes to merge into the base set
     *
     * @return array the merged attributes
     */
    public function mergeQuestionAttributes($aBaseAttributes, $aExtendedAttributes)
    {
        $aAttributes = $aBaseAttributes;
        foreach ($aExtendedAttributes as $key => $attribute) {
            // Omit the attribute if it has no name.
            // This shouldn't happen if sanitizeQuestionAttributes() is used.
            if (!isset($attribute['name'])) {
                continue;
            }

            $sAttributeName = $attribute['name'];
            $sInputType = $attribute['inputtype'];
            // remove attribute if inputtype is empty
            if (empty($sInputType)) {
                unset($aAttributes[$sAttributeName]);
            } else {
                $aCustomAttribute = array_merge(
                    \QuestionAttribute::getDefaultSettings(),
                    $attribute
                );
                $aAttributes[$sAttributeName] = $aCustomAttribute;
            }
        }
        return $aAttributes;
    }

    /**
     * Sanitizes an array of question attributes.
     * Current tasks:
     *  - makes sure that attributes have a name (removes them if name cannot be determined)
     *  - replaces empty arrays (generally resulting from empty xml nodes) with null.
     *
     * @param array $aAttributes the array of attributes to sanitize
     *
     * @return array the array of sanitized attributes
     */
    public function sanitizeQuestionAttributes($aAttributes)
    {
        $aSanitizedAttributes = [];
        foreach ($aAttributes as $key => $aAttribute) {
            // Make sure the attribute has a name.
            if (!is_numeric($key)) {
                $aAttribute['name'] = $key;
            } else {
                if (!isset($aAttribute['name'])) {
                    continue;
                }
            }

            // Replace empty arrays with null
            foreach ($aAttribute as $propertyName => $propertyValue) {
                if ($propertyValue === []) {
                    $aAttribute[$propertyName] = null;
                }
            }

            // Make sure "options" have the expected structure
            if (isset($aAttribute['options']['option']) && is_array($aAttribute['options']['option'])) {
                $aAttribute['options'] = $aAttribute['options']['option'];
            }

            $aSanitizedAttributes[$aAttribute['name']] = $aAttribute;
        }
        return $aSanitizedAttributes;
    }

    /**
     * Returns the received array of attributes filled with the values specified, taking into account the
     * 'i18n' property of the attributes.
     *
     * Both this and rewriteQuestionAttributeArray() are helper methods and accomplish quite similar tasks,
     * but the output is different: rewriteQuestionAttributeArray returns a name -> value array, while here
     * we return a complete definition map and the value as a piece of information mingled into it.
     *
     * @param array $aAttributes the attributes to be filled
     * @param array $aAttributeValues the values for the attributes
     * @param array $aLanguages the languages to use for i18n attributes
     *
     * @return array the same source attributes with their corresponding values (when available)
     */
    public function fillAttributesWithValues($aAttributes, $aAttributeValues, $aLanguages = [])
    {
        foreach ($aAttributes as $iKey => $aAttribute) {
            if ($aAttribute['i18n'] == false) {
                if (isset($aAttributeValues[$aAttribute['name']][''])) {
                    $aAttributes[$iKey]['value'] = $aAttributeValues[$aAttribute['name']][''];
                } else {
                    $aAttributes[$iKey]['value'] = $aAttribute['default'];
                }
                // Sanitize value in case it's saved as empty array
                if ($aAttributes[$iKey]['value'] === []) {
                    $aAttributes[$iKey]['value'] = null;
                }
            } else {
                foreach ($aLanguages as $sLanguage) {
                    if (isset($aAttributeValues[$aAttribute['name']][$sLanguage])) {
                        $aAttributes[$iKey][$sLanguage]['value'] = $aAttributeValues[$aAttribute['name']][$sLanguage];
                    } else {
                        $aAttributes[$iKey][$sLanguage]['value'] = $aAttribute['default'];
                    }
                    // Sanitize value in case it's saved as empty array
                    if ($aAttributes[$iKey][$sLanguage]['value'] === []) {
                        $aAttributes[$iKey][$sLanguage]['value'] = null;
                    }
                }
            }
        }
        return $aAttributes;
    }

    /**
     * Receives an array of question attributes and groups them by category.
     * Used by advanced settings widget.
     *
     * @param array $aAttributes
     * @return array Grouped question attributes, with category as array key
     */
    public function groupAttributesByCategory($aAttributes)
    {
        $aByCategory = [];
        foreach ($aAttributes as $aAttribute) {
            $aByCategory[$aAttribute['category']][] = $aAttribute;
        }
        return $aByCategory;
    }

    /**
     * Returns the question attributes added by plugins ('newQuestionAttributes' event) for
     * the specified question type.
     *
     * @param string $sQuestionType     the question type to retrieve the attributes for.
     *
     * @return array    the question attributes added by plugins
     */
    public function getAttributesFromPlugin($sQuestionType)
    {
        $aAttributes = \QuestionAttribute::getOwnQuestionAttributesViaPlugin();
        if (empty($aAttributes)) {
            return [];
        }

        // Filter to get this question type setting
        $aQuestionTypeAttributes = $this->filterAttributesByQuestionType($aAttributes, $sQuestionType);

        // Complete category if missing
        $aQuestionTypeAttributes = $this->fillMissingCategory($aQuestionTypeAttributes, gT('Plugin'));

        $aQuestionTypeAttributes = $this->sanitizeQuestionAttributes($aQuestionTypeAttributes);

        return $aQuestionTypeAttributes;
    }

    /**
     * Filters an array of question attribute definitions by question type
     *
     * @param array $aAttributes    array of question attribute definitions to filter
     * @param string $sQuestionType the question type that the attributes should apply to
     *
     * @return array    an array containing only the question attributes that match the specified question type
     */
    public function filterAttributesByQuestionType($aAttributes, $sQuestionType)
    {
        $aQuestionTypeAttributes = array_filter($aAttributes, function ($attribute) use ($sQuestionType) {
            return $this->attributeAppliesToQuestionType($attribute, $sQuestionType);
        });

        return $aQuestionTypeAttributes;
    }

    /**
     * Check if question attribute applies to a specific question type
     *
     * @param array $aAttribute     question attribute definition
     * @param string $sQuestionType the question type that the attribute should apply to
     *
     * @return bool     returns true if the question attribute applies to the specified question type
     */
    public function attributeAppliesToQuestionType($aAttribute, $sQuestionType)
    {
        return isset($aAttribute['types']) && stripos($aAttribute['types'], $sQuestionType) !== false;
    }

    /**
     * Makes sure all the question attributes in an array have a category. If an attribute's
     * category is missing, it's filled with the specified category name.
     *
     * @param array $aAttributes    array of question attribute definitions
     * @param string $sCategoryName the category name to use if an attribute doesn't have one
     *
     * @return array    returns the array attributes with Category field complete
     */
    public function fillMissingCategory($aAttributes, $sCategoryName)
    {
        foreach ($aAttributes as &$aAttribute) {
            if (empty($aAttribute['category'])) {
                $aAttribute['category'] = $sCategoryName;
            }
        }
        return $aAttributes;
    }

    /**
     * This function returns an array of the attributes for the particular question
     * including their values set in the database
     *
     * @param \Question $oQuestion  The question object
     * @param string|null $sLanguage If you give a language then only the attributes for that language are returned
     * @param string|null $sQuestionThemeOverride   Name of the question theme to use instead of the question's current theme
     * @param boolean $advancedOnly If set to true, only the advanced attributes will be returned
     * @return array
     */
    public function getQuestionAttributesWithValues($oQuestion, $sLanguage = null, $sQuestionThemeOverride = null, $advancedOnly = false)
    {
        $oSurvey = $oQuestion->survey;
        if (empty($oSurvey)) {
            throw new \Exception('This question has no survey - qid = ' . json_encode($oQuestion->qid));
        }

        // Get attribute values
        $aAttributeValues = \QuestionAttribute::getAttributesAsArrayFromDB($oQuestion->qid);

        // Get question theme name if not specified
        $sQuestionTheme = !empty($aAttributeValues['question_template']['']) ? $aAttributeValues['question_template'][''] : 'core';
        $sQuestionTheme = !empty($sQuestionThemeOverride) ? $sQuestionThemeOverride : $sQuestionTheme;

        // Get advanced attribute definitions for the question type
        $aQuestionTypeAttributes = \QuestionAttribute::getQuestionAttributesSettings($oQuestion->type, $advancedOnly);

        // Get question theme attribute definitions
        $aThemeAttributes = \QuestionTheme::getAdditionalAttrFromExtendedTheme($sQuestionTheme, $oQuestion->type);

        // Merge the attributes with the question theme ones
        $aAttributes = $this->mergeQuestionAttributes($aQuestionTypeAttributes, $aThemeAttributes);

        // Get question attributes from plugins ('newQuestionAttributes' event)
        $aPluginAttributes = $this->getAttributesFromPlugin($oQuestion->type);
        $aAttributes = $this->mergeQuestionAttributes($aAttributes, $aPluginAttributes);

        uasort($aAttributes, 'categorySort');

        // Fill attributes with values
        $aLanguages = is_null($sLanguage) ? $oSurvey->allLanguages : [$sLanguage];
        $aAttributes = $this->fillAttributesWithValues($aAttributes, $aAttributeValues, $aLanguages);

        return $aAttributes;
    }
}
