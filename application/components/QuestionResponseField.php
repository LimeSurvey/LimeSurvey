<?php

/**
 * This class contains a fields' details for a field that is part of a response to a question.
 * It replaces some of the arrays with informal structure.
 * @property-read string $name
 * @property-read Question $question
 */
class QuestionResponseField extends ResponseField implements JsonSerializable
{
    /**
     * @var Question
     */
    private $_question;

    private $_value;

    private $_labels = [];

    private $_relevanceEquation = true;

    private $_relevanceScript;

    public function __construct($name, $code, Question $question) {
        parent::__construct($name, $code);
        $this->_question = $question;
    }

    public function getValue() {
        if (!isset($this->_value)) {
            throw new \Exception("Value not set.");
        }
    }

    public function setValue($value) {
        $this->_value = $value;
    }
    public function getQuestion() {
        return $this->_question;
    }

    public function getRelevanceScript() {
        if (!isset($this->_relevanceScript)) {
            throw new \Exception("Relevance must be set for every field.");
        }
        return $this->_relevanceScript;
    }

    public function setRelevanceScript($value) {
        $this->_relevanceScript = $value;
    }

    /**
     * Check if this field only takes numerical values.
     */
    public function getIsNumerical() {
        return $this->_numerical;
    }

    public function setIsNumerical($value) {
        return $this->_numerical = $value;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
            return [
                'code' => $this->_code,
                'numerical' => $this->isNumerical,
                'value' => $this->_value,
                'name' => $this->_name,
                'relevance' => $this->getRelevanceScript(),
                'labels' => $this->getLabels()
            ];
    }

    public function getLabels() {
        return $this->_labels;
    }

    public function setLabels(array $labels) {
        $this->_labels = $labels;
    }

    public function getLabel($answer) {
        return TbArray::getValue($answer, $this->getLabels(), null);
    }

}