<?php

namespace ls\components;

use CTypedList;
use ls\components\QuestionValidationResult;

class QuestionValidationResultCollection extends CTypedList
{
    /**
     * Constructor.
     * @param string $type class type
     */
    public function __construct()
    {
        parent::__construct(QuestionValidationResult::class);
    }

    /**
     * Returns true if all validation results in this collection pass mandatory validation.
     */
    public function getPassedMandatory()
    {
        /** @var QuestionValidationResult $validationResult */
        foreach ($this->iterator as $validationResult) {
            if (!$validationResult->getPassedMandatory()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if all validation results in this collection have a success status.
     */
    public function getSuccess()
    {
        /** @var QuestionValidationResult $validationResult */
        foreach ($this->iterator as $validationResult) {
            if (!$validationResult->getSuccess()) {
                return false;
            }
        }

        return true;
    }


    public function getMessagesAsString($separator = "\n")
    {
        $messages = [];
        /** @var QuestionValidationResult $validationResult */
        foreach ($this->iterator as $validationResult) {
            $messages[] = $validationResult->getMessagesAsString($separator);
        }

        return implode($separator, \Cake\Utility\Hash::flatten($messages));
    }

}