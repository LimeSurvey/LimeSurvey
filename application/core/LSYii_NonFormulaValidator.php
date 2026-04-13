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

/**
 * Validator class for potential CSV injection attacks.
 * Checks the value doesn't start with =, +, -, @, TAB, or Carriage Return.
 */
class LSYii_NonFormulaValidator extends CValidator
{

    protected const NON_FORMULA_REGEX = '/^[=\+\-\@\t\r]/';

    public function validateAttribute($object, $attribute)
    {
        if (empty($object->$attribute)) {
            return;
        }
        if (preg_match(self::NON_FORMULA_REGEX, $object->$attribute)) {
            $this->addError($object, $attribute, gT('The value cannot start with =, +, -, @, TAB, or Carriage Return.'));
        }
    }
}
