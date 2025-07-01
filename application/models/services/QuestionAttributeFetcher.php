<?php

namespace LimeSurvey\Models\Services;

/**
 * Fetches question attribute definitions from the available providers
 */
class QuestionAttributeFetcher
{
    /** @var \Question the question where the attributes should apply */
    private $question;

    /** @var array<string,mixed> array of options to pass to the providers */
    private $options = [];

    /** @var array<QuestionAttributeProvider> array of question attribute providers */
    private $providers = [];

    public function __construct($providers = null)
    {
        if (is_null($providers)) {
            $providers = [
                new CoreQuestionAttributeProvider(),
                new ThemeQuestionAttributeProvider(),
                new PluginQuestionAttributeProvider(),
                new HiddenQuestionAttributeProvider(),
            ];
        }
        $this->providers = $providers;
    }

    /**
     * Returns the question attribute definitions according to the specified filters,
     * from all available sources.
     *
     * @return array<string,array> array of question attribute definitions
     * @throws \InvalidArgumentException if no question is specified
     */
    public function fetch()
    {
        if (empty($this->question)) {
            throw new \InvalidArgumentException(gT("No question specified."));
        }

        $questionAttributeHelper = new QuestionAttributeHelper();

        /** @var array<string,array> retrieved attribute definitions*/
        $allAttributes = [];

        // We retrieve the attributes from each provider, sanitize them, and merge them.
        foreach ($this->providers as $provider) {
            $options = array_merge($this->options, ['question' => $this->question]);
            $attributes = $provider->getDefinitions($options);
            $sanitizedAttributes = $questionAttributeHelper->sanitizeQuestionAttributes($attributes);
            $allAttributes = $questionAttributeHelper->mergeQuestionAttributes($allAttributes, $sanitizedAttributes);
        }

        // Sort by category
        $sortedAttributes = $questionAttributeHelper->sortAttributesByCategory($allAttributes);

        return $sortedAttributes;
    }

    /**
     * Populates the $attributeDefinitions with their corresponding values.
     * If no $language is specified, the values for all survey languages are retrieved.
     * A question must be set with QuestionAttributeFetcher::setQuestion() before calling this method.
     *
     * @param array<string,array> $attributeDefinitions the array of attribute definitions that will be filled with values
     * @param string|null $language the language to use for i18n enabled attributes. If null, all survey languages are considered.
     *
     * @return array<string,array>  the attributes from $attributeDefinitions, with their values.
     * @throws \Exception if the question ()
     *
     * TODO: Move to QuestionAttributeHelper? Not sure if it belongs here.
     */
    public function populateValues($attributeDefinitions, $language = null)
    {
        if (empty($attributeDefinitions)) {
            return [];
        }

        if (empty($this->question)) {
            return $attributeDefinitions;
        }

        static $survey = null;
        if ($survey === null) {
            $survey = $this->question->survey;
        }
        if (isset($survey->sid) && $survey->sid !== $this->question->sid) {
            $survey = $this->question->survey;
        }
        if ($survey === null) {
            throw new \Exception(sprintf('This question has no survey - qid = %s', json_encode($this->question->qid)));
        }

        $questionAttributeHelper = new QuestionAttributeHelper();

        // Get attribute values
        if (!empty($this->question->qid)) {
            $attributeValues = \QuestionAttribute::model()->getAttributesAsArrayFromDB($this->question->qid);
        } else {
            $attributeValues = $questionAttributeHelper->getUserDefaultsForQuestionType($this->question->type);
        }

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
    public function resetOptions()
    {
        $this->options = [];
    }

    /**
     * Adds a new filter or overrides an existing one
     *
     * @param string $key   the name of the filter
     * @param mixed $value
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Convenience method to add a question type filter
     *
     * @param string $questionType the name of the question theme
     */
    public function setQuestionType($questionType)
    {
        $this->setOption('questionType', $questionType);
    }

    /**
     * Convenience method to add a question theme filter
     *
     * @param string $questionTheme the name of the question theme
     */
    public function setTheme($questionTheme)
    {
        $this->setOption('questionTheme', $questionTheme);
    }

    /**
     * Convenience method to add the 'advancedOnly' filter
     *
     * @param boolean $advancedOnly
     */
    public function setAdvancedOnly($advancedOnly)
    {
        $this->setOption('advancedOnly', $advancedOnly);
    }
}
