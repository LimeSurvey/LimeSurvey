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

    private $_relevanceEquation = true;

    public function __construct($name, Question $question) {
        parent::__construct($name);
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
    /**
     * @return string The name of this field in sgqa format.
     * Example: 13455X123X12other
     */
    public function getName() {
        return $this->_name;
    }

    public function getQuestion() {
        return $this->_question;
    }
    /**
     * @return string The EM code for this field.
     * Example: q1_other
     */
    public function getCode() {
        $code = $this->question->title;
        if (substr_compare($this->name, 'other', -5, 5) === 0) {
            $code .= '_other';
        } elseif (substr_compare($this->name, 'comment', -7, 7) === 0) {
            $code .= '_comment';
        }
        return $code;
    }

    public function getRelevanceScript() {
        return $this->question->getRelevanceScript();
    }

    /**
     * Check if this field only takes numerical values.
     */
    public function isNumerical() {
        return false;
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
            'code' => $this->code,
            'numerical' => $this->isNumerical(),
            'value' => $this->_value,
            'name' => $this->_name,
            'relevance' => $this->getRelevanceScript(),
            'labels' => $this->getLabels()
        ];
    }

    public function getLabels() {
        $result = [];
        /** @var Answer $answer */
        foreach($this->question->answers as $answer) {
            $result[$answer->code] = $answer->answer;
        }
        return $result;
    }

    public function getLabel($answer) {
        return TbArray::getValue($answer, $this->getLabels(), null);
    }

}