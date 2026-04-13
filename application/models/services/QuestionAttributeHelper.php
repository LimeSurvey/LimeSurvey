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
     * @param array $baseAttributes    the base set of attributes
     * @param array $extendedAttributes    the attributes to merge into the base set
     *
     * @return array the merged attributes
     */
    public function mergeQuestionAttributes($baseAttributes, $extendedAttributes)
    {
        $attributes = $baseAttributes;
        foreach ($extendedAttributes as $attribute) {
            // Omit the attribute if it has no name.
            // This shouldn't happen if sanitizeQuestionAttributes() is used.
            if (!isset($attribute['name'])) {
                continue;
            }

            $attributeName = $attribute['name'];
            $inputType = $attribute['inputtype'];
            // remove attribute if inputtype is empty
            if (empty($inputType)) {
                unset($attributes[$attributeName]);
            } else {
                $customAttribute = array_merge(
                    \QuestionAttribute::getDefaultSettings(),
                    $attribute
                );
                $attributes[$attributeName] = $customAttribute;
            }
        }
        return $attributes;
    }

    /**
     * Sanitizes an array of question attributes.
     * Current tasks:
     *  - makes sure that attributes have a name (removes them if name cannot be determined)
     *  - replaces empty arrays (generally resulting from empty xml nodes) with null.
     *
     * @param array $attributes the array of attributes to sanitize
     *
     * @return array<string,array> the array of sanitized attributes
     * @todo Pure function - move outside class?
     */
    public function sanitizeQuestionAttributes($attributes)
    {
        /** @var array<string,array> An array of sanitized question attributes */
        $sanitizedAttributes = [];
        foreach ($attributes as $key => $attribute) {
            // Make sure the attribute has a name.
            if (!is_numeric($key)) {
                $attribute['name'] = $key;
            } else {
                if (!isset($attribute['name'])) {
                    continue;
                }
            }

            // Replace empty arrays with null
            foreach ($attribute as $propertyName => $propertyValue) {
                if ($propertyValue === []) {
                    $attribute[$propertyName] = null;
                }
            }

            // Make sure "options" have the expected structure
            if (isset($attribute['options']['option']) && is_array($attribute['options']['option'])) {
                $attribute['options'] = $attribute['options']['option'];
            }

            $sanitizedAttributes[$attribute['name']] = $attribute;
        }
        return $sanitizedAttributes;
    }

    /**
     * Returns the received array of attributes filled with the values specified, taking into account the
     * 'i18n' property of the attributes.
     *
     * Both this and rewriteQuestionAttributeArray() are helper methods and accomplish quite similar tasks,
     * but the output is different: rewriteQuestionAttributeArray returns a name -> value array, while here
     * we return a complete definition map and the value as a piece of information mingled into it.
     *
     * @param array $attributes the attributes to be filled
     * @param array $attributeValues the values for the attributes
     * @param array $languages the languages to use for i18n attributes
     *
     * @return array the same source attributes with their corresponding values (when available)
     * @todo Pure function - move outside class?
     */
    public function fillAttributesWithValues($attributes, $attributeValues, $languages = [])
    {
        foreach ($attributes as $key => $attribute) {
            if ($attribute['i18n'] == false) {
                if (isset($attributeValues[$attribute['name']][''])) {
                    $value = $attributeValues[$attribute['name']][''];
                    if ($key === 'image') {
                        $value = $this->decodeImageAttributes($value);
                    }
                    $attributes[$key]['value'] = $value;
                } else {
                    $attributes[$key]['value'] = $attribute['default'];
                }
                // Sanitize value in case it's saved as empty array
                if ($attributes[$key]['value'] === []) {
                    $attributes[$key]['value'] = null;
                }
            } else {
                foreach ($languages as $language) {
                    if (isset($attributeValues[$attribute['name']][$language])) {
                        $attributes[$key][$language]['value'] = $attributeValues[$attribute['name']][$language];
                    } else {
                        $attributes[$key][$language]['value'] = $attribute['default'];
                    }
                    // Sanitize value in case it's saved as empty array
                    if ($attributes[$key][$language]['value'] === []) {
                        $attributes[$key][$language]['value'] = null;
                    }
                }
            }
        }
        return $attributes;
    }

    /**
     * Receives an array of question attributes and groups them by category.
     * Used by advanced settings widget.
     *
     * @param array $attributes
     * @return array Grouped question attributes, with category as array key
     * @todo Pure function - move outside class?
     */
    public function groupAttributesByCategory($attributes)
    {
        $attributesByCategory = [];
        foreach ($attributes as $attribute) {
            $attributesByCategory[$attribute['category']][] = $attribute;
        }
        return $attributesByCategory;
    }

    /**
     * Filters an array of question attribute definitions by question type
     *
     * @param array $attributes    array of question attribute definitions to filter
     * @param string $questionType the question type that the attributes should apply to
     *
     * @return array    an array containing only the question attributes that match the specified question type
     */
    public function filterAttributesByQuestionType($attributes, $questionType)
    {
        $questionTypeAttributes = array_filter($attributes, function ($attribute) use ($questionType) {
            return $this->attributeAppliesToQuestionType($attribute, $questionType);
        });

        return $questionTypeAttributes;
    }

    /**
     * Check if question attribute applies to a specific question type
     *
     * @param array $attribute     question attribute definition
     * @param string $questionType the question type that the attribute should apply to
     *
     * @return bool     returns true if the question attribute applies to the specified question type
     */
    public function attributeAppliesToQuestionType($attribute, $questionType)
    {
        return isset($attribute['types']) && stripos((string) $attribute['types'], $questionType) !== false;
    }

    /**
     * Makes sure all the question attributes in an array have a category. If an attribute's
     * category is missing, it's filled with the specified category name.
     *
     * @param array $attributes    array of question attribute definitions
     * @param string $sCategoryName the category name to use if an attribute doesn't have one
     *
     * @return array    returns the array attributes with Category field complete
     */
    public function fillMissingCategory($attributes, $categoryName)
    {
        foreach ($attributes as &$attribute) {
            if (empty($attribute['category'])) {
                $attribute['category'] = $categoryName;
            }
        }
        return $attributes;
    }

    /**
     * This function returns an array of the attributes for the particular question
     * including their values set in the database
     *
     * @param \Question $question  The question object
     * @param string|null $language If you give a language then only the attributes for that language are returned
     * @param string|null $questionThemeOverride   Name of the question theme to use instead of the question's current theme
     * @param boolean $advancedOnly If set to true, only the advanced attributes will be returned
     * @return array
     */
    public function getQuestionAttributesWithValues($question, $language = null, $questionThemeOverride = null, $advancedOnly = false)
    {
        $questionAttributeFetcher = new \LimeSurvey\Models\Services\QuestionAttributeFetcher();
        $questionAttributeFetcher->setQuestion($question);
        $questionAttributeFetcher->setAdvancedOnly($advancedOnly);
        if (!empty($questionThemeOverride)) {
            $questionAttributeFetcher->setTheme($questionThemeOverride);
        }

        $questionAttributeDefinitions = $questionAttributeFetcher->fetch();
        $questionAttributesWithValues = $questionAttributeFetcher->populateValues($questionAttributeDefinitions, $language);

        return $questionAttributesWithValues;
    }

    /**
     * Comparison function for sorting question attributes by category with uasort().
     *
     * @param array<string,mixed> $a    First question attribute to compare
     * @param array<string,mixed> $b    Second question attribute to compare
     * @return int
     * @todo No state used, so no OOP needed, move to function at some point.
     */
    protected function categorySort($a, $b)
    {
        $categoryOrders = $this->getCategoryOrders();
        $orderA = $categoryOrders[$a['category']] ?? PHP_INT_MAX;
        $orderB = $categoryOrders[$b['category']] ?? PHP_INT_MAX;
        if ($orderA == $orderB) {
            $result = strnatcasecmp((string) $a['category'], (string) $b['category']);
            if ($result == 0) {
                $result = $a['sortorder'] - $b['sortorder'];
            }
        } else {
            $result = $orderA - $orderB;
        }
        return $result;
    }

    /**
     * Returns the array of categories with their assigned order.
     * The array doesn't contain all the posible categories, only those with an order assigned.
     *
     * @return array<string,int>
     */
    public function getCategoryOrders()
    {
        $orders = [
            'Logic' => 1,
            'Display' => 2,
            'Input' => 3,
            'Other' => 4,
            'Timer' => 5,
            'Statistics' => 6,
        ];
        return $orders;
    }

    /**
     * Sorts an array of question attributes by category.
     * Sorting is based on a predefined list of orders (see QuestionAtributeHelper::getCategoryOrders()).
     * Categories without a predefined order are considered less relevant.
     * Categories with the same order are sorted alphabetically.
     *
     * @param array<string,array> $attributes
     * @return array<string,array>
     */
    public function sortAttributesByCategory($attributes)
    {
        $attributesCopy = $attributes;
        uasort($attributesCopy, [$this, 'categorySort']);
        return $attributesCopy;
    }

    /**
     * Returns the user's default values for the specified question type
     * @param string $questionType
     * @return array<string,mixed>
     */
    public function getUserDefaultsForQuestionType($questionType)
    {
        $defaultValues = [];
        $userDefaultQuestionAttributes = \SettingsUser::getUserSettingValue('question_default_values_' . $questionType);
        if ($userDefaultQuestionAttributes !== null) {
            $defaultValuesByCategory = json_decode((string) $userDefaultQuestionAttributes, true);
            foreach ($defaultValuesByCategory as $attributes) {
                foreach ($attributes as $attribute => $value) {
                    if (!is_array($value)) {
                        $value = ['' => $value];
                    }
                    $defaultValues[$attribute] = $value;
                }
            }
        }
        return $defaultValues;
    }

    private function decodeImageAttributes($jsonString)
    {
        $decoded = json_decode($jsonString, true);
        return !$decoded ? $jsonString : $decoded;
    }
}
