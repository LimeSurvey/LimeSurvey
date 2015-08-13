<?php

/**
 * This class contains a fields' details for a field that is part of a response to a question.
 * It replaces some of the arrays with informal structure.
 * @property-read string $name
 * @property-read Question $question
 */
class QuestionResponseField extends ResponseField
{
    /**
     * @var Question
     */
    private $_question;

    public function __construct($name, Question $question) {
        parent::__construct($name);
        $this->_question = $question;
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

    /**
     * Check if this field only takes numerical values.
     */
    public function isNumerical() {
        return false;
    }
}