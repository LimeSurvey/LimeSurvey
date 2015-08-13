<?php

/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/13/15
 * Time: 10:24 AM
 */
class QuestionValidationResult {

    /** @var Question  */
    protected $_question;

    /**
     * @var Array of validation messages per field.
     */
    protected $_messages = [];

    /**
     * @var bool
     */
    protected $_mandatoryPassed = false;

    public function __construct($question)
    {
        $this->_question = $question;
    }

    public function getSuccess() {
        return empty($this->_messages);
    }

    public function addMessage($field, $message) {
        $this->_messages[$field][] = $message;
    }

    public function getQuestion() {
        return $this->_question;
    }

    /**
     * Temporary function, since LS for some reason handles mandatory validation different from other validation.
     * @deprecated
     */
    public function getPassedMandatory() {
        return $this->_mandatoryPassed;
    }

    /**
     * Temporary function, since LS for some reason handles mandatory validation different from other validation.
     * @deprecated
     */
    public function setPassedMandatory($value) {
        $this->_mandatoryPassed = $value;
    }

    public function getMessages() {
        return $this->_messages;
    }


    public function getMessagesAsString($separator = "\n") {
        return implode($separator, \Cake\Utility\Hash::flatten($this->_messages));
    }

}