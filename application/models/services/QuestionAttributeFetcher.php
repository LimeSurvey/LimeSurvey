<?php

namespace LimeSurvey\Models\Services;

/**
 * Fetches question attribute definitions from the available providers
 */

class QuestionAttributeFetcher
{
    /** @var \Question the question where the attributes should apply */
    private $question;

    /** @var array<string,mixed> array of filters to pass to the providers */
    private $filters = [];

    /** @var array<QuestionAttributeProvider> array of question attribute providers */
    private $providers = [];

    public function __construct()
    {
        $this->providers = [
            new CoreQuestionAttributeProvider(),
            new ThemeQuestionAttributeProvider(),
            new PluginQuestionAttributeProvider(),
        ];
    }

    /**
     * Returns the question attribute definitions according to the specified filters,
     * from all available sources.
     *
     * @return array<string,array> array of question attribute definitions
     */
    public function fetch()
    {
        if (empty($this->question)) {
            return [];
        }

        $questionAttributeHelper = new QuestionAttributeHelper();

        /** @var array<string,array> retrieved attribute definitions*/
        $allAttributes = [];

        // We retrieve the attributes from each provider, sanitize them, and merge them.
        foreach ($this->providers as $provider) {
            $attributes = $provider->getDefinitions($this->question, $this->filters);
            $sanitizedAttributes = $questionAttributeHelper->sanitizeQuestionAttributes($attributes);
            $allAttributes = $questionAttributeHelper->mergeQuestionAttributes($allAttributes, $sanitizedAttributes);
        }

        // Sort by category
        uasort($allAttributes, 'categorySort');

        return $allAttributes;
    }

    public function populateValues($attributeDefinitions, $language = null)
    {
        if (empty($attributeDefinitions)) {
            return [];
        }

        if (empty($this->question)) {
            return $attributeDefinitions;
        }

        $survey = $this->question->survey;
        if (empty($survey)) {
            throw new \Exception(gT(sprintf('This question has no survey - qid = %s', json_encode($this->question->qid))));
        }

        $questionAttributeHelper = new QuestionAttributeHelper();

        // Get attribute values
        $attributeValues = $questionAttributeHelper->getAttributesValuesFromDB($this->question->qid);

        // Fill attributes with values
        $languages = is_null($language) ? $survey->allLanguages : [$language];
        $attributesWithValues = $questionAttributeHelper->fillAttributesWithValues($attributeDefinitions, $attributeValues, $languages);

        return $attributesWithValues;
    }

    /**
     * Sets the question to use when fetching the attributes
     *
     * @param \Question $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * Clears the filters
     */
    public function resetFilters()
    {
        $this->filters = [];
    }

    /**
     * Adds a new filter or overrides an existing one
     *
     * @param string $key   the name of the filter
     * @param mixed $value
     */
    public function setFilter($key, $value)
    {
        $this->filters[$key] = $value;
    }

    /**
     * Convenience method to add a question theme filter
     *
     * @param string $questionTheme the name of the question theme
     */
    public function setTheme($questionTheme)
    {
        $this->setFilter('questionTheme', $questionTheme);
    }

    /**
     * Convenience method to add the 'advancedOnly' filter
     *
     * @param boolean $advancedOnly
     */
    public function setAdvancedOnly($advancedOnly)
    {
        $this->setFilter('advancedOnly', $advancedOnly);
    }
}
