<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

class LSYii_EmailIDNAValidator extends CValidator
{

    public $allowEmpty = false;
    public $allowMultiple = false;
    public $allowInherit = false;

    public function validateAttribute($object, $attribute)
    {
        // If the attribute is empty and empty values are allowed, it's valid.
        if ($object->$attribute == '' && $this->allowEmpty) {
            return;
        }

        // If the attribute is 'inherit' and inherited values are allowed (like in survey settings), it's valid.
        if ($object->$attribute == 'inherit' && $this->allowInherit) {
            return;
        }

        // If the attribute accepts multiple emails, split them into an array.
        // Otherwise, create an array with the single email.
        if ($this->allowMultiple) {
            $aEmailAdresses = preg_split("/(,|;)/", (string) $object->$attribute);
        } else {
            $aEmailAdresses = array($object->$attribute);
        }

        foreach ($aEmailAdresses as $sEmailAddress) {
            if (!LimeMailer::validateAddress($sEmailAddress)) {
                $this->addError($object, $attribute, gT('Invalid email address.'));
                return;
            }
        }
        return;
    }
}
