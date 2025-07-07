<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

class LSYii_OtherSettingsValidator extends CValidator
{
    /**
     * Validates all other settings
     * @return boolean Whether all settings are valid
     */
    public function validateAttribute($object, $attribute)
    {
        $otherSettings = json_decode($object->$attribute, true) ?: [];
        $isValid = true;
        foreach ($otherSettings as $otherSetting => $value) {
            if (!$this->checkOtherSetting($object, $otherSetting, $value)) {
                $isValid = false;
            }
        }
        return $isValid;
    }

    /**
     * Validates a single other setting
     * @param string $attribute The setting name
     * @param mixed $value The setting value
     * @return boolean Whether the setting is valid
     */
    public function checkOtherSetting($object, $attribute, $value)
    {
        $validationRules = [
            'question_code_prefix' => [
                'pattern' => '/^[A-Za-z][A-Za-z0-9]{0,14}$/',
                'message' => gT("Question code prefix must start with a letter and can only contain alphanumeric characters. Maximum length is 15 characters.")
            ],
            'subquestion_code_prefix' => [
                'pattern' => '/^$|^[A-Za-z0-9]{0,5}$/',
                'message' => gT("Subquestion code prefix must start with a letter and can only contain alphanumeric characters. Maximum length is 5 characters.")
            ],
            'answer_code_prefix' => [
                'pattern' => '/^$|^[A-Za-z0-9]{0,2}$/',
                'message' => gT("Answer code prefix must start with a letter and can only contain alphanumeric characters. Maximum length is 2 characters.")
            ]
        ];
        // If this is not a setting we validate, return true
        if (!isset($validationRules[$attribute])) {
            return true;
        }
        $rule = $validationRules[$attribute];
        $isValid = preg_match($rule['pattern'], $value);
        if (!$isValid) {
            $this->addError($object, $attribute, $rule['message']);
        }
        return (bool)$isValid;
    }
}
