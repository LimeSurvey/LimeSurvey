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
            // TODO: move to sanitizeQuestionAttributes()
            // Determine the attribute name or continue with the next
            if (!isset($attribute['name'])) {
                if (!is_numeric($key)) {
                    $attribute['name'] = $key;
                } else {
                    continue;
                }
            }

            $sAttributeName = $attribute['name'];
            $sInputType = $attribute['inputtype'];
            // remove attribute if inputtype is empty
            if (empty($sInputType)) {
                unset($aAttributes[$sAttributeName]);
            } else {
                $aCustomAttribute = array_merge(
                    \QuestionAttribute::getDefaultSettings(),
                    array("category" => gT("Template")),
                    $attribute
                );
                $aAttributes[$sAttributeName] = $aCustomAttribute;
            }
        }
        return $aAttributes;
    }

    /**
     * Sanitizes an array of question attributes.
     * Currently just replaces empty arrays (generally resulting from empty xml nodes) with null.
     *
     * @param array $aAttributes the array of attributes to sanitize
     *
     * @return array the array of sanitized attributes
     */
    public function sanitizeQuestionAttributes($aAttributes)
    {
        // Replace empty arrays with null
        foreach ($aAttributes as &$aAttribute) {
            foreach ($aAttribute as $propertyName => $propertyValue) {
                if ($propertyValue === []) {
                    $aAttribute[$propertyName] = null;
                }
            }
        }
        return $aAttributes;
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
}
